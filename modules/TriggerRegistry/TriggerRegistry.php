<?php

class   ErebotModule_TriggerRegistry
extends ErebotModuleBase
{
    protected $triggers;

    const MATCH_ANY = '*';

    public function reload($flags)
    {
        if ($flags & self::RELOAD_MEMBERS) {
            $this->triggers = array(self::MATCH_ANY => array());
        }
    }

    protected function containsRecursive(&$array, &$value)
    {
        if (!is_array($array))
            return FALSE;

        if (in_array($value, $array))
            return TRUE;

        foreach ($array as &$sub)
            if ($this->containsRecursive($sub, $value))
                return TRUE;
        return FALSE;
    }

    public function registerTriggers($triggers, $channel)
    {
        if (!is_array($triggers))
            $triggers = array($triggers);

        if (!is_string($channel)) {
            $translator = $this->getTranslator(FALSE);
            throw new EErebotInvalidValue($translator->gettext(
                'Invalid channel'));
        }

        foreach ($triggers as &$trigger) {
            if ($channel != self::MATCH_ANY && isset($this->triggers[$channel]))
                if ($this->containsRecursive($this->triggers[$channel], $trigger))
                    return NULL;

            if ($this->containsRecursive($this->triggers[self::MATCH_ANY], $trigger))
                return NULL;
        }
        unset($trigger);

        $this->triggers[$channel][] = $triggers;
        end($this->triggers[$channel]);
        return $channel.' '.key($this->triggers[$channel]);
    }

    public function freeTriggers($token)
    {
        $translator = $this->getTranslator(FALSE);

        if (!is_string($token))
            throw new EErebotInvalidValue($translator->gettext(
                'Invalid token'));

        list($chan, $pos) = explode(' ', $token);

        if (!isset($this->triggers[$chan][$pos]))
            throw new EErebotNotFound($translator->gettext(
                'No such triggers'));

        unset($this->triggers[$chan][$pos]);
    }

    public function getChanTriggers($chan)
    {
        if (!isset($this->triggers[$chan])) {
            $translator = $this->getTranslator(FALSE);
            throw new EErebotNotFound(sprintf($translator->gettext(
                'No triggers found for channel "%s"'), $chan));
        }

        return $this->triggers[$chan];
    }

    public function getTriggers($token)
    {
        list($chan, $pos) = explode(' ', $token);

        if (!isset($this->triggers[$chan][$pos])) {
            $translator = $this->getTranslator(FALSE);
            throw new EErebotNotFound($translator->gettext(
                'No such triggers'));
        }

        return $this->triggers[$chan][$pos];
    }
}

?>
