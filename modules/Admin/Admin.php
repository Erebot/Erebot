<?php

class   ErebotModule_Admin
extends ErebotModuleBase
{
    protected $handlers;
    protected $triggers;

    public function reload($flags)
    {
        if ($flags & self::RELOAD_METADATA) {
            $this->addMetadata(self::META_DEPENDS, 'TriggerRegistry');
        }

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
                    $message    = $this->gettext('Could not register trigger '.
                                    'for admin command "<var name="command"'.
                                    '/>"');
                    $tpl        = ErebotStyling($message, $this->translator);
                    $tpl->assign('command', $default);
                    throw new Exception($tpl->render());
                }

                $filter = new ErebotTextFilter($this->mainCfg);
                $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $trigger, TRUE);
                $filter->addPattern(ErebotTextFilter::TYPE_WILDCARD,    $trigger.' *', TRUE);
                $this->handlers[$default]   =   new ErebotEventHandler(
                                                    array($this, $handler),
                                                    'iErebotEventMessageText',
                                                    NULL, $filter);
                $this->connection->addEventHandler($this->handlers[$default]);
            }

            // Join
            $trigger = $this->parseString('trigger_join', 'join');
            $this->triggers['join'] = $registry->registerTriggers($trigger, $match_any);
            if ($this->triggers['join'] === NULL) {
                $message    = $this->translator->gettext('Could not register trigger '.
                                'for admin command "join"');
                throw new Exception($message);
            }

            $filter = new ErebotTextFilter($this->mainCfg);
            $filter->addPattern(ErebotTextFilter::TYPE_WILDCARD,    $trigger.' &',      TRUE);
            $filter->addPattern(ErebotTextFilter::TYPE_WILDCARD,    $trigger.' & *',    TRUE);
            $this->handlers['join'] =   new ErebotEventHandler(
                                            array($this, 'handleJoin'),
                                            'iErebotEventMessageText',
                                            NULL, $filter);
            $this->connection->addEventHandler($this->handlers['join']);

            // Reload
            $trigger = $this->parseString('trigger_reload', 'reload');
            $this->triggers['reload'] = $registry->registerTriggers($trigger, $match_any);
            if ($this->triggers['reload'] === NULL) {
                $message    = $this->translator->gettext('Could not register trigger '.
                                'for admin command "reload"');
                throw new Exception($message);
            }

            $filter = new ErebotTextFilter($this->mainCfg);
            $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $trigger, TRUE);
            $filter->addPattern(ErebotTextFilter::TYPE_WILDCARD,    $trigger.' *', TRUE);
            $this->handlers['reload']   =   new ErebotEventHandler(
                                                array($this, 'handleReload'),
                                                'iErebotEventMessageText',
                                                NULL, $filter);
            $this->connection->addEventHandler($this->handlers['reload']);
        }
    }

    public function handlePart(iErebotEventMessageText $event)
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

    public function handleQuit(iErebotEventMessageText $event)
    {
        $text   = $event->getText();
        $msg    = ErebotUtils::gettok($text, 1);
        if (rtrim($msg) == '')
            $msg = NULL;
        $exitEvent = new ErebotEventExit($this->connection);
        $this->connection->dispatchEvent($exitEvent);
        $this->connection->disconnect($msg);
    }

    public function handleVoice(iErebotEventMessageText $event)
    {
        
    }

    public function handleDeVoice(iErebotEventMessageText $event)
    {
        
    }

    public function handleHalfOp(iErebotEventMessageText $event)
    {
        
    }

    public function handleDeHalfOp(iErebotEventMessageText $event)
    {
        
    }

    public function handleOp(iErebotEventMessageText $event)
    {
        
    }

    public function handleDeOp(iErebotEventMessageText $event)
    {
        
    }

    public function handleProtect(iErebotEventMessageText $event)
    {
        
    }

    public function handleDeProtect(iErebotEventMessageText $event)
    {
        
    }

    public function handleOwner(iErebotEventMessageText $event)
    {
        
    }

    public function handleDeOwner(iErebotEventMessageText $event)
    {
        
    }

    public function handleJoin(iErebotEventMessageText $event)
    {
        $text   = $event->getText();
        $args   = ErebotUtils::gettok($text, 1);

        $this->sendCommand('JOIN '.$args);
    }

    public function handleReload(iErebotEventMessageText &$event)
    {
        if ($event instanceof iErebotEventPrivate) {
            $target = $event->getSource();
            $chan   = NULL;
        }
        else
            $target = $chan = $event->getChan();

        $translator = $this->getTranslator($chan);
        if (!function_exists('runkit_import')) {
            $msg = $translator->gettext('The runkit extension is needed to perform hot-reload.');
            $this->sendMessage($chan, $msg);
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
            $msg = $translator->gettext('The following files could not be '.
                'reloaded: <for from="files" item="file"><var name="file"/>'.
                '</for>');
            $tpl = new ErebotStyling($msg, $translator);
            $tpl->assign('files', $wrong);
            $this->sendMessage($target, $tpl->render());
            return;
        }
        else
            $msg = $translator->gettext('Successfully reloaded files.');
            $this->sendMessage($target, $msg);
    }
}

/** } */

?>
