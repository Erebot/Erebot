<?php

interface iErebotConnection
{
    public function __construct(iErebot &$bot, iErebotServerConfig &$config);
    public function connect();
    public function disconnect($quitMessage = NULL);
    public function pushLine($line);
    public function & getConfig($chan);
    public function & getSocket();
    public function emptyReadQueue();
    public function emptySendQueue();
    public function processIncomingData();
    public function processOutgoingData();
    public function processQueuedData();
    public function & getBot();
    public function & loadModule($module, $chan = NULL);
    public function getModules($chan = NULL);
    public function & getModule($name, $type, $chan = NULL);
    public function addRawHandler(iErebotRawHandler &$handler);
    public function removeRawHandler(iErebotRawHandler &$handler);
    public function addEventHandler(iErebotEventHandler &$handler);
    public function removeEventHandler(iErebotEventHandler &$handler);
    public function dispatchEvent(iErebotEvent &$event);
    public function dispatchRaw(iErebotRaw &$raw);
}

?>
