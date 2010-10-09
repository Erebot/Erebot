<?php

include_once('src/core.php');
include_once('tests/configStub.php');

class ErebotStubbedCore
extends Erebot
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

?>
