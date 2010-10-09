<?php

include_once(dirname(dirname(__FILE__)).'/src/utils.php');
ErebotUtils::incl('../src/config/serverConfig.php');
ErebotUtils::incl('../src/config/networkConfig.php');
ErebotUtils::incl('../src/config/mainConfig.php');

class ErebotStubbedI18n
{
    public function gettext($message)
    {
        return $message;
    }
}

class ErebotStubbedMainConfig
extends ErebotMainConfig
{
    public function __construct($configData, $source) {}
}

class ErebotStubbedNetworkConfig
extends ErebotNetworkConfig
{
    public function __construct(iErebotMainConfig &$mainCfg, SimpleXMLElement &$xml) {}
}

class ErebotStubbedServerConfig
extends ErebotServerConfig
{
    public function __construct(iErebotNetworkConfig &$netCfg, SimpleXMLElement &$xml)
    {
        
    }

    public function getConnectionURL() { return NULL; }
    public function & getNetworkCfg() { return $this; }

    static public function create($modules = array())
    {
        $xml = new SimpleXMLElement('<stub/>');
        $mainCfg = new ErebotStubbedMainConfig(NULL, NULL);
        $netCfg = new ErebotStubbedNetworkConfig($mainCfg, $xml);
        $c = __CLASS__;
        $that = new $c($netCfg, $xml);
        $that->modules = $modules;
        return $that;
    }

    public function getModules($recursive)
    {
        return $this->modules;
    }

    public function getTranslator($component)
    {
        return new ErebotStubbedI18n();
    }

    public function & getMainCfg()
    {
        return $this;
    }
}

?>
