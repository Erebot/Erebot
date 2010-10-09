<?php

class Erebot_chmodes
{
    const STATUS_REGULAR	= 0x00;
    const STATUS_VOICE		= 0x01;
    const STATUS_HALFOP		= 0x02;
    const STATUS_OPERATOR	= 0x04;
    const STATUS_PROTECTED	= 0x08;
    const STATUS_OWNER		= 0x10;
    const STATUS_UNKNOWN	= FALSE;

    private $mapping		= array();
    private static $modes	= array();

    private $bot	= FALSE;
    public function __construct(Erebot $bot)
    {
        $bot->addEvent(ErebotEvent::ON_CONNECT, NULL, array($this, 'handleConnect'));
        $bot->registerNumeric(array($this, 'handleNames'), 353);

        $bot->addEvent(ErebotEvent::ON_OP, 		NULL, array($this, 'handleChanUMode'));
        $bot->addEvent(ErebotEvent::ON_DEOP, 	NULL, array($this, 'handleChanUMode'));
        $bot->addEvent(ErebotEvent::ON_HELP, 	NULL, array($this, 'handleChanUMode'));
        $bot->addEvent(ErebotEvent::ON_DEHELP, 	NULL, array($this, 'handleChanUMode'));
        $bot->addEvent(ErebotEvent::ON_VOICE, 	NULL, array($this, 'handleChanUMode'));
        $bot->addEvent(ErebotEvent::ON_DEVOICE,	NULL, array($this, 'handleChanUMode'));

        $bot->addEvent(ErebotEvent::ON_JOIN,	NULL, array($this, 'handleJoin'));

        $bot->addEvent(ErebotEvent::ON_QUIT,	NULL, array($this, 'handleQuit'));
        $bot->addEvent(ErebotEvent::ON_PART,	NULL, array($this, 'handleDisappear'));
        $bot->addEvent(ErebotEvent::ON_KICK,	NULL, array($this, 'handleDisappear'));

        $bot->addEvent(ErebotEvent::ON_NICK,	NULL, array($this, 'handleNick'));

        $this->bot = $bot;
    }

    public function handleConnect(ErebotEvent &$event, $filters)
    {
        $options		= $this->bot->getServerOptions();
        $this->mapping 	= array(
            '@'	=> Erebot_chmodes::STATUS_OPERATOR,
            '+'	=> Erebot_chmodes::STATUS_VOICE,
        );

        if (isset($options['PREFIX'])) {
            $prefix = $options['PREFIX'];
            $ok = preg_match('/\\(([^\\)]+)\\)(.+)/', $prefix, $matches);

            if ($ok && ($len = strlen($matches[1])) == strlen($matches[2])) {
                $new_mapping = array();

                for ($i = 0; $i < $len; $i++) {
                    $status = FALSE;
                    switch ($matches[1][$i]) {
                        case 'v':
                            $status = Erebot_chmodes::STATUS_VOICE;
                            break;

                        case 'h':
                            $status = Erebot_chmodes::STATUS_HALFOP;
                            break;

                        case 'o':
                            $status = Erebot_chmodes::STATUS_OPERATOR;
                            break;

                        case 'a':
                            $status = Erebot_chmodes::STATUS_PROTECTED;
                            break;

                        case 'q':
                            $status = Erebot_chmodes::STATUS_OWNER;
                            break;
                    }

                    if ($status == FALSE)
                        continue;
                    else $new_mapping[$this->bot->extractNick(
                        $matches[2][$i], TRUE)] = $status;
                }

                if (count($new_mapping))
                    $this->mapping = $new_mapping;
            }
        }
    }

    public function handleNames($type, $source, $target, $msg)
    {
        $parts = explode(' ', $msg);

        if (!$this->bot->isChannel($parts[0]))
            array_shift($parts);
        if (count($parts) == 1) return;

        $chan = strtolower(array_shift($parts));
        if ($parts[0][0] == ':')
            $parts[0] = substr($parts[0], 1);

        foreach ($parts as $data) {
            $status = Erebot_chmodes::STATUS_REGULAR;
            $length = strlen($data);
            for ($i = 0; $i < $length; $i++) {
                if (isset($this->mapping[$data[$i]]))
                    $status |= $this->mapping[$data[$i]];
                else break;
            }
            if ($i < $length)
                self::$modes[$chan]['nicks'][$this->bot->extractNick(
                    substr($data, $i), TRUE)] = $status;
        }
    }

    public function handleChanUMode(ErebotEvent &$event, $filters)
    {
        $chan = strtolower($event->chan());

        $nick = $event->opnick().$event->hnick().$event->vnick();
        $nick = $this->bot->extractNick($nick, TRUE);

        switch ($event->type()) {
            case ErebotEvent::ON_OP:
                self::$modes[$chan]['nicks'][$nick] |=  self::STATUS_OPERATOR;
                break;

            case ErebotEvent::ON_DEOP:
                self::$modes[$chan]['nicks'][$nick] &= ~self::STATUS_OPERATOR;
                break;

            case ErebotEvent::ON_HELP:
                self::$modes[$chan]['nicks'][$nick] |=  self::STATUS_HALFOP;
                break;

            case ErebotEvent::ON_DEHELP:
                self::$modes[$chan]['nicks'][$nick] &= ~self::STATUS_HALFOP;
                break;

            case ErebotEvent::ON_VOICE:
                self::$modes[$chan]['nicks'][$nick] |=  self::STATUS_VOICE;
                break;

            case ErebotEvent::ON_DEVOICE:
                self::$modes[$chan]['nicks'][$nick] &= ~self::STATUS_VOICE;
                break;

            default:
                return;
        }
    }

    public function handleJoin(ErebotEvent &$event, $filters)
    {
        $nick = $this->bot->extractNick($event->nick(), TRUE);
        self::$modes[$event->chan()]['nicks'][$nick] = self::STATUS_REGULAR;
    }

    public function handleQuit(ErebotEvent &$event, $filters)
    {
        $nick   = $this->bot->extractNick($event->nick(), TRUE);
        $chans  = array_keys(self::$modes);
        foreach ($chans as $chan) {
            unset(self::$modes[$chan]['nicks'][$nick]);
        }
    }

    public function handleDisappear(ErebotEvent &$event, $filters)
    {
        $nick = $event->type() == ErebotEvent::ON_KICK ?
            $event->knick() : $event->nick();
        $nick = $this->bot->extractNick($nick, TRUE);

        if ($nick == $this->bot->botNick())
            unset(self::$modes[$event->chan()]);
        else unset(self::$modes[$event->chan()]['nicks'][$nick]);
    }

    public function handleNick(ErebotEvent &$event, $filters)
    {
        $old_nick 	= $this->bot->extractNick($event->nick(), TRUE);
        $new_nick 	= $this->bot->extractNick($event->newnick(), TRUE);
        $chans		= array_keys(self::$modes);

        foreach ($chans as $chan) {
            if (isset(self::$modes[$chan]['nicks'][$old_nick])) {
                self::$modes[$chan]['nicks'][$new_nick] =
                    self::$modes[$chan]['nicks'][$old_nick];
                unset(self::$modes[$chan]['nicks'][$old_nick]);
            }
        }
    }

    public static function getUserStatus($chan, $nick)
    {
        $nick = Erebot::extractNick($nick, TRUE);
        if (!isset(self::$modes[$chan]['nicks'][$nick]))
            return self::STATUS_UNKNOWN;
        return self::$modes[$chan]['nicks'][$nick];
    }
}

?>
