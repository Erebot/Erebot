<?php

ErebotUtils::incl('configProxy.php');

interface   iErebotServerConfig
extends     iErebotConfigProxy
{
    public function __construct(iErebotNetworkConfig &$netCfg, SimpleXMLElement &$xml);
    public function getConnectionURL();
    public function & getNetworkCfg();
}

?>
