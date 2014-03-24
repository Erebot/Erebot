<?php
/*
    This file is part of Erebot, a modular IRC bot written in PHP.

    Copyright © 2010 François Poirotte

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

namespace Erebot;

/**
 * \brief
 *      A simple prompt which can be used to send commands remotely.
 *
 * This class can be used by external processes to send commands through
 * the bot. It creates a UNIX socket other programs can send commands to
 * whenever they want the bot to send certain commands to an IRC server.
 *
 * Such commands must be prefixed by a pattern (which accepts '*' and '?'
 * as wildcards, but not the full grammar of regular expressions) to
 * indicate which server(s) the command must be sent to (hint: '*' can
 * be used to refer to all servers the bot is connected to).
 *
 * This makes in possible to display the output of some shell script
 * on IRC. For example, the following command would display the output
 * of \a /some/command.sh to #Erebot on all servers the bot is currently
 * connected to:
 * \code
 *      /some/command.sh | sed 's/^/* PRIVMSG #Erebot :/' | \
 *          socat - UNIX-SENDTO:/path/to/the/prompt.sock
 * \endcode
 */
class Prompt implements \Erebot\Interfaces\ReceivingConnection
{
    /// A bot object implementing the Erebot::Interfaces::Core interface.
    protected $_bot;

    /// The underlying socket, represented as a stream.
    protected $_socket;

    /// I/O manager for the socket.
    protected $_io;

    /**
     * Constructs the UNIX socket that represents the prompt.
     *
     * \param Erebot::Interfaces::Core $bot
     *      Instance of the bot to operate on.
     *
     * \param string $connector
     *      (optional) Path where the newly-created UNIX socket
     *      will be made accessible. The default is to create
     *      a UNIX socket named "Erebot.sock" in the system's
     *      temporary directory (usually "/tmp/").
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
        \Erebot\Interfaces\Core $bot,
        $connector = NULL,
        $group = NULL,
        $perms = 0660
    )
    {
        $this->_bot         = $bot;

        if ($connector === NULL)
            $connector = sys_get_temp_dir() .
                        DIRECTORY_SEPARATOR .
                        'Erebot.sock';
        $this->_socket = stream_socket_server(
            "udg://".$connector,
            $errno, $errstr,
            STREAM_SERVER_BIND
        );
        if (!$this->_socket)
            throw new Exception("Could not create prompt (".$errstr.")");

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

        $this->_io  = new \Erebot\LineIO(\Erebot\LineIO::EOL_ANY, $this->_socket);
        $logger = \Plop::getInstance();
        $logger->info(
            $bot->gettext('Prompt started in "%(path)s"'),
            array('path' => $connector)
        );
    }

    /// Destructor.
    public function __destruct()
    {
        $this->disconnect();
    }

    public function connect()
    {
        $this->_bot->addConnection($this);
    }

    public function disconnect($quitMessage = NULL)
    {
        $this->_bot->removeConnection($this);
        if ($this->_socket !== NULL)
            stream_socket_shutdown($this->_socket, STREAM_SHUT_RDWR);
        $this->_socket = NULL;
    }

    public function isConnected()
    {
        return TRUE;
    }

    public function getSocket()
    {
        return $this->_socket;
    }

    public function getIO()
    {
        return $this->_io;
    }

    public function read()
    {
        $res = $this->_io->read();
        if ($res === FALSE)
            throw new \Erebot\ConnectionFailureException('Disconnected');
        return $res;
    }

    /// Processes commands queued in the input buffer.
    public function process()
    {
        for ($i = $this->_io->inReadQueue(); $i > 0; $i--)
            $this->_handleMessage($this->_io->pop());
    }

    /**
     * Handles a line received from the prompt.
     *
     * \param string $line
     *      A single line of text received from the prompt,
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
            if (!($connection instanceof \Erebot\Interfaces\SendingConnection) ||
                $connection == $this)
                continue;

            $config = $connection->getConfig(NULL);
            $netConfig = $config->getNetworkCfg();
            if (preg_match('@^'.$pattern.'$@Di', $netConfig->getName()))
                $connection->getIO()->push($line);
        }
    }

    public function getBot()
    {
        return $this->_bot;
    }

    public function getConfig($chan)
    {
        return NULL;
    }

    /**
     * Destroys the socket used by the prompt
     * whenever Erebot exits.
     *
     * \param string $socket
     *      Path to the UNIX socket used to control Erebot.
     *
     * \return
     *      This method does not have a return value.
     */
    static public function _cleanup_socket($socket)
    {
        @unlink($socket);
    }
}

