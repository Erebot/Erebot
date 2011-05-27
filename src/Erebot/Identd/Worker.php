<?php

class       Erebot_Identd_Worker
implements  Erebot_Interface_Connection
{
    protected $_bot;
    protected $_socket;
    protected $_incomingData;
    protected $_sndQueue;
    protected $_rcvQueue;

    public function __construct(
        Erebot_Interface_Core           $bot,
        Erebot_Interface_Config_Server  $config = NULL)
    {
        $this->_bot = $bot;
        $this->_socket = NULL;
        $this->_incomingData = '';
        $this->_sndQueue        = array();
        $this->_rcvQueue        = array();
    }

    public function __destruct()
    {
    }

    public function setSocket($socket)
    {
        if ($this->_socket !== NULL)
            $this->_bot->removeConnection($this);
        $this->_socket = $socket;
        $this->_bot->addConnection($this);
    }

    public function connect() {}
    public function disconnect($quitMessage = NULL)
    {
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

    public function pushLine($line) {}

    public function processIncomingData() {
        $received   = fread($this->_socket, 4096);
        if ($received === FALSE || feof($this->_socket))
            return;

        $this->_incomingData .= $received;
        while ($this->_getSingleLine())
            ;   // Read messages.
    }

    public function processOutgoingData() {}

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
    public function getConfig($chan) { return NULL; }
    public function getSocket() { return $this->_socket; }
    public function emptyReadQueue() { return TRUE; }
    public function emptySendQueue() { return TRUE; }
    public function getBot() { return $this->_bot; }
    public function loadModule($module, $chan = NULL) {}
    public function getModule($name, $chan = NULL, $autoload = TRUE) {}
    public function getModules($chan = NULL) { return array(); }
    public function addRawHandler(Erebot_Interface_RawHandler $handler) {}
    public function removeRawHandler(Erebot_Interface_RawHandler $handler) {}
    public function addEventHandler(Erebot_Interface_EventHandler $handler) {}
    public function removeEventHandler(Erebot_Interface_EventHandler $handler) {}
    public function dispatch(Erebot_Interface_Event_Base_Generic $event = NULL) {}
    public function irccmp($a, $b) {}
    public function ircncmp($a, $b, $len) {}
    public function irccasecmp($a, $b, $mappingName = NULL) {}
    public function ircncasecmp($a, $b, $len, $mappingName = NULL) {}
    public function isChannel($chan) {}
    public function normalizeNick($nick, $mappingName = NULL) {}
    public function makeEvent($iface) { return NULL; }
}

