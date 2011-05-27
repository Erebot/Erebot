<?php

class       Erebot_Identd_Server
implements  Erebot_Interface_Connection
{
    protected $_bot;
    protected $_socket;

    public function __construct(
        Erebot_Interface_Core           $bot,
        Erebot_Interface_Config_Server  $config = NULL)
    {
        $this->_bot = $bot;
        $this->_socket = NULL;
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

    public function isConnected() { return TRUE; }
    public function pushLine($line) {}
    public function getConfig($chan) { return NULL; }
    public function getSocket() { return $this->_socket; }
    public function emptyReadQueue() { return TRUE; }
    public function emptySendQueue() { return TRUE; }
    public function processIncomingData()
    {
        $socket = stream_socket_accept($this->_socket);
        if (!$socket)
            return;
        $worker = new Erebot_Identd_Worker($this->_bot);
        $worker->setSocket($socket);
    }
    public function processOutgoingData() {}
    public function processQueuedData() {}
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

