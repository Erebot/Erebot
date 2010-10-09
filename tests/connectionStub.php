<?php

include_once(dirname(dirname(__FILE__)).'/src/utils.php');
ErebotUtils::incl('coreStub.php');
ErebotUtils::incl('configStub.php');
ErebotUtils::incl('../src/connection.php');

class ErebotStubbedConnection
extends ErebotConnection
{
    protected function loadChannelModules()
    {
        // Do nothing.
    }

    public function __destruct()
    {
        // We need to short-circuit the parent's destructor.
    }

    public function getSendQueue()
    {
        $res = $this->sndQueue;
        $this->sndQueue = array();
        return $res;
    }
}

?>
