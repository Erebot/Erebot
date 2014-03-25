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

namespace Erebot\Identd;

/**
 * \brief
 *      A worker process for the Identification Protocol (RFC 1413).
 *
 * \see
 *      http://www.ietf.org/rfc/rfc1413.txt
 */
class Worker implements
    \Erebot\Interfaces\SendingConnection,
    \Erebot\Interfaces\ReceivingConnection
{
    /// A bot object implementing the Erebot::Interfaces::Core interface.
    protected $bot;

    /// The underlying socket, represented as a stream.
    protected $socket;

    /// I/O manager for the socket.
    protected $io;

    /**
     * Creates a worker object capable of handling
     * a single identification request.
     * As soon as the request has been handled and
     * a response has been sent, this worker will
     * destroy itself.
     *
     * \param Erebot::Interfaces::Core $bot
     *      Instance of the bot to operate on.
     *
     * \param resource $socket
     *      A socket connected to a client requesting
     *      an identification from our IdentD server.
     */
    public function __construct(\Erebot\Interfaces\Core $bot, $socket)
    {
        if (!is_resource($socket)) {
            throw new \Erebot\InvalidValueException('Not a valid socket');
        }
        $this->bot      = $bot;
        $this->socket   = $socket;
        $this->io       = new \Erebot\LineIO(
            \Erebot\LineIO::EOL_WIN,
            $this->socket
        );
    }

    /// Destructor.
    public function __destruct()
    {
    }

    public function connect()
    {
    }

    public function disconnect($quitMessage = null)
    {
        $this->bot->removeConnection($this);
        if ($this->socket !== null) {
            stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);
        }
        $this->socket = null;
    }

    public function read()
    {
        return $this->io->read();
    }

    /// Processes commands queued in the input buffer.
    public function process()
    {
        if (!$this->io->inReadQueue()) {
            return;
        }

        $line = $this->handleMessage($this->io->pop());
        if ($line) {
            $this->io->push($line);
            $this->io->write();
        }

        $this->bot->removeConnection($this);
        $this->disconnect();
    }

    /**
     * Handles an IdentD request.
     *
     * \param string $line
     *      IdentD request to handle.
     *
     * \retval string
     *      Message to send as the response
     *      to this request.
     *
     * \retval false
     *      The request was malformed.
     */
    protected function handleMessage($line)
    {
        if (!is_string($line)) {
            return false;
        }

        $parts = array_map('trim', explode(',', $line));
        if (count($parts) != 2) {
            return false;
        }

        $line = implode(" , ", $parts);
        if (!ctype_digit($parts[0]) || !ctype_digit($parts[1])) {
            return $line . " : ERROR : INVALID-PORT";
        }

        $cport = (int) $parts[0];
        $sport = (int) $parts[1];

        if ($sport <= 0 || $sport > 65535 || $cport <= 0 || $cport > 65535) {
            return $line . " : ERROR : INVALID-PORT";
        }

        foreach ($this->bot->getConnections() as $connection) {
            if ($connection == $this) {
                continue;
            }

            $socket = $connection->getSocket();

            $rport = (int) substr(strrchr(stream_socket_get_name($socket, true), ':'), 1);
            if ($rport != $sport) {
                continue;
            }

            $lport = (int) substr(strrchr(stream_socket_get_name($socket, false), ':'), 1);
            if ($lport != $cport) {
                continue;
            }

            try {
                $config     = $connection->getConfig(null);
                $identity   = $config->parseString(
                    '\\Erebot\\Module\\IrcConnector',
                    'identity',
                    ''
                );
                return $line . " : USERID : UNIX : " . $identity;
            } catch (\Erebot\Exception $e) {
                return $line . " : ERROR : HIDDEN-USER ";
            }
        }

        return $line . " : ERROR : NO-USER";
    }

    public function isConnected()
    {
        return true;
    }

    public function getSocket()
    {
        return $this->socket;
    }

    public function write()
    {
    }

    public function getBot()
    {
        return $this->bot;
    }

    public function getConfig($chan)
    {
        return null;
    }

    public function getIO()
    {
        return $this->io;
    }
}
