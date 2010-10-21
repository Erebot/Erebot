<?php

include_once(dirname(__FILE__).'/mainConfigStub.php');

class       ErebotStubbedCore
implements  iErebot
{
    static protected $_stubModules = array();

    public function __construct(iErebotMainConfig $config = NULL)
    {
        $this->_timers          =
        $this->_modulesMapping  =   array();
        if ($config === NULL)
            $config = new ErebotStubbedMainConfig(NULL, NULL);
        $this->_mainCfg         =   $config;
    }

    public function getConnections()
    {
        return array();
    }
    
    public function start($connectionCls = NULL)
    {
    }

    public function stop()
    {
    }

    public function getTimers()
    {
        return array();
    }

    public function addTimer(iErebotTimer &$timer)
    {
    }

    public function removeTimer(iErebotTimer &$timer)
    {
    }

    static public function getVersion()
    {
        return self::VERSION;
    }

    public function addConnection(iErebotConnection &$connection)
    {
    }

    public function removeConnection(iErebotConnection &$connection)
    {
    }

    public function gettext($msg)
    {
        return $msg;
    }

    public function loadModule($module)
    {
        if (isset(self::$_stubModules[$module]))
            return self::$_stubModules[$module];
        $class = parent::loadModule($module);
        self::$_stubModules[$module] = $class;
        return $class;
    }

    public function addModuleMapping($modName, $className)
    {
        self::$_stubModules[$modName] = $className;
    }

    public function moduleNameToClass($modName)
    {
        if (!isset(self::$_stubModules[$modName]))
            throw new EErebotNotFound('No such module');
        return self::$_stubModules[$modName];
    }

    public function moduleClassToName($className)
    {
        if (is_object($className))
            $className = get_class($className);

        $modName = array_search($className, self::$_stubModules);
        if ($modName === FALSE)
            throw new EErebotNotFound('No such module');
        return $modName;
    }
}

