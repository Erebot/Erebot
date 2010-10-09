<?php

class   ErebotModule_MiniSed
extends ErebotModuleBase
{
    protected $handler;
    protected $rawHandler;
    protected $chans;

    const REPLACE_PATTERN = '@^[sS]([^\\\\a-zA-Z0-9])(.*\\1.*)\\1$@';

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
            $this->connection->removeEventHandler($this->rawHandler);
        }

        if ($flags & self::RELOAD_HANDLERS) {
            $registry   =&  $this->connection->getModule('TriggerRegistry',
                                ErebotConnection::MODULE_BY_NAME);
            $match_any  =   ErebotUtils::getVStatic($registry, 'MATCH_ANY');

            $filter         = new ErebotTextFilter($this->mainCfg);
            $filter->addPattern(ErebotTextFilter::TYPE_REGEXP, self::REPLACE_PATTERN, FALSE);
            $this->handler  = new ErebotEventHandler(
                                    array($this, 'handleSed'),
                                    'ErebotEventTextChan',
                                    NULL, $filter);
            $this->connection->addEventHandler($this->handler);

            $this->rawHandler   = new ErebotEventHandler(
                                        array($this, 'handleRawText'),
                                        'ErebotEventTextChan',
                                        NULL);
            $this->connection->addEventHandler($this->rawHandler);
        }

        if ($flags & self::RELOAD_MEMBERS)
            $this->chans = array();
    }

    public function handleSed(iErebotEventChan &$event)
    {
        $chan = $event->getChan();
        if (!isset($this->chans[$chan]))
            return;

        $previous = $this->chans[$chan];
        preg_match(self::REPLACE_PATTERN, $event->getText(), $matches);

        $parts  = array();
        $base   = 0;
        $char   = $matches[1];
        $text   = $matches[2];
        while ($text != '') {
            $pos = $base + strcspn($text, '\\'.$char, $base);
            if ($pos >= strlen($text) || $text[$pos] == $char) {
                $parts[]    = substr($text, 0, $pos);
                $text       = substr($text, $pos + 1);
                $base       = 0;
            }

            else
                $base = $pos + 2;
        }

        $nb_parts   = count($parts);
        if ($nb_parts < 2 || $nb_parts > 3)
            return; // Silently ignore invalid patterns

        if (!preg_match('/[a-zA-Z0-9]/', $parts[0]))
            return;

        $pattern    = '@'.str_replace('@', '\\@', $parts[0]).'@'.
                        (isset($parts[2]) ? $parts[2] : '');
        $subject    = $parts[1];

        $replaced   = preg_replace($pattern, $subject, $previous);
        $this->chans[$chan] = $replaced;
        $this->sendMessage($chan, $replaced);

        return FALSE;
    }

    public function handleRawText(iErebotEvent &$event)
    {
        $this->chans[$event->getChan()] = $event->getText();
    }
}

?>
