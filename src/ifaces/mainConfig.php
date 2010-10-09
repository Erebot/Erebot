<?php

ErebotUtils::incl('configProxy.php');

interface   iErebotMainConfig
extends     iErebotConfigProxy
{
    public function __construct($configData, $source);
    public function __clone();
    public function load($configData, $source);
    public function & getNetworkCfg($network);
    public function getNetworks();
    public function getVersion();
    public function getTimezone();
}

?>
