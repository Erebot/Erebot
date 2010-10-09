<?php

class Erebot_admin
{
    private $bot = FALSE;
    public function __construct(Erebot $bot)
    {
        $this->bot = $bot;

        $bot->addEvent(ErebotEvent::ON_TEXT,
            array('matchtext' => '!join *', 'matchtype' => Erebot::MATCHTEXT_WILDCARD),
            array($this, 'handleJoin'));

        $bot->addEvent(ErebotEvent::ON_TEXT,
            array('matchtext' => '!part', 'matchtype' => Erebot::MATCHTEXT_STATIC),
            array($this, 'handlePart'));

        $bot->addEvent(ErebotEvent::ON_TEXT,
            array('matchtext' => '!part *', 'matchtype' => Erebot::MATCHTEXT_WILDCARD),
            array($this, 'handlePart'));

        $bot->addEvent(ErebotEvent::ON_TEXT,
            array('matchtext' => '!quit', 'matchtype' => Erebot::MATCHTEXT_STATIC),
            array($this, 'handleQuit'));

        $bot->addEvent(ErebotEvent::ON_TEXT,
            array('matchtext' => '!quit *', 'matchtype' => Erebot::MATCHTEXT_WILDCARD),
            array($this, 'handleQuit'));

        $bot->addEvent(ErebotEvent::ON_TEXT,
            array('matchtext' => '!reload', 'matchtype' => Erebot::MATCHTEXT_STATIC),
            array($this, 'handleReload'));

        $bot->addEvent(ErebotEvent::ON_TEXT,
            array('matchtext' => '!reload *', 'matchtype' => Erebot::MATCHTEXT_WILDCARD),
            array($this, 'handleReload'));

        $triggers = array('voice', 'halfop', 'op', 'protect', 'owner');
        foreach ($triggers as $trigger) {
            $bot->addEvent(ErebotEvent::ON_TEXT,
                array('matchtext' => '!'.$trigger, 'matchtype' => Erebot::MATCHTEXT_STATIC),
                array($this, 'handleCommand'));
            $bot->addEvent(ErebotEvent::ON_TEXT,
                array('matchtext' => '!'.$trigger.' *', 'matchtype' => Erebot::MATCHTEXT_WILDCARD),
                array($this, 'handleCommand'));

            $bot->addEvent(ErebotEvent::ON_TEXT,
                array('matchtext' => '!de'.$trigger, 'matchtype' => Erebot::MATCHTEXT_STATIC),
                array($this, 'handleCommand'));
            $bot->addEvent(ErebotEvent::ON_TEXT,
                array('matchtext' => '!de'.$trigger.' *', 'matchtype' => Erebot::MATCHTEXT_WILDCARD),
                array($this, 'handleCommand'));
        }

    }

    public static function isAdmin($nick)
    {
        $nick = strtolower($nick);
        $admins = array(
            'looksup',
            'clicky',
            'clickiie',
            'missingno',
            'bigbrowser'
        );
        return in_array($nick, $admins);
    }

    public function handleJoin(ErebotEvent &$event, $filters)
    {
        if (!$this->isAdmin($event->nick()))
            return;

        $nb     = $this->bot->numtok($event->text());
        $keys   = '';

        switch ($nb) {
            case 1:
                $this->bot->sendCommand('PRIVMSG '.$event->target().
                    ' :Not enough parameters.');
            case 0:
                return;

            default:
                $keys   = $this->bot->gettok($event->text(), 2, 1);
            case 2:
                $chans  = $this->bot->gettok($event->text(), 1, 1);
        }

        $this->bot->sendCommand('JOIN '.$chans.' '.$keys);
    }

    public function handlePart(ErebotEvent &$event, $filters)
    {
        if (!$this->isAdmin($event->nick()))
            return;

        $nb     = $this->bot->numtok($event->text());
        $chans  = '0';
        $msg    = 'Leaving';

        switch ($nb) {
            case 1:
                $this->bot->sendCommand('PRIVMSG '.$event->target().
                    ' :Not enough parameters.');
            case 0:
                return;

            default:
                $msg    = $this->bot->gettok($event->text(), 2);
            case 2:
                $chans  = $this->bot->gettok($event->text(), 1, 1);
        }

        $this->bot->sendCommand('PART '.$chans.' :'.$msg.
            ' ('.$this->bot->extractNick($event->nick()).')');
    }

