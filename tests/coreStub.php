<?php

include_once(dirname(dirname(__FILE__)).'/src/utils.php');
ErebotUtils::incl('../src/core.php');

class ErebotStubbedCore
extends Erebot
{
    public function __construct()
    {
        $this->timers           =
        $this->modulesMapping   =   array();
    }
}

?>
