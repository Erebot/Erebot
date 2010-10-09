<?php

ErebotUtils::incl('configProxy.php');

interface   iErebotChannelConfig
extends     iErebotConfigProxy
{
    public function __construct(iErebotNetworkConfig &$netCfg, SimpleXMLElement &$xml);
    public function getName();
    public function & getNetworkCfg();
}

?>
