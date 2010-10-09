<?php

ErebotUtils::incl('configProxy.php');

interface   iErebotNetworkConfig
extends     iErebotConfigProxy
{
    public function __construct(iErebotMainConfig &$mainCfg, SimpleXMLElement &$xml);
    public function getName();
    public function & getServerCfg($server);
    public function getServers();
    public function & getChannelCfg($channel);
    public function getChannels();
}

?>
