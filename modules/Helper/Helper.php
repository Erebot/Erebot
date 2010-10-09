<?php

class   ErebotModule_Helper
extends ErebotModuleBase
{
    protected $trigger;
    protected $handler;
    protected $helpTopics;

    public function reload($flags)
    {
        $registry   =&  $this->connection->getModule('TriggerRegistry', ErebotConnection::MODULE_BY_NAME);
        $match_any  =   ErebotUtils::getVStatic($registry, 'MATCH_ANY');

        if (!($flags & self::RELOAD_INIT)) {
            $this->connection->removeEventHandler($this->handler);
            $registry->freeTriggers($this->trigger, $match_any);
        }

        if ($flags & self::RELOAD_HANDLERS) {
            // We can't use parseTrigger here because the module
            // is not registered for this connection yet.
            // So we set everything up and add the help stuff manually.
            $trigger        = $this->parseString('trigger', 'help');
            $this->trigger  = $registry->registerTriggers($trigger, $match_any);
            if ($this->trigger === NULL) {
                $translator = $this->getTranslator(FALSE);
                throw new Exception($translator->gettext(
                    'Could not register Help trigger'));
            }

            $filter         = new ErebotTextFilter();
            $filter->addPattern(ErebotTextFilter::TYPE_STATIC, $trigger, TRUE);
            $filter->addPattern(ErebotTextFilter::TYPE_WILDCARD, $trigger.' *', TRUE);
            $this->handler  = new ErebotEventHandler(
                                    array($this, 'handleHelp'),
                                    ErebotEvent::ON_TEXT,
                                    NULL, $filter);
            $this->connection->addEventHandler($this->handler);
            // Add help support to module.
            $this->addSomeHelpTopic($this, $trigger);
        }
    }

    public function addSomeHelpTopic(&$module, $trigger)
    {
        $this->helpTopics[strtolower($trigger)] =& $module;
    }

    public function getHelp($trigger, $chan)
    {
        $registry   =&  $this->connection->getModule('TriggerRegistry',
                            ErebotConnection::MODULE_BY_NAME);
        $triggers   =   $registry->getTriggers($this->trigger);
        $any_chan   =   ErebotUtils::getVStatic($registry, 'MATCH_ANY');
        $translator =   $this->getTranslator($chan);

        if ($chan === NULL)
            $chan = $any_chan;

        if (in_array($trigger, $triggers)) {
            $commands = array();
            $msg = $translator->gettext("
Provides help about available commands.
Use '!help <command>' to get help on a specific command.
The following commands are currently available: %s.
");

            try {
                $triggers = $registry->getChanTriggers($chan);
                foreach ($triggers as $group)
                    $commands = array_merge($commands, $group);
            }
            catch (EErebotNotFound $e) {}

            try {
                $triggers = $registry->getChanTriggers($any_chan);
                foreach ($triggers as $group)
                    $commands = array_merge($commands, $group);
            }
            catch (EErebotNotFound $e) {}

            $commands = array_unique($commands);
            sort($commands);
            return sprintf($msg, implode(', ', $commands));
        }
    }

    public function handleHelp(ErebotEvent &$event)
    {
        $text       = preg_split('/\s+/', rtrim($event->getText()));
        $chan       = $event->getChan();
        $target     = $event->getTarget();
        $foo        = array_shift($text); // Consume '!help' trigger.
        $translator = $this->getTranslator($chan);

        if (!count($text)) {
            $registry   =&  $this->connection->getModule('TriggerRegistry',
                                ErebotConnection::MODULE_BY_NAME);
            $triggers   =   $registry->getTriggers($this->trigger);
            if (!count($triggers))
                throw EErebot('Expected exactly 1 trigger, but none was found');
            $trigger    =   $triggers[0];
        }
        else
            $trigger = array_shift($text);

        $help = FALSE;
        if (isset($this->helpTopics[$trigger]) &&
            is_callable(array($this->helpTopics[$trigger], 'getHelp')))
            $help = $this->helpTopics[$trigger]->getHelp($trigger, $chan);

        if ($help === FALSE) {
            $message = $translator->gettext('No help available on "<var name="topic"/>".');
            $tpl = new ErebotStyling($message);
            $tpl->assign('topic', $trigger);

            $this->sendMessage($target, $tpl->render());
            return;
        }

        $this->sendMessage($target, $help);
    }
}

?>
