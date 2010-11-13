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

/**
 * \brief
 *      A whitelist/blacklist mechanism for targets of an event.
 *
 *  Depending on a set of allow/deny rules, this class can
 *  filter out unwanted events depending on the target they're
 *  being addressed to.
 */
class       Erebot_EventTarget
implements  Erebot_Interface_EventTarget
{
    protected $_allow;
    protected $_deny;
    protected $_order;

    // Documented in the interface.
    public function __construct($order)
    {
        $this->setOrder($order);
        $this->_allow   = array();
        $this->_deny    = array();
    }

    public function __destruct()
    {
    }

    // Documented in the interface.
    public function setOrder($order)
    {
        if ($order !== self::ORDER_ALLOW_DENY &&
            $order !== self::ORDER_DENY_ALLOW)
            throw new Erebot_InvalidValueException('Invalid order for allow/deny rules');

        $this->_order = $order;
    }

    // Documented in the interface.
    public function getOrder()
    {
        return $this->_order;
    }

    // Documented in the interface.
    public function match(Erebot_Interface_Event_Generic &$event)
    {
        if (!in_array('iErebotEventSource', class_implements($event)) ||
            !in_array('iErebotEventChan', class_implements($event)))
            return TRUE;

        $allowed    = $this->hasMatchingRule($event, $this->_allow);
        $denied     = $this->hasMatchingRule($event, $this->_deny);

        if ($allowed && !$denied)
            return TRUE;

        if (!$allowed && $denied)
            return FALSE;

        return ($this->_order === self::ORDER_DENY_ALLOW);
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
    public function addRule(
        $type,
        $nick = self::MATCH_ALL,
        $chan = self::MATCH_ALL
    )
    {
        if (!is_string($nick) && $nick !== self::MATCH_ALL)
            throw new Erebot_InvalidValueException('Bad nickname filter');

        if (!is_string($chan) && $chan !== self::MATCH_ALL &&
            $chan !== self::MATCH_CHANNEL && $chan !== self::MATCH_PRIVATE)
            throw new Erebot_InvalidValueException('Bad channel filter');

        switch ($type) {
            case self::TYPE_ALLOW:
                $array = '_allow';
                break;

            case self::TYPE_DENY:
                $array = '_deny';
                break;

            default:
                throw new Erebot_InvalidValueException('Invalid rule type');
        }

        array_push($this->$array, array('nick' => $nick, 'chan' => $chan));
    }

    // Documented in the interface.
    public function removeRule(
        $type,
        $nick = self::MATCH_ALL,
        $chan = self::MATCH_ALL
    )
    {
        if (!is_string($nick) && $nick !== self::MATCH_ALL)
            throw new Erebot_InvalidValueException('Bad nickname filter');

        if (!is_string($chan) && $chan !== self::MATCH_ALL &&
            $chan !== self::MATCH_CHANNEL && $chan !== self::MATCH_PRIVATE)
            throw new Erebot_InvalidValueException('Bad channel filter');

        switch ($type) {
            case self::TYPE_ALLOW:
                $array = '_allow';
                break;

            case self::TYPE_DENY:
                $array = '_deny';
                break;

            default:
                throw new Erebot_InvalidValueException('Invalid rule type');
        }

        $key = array_search(
            array('nick' => $nick, 'chan' => $chan),
            $this->$array
        );
        if ($key === FALSE)
            throw new Erebot_NotFoundException('No such rule');

        $ref =& $this->$array;
        unset($ref[$key]);
        unset($ref);
    }
}

