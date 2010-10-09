<?php

interface iErebot
{
    public function __construct(iErebotMainConfig $config = NULL);
    public function getConnections();
    public function start($connectionCls = NULL);
    public function stop();
    public function quitGracefully($signum);
    public function getTimers();
    public function addTimer(iErebotTimer &$timer);
    public function removeTimer(iErebotTimer &$timer);
    public static function getVersion();
    public function loadModule($module);
    public function moduleNameToClass($modName);
    public function moduleClassToName($className);
    public function addConnection(iErebotConnection &$connection);
    public function removeConnection(iErebotConnection &$connection);
    public function gettext($message);
}

?>
