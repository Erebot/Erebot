<?php

class   ErebotModule_Admin
extends ErebotModuleBase
{
    protected $handlers;
    protected $triggers;

    public function reload($flags)
    {
        $translator = $this->getTranslator(FALSE);

        if ($flags & self::RELOAD_HANDLERS) {
            $registry   = $this->connection->getModule('TriggerRegistry',
                                ErebotConnection::MODULE_BY_NAME);
            $match_any  = ErebotUtils::getVStatic($registry, 'MATCH_ANY');

            if (!($flags & self::RELOAD_INIT)) {
                foreach ($this->triggers as $name => $value) {
                    $this->connection->removeEventHandler($this->handlers[$name]);
                    $registry->freeTriggers($value, $match_any);
                }
            }

            $this->handlers = $this->triggers = array();

            $triggers = array(
                            'part'      => 'handlePart',
                            'quit'      => 'handleQuit',
                            'voice'     => 'handleVoice',
                            'devoice'   => 'handleDeVoice',
                            'halfop'    => 'handleHalfOp',
                            'dehalfop'  => 'handleDeHalfOp',
                            'op'        => 'handleOp',
                            'deop'      => 'handleDeOp',
                            'protect'   => 'handleProtect',
                            'deprotect' => 'handleDeProtect',
                            'owner'     => 'handleOwner',
                            'deowner'   => 'handleDeOwner',
                        );

            foreach ($triggers as $default => $handler) {
                $trigger = $this->parseString('trigger_'.$default, $default);
                $this->triggers[$default] = $registry->registerTriggers($trigger, $match_any);
                if ($this->triggers[$default] === NULL) {
                    $message    = $translator->gettext('Could not register trigger '.
                                    'for admin command "<var name="command"'.
                                    '/>"');
                    $tpl        = ErebotStyling($message);
                    $tpl->assign('command', $default);
                    throw new Exception($tpl->render());
                }

                $filter = new ErebotTextFilter();
                $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $trigger, TRUE);
                $filter->addPattern(ErebotTextFilter::TYPE_WILDCARD,    $trigger.' *', TRUE);
                $this->handlers[$default]   =   new ErebotEventHandler(
                                                    array($this, $handler),
                                                    ErebotEvent::ON_TEXT,
                                                    NULL, $filter);
                $this->connection->addEventHandler($this->handlers[$default]);
            }

            // Join
            $trigger = $this->parseString('trigger_join', 'join');
            $this->triggers['join'] = $registry->registerTriggers($trigger, $match_any);
            if ($this->triggers['join'] === NULL) {
                $message    = $translator->gettext('Could not register trigger '.
                                'for admin command "join"');
                throw new Exception($message);
            }

            $filter = new ErebotTextFilter();
            $filter->addPattern(ErebotTextFilter::TYPE_WILDCARD,    $trigger.' &',      TRUE);
            $filter->addPattern(ErebotTextFilter::TYPE_WILDCARD,    $trigger.' & *',    TRUE);
            $this->handlers['join'] =   new ErebotEventHandler(
                                            array($this, 'handleJoin'),
                                            ErebotEvent::ON_TEXT,
                                            NULL, $filter);
            $this->connection->addEventHandler($this->handlers['join']);

            // Reload
            $trigger = $this->parseString('trigger_reload', 'reload');
            $this->triggers['reload'] = $registry->registerTriggers($trigger, $match_any);
            if ($this->triggers['reload'] === NULL) {
                $message    = $translator->gettext('Could not register trigger '.
                                'for admin command "reload"');
                throw new Exception($message);
            }

            $filter = new ErebotTextFilter();
            $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $trigger, TRUE);
            $filter->addPattern(ErebotTextFilter::TYPE_WILDCARD,    $trigger.' *', TRUE);
            $this->handlers['reload']   =   new ErebotEventHandler(
                                                array($this, 'handleReload'),
                                                ErebotEvent::ON_TEXT,
                                                NULL, $filter);
            $this->connection->addEventHandler($this->handlers['reload']);
        }
    }

    public function handlePart(ErebotEvent $event)
    {
        $text       = $event->getText();
        $chans      = ErebotUtils::gettok($text, 1, 1);
        $message    = ErebotUtils::gettok($text, 2);

        if ($chans == '*')
            $targets    = '0';
        else if (substr($chans, 0, 1) == '#')
            $targets    = $chans;
        else {
            $targets    = $event->getChan();
            $message    = ErebotUtils::gettok($text, 1);
        }

        $this->sendCommand('PART '.$targets.' :'.$message);
    }

    public function handleQuit(ErebotEvent $event)
    {
        $text = $event->getText();
        $this->sendCommand('QUIT :'.ErebotUtils::gettok($text, 1));
    }

    public function handleVoice(ErebotEvent $event)
    {
        
    }

    public function handleDeVoice(ErebotEvent $event)
    {
        
    }

    public function handleHalfOp(ErebotEvent $event)
    {
        
    }

    public function handleDeHalfOp(ErebotEvent $event)
    {
        
    }

    public function handleOp(ErebotEvent $event)
    {
        
    }

    public function handleDeOp(ErebotEvent $event)
    {
        
    }

    public function handleProtect(ErebotEvent $event)
    {
        
    }

    public function handleDeProtect(ErebotEvent $event)
    {
        
    }

    public function handleOwner(ErebotEvent $event)
    {
        
    }

    public function handleDeOwner(ErebotEvent $event)
    {
        $this->sendMessage($event->getChan(), "!!");
    }

    public function handleJoin(ErebotEvent $event)
    {
        $text   = $event->getText();
        $args   = ErebotUtils::gettok($text, 1);

        $this->sendCommand('JOIN '.$args);
    }

    public function handleReload(ErebotEvent $event)
    {
        $chan   = $event->getChan();

        if (!function_exists('runkit_import')) {
            $this->sendMessage($chan, 'The runkit extension is needed to perform hot-reload.');
            return;
        }

        $files  = get_included_files();
        $wrong  = array();
        foreach ($files as $file) {
            if (substr($file, -4) == '.php') {
                $ok	= runkit_import($file,
                    RUNKIT_IMPORT_FUNCTIONS |
                    RUNKIT_IMPORT_CLASSES   |
                    RUNKIT_IMPORT_OVERRIDE
                );

                if (!$ok)
                    $wrong[] = $file;
            }
        }

        if (count($wrong)) {
            $this->sendMessage($chan, 'The following files could not be reloaded: '.implode($wrong));
            return;
        }
        else
            $this->sendMessage($chan, 'Successfully reloaded files.');
    }
}

?>
