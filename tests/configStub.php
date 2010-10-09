<?php

include_once('src/config/serverConfig.php');
include_once('src/config/networkConfig.php');
include_once('src/config/mainConfig.php');
include_once('src/i18n.php');

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
        $this->_proxified       =&  $this;
        $this->_commandsPrefix  =   '!';
        $this->_modules         =   array();
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
        $this->_proxified   =&  $mainCfg;
        $this->_translator  =   NULL;
        $this->_modules     =   array();
    }
}

class   ErebotStubbedServerConfig
extends ErebotServerConfig
{
    public function __construct(iErebotNetworkConfig &$netCfg, SimpleXMLElement &$xml)
    {
        $this->_proxified   =&  $netCfg;
        $this->_translator  =   NULL;
        $this->_modules     =   array();
    }

    public function getConnectionURL() { return NULL; }
    public function & getNetworkCfg() { return $this; }

    static public function create(array $modules)
    {
        $c          = __CLASS__;
        $xml        = new SimpleXMLElement('<stub/>');
        $mainCfg    = new ErebotStubbedMainConfig(NULL, NULL);
        $netCfg     = new ErebotStubbedNetworkConfig($mainCfg, $xml);
        $that       = new $c($netCfg, $xml);

        foreach ($modules as $modName => $xmlConfig) {
            if (!$xmlConfig)
                $xmlConfig = '<module name="'.htmlspecialchars($modName).'" '.
                    'xmlns="http://www.erebot.net/xmlns/erebot"/>';
            $dom = simplexml_load_string($xmlConfig);
            $that->_modules[$modName] = new ErebotModuleConfig($dom);
        }

        return $that;
    }
}

?>
