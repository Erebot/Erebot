<?php

include_once(dirname(dirname(__FILE__)).'/src/utils.php');
ErebotUtils::incl('coreStub.php');
ErebotUtils::incl('../src/connection.php');

class ErebotStubbedI18n
{
    public function gettext($message)
    {
        return $message;
    }
}

class ErebotStubbedConfig
{
    public function getTranslator()
    {
        return new ErebotStubbedI18n();
    }

    public function & getMainCfg()
    {
        return $this;
    }
}

class ErebotStubbedConnection
extends ErebotConnection
{
    public function __construct($modules = array())
    {
        // We need to short-circuit the parent's constructor.
        $this->channelModules   = array();
        $this->plainModules     = array();
        $this->moduleClasses    = array();
        $this->raws             = array();
        $this->events           = array();

        if (count($modules))
            $this->bot = new ErebotStubbedCore();

        foreach ($modules as &$module) {
            $this->loadModule($module, NULL);
        }
        unset($module);
    }

    public function __destruct()
    {
        // We need to short-circuit the parent's destructor.
    }

    public function pushLine($line)
    {
        parent::pushLine($line);
        $this->sndQueue = array();
    }

    public function & getConfig($chan)
    {
        $res = new ErebotStubbedConfig();
        return $res;
    }
}

?>
