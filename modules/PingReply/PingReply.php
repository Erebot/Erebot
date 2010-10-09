<?php

class   ErebotModule_PingReply
extends ErebotModuleBase
{
    public function reload($flags)
    {
        if ($flags & self::RELOAD_HANDLERS) {
            $handler = new ErebotEventHandler(array($this, 'handlePing'), ErebotEvent::ON_PING);
            $this->connection->addEventHandler($handler);
        }
    }

    public function handlePing(ErebotEvent &$event)
    {
        $this->sendCommand('PONG '.$event->getSource());
    }
}

?>