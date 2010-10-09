<?php

class   ErebotModule_ChanTracker
extends ErebotModuleBase
{
    protected $chans;

    public function reload($flags)
    {
        if ($flags & self::RELOAD_INIT) {
            $this->chans = array();
        }

        if ($flags & self::RELOAD_HANDLERS) {
            $this->connection->addEventHandler(array($this, ''));
        }
    }


}

?>