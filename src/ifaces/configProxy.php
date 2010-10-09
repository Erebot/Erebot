<?php

interface iErebotConfigProxy
{
    public function getTranslator($component);
    public function & getMainCfg();
    public function getModules($recursive);
    public function parseBool($module, $param, $default = NULL);
    public function parseString($module, $param, $default = NULL);
    public function parseInt($module, $param, $default = NULL);
    public function parseReal($module, $param, $default = NULL);
}

?>
