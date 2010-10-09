<?php
/*
    This file is part of Erebot.

    Erebot is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Erebot is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Erebot.  If not, see <http://www.gnu.org/licenses/>.
*/

class   ErebotModule_Admin
extends ErebotModuleBase
{
    static protected $_metadata = array(
        'requires'  =>  array('TriggerRegistry'),
    );
    protected $_handlers;
    protected $_triggers;

    public function reload($flags)
    {
        if ($flags & self::RELOAD_HANDLERS) {
            $registry   = $this->_connection->getModule('TriggerRegistry',
                                ErebotConnection::MODULE_BY_NAME);
            $matchAny  = ErebotUtils::getVStatic($registry, 'MATCH_ANY');

            if (!($flags & self::RELOAD_INIT)) {
                foreach ($this->_triggers as $name => $value) {
                    $this->_connection->removeEventHandler($this->_handlers[$name]);
                    $registry->freeTriggers($value, $matchAny);
                }
            }

            $this->_handlers = $this->_triggers = array();

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
                $this->_triggers[$default] = $registry->registerTriggers($trigger, $matchAny);
                if ($this->_triggers[$default] === NULL) {
                    $message    = $this->gettext('Could not register trigger '.
                                    'for admin command "<var name="command"'.
                                    '/>"');
                    $tpl        = ErebotStyling($message, $this->_translator);
                    $tpl->assign('command', $default);
                    throw new Exception($tpl->render());
                }

                $filter = new ErebotTextFilter($this->_mainCfg);
                $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $trigger, TRUE);
                $filter->addPattern(ErebotTextFilter::TYPE_WILDCARD,    $trigger.' *', TRUE);
                $this->_handlers[$default]   =   new ErebotEventHandler(
                                                    array($this, $handler),
                                                    'iErebotEventMessageText',
                                                    NULL, $filter);
                $this->_connection->addEventHandler($this->_handlers[$default]);
            }

            // Join
            $trigger = $this->parseString('trigger_join', 'join');
            $this->_triggers['join'] = $registry->registerTriggers($trigger, $matchAny);
            if ($this->_triggers['join'] === NULL) {
                $message    = $this->_translator->gettext('Could not register trigger '.
                                'for admin command "join"');
                throw new Exception($message);
            }

            $filter = new ErebotTextFilter($this->_mainCfg);
            $filter->addPattern(ErebotTextFilter::TYPE_WILDCARD,    $trigger.' &',      TRUE);
            $filter->addPattern(ErebotTextFilter::TYPE_WILDCARD,    $trigger.' & *',    TRUE);
            $this->_handlers['join'] =   new ErebotEventHandler(
                                            array($this, 'handleJoin'),
                                            'iErebotEventMessageText',
                                            NULL, $filter);
            $this->_connection->addEventHandler($this->_handlers['join']);

            // Reload
            $trigger = $this->parseString('trigger_reload', 'reload');
            $this->_triggers['reload'] = $registry->registerTriggers($trigger, $matchAny);
            if ($this->_triggers['reload'] === NULL) {
                $message    = $this->_translator->gettext('Could not register trigger '.
                                'for admin command "reload"');
                throw new Exception($message);
            }

            $filter = new ErebotTextFilter($this->_mainCfg);
            $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $trigger, TRUE);
            $filter->addPattern(ErebotTextFilter::TYPE_WILDCARD,    $trigger.' *', TRUE);
            $this->_handlers['reload']   =   new ErebotEventHandler(
                                                array($this, 'handleReload'),
                                                'iErebotEventMessageText',
                                                NULL, $filter);
            $this->_connection->addEventHandler($this->_handlers['reload']);
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
        $exitEvent = new ErebotEventExit($this->_connection);
        $this->_connection->dispatchEvent($exitEvent);
        $this->_connection->disconnect($msg);
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

