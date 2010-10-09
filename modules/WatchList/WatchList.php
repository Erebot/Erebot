<?php

class   ErebotModule_WatchList
extends ErebotModuleBase
{
    protected $watchedNicks;

    public function reload($flags)
    {
        $handler    =   new ErebotEventHandler(
                            array($this, 'handleConnect'),
                            'ErebotEventConnect');
        $this->connection->addEventHandler($handler);
        $watchedNicks = $this->parseString('nicks', '');
        $watchedNicks = str_replace(',', ' ', $watchedNicks);
        $this->watchedNicks = array_filter(array_map('trim',
                                explode(' ', $watchedNicks)));
    }

    public function handleConnect(iErebotEvent &$event)
    {
        if (!count($this->watchedNicks))
            return;

        $this->sendCommand('WATCH +'.implode(' +', $this->watchedNicks));
    }
}

?>
