<?php

class ErebotStubbedConnection
implements iErebotConnection
{
    public function __construct(iErebot &$bot, iErebotServerConfig &$config)
    {
    }

    public function __destruct()
    {
    }

    public function connect()
    {
    }

    public function disconnect($quitMessage = NULL)
    {
    }

    public function pushLine($line)
    {
    }

    public function & getConfig($chan)
    {
        $null = NULL;
        return $null;
    }

    public function & getSocket()
    {
        $null = NULL;
        return $null;
    }

    public function emptyReadQueue()
    {
        return TRUE;
    }

    public function emptySendQueue()
    {
        return TRUE;
    }

    public function processIncomingData()
    {
    }

    public function processOutgoingData()
    {
    }

    public function processQueuedData()
    {
    }

    protected function _loadChannelModules()
    {
        // Do nothing.
    }

    public function & getBot()
    {
        $null = NULL;
        return $null;
    }

    public function & loadModule($module, $chan = NULL)
    {
        $null = NULL;
        return $null;
    }

    public function getModules($chan = NULL)
    {
        return array();
    }

    public function & getModule($name, $type, $chan = NULL)
    {
        $null = NULL;
        return $null;
    }

    public function getSendQueue()
    {
        $res = $this->_sndQueue;
        $this->_sndQueue = array();
        return $res;
    }

    public function addRawHandler(iErebotRawHandler &$handler)
    {
    }

    public function removeRawHandler(iErebotRawHandler &$handler)
    {
    }

    public function addEventHandler(iErebotEventHandler &$handler)
    {
    }

    public function removeEventHandler(iErebotEventHandler &$handler)
    {
    }

    public function dispatchEvent(iErebotEvent &$event)
    {
    }

    public function dispatchRaw(iErebotRaw &$raw)
    {
    }
}

