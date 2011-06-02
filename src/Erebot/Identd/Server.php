<?php

class       Erebot_Identd_Server
implements  Erebot_Interface_ReceivingConnection
{
    protected $_bot;
    protected $_socket;
    protected $_workerCls;

    public function __construct(
        Erebot_Interface_Core   $bot,
                                $connector  = '0.0.0.0:113',
                                $workerCls  = 'Erebot_Identd_Worker'
    )
    {
        
        $this->_bot         = $bot;
        $this->_workerCls   = $workerCls;
        $this->_socket      = stream_socket_server("tcp://".$connector, $errno, $errstr);
        if (!$this->_socket)
            throw new Exception("Could not create identd server (".$errstr.")");
    }

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

    public function isConnected() { return TRUE; }
    public function getSocket() { return $this->_socket; }
    public function emptyReadQueue() { return TRUE; }
    public function processIncomingData()
    {
        $socket = stream_socket_accept($this->_socket);
        if (!$socket)
            return;
        $worker = new $this->_workerCls($this->_bot, $socket);
    }
    public function processQueuedData() {}
    public function getBot() { return $this->_bot; }
}

