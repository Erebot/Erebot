<?php

class   ErebotModule_PingReply
extends ErebotModuleBase
{
    public function reload($flags)
    {
        if ($flags & self::RELOAD_HANDLERS) {
            $handler = new ErebotEventHandler(
                            array($this, 'handlePing'),
                            'ErebotEventPing');
            $this->connection->addEventHandler($handler);
        }
    }

    public function handlePing(iErebotEventText &$event)
    {
        $this->sendCommand('PONG '.$event->getText());
    }
}

?>