    public function handleQuit(ErebotEvent &$event, $filters)
    {
        if (!$this->isAdmin($event->nick()))
            return;

        $msg    = $this->bot->gettok($event->text(), 1);
        $msg    = strlen($msg) ? $msg : 'Exiting!';

        $this->bot->sendCommand('QUIT '.$msg.
            ' ('.$this->bot->extractNick($event->nick()).')');
        sleep(2);
        $this->bot->__destruct();
    }

    public function handleReload(ErebotEvent &$event, $filters)
    {
        if ($this->isAdmin($event->nick())) {
            $dest = $event->target();

            if (!function_exists('runkit_import')) {
                $this->bot->sendCommand('PRIVMSG '.$dest.
                    ' :The runkit extension is needed to perform hot-reload.');
                return;
            }

            $path	= dirname(__FILE__).'/';
            $files	= scandir($path);
            foreach ($files as $file) {
                if (substr($file, -4) == '.php') {
                    $ok	= @runkit_import($path.$file,
                        RUNKIT_IMPORT_CLASSES | RUNKIT_IMPORT_OVERRIDE);
                    if (!$ok) break;
                }
            }

            if (!$ok) {
                $this->bot->sendCommand('PRIVMSG '.$dest.
                    ' :Could not reload core... The bot may stop'.
                    ' working correctly from now on!');
                return;
            }
            else $this->bot->sendCommand('PRIVMSG '.$dest.
                    ' :Successfully reloaded core.');

            if ($this->bot->numtok($event->text()) > 1)
                $modules = explode(' ', $this->bot->gettok($event->text(), 1));
            else {
                $modules = $this->bot->getModules();
                if (!count($modules)) {
                    $this->bot->sendCommand('PRIVMSG '.$dest.
                        ' :No modules loaded... No need for reloading.');
                    return;
                }
            }

            $success = array();
            $failure = array();

            foreach ($modules as $module) {
                $ok = @runkit_import($path.$module.'.php',
                    RUNKIT_IMPORT_CLASSES | RUNKIT_IMPORT_OVERRIDE);

                if (!$ok)   $failure[] = $module;
                else        $success[] = $module;
            }

            if (count($success))
                $this->bot->sendCommand('PRIVMSG '.$dest.
                    ' :Successfully reloaded the following modules: '.
                    implode(', ', $success).'.');

            if (count($failure))
                $this->bot->sendCommand('PRIVMSG '.$dest.
                    ' :WARNING: Could not reload the following modules: '.
                    implode(', ', $failure).'.');

        } // is_admin
    }

    protected function handleChannelOperation(ErebotEvent &$event, $mode)
    {
        if (!$this->isAdmin($event->nick()))
            return;

        $text   = $event->text();
        $chan   = $event->chan();
        $target = $event->nick();

        for ($i = 1; $i <= 2; $i++) {
            $tok = Erebot::gettok($text, $i, 1);
            if ($tok === NULL)
                continue;

            $tok = substr($tok, 0, strcspn($tok, ','));

            if ($this->bot->isChannel($tok))
                $chan = $tok;
            else if ($tok != '')
                $target = $tok;
        }

        $this->bot->sendCommand('MODE '.$chan.' '.$mode.' '.$target);
    }

    public function handleCommand(ErebotEvent &$event)
    {
        $command    = Erebot::gettok($event->text(), 0, 1);
        $command    = strtolower(substr($command, 1));
        if (substr($command, 0, 2) == 'de') {
            $mode       = '-';
            $command    = substr($command, 2);
        }
        else $mode  = '+';

        $commands   = array(
            'owner'     => 'q',
            'protect'   => 'a',
            'op'        => 'o',
            'halfop'    => 'h',
            'voice'     => 'v',
        );

        if (isset($commands[$command]))
            $this->handleChannelOperation($event, $mode.$commands[$command]);

        $event->haltdef();
    }
}

?>
