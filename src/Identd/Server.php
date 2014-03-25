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
 *      A server compatible with the Identification Protocol (RFC 1413).
 *
 * \see
 *      http://www.ietf.org/rfc/rfc1413.txt
 */
class Server implements \Erebot\Interfaces\ReceivingConnection
{
    /// A bot object implementing the Erebot::Interfaces::Core interface.
    protected $bot;

    /// The underlying socket, represented as a stream.
    protected $socket;

    /// Class to use to process IdentD requests.
    protected $workerCls;

    /**
     * Create a new instance of the IdentD server.
     *
     * \param Erebot::Interfaces::Core $bot
     *      Instance of the bot to operate on.
     *
     * \param string $connector
     *      (optional) A string of the form "address:port"
     *      describing the IP address and port the server
     *      should listen on. The default is to listen on
     *      port 113 (as per RFC 1413) on all available
     *      interfaces (ie. "0.0.0.0:113").
     *
     * \param string $workerCls
     *      (optional) Instances of this class will be created
     *      to handle identification requests. The default is
     *      "Erebot::Identd::Worker".
     *
     * \see
     *      http://www.ietf.org/rfc/rfc1413.txt for information
     *      on the identification protocol.
     */
    public function __construct(
        \Erebot\Interfaces\Core $bot,
        $connector = '0.0.0.0:113',
        $workerCls = '\\Erebot\\Identd\\Worker'
    ) {
        $this->bot          = $bot;
        $this->workerCls    = $workerCls;
        $this->socket       = stream_socket_server('tcp://' . $connector, $errno, $errstr);
        if (!$this->socket) {
            throw new \Exception(
                "Could not create identd server (".$errstr.")"
            );
        }
    }

    /// Destructor.
    public function __destruct()
    {
        $this->disconnect();
    }

    public function connect()
    {
        $this->bot->addConnection($this);
    }

    public function disconnect($quitMessage = null)
    {
        $this->bot->removeConnection($this);
        if ($this->socket !== null) {
            stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);
        }
        $this->socket = null;
    }

    public function isConnected()
    {
        return true;
    }

    public function getSocket()
    {
        return $this->socket;
    }

    public function emptyReadQueue()
    {
        return true;
    }

    public function read()
    {
        $socket = stream_socket_accept($this->socket);
        if (!$socket) {
            return false;
        }
        $worker = new $this->workerCls($this->bot, $socket);
        return $worker;
    }

    /// Processes commands queued in the input buffer.
    public function process()
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
        return null;
    }
}
