<?php

include_once(dirname(dirname(__FILE__)).'/src/utils.php');
ErebotUtils::incl('../src/core.php');

class ErebotStubbedCore
extends Erebot
{
    static $stub_modules = array();

    public function __construct(iErebotMainConfig $config = NULL)
    {
        $this->timers           =
        $this->modulesMapping   =   array();
    }

    public function loadModule($module)
    {
        if (isset(self::$stub_modules[$module]))
            return self::$stub_modules[$module];
        $class = parent::loadModule($module);
        self::$stub_modules[$module] = $class;
        return $class;
    }

    public function moduleNameToClass($modName)
    {
        if (!isset(self::$stub_modules[$modName]))
            throw new EErebotNotFound('No such module');
        return self::$stub_modules[$module];
    }

    public function moduleClassToName($className)
    {
        if (is_object($className))
            $className = get_class($className);

        $modName = array_search($className, self::$stub_modules);
        if ($modName === FALSE)
            throw new EErebotNotFound('No such module');
        return $modName;
    }
}

?>
