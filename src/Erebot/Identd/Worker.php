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
 *      A worker process for the Identification Protocol (RFC 1413).
 *
 * \see
 *      http://www.ietf.org/rfc/rfc1413.txt
 */
class       Erebot_Identd_Worker
implements  Erebot_Interface_ReceivingConnection,
            Erebot_Interface_SendingConnection
{
    /// A bot object implementing the Erebot_Interface_Core interface.
    protected $_bot;
    /// The underlying socket, represented as a stream.
    protected $_socket;
    /// A raw buffer for incoming data.
    protected $_incomingData;
    /// A FIFO queue for incoming messages.
    protected $_rcvQueue;

    /// \copydoc Erebot_Interface_Connection::__construct()
    public function __construct(Erebot_Interface_Core $bot, $socket = NULL)
    {
        if (!is_resource($socket))
            throw new Erebot_InvalidValueException('Not a valid socket');
        $this->_bot             = $bot;
        $this->_socket          = $socket;
        $this->_incomingData    = '';
        $this->_rcvQueue        = array();
    }

    /// Destructor.
    public function __destruct()
    {
    }

    /// \copydoc Erebot_Interface_Connection::connect()
    public function connect()
    {
    }

    /// \copydoc Erebot_Interface_Connection::disconnect()
    public function disconnect($quitMessage = NULL)
    {
        $this->_bot->removeConnection($this);
        if ($this->_socket !== NULL)
            stream_socket_shutdown($this->_socket, STREAM_SHUT_RDWR);
        $this->_socket = NULL;
    }

    /**
     * Reads a single line to data from the worker's socket.
     *
     * \retval bool
     *      TRUE if a line could be read, FALSE otherwise.
     */
    protected function _getSingleLine()
    {
        $pos = strpos($this->_incomingData, "\r\n");
        if ($pos === FALSE)
            return FALSE;

        $line = substr($this->_incomingData, 0, $pos);
        $this->_incomingData    = substr($this->_incomingData, $pos + 2);
        $this->_rcvQueue[]      = $line;
        return TRUE;
    }

    /// \copydoc Erebot_Interface_ReceivingConnection::processIncomingData()
    public function processIncomingData()
    {
        $received   = fread($this->_socket, 4096);
        if ($received === FALSE || feof($this->_socket))
            return;

        $this->_incomingData .= $received;
        while ($this->_getSingleLine())
            ;   // Read messages.
    }

    /// \copydoc Erebot_Interface_ReceivingConnection::processQueuedData()
    public function processQueuedData()
    {
        if (!count($this->_rcvQueue))
            return;

        $line = array_shift($this->_rcvQueue);
        $line = $this->_handleMessage($line);
        if ($line) {
            // Make sure we send the whole line,
            // with a trailing CR LF sequence.
            $line .= "\r\n";
            for (
                $written = 0, $len = strlen($line);
                $written < $len;
                $written += $fwrite
            ) {
                $fwrite = fwrite($this->_socket, substr($line, $written));
                if ($fwrite === FALSE)
                    break;
            }
        }

        $this->_bot->removeConnection($this);
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
     * \retval FALSE
     *      The request was malformed.
     */
    protected function _handleMessage($line)
    {
        if (!is_string($line))
            return FALSE;

        $parts = array_map('trim', explode(',', $line));
        if (count($parts) != 2)
            return FALSE;

        $line = implode(" , ", $parts);
        if (!ctype_digit($parts[0]) || !ctype_digit($parts[1]))
            return $line . " : ERROR : INVALID-PORT";

        $cport = (int) $parts[0];
        $sport = (int) $parts[1];

        if ($sport <= 0 || $sport > 65535 || $cport <= 0 || $cport > 65535)
            return $line . " : ERROR : INVALID-PORT";

        foreach ($this->_bot->getConnections() as $connection) {
            if ($connection == $this)
                continue;

            $socket = $connection->getSocket();

            $rport = (int) substr(
                strrchr(stream_socket_get_name($socket, TRUE), ':'), 1
            );
            if ($rport != $sport)
                continue;

            $lport = (int) substr(
                strrchr(stream_socket_get_name($socket, FALSE), ':'), 1
            );
            if ($lport != $cport)
                continue;

            try {
                $config     = $connection->getConfig(NULL);
                $identity   = $config->parseString(
                    'Erebot_Module_IrcConnector',
                    'identity',
                    ''
                );
                return $line . " : USERID : UNIX : " . $identity;
            }
            catch (Erebot_Exception $e) {
                return $line . " : ERROR : HIDDEN-USER ";
            }
        }

        return $line . " : ERROR : NO-USER";
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
        return TRUE;
    }

    /// \copydoc Erebot_Interface_SendingConnection::emptySendQueue()
    public function emptySendQueue()
    {
        return TRUE;
    }

    /// \copydoc Erebot_Interface_SendingConnection::pushLine()
    public function pushLine($line)
    {
    }

    /// \copydoc Erebot_Interface_SendingConnection::processOutgoingData()
    public function processOutgoingData()
    {
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
}

