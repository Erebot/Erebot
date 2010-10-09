<?php

class   ErebotModule_Helper
extends ErebotModuleBase
{
    protected $trigger;
    protected $handler;
    protected $helpTopics;
    protected $helpCallbacks;

    public function reload($flags)
    {
        if ($flags & self::RELOAD_METADATA) {
            $this->addMetadata(self::META_DEPENDS, 'TriggerRegistry');
        }

        if (!($flags & self::RELOAD_INIT)) {
            $registry   =&  $this->connection->getModule('TriggerRegistry',
                                ErebotConnection::MODULE_BY_NAME);
            $match_any  =   ErebotUtils::getVStatic($registry, 'MATCH_ANY');

            $this->connection->removeEventHandler($this->handler);
            $registry->freeTriggers($this->trigger, $match_any);
        }

        if ($flags & self::RELOAD_HANDLERS) {
            $registry   =&  $this->connection->getModule('TriggerRegistry',
                                ErebotConnection::MODULE_BY_NAME);
            $match_any  =   ErebotUtils::getVStatic($registry, 'MATCH_ANY');

            $trigger        = $this->parseString('trigger', 'help');
            $this->trigger  = $registry->registerTriggers($trigger, $match_any);
            if ($this->trigger === NULL)
                throw new Exception($this->translator->gettext(
                    'Could not register Help trigger'));

            $filter         = new ErebotTextFilter($this->mainCfg);
            $filter->addPattern(ErebotTextFilter::TYPE_STATIC, $trigger, TRUE);
            $filter->addPattern(ErebotTextFilter::TYPE_WILDCARD, $trigger.' *', TRUE);
            $this->handler  = new ErebotEventHandler(
                                    array($this, 'handleHelp'),
                                    'iErebotEventMessageText',
                                    NULL, $filter);
            $this->connection->addEventHandler($this->handler);

            // Add help support to Help module.
            // This has to be done by hand, because the module
            // is not registered for this connection yet.
            $this->realRegisterHelpMethod($this, array($this, 'getHelp'));
        }
    }

    public function realRegisterHelpMethod(ErebotModuleBase &$module, $callback)
    {
        $bot =& $this->connection->getBot();
        $moduleName = strtolower($bot->moduleClassToName($module));
        $reflector  = new ReflectionParameter($callback, 0);
        $cls        = $reflector->getClass();
        if ($cls === NULL || !$cls->implementsInterface('iErebotEventMessageCapable'))
            throw new EErebotInvalidValue('Invalid signature');

        $this->helpCallbacks[$moduleName] =& $callback;
        return TRUE;
    }

    public function getHelp(iErebotEventMessageText &$event, $words)
    {
        if ($event instanceof iErebotEventPrivate) {
            $target = $event->getSource();
            $chan   = NULL;
        }
        else
            $target = $chan = $event->getChan();

        $translator = $this->getTranslator($chan);
        $trigger    = $this->parseString('trigger', 'help');

        $bot        =&  $this->connection->getBot();
        $moduleName =   strtolower($bot->moduleClassToName($this));
        $nbArgs     =   count($words);

        // "!help Helper"
        if ($nbArgs == 1 && $words[0] == $moduleName) {
            $modules = array_keys($this->connection->getModules($chan));
            $msg = $translator->gettext('
<b>Usage</b>: "!<var name="trigger"/> &lt;<u>Module</u>&gt; [<u>command</u>]".
Module names must start with an uppercase letter but are not case-sensitive
otherwise.
The following modules are loaded: <for from="modules" item="module">
<b><var name="module"/></b></for>.
');
            $tpl = new ErebotStyling($msg, $translator);
            $tpl->assign('modules', $modules);
            $tpl->assign('trigger', $trigger);
            $this->sendMessage($target, $tpl->render());
            return TRUE;
        }

        if ($nbArgs < 2 || $words[1] != $trigger)
            return FALSE;

        // "!help Helper *" or just "!help"
        $msg = $translator->gettext('
<b>Usage</b>: "!<var name="trigger"/> &lt;<u>Module</u>&gt; [<u>command</u>]"
or "!<var name="trigger"/> &lt;<u>command</u>&gt;".
Provides help about a particular module or command.
Use "!<var name="trigger"/> <var name="this"/>" for a list of currently loaded
modules.
');
        $bot =& $this->connection->getBot();
        $tpl = new ErebotStyling($msg, $translator);
        $tpl->assign('this',    $bot->moduleClassToName($this));
        $tpl->assign('trigger', $trigger);
        $this->sendMessage($target, $tpl->render());
        return TRUE;
    }

    public function handleHelp(iErebotEventMessageText &$event)
    {
        if ($event instanceof iErebotEventPrivate) {
            $target = $event->getSource();
            $chan   = NULL;
        }
        else
            $target = $chan = $event->getChan();

        $text       = preg_split('/\s+/', rtrim($event->getText()));
        $foo        = array_shift($text); // Consume '!help' trigger.
        $translator = $this->getTranslator($chan);

        // Just "!help". Emulate "!help Help help".
        if (!count($text)) {
            $bot =& $this->connection->getBot();
            $text = array($bot->moduleClassToName($this), 'help');
        }

        $moduleName = NULL;
        // If the first letter of the first word is in uppercase,
        // this is a request for help on a module (!help Module).
        $first = substr($text[0][0], 0, 1);
        if ($first == strtoupper($first))
            $moduleName = strtolower(array_shift($text));

        // Got request on a module, check if it exists/has a callback.
        if ($moduleName !== NULL &&
            !isset($this->helpCallbacks[$moduleName])) {
            $msg = $translator->gettext(
                'No such module <b><var name="module"/></b> '.
                'or no help available.');
            $tpl = new ErebotStyling($msg, $translator);
            $tpl->assign('module', $moduleName);
            return $this->sendMessage($target, $tpl->render());
        }

        // Now, use the appropriate callback to handle the request.
        // If the request directly concerns a command (!help command),
        // loop through all callbacks until one handles the request.
        if ($moduleName === NULL)
            $moduleNames = array_map('strtolower', array_keys(
                            $this->connection->getModules($chan)));
        else
            $moduleNames = array($moduleName);

        foreach ($moduleNames as $modName) {
            if (!isset($this->helpCallbacks[$modName]))
                continue;
            $callback = $this->helpCallbacks[$modName];
            $words = $text;
            array_unshift($words, $moduleName);
            if (call_user_func($callback, $event, $words))
                return;
        }

        // No callback handled this request.
        // We assume no help is available.
        $msg = $translator->gettext('No help available on the given '.
                                    'module or command.');
        $tpl = new ErebotStyling($msg, $translator);
        $this->sendMessage($target, $tpl->render());
    }
}

?>
