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
    protected $_bot;

    /// The underlying socket, represented as a stream.
    protected $_socket;

    /// Class to use to process IdentD requests.
    protected $_workerCls;

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
    )
    {

        $this->_bot         = $bot;
        $this->_workerCls   = $workerCls;
        $this->_socket      = stream_socket_server(
            "tcp://".$connector,
            $errno, $errstr
        );
        if (!$this->_socket)
            throw new Exception(
                "Could not create identd server (".$errstr.")"
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

    public function emptyReadQueue()
    {
        return TRUE;
    }

    public function read()
    {
        $socket = stream_socket_accept($this->_socket);
        if (!$socket)
            return FALSE;
        $worker = new $this->_workerCls($this->_bot, $socket);
        return $worker;
    }

    /// Processes commands queued in the input buffer.
    public function process()
    {
    }

    public function getBot()
    {
        return $this->_bot;
    }

    public function getConfig($chan)
    {
        return NULL;
    }

    public function getIO()
    {
        return NULL;
    }
}

