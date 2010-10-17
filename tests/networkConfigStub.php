<?php

class       ErebotStubbedNetworkConfig
implements  iErebotNetworkConfig
{
    public function __construct(iErebotMainConfig &$mainCfg, SimpleXMLElement &$xml)
    {
        $this->_proxified   =&  $mainCfg;
        $this->_translator  =   NULL;
        $this->_modules     =   array();
    }

    public function getName()
    {
        return NULL;
    }

    public function & getServerCfg($server)
    {
        $null = NULL;
        return $null;
    }

    public function getServers()
    {
        return array();
    }

    public function & getChannelCfg($channel)
    {
        $null = NULL;
        return $null;
    }

    public function getChannels()
    {
        return array();
    }

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
}

