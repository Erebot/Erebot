<?php

include_once('src/utils.php');
include_once('tests/coreStub.php');
include_once('tests/configStub.php');
include_once('src/connection.php');

class ErebotStubbedConnection
extends ErebotConnection
{
    protected function _loadChannelModules()
    {
        // Do nothing.
    }

    public function __destruct()
    {
        // We need to short-circuit the parent's destructor.
    }

    public function getSendQueue()
    {
        $res = $this->_sndQueue;
        $this->_sndQueue = array();
        return $res;
    }
}

?>
