<?php

include_once(dirname(dirname(__FILE__)).'/src/utils.php');
ErebotUtils::incl('../src/config/serverConfig.php');
ErebotUtils::incl('../src/config/networkConfig.php');
ErebotUtils::incl('../src/config/mainConfig.php');
ErebotUtils::incl('../src/i18n.php');

class       ErebotStubbedI18n
implements  iErebotI18n
{
    public function __construct($locale = NULL, $component = NULL)
    {

    }

    public function getLocale()
    {
        return 'en_US';
    }

    public function gettext($message)
    {
        return $message;
    }

    public function formatDuration($duration)
    {
        return NULL;
    }
}

class   ErebotStubbedMainConfig
extends ErebotMainConfig
{
    public function __construct($configData, $source)
    {
        $this->proxified        =&  $this;
        $this->commands_prefix  =   '!';
        $this->modules          =   array();
    }

    public function getTranslator($component)
    {
        return new ErebotStubbedI18n();
    }
}

class   ErebotStubbedNetworkConfig
extends ErebotNetworkConfig
{
    public function __construct(iErebotMainConfig &$mainCfg, SimpleXMLElement &$xml)
    {
        $this->proxified    =&  $mainCfg;
        $this->translator   =   NULL;
        $this->modules      =   array();
    }
}

class   ErebotStubbedServerConfig
extends ErebotServerConfig
{
    public function __construct(iErebotNetworkConfig &$netCfg, SimpleXMLElement &$xml)
    {
        $this->proxified    =&  $netCfg;
        $this->translator   =   NULL;
        $this->modules      =   array();
    }

    public function getConnectionURL() { return NULL; }
    public function & getNetworkCfg() { return $this; }

    static public function create(array $modules)
    {
        $xml = new SimpleXMLElement('<stub/>');
        $mainCfg = new ErebotStubbedMainConfig(NULL, NULL);
        $netCfg = new ErebotStubbedNetworkConfig($mainCfg, $xml);
        $c = __CLASS__;
        $that = new $c($netCfg, $xml);
        foreach ($modules as $modName => $xmlConfig) {
            if (!$xmlConfig)
                $xmlConfig = '<module name="'.htmlspecialchars($modName).'" '.
                    'xmlns="http://www.erebot.net/xmlns/erebot"/>';
            $dom = simplexml_load_string($xmlConfig);
            $that->modules[$modName] = new ErebotModuleConfig($dom);
        }
        return $that;
    }
}

?>
