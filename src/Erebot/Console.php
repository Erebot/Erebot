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

    /// \copydoc Erebot_Interface_Connection::__construct()
    public function __construct(
        Erebot_Interface_Core   $bot,
                                $connector  = '/tmp/Erebot.sock',
                                $group      = NULL,
                                $perms      = 0777
    )
    {
        $this->_bot         = $bot;
        $this->_socket      = stream_socket_server("udg://".$connector, $errno, $errstr, STREAM_SERVER_BIND);
        if (!$this->_socket)
            throw new Exception("Could not create console (".$errstr.")");
        $this->_rcvQueue        = array();
        $this->_incomingData    = '';

        $logging    = Plop::getInstance();
        $logger     = $logging->getLogger(__FILE__);
        $logger->info($bot->gettext('Console started in "%s"'), $connector);

        register_shutdown_function(
            array(__CLASS__, '_cleanup_socket'),
            $connector
        );
    }

    /**
     * Destructor.
     */
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
    public function isConnected() { return TRUE; }

    /// \copydoc Erebot_Interface_Connection::getSocket()
    public function getSocket() { return $this->_socket; }

    /// \copydoc Erebot_Interface_ReceivingConnection::emptyReadQueue()
    public function emptyReadQueue() {
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

    protected function _handleMessage($line)
    {
        foreach ($this->_bot->getConnections() as $connection)
            if ($connection instanceof Erebot_Interface_SendingConnection &&
                $connection != $this)
                $connection->pushLine($line);
    }

    /// \copydoc Erebot_Interface_Connection::getBot()
    public function getBot() { return $this->_bot; }

    /// \copydoc Erebot_Interface_Connection::getConfig()
    public function getConfig($chan) { return NULL; }

    static public function _cleanup_socket($socket)
    {
        @unlink($socket);
    }
}

