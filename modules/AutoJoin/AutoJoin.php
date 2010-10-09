<?php

class   ErebotModule_AutoJoin
extends ErebotModuleBase
{
    public function reload($flags)
    {
        if ($this->channel === NULL)
            return;

        if ($flags & self::RELOAD_HANDLERS) {
            $handler = new ErebotEventHandler(
                array($this, 'handleConnect'),
                ErebotEvent::ON_CONNECT);
            $this->connection->addEventHandler($handler);
        }
    }

    public function handleConnect(ErebotEvent &$event)
    {
        $key = $this->parseString('key', '');
        $this->sendCommand('JOIN '.$this->channel.
            ($key != '' ? ' '.$key : ''));
    }
}

?>
