<?php

ErebotUtils::incl('../ifaces/eventTargets.php');

/**
 * \brief
 *      A whitelist/blacklist mechanism for targets of an event.
 *
 *  Depending on a set of allow/deny rules, this class can
 *  filter out unwanted events depending on the target they're
 *  being addressed to.
 */
class       ErebotEventTargets
implements  iErebotEventTargets
{
    protected $allow;
    protected $deny;
    protected $order;

    // Documented in the interface.
    public function __construct($order)
    {
        $this->setOrder($order);
        $this->allow    = array();
        $this->deny     = array();
    }

    public function __destruct()
    {
    }

    // Documented in the interface.
    public function setOrder($order)
    {
        if ($order !== self::ORDER_ALLOW_DENY &&
            $order !== self::ORDER_DENY_ALLOW)
            throw new EErebotInvalidValue('Invalid order for allow/deny rules');

        $this->order = $order;
    }

    // Documented in the interface.
    public function getOrder()
    {
        return $this->order;
    }

    // Documented in the interface.
    public function match(iErebotEvent &$event)
    {
        if (!in_array('iErebotEventSource', class_implements($event)) ||
            !in_array('iErebotEventChan', class_implements($event)))
            return TRUE;

        $allowed    = $this->hasMatchingRule($event, $this->allow);
        $denied     = $this->hasMatchingRule($event, $this->deny);

        if ($allowed && !$denied)
            return TRUE;

        if (!$allowed && $denied)
            return FALSE;

        return ($this->order == self::ORDER_DENY_ALLOW);
    }

    protected function hasMatchingRule(&$event, &$ruleset)
    {
        $source = $event->getSource();
        $chan   = $event->getChan();

        foreach ($ruleset as &$rule) {
            if ($rule['nick'] != $source &&
                $rule['nick'] !== self::MATCH_ALL)
                continue;

            if ($rule['chan'] === self::MATCH_PRIVATE &&
                $chan !== NULL)
                continue;

            switch ($rule['chan']) {
                case $chan:
                case self::MATCH_CHANNEL:
                    if ($chan === NULL)
                        continue;
                case self::MATCH_ALL:
                    return TRUE;
            }
        }
        unset($rule);
        return FALSE;
    }

    // Documented in the interface.
    public function addRule($type, $nick = self::MATCH_ALL, $chan = self::MATCH_ALL)
    {
        if (!is_string($nick) && $nick !== self::MATCH_ALL)
            throw new EErebotInvalidValue('Bad nickname filter');

        if (!is_string($chan) && $chan !== self::MATCH_ALL &&
            $chan !== self::MATCH_CHANNEL && $chan !== self::MATCH_PRIVATE)
            throw new EErebotInvalidValue('Bad channel filter');

        switch ($type) {
            case self::TYPE_ALLOW:  $array = 'allow';   break;
            case self::TYPE_DENY:   $array = 'deny';    break;
            default:                throw new EErebotInvalidValue('Invalid rule type');
        }

        array_push($this->$array, array('nick' => $nick, 'chan' => $chan));
    }

    // Documented in the interface.
    public function removeRule($type, $nick = self::MATCH_ALL, $chan = self::MATCH_ALL)
    {
        if (!is_string($nick) && $nick !== self::MATCH_ALL)
            throw new EErebotInvalidValue('Bad nickname filter');

        if (!is_string($chan) && $chan !== self::MATCH_ALL &&
            $chan !== self::MATCH_CHANNEL && $chan !== self::MATCH_PRIVATE)
            throw new EErebotInvalidValue('Bad channel filter');

        switch ($type) {
            case self::TYPE_ALLOW:  $array = 'allow';   break;
            case self::TYPE_DENY:   $array = 'deny';    break;
            default:                throw new EErebotInvalidValue('Invalid rule type');
        }

        $key = array_search(array('nick' =>$nick, 'chan' => $chan), $this->$array);
        if ($key === FALSE)
            throw new EErebotNotFound('No such rule');

        $ref =& $this->$array;
        unset($ref[$key]);
        unset($ref);
    }
}

?>
