<?php

class Erebot_catcher
{
    private $connected  = FALSE;
    private $bot        = FALSE;
    private $events     = array();
    private $enabled    = FALSE;

    public function __construct(Erebot $bot)
    {
        $reflect	= new ReflectionClass('ErebotEvent');
        $constants	= $reflect->getConstants();

        $bot->addEvent(ErebotEvent::ON_TEXT,
            array(
                'matchtext' => '!catcher &',
                'matchtype' => Erebot::MATCHTEXT_WILDCARD
            ),
            array($this, 'handleCatcher')
        );

        foreach ($constants as $name => $val) {
            if (substr($name, 0, 3) == 'ON_') {
                $bot->addEvent($val, array(), array($this, 'handleEvent'));
                $this->events[$val] = $name;
            }
        }

        $this->bot = $bot;
    }

    public function handleCatcher(ErebotEvent &$event, $filters)
    {
        $nick = $event->nick();
        if (!Erebot_admin::isAdmin($nick))
            return;

        $status = $this->bot->gettok($event->text(), 1, 1);
        $enabled =  (
                        !strcasecmp($status, 'on') ||
                        !strncasecmp($status, 'enable', 6) ||
                        intval($status)
                    );
        $this->enabled = ($enabled ? $nick : FALSE);

        $this->bot->sendCommand('PRIVMSG '.$nick." :Catcher's status is ".
            ($enabled ? 'ON' : 'OFF'));
    }

    public function handleEvent(ErebotEvent &$event, $filters)
    {
        if ($event->type() != ErebotEvent::ON_CONNECT) {
            if (!$this->connected || $this->enabled === FALSE)
                return;
        }
        else {
            $this->connected = TRUE;
            return;
        }

        $intro	= 'PRIVMSG '.$this->enabled.' :['.$this->events[$event->type()].
                    '] Origin = "'.$event->nick().'"';
        $bnick	= '';

        switch ($event->type()) {
            case ErebotEvent::ON_CTCPREPLY:
                $this->bot->sendCommand($intro.', Message = "'.$event->text().'"');
                break;

            case ErebotEvent::ON_ACTION:
            case ErebotEvent::ON_NOTICE:
            case ErebotEvent::ON_TEXT:
                $this->bot->sendCommand($intro.', Target = "'.
                    $event->target().'", Message = "'.$event->text().'"');
                break;

            case ErebotEvent::ON_INVITE:
                $this->bot->sendCommand($intro.' invited me on "'.$event->chan().'"');
                break;

            case ErebotEvent::ON_JOIN:
            case ErebotEvent::ON_PART:
                $this->bot->sendCommand($intro.', Chan = "'.$event->chan().
                        '"'.($event->type() == ErebotEvent::ON_PART
                        ? ', Message = "'.$event->text().'"' : ''));
                break;

            case ErebotEvent::ON_BAN:
            case ErebotEvent::ON_UNBAN:
                $bnick = ', Bnick = "'.$event->bnick().'"';
            case ErebotEvent::ON_OP:
            case ErebotEvent::ON_DEOP:
            case ErebotEvent::ON_HELP:
            case ErebotEvent::ON_DEHELP:
            case ErebotEvent::ON_VOICE:
            case ErebotEvent::ON_DEVOICE:
                $this->bot->sendCommand($intro.', Chan = "'.$event->chan().
                    '", Target = "'.$event->text().'"'.$bnick);
                break;

            case ErebotEvent::ON_RAWMODE:
            case ErebotEvent::ON_MODE:
                $this->bot->sendCommand($intro.', Chan = "'.$event->chan().
                    '", Modes = "'.$event->text().'"');
                break;

            case ErebotEvent::ON_NICK:
                $this->bot->sendCommand($intro.', New nick = "'.$event->newnick().'"');
                break;

            case ErebotEvent::ON_KICK:
                $this->bot->sendCommand($intro.', Chan = "'.$event->chan().
                        '", Target = "'.$event->knick().'"'.
                        ', Message = "'.$event->text().'"');
                break;

            case ErebotEvent::ON_PING:
            case ErebotEvent::ON_PONG:
                $this->bot->sendCommand($intro.', Target = "'.$event->target().'"');
                break;

            case ErebotEvent::ON_TOPIC:
                $this->bot->sendCommand($intro.', Chan = "'.$event->chan().
                    '", New topic = "'.$event->text().'"');
                break;

            case ErebotEvent::ON_QUIT:
                $this->bot->sendCommand($intro.', Message = "'.$event->text().'"');
                break;
        }
    }
}

?>
