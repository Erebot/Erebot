<?php
/*
    This file is part of Erebot.

    Erebot is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Erebot is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Erebot.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * \brief
 *      A simple console which can be used to send commands to the bot.
 */
class       Erebot_Console
implements  Erebot_Interface_ReceivingConnection
{
    /// A bot object implementing the Erebot_Interface_Core interface.
    protected $_bot;

    /// The underlying socket, represented as a stream.
    protected $_socket;

    /// A FIFO queue for incoming messages.
    protected $_rcvQueue;

    /// A raw buffer for incoming data.
    protected $_incomingData;

    /**
     * Constructs the UNIX socket that represents the console.
     *
     * \param Erebot_Interface_Core $bot
     *      Instance of the bot to operate on.
     *
     * \param string $connector
     *      (optional) Path where the newly-created UNIX socket
     *      will be made accessible. The default is to create
     *      a UNIX socket named "Erebot.sock" in the system's
     *      temporary directory.
     *
     * \param mixed $group
     *      (optional) Either the name or the identifier
     *      of the UNIX group the socket will belong to.
     *      The default is to not change the group of the
     *      socket (i.e. to keep whatever is the main group
     *      for the user running Erebot).
     *
     * \param int $perms
     *      (optional) UNIX permissions the newly-created
     *      socket will receive. The default is to give
     *      read/write access to the user running Erebot and
     *      to the group the socket belongs to (see $group).
     *
     * \note
     *      On most systems, only the superuser may change
     *      the group of a file arbitrarily, while other
     *      users may only change it to a group they are
     *      a member of.
     *
     * \warning
     *      Using the wrong combination of $group and $perms
     *      may lead to security issues. Use with caution.
     *      The default values are safe as long as you trust
     *      users belonging to the same group as the main
     *      group of the user running Erebot.
     *
     * \see
     *      http://php.net/chmod provides more information
     *      on the meaning of $perms' value
     *      (see the description for \c mode).
     */
    public function __construct(
        Erebot_Interface_Core   $bot,
                                $connector  = '/tmp/Erebot.sock',
                                $group      = NULL,
                                $perms      = 0660
    )
    {
        $this->_bot         = $bot;
        $this->_socket      = stream_socket_server(
            "udg://".$connector,
            $errno, $errstr,
            STREAM_SERVER_BIND
        );
        if (!$this->_socket)
            throw new Exception("Could not create console (".$errstr.")");

        register_shutdown_function(
            array(__CLASS__, '_cleanup_socket'),
            $connector
        );

        // Change group.
        if ($group !== NULL) {
            if (!@chgrp($connector, $group)) {
                throw new Exception(
                    "Could not change group to '$group' for '$connector'"
                );
            }
        }

        // Change permissions.
        if (!chmod($connector, $perms)) {
            throw new Exception(
                "Could not set permissions to $perms on '$connector'"
            );
        }

        // Flush any received data on the socket, because
        // any data sent before the group and permissions
        // were set may have come from an untrusted source.
        $flush = array($this->_socket);
        $dummy = NULL;
        while (stream_select($flush, $dummy, $dummy, 0) == 1) {
            if (fread($this->_socket, 8192) === FALSE)
                throw new Exception("Error while flushing the socket");
        }

        $this->_rcvQueue        = array();
        $this->_incomingData    = '';

        $logging    = Plop::getInstance();
        $logger     = $logging->getLogger(__FILE__);
        $logger->info($bot->gettext('Console started in "%s"'), $connector);
    }

    /// Destructor.
    public function __destruct()
    {
        $this->disconnect();
    }

    /// \copydoc Erebot_Interface_Connection::connect()
    public function connect()
    {
        $this->_bot->addConnection($this);
    }

    /// \copydoc Erebot_Interface_Connection::disconnect()
    public function disconnect($quitMessage = NULL)
    {
        $this->_bot->removeConnection($this);
        if ($this->_socket !== NULL)
            stream_socket_shutdown($this->_socket, STREAM_SHUT_RDWR);
        $this->_socket = NULL;
    }

    /// \copydoc Erebot_Interface_Connection::isConnected()
    public function isConnected()
    {
        return TRUE;
    }

    /// \copydoc Erebot_Interface_Connection::getSocket()
    public function getSocket()
    {
        return $this->_socket;
    }

    /// \copydoc Erebot_Interface_ReceivingConnection::emptyReadQueue()
    public function emptyReadQueue()
    {
        return (count($this->_rcvQueue) == 0);
    }

    /**
     * Retrieves a single line of text from the incoming buffer
     * and puts it in the incoming FIFO.
     *
     * \retval TRUE
     *      Whether a line could be fetched from the buffer.
     *
     * \retval FALSE
     *      ... or not.
     *
     * \note
     *      Lines fetched by this method are always UTF-8 encoded.
     */
    protected function _getSingleLine()
    {
        $pos = strpos($this->_incomingData, "\r\n");
        if ($pos === FALSE)
            return FALSE;

        $line = Erebot_Utils::toUTF8(substr($this->_incomingData, 0, $pos));
        $this->_incomingData    = substr($this->_incomingData, $pos + 2);
        $this->_rcvQueue[]      = $line;

        $logging    = Plop::getInstance();
        $logger     = $logging->getLogger(
            __FILE__ . DIRECTORY_SEPARATOR . 'input'
        );
        $logger->debug("%s", addcslashes($line, "\000..\037"));
        return TRUE;
    }

    /// \copydoc Erebot_Interface_ReceivingConnection::processIncomingData()
    public function processIncomingData()
    {
        $received   = fread($this->_socket, 4096);
        if ($received === FALSE || feof($this->_socket)) {
            throw new Erebot_ConnectionFailureException('Disconnected');
        }

        $this->_incomingData .= $received;
        while ($this->_getSingleLine())
            ;   // Read messages.
    }

    /// \copydoc Erebot_Interface_ReceivingConnection::processQueuedData()
    public function processQueuedData()
    {
        if (!count($this->_rcvQueue))
            return;

        while (count($this->_rcvQueue))
            $this->_handleMessage(array_shift($this->_rcvQueue));
    }

    /**
     * Handles a line received from the console.
     *
     * \param string $line
     *      A single line of text received from the console,
     *      with the end-of-line sequence stripped.
     */
    protected function _handleMessage($line)
    {
        $pos = strpos($line, ' ');
        if ($pos === FALSE)
            return;

        $pattern    = preg_quote(substr($line, 0, $pos), '@');
        $pattern    = strtr($pattern, array('\\?' => '.?', '\\*' => '.*'));
        $line       = substr($line, $pos + 1);
        if ($line === FALSE)
            return;

        foreach ($this->_bot->getConnections() as $connection) {
            if (!($connection instanceof Erebot_Interface_SendingConnection) ||
                $connection == $this)
                continue;

            $config = $connection->getConfig(NULL);
            $netConfig = $config->getNetworkCfg();
            if (preg_match('@^'.$pattern.'$@Di', $netConfig->getName()))
                $connection->pushLine($line);
        }
    }

    /// \copydoc Erebot_Interface_Connection::getBot()
    public function getBot()
    {
        return $this->_bot;
    }

    /// \copydoc Erebot_Interface_Connection::getConfig()
    public function getConfig($chan)
    {
        return NULL;
    }

    /**
     * Destroys the socket used by the console
     * whenever Erebot exits.
     *
     * \param string $socket
     *      Path to the UNIX socket used to control Erebot.
     */
    static public function _cleanup_socket($socket)
    {
        @unlink($socket);
    }
}

