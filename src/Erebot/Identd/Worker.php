<?php

class       Erebot_Identd_Worker
implements  Erebot_Interface_ReceivingConnection,
            Erebot_Interface_SendingConnection
{
    protected $_bot;
    protected $_socket;
    protected $_incomingData;
    protected $_rcvQueue;

    public function __construct(Erebot_Interface_Core $bot, $socket = NULL)
    {
        if (!is_resource($socket))
            throw new Erebot_InvalidValueException('Not a valid socket');
        $this->_bot             = $bot;
        $this->_socket          = $socket;
        $this->_incomingData    = '';
        $this->_rcvQueue        = array();
    }

    public function __destruct()
    {
        $this->_disconnect();
    }

    public function connect() {}
    public function disconnect($quitMessage = NULL)
    {
        $this->_bot->removeConnection($this);
        if ($this->_socket !== NULL)
            stream_socket_shutdown($this->_socket, STREAM_SHUT_RDWR);
        $this->_socket = NULL;
    }

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

    public function processIncomingData() {
        $received   = fread($this->_socket, 4096);
        if ($received === FALSE || feof($this->_socket))
            return;

        $this->_incomingData .= $received;
        while ($this->_getSingleLine())
            ;   // Read messages.
    }

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

    protected function _handleMessage($line)
    {
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

            $rport = (int) substr(strrchr(
                stream_socket_get_name($socket, TRUE), ':'), 1);
            if ($rport != $sport)
                continue;

            $lport = (int) substr(strrchr(
                stream_socket_get_name($socket, FALSE), ':'), 1);
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

    public function isConnected() { return TRUE; }
    public function getSocket() { return $this->_socket; }
    public function emptyReadQueue() { return TRUE; }
    public function emptySendQueue() { return TRUE; }
    public function pushLine($line) {}
    public function getBot() { return $this->_bot; }
    public function processOutgoingData() {}
    public function getConfig() { return NULL; }
}

