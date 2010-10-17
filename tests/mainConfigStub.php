<?php

class       ErebotStubbedMainConfig
implements  iErebotMainConfig
{
    public function __construct($configData, $source)
    {
        $this->_proxified       =&  $this;
        $this->_modules         =   array();
    }

    public function __clone()
    {
        throw new Exception();
    }

    public function load($configData, $source)
    {
    }

    public function & getNetworkCfg($network)
    {
        $null = NULL;
        return $null;
    }

    public function getNetworks()
    {
        return array();
    }

    public function getVersion()
    {
        return NULL;
    }

    public function getTimezone()
    {
        return NULL;
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

    public function getCommandsPrefix()
    {
        return '!';
    }
}

