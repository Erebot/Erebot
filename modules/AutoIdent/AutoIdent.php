<?php

class   ErebotModule_AutoIdent
extends ErebotModuleBase
{
    protected $password;

    public function reload($flags)
    {
        if ($flags & self::RELOAD_HANDLERS) {
            $targets        = new ErebotEventTargets(ErebotEventTargets::ORDER_ALLOW_DENY);
            $this->password = $this->parseString('password');

            $nicknames  = explode(' ', $this->parseString('nickserv', 'nickserv'));
            foreach ($nicknames as &$nickname) {
                $targets->addRule(ErebotEventTargets::TYPE_ALLOW, $nickname);
            }
            unset($nickname);

            $pattern    =   $this->parseString('pattern');
            $pattern    =   '/'.str_replace('/', '\\/', $pattern).'/i';

            /// @TODO use only one handler...
            $filter     =   new ErebotTextFilter(ErebotTextFilter::TYPE_REGEXP, $pattern);
            $handler    =   new ErebotEventHandler(
                                array($this, 'handleIdentRequest'),
                                array(
                                    'ErebotEventTextPrivate',
                                    'ErebotEventNoticePrivate',
                                ),
                                $targets, $filter);
            $this->connection->addEventHandler($handler);
        }
    }

    public function handleIdentRequest(iErebotEventSource &$event)
    {
        $this->sendMessage($event->getSource(), 'IDENTIFY '.$this->password);
    }
}

?>
