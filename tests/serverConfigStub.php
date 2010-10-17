<?php

class       ErebotStubbedServerConfig
implements  iErebotServerConfig
{
    public function __construct(iErebotNetworkConfig &$netCfg, SimpleXMLElement &$xml)
    {
        $this->_proxified   =&  $netCfg;
        $this->_translator  =   NULL;
        $this->_modules     =   array();
    }

    public function getConnectionURL() { return NULL; }
    public function & getNetworkCfg() { return $this; }

    public function & getMainCfg()
    {
        $null = NULL;
        return $null;
    }

    public function getModules($recursive)
    {
        return array();
    }

    public function & getModule($moduleName)
    {
        $null = NULL;
        return $null;
    }

    public function getTranslator($component)
    {
        return new ErebotStubbedI18n();
    }

    public function parseBool($module, $param, $default = NULL)
    {
        return NULL;
    }

    public function parseString($module, $param, $default = NULL)
    {
        return NULL;
    }

    public function parseInt($module, $param, $default = NULL)
    {
        return NULL;
    }

    public function parseReal($module, $param, $default = NULL)
    {
        return NULL;
    }

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

