<?php

class   ErebotModule_PhpFilter
extends ErebotModuleBase
{
    protected $trigger;
    protected $cmdHandler;
    protected $usageHandler;

    public function reload($flags)
    {
        if ($flags & self::RELOAD_METADATA) {
            $this->addMetadata(self::META_DEPENDS, 'TriggerRegistry');
            $this->addMetadata(self::META_DEPENDS, 'Helper');
        }

        if ($flags & self::RELOAD_HANDLERS) {
            $registry   = $this->connection->getModule('TriggerRegistry', ErebotConnection::MODULE_BY_NAME);
            $match_any  = ErebotUtils::getVStatic($registry, 'MATCH_ANY');

            if (!($flags & self::RELOAD_INIT)) {
                $this->connection->removeEventHandler($this->cmdHandler);
                $this->connection->removeEventHandler($this->usageHandler);
                $registry->freeTriggers($this->trigger, $match_any);
            }

            $trigger        = $this->parseString('trigger', 'filter');
            $this->trigger  = $registry->registerTriggers($trigger, $match_any);
            if ($this->trigger === NULL) {
                $translator = $this->getTranslator(FALSE);
                throw new Exception($translator->gettext(
                    'Could not register Filter trigger'));
            }

            $filter2    = new ErebotTextFilter(ErebotTextFilter::TYPE_WILDCARD, $trigger.' & *', TRUE);
            $this->cmdHandler   = new ErebotEventHandler(
                                        array($this, 'handleFilter'),
                                        'iErebotEventMessageText',
                                        NULL, $filter2);
            $this->connection->addEventHandler($this->cmdHandler);

            $filter1    = new ErebotTextFilter();
            $filter1->addPattern(ErebotTextFilter::TYPE_STATIC, $trigger, TRUE);
            $filter1->addPattern(ErebotTextFilter::TYPE_WILDCARD, $trigger.' &', TRUE);
            $this->usageHandler  = new ErebotEventHandler(
                                        array($this, 'handleUsage'),
                                        'iErebotEventMessageText',
                                        NULL, $filter1);
            $this->connection->addEventHandler($this->usageHandler);
            $this->registerHelpMethod(array($this, 'getHelp'));
        }
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
        $trigger    = $this->parseString('trigger', 'filter');

        $bot        =&  $this->connection->getBot();
        $moduleName =   strtolower($bot->moduleClassToName($this));
        $nbArgs     =   count($words);

        if ($nbArgs == 1 && $words[0] == $moduleName) {
            $msg = $translator->gettext('
Provides the <b><var name="trigger"/></b> command which transforms the given
input using some PHP filter.
');
            $formatter = new ErebotStyling($msg);
            $formatter->assign('trigger', $trigger);
            $this->sendMessage($target, $formatter->render());
            return TRUE;
        }

        if ($nbArgs < 2)
            return FALSE;

        if ($words[1] == $trigger) {
            $msg = $translator->gettext('
<b>Usage:</b> !<var name="trigger"/> &lt;<u>filter</u>&gt; &lt;<u>input</u>&gt;.
Transforms the given &lt;<u>input</u>&gt; using the given &lt;<u>filter</u>&gt;.
The following filters are available: <for from="filters" item="filter">
<b><var name="filter"/></b></for>.
');
            $formatter = new ErebotStyling($msg);
            $formatter->assign('trigger', $trigger);
            $formatter->assign('filters', $this->getAllowedFilters());
            $this->sendMessage($target, $formatter->render());
            return TRUE;
        }
    }

    public function getAllowedFilters()
    {
        // By default, allow only filters from the
        // "string." & "convert." families of filters.
        $default    = 'string.*,convert.*';
        $normalizer = create_function('$a', 'return trim($a);');
        $whitelist  = explode(',', $this->parseString('whitelist', $default));
        $whitelist  = array_map($normalizer, $whitelist);
        $filters    = stream_get_filters();
        $allowed    = array();

        $key        = array_search('convert.*', $filters);
        if ($key !== FALSE) {
            unset($filters[$key]);
            $filters[] = 'convert.base64-encode';
            $filters[] = 'convert.base64-decode';
            $filters[] = 'convert.quoted-printable-encode';
            $filters[] = 'convert.quoted-printable-decode';
        }
        unset($filters[$key]);

        foreach ($filters as $filter) {
            $allow = FALSE;
            foreach ($whitelist as $allowedFilter) {
                if (fnmatch($allowedFilter, $filter)) {
                    $allow = TRUE;
                    break;
                }
            }
            if ($allow)
                $allowed[] = $filter;
        }

        return $allowed;
    }

    public function handleUsage(iErebotEventMessageText &$event)
    {
        if ($event instanceof iErebotEventPrivate) {
            $target = $event->getSource();
            $chan   = NULL;
        }
        else
            $target = $chan = $event->getChan();
        $translator = $this->getTranslator($chan);
        $cmd        = $this->usageHandler->getFilters()->getPatterns(
                          ErebotTextFilter::TYPE_STATIC);
        $message    = $translator->gettext('Usage: <b><var name="cmd"/> '.
                '&lt;filter&gt; &lt;text&gt;</b>. Available filters: '.
                '<for from="filters" item="filter">'.
                '<var name="filter"/></for>.');

        $tpl = new ErebotStyling($message);
        $tpl->assign('cmd', '!'.substr($cmd[0], 1));
        $tpl->assign('filters', $this->getAllowedFilters());
        $this->sendMessage($target, $tpl->render());
        return $event->preventDefault(TRUE);
    }

    public function handleFilter(iErebotEventMessageText &$event)
    {
        if ($event instanceof iErebotEventPrivate) {
            $target = $event->getSource();
            $chan   = NULL;
        }
        else
            $target = $chan = $event->getChan();
        $filter     = $event->getText()->getTokens(1, 1);
        $text       = $event->getText()->getTokens(2);
        $translator = $this->getTranslator($chan);

        $allowed    = FALSE;
        foreach ($this->getAllowedFilters() as $allowedFilter) {
            if (fnmatch($allowedFilter, $filter)) {
                $allowed = TRUE;
                break;
            }
        }

        if (!$allowed) {
            $message = $translator->gettext('No such filter "<var name="filter"/>" '.
                                    'or filter blocked.');

            $tpl = new ErebotStyling($message);
            $tpl->assign('filter', $filter);
            $this->sendMessage($target, $tpl->render());
            return $event->preventDefault(TRUE);
        }

        $fp = fopen('php://memory', 'w+');
        stream_filter_append($fp, $filter, STREAM_FILTER_WRITE);
        fwrite($fp, $text);
        rewind($fp);
        $text = stream_get_contents($fp);

        $message = '<b><var name="filter"/></b>: <var name="result"/>';
        $tpl = new ErebotStyling($message);
        $tpl->assign('filter', $filter);
        $tpl->assign('result', $text);
        $this->sendMessage($target, $tpl->render());
        return $event->preventDefault(TRUE);
    }
}

?>
