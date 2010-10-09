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
 *      Interface to filter events based on their target.
 *
 * This interface provides the necessary methods
 * to filter events based on their target.
 */
interface iErebotEventTargets
{
    /**
     * Test all allow declarations first, then all deny declarations.
     * If only an allow declaration matches, the event matches.
     * If only a deny declaration matches, the event does not match.
     * If both an allow and a deny declaration match, the event does not match.
     * If no declaration matches, the event does not match.
     */
    const ORDER_ALLOW_DENY  = 0;

    /**
     * Test all deny declarations first, then all allow declarations.
     * If only an allow declaration matches, the event matches.
     * If only a deny declaration matches, the event does not match.
     * If both an allow and a deny declaration match, the event matches.
     * If no declaration matches, the event matches.
     */
    const ORDER_DENY_ALLOW  = 1;

    /// Used to add or remove an allow declaration.
    const TYPE_ALLOW        = 0;

    /// Used to add or remove a deny declaration.
    const TYPE_DENY         = 1;

    /// Matches a message sent in a private query.
    const MATCH_PRIVATE     = 0;

    /// Matches a message sent in a channel.
    const MATCH_CHANNEL     = 1;

    /// Matches a message, no matter where.
    const MATCH_ALL         = 2;

    /**
     * Constructs a target for event matching.
     *
     * \param $order
     *      Controls the order in which declarations are processed.
     *      See iErebotEventTargets::setOrder() for acceptable values.
     */
    public function __construct($order);

    /**
     * Sets the order in which declarations are processed.
     *
     * \param $order
     *      Either iErebotEventTargets::ORDER_ALLOW_DENY to process
     *      allow declarations before deny declarations,
     *      or iErebotEventTargets::ORDER_DENY_ALLOW to process
     *      deny declarations first, then allow declarations.
     */
    public function setOrder($order);

    /**
     * Returns the order in which declarations are processed.
     *
     * \return
     *      Returns one of the iErebotEventTargets::ORDER_*
     *      constants to indicate the order in which declarations
     *      are processed, according to the last call to
     *      iErebotEventTargets::setOrder() or the order set
     *      at construction time.
     */
    public function getOrder();

    /**
     * Tests whether the given $event matches one of the allow
     * targets declared in this instance.
     *
     * \param $event
     *      An object to test, implementing the iErebotEvent
     *      interface.
     *
     * \return
     *      Returns TRUE if the $event matches a target or
     *      FALSE otherwise.
     */
    public function match(iErebotEvent &$event);

    /**
     * Adds a matching rule to this instance.
     *
     * \param $type
     *      The type of rule to add (allow or deny).
     *      Pass one of iErebotEventTargets::TYPE_ALLOW
     *      or iErebotEventTargets::TYPE_DENY for this
     *      parameter.
     *
     * \param $nick
     *      The specific nickname to accept/reject.
     *      The default (iErebotEventTargets::MATCH_ALL)
     *      accepts/rejects every nick depending on the
     *      order set during construction or with the
     *      iErebotEventTargets::setOrder() method.
     *
     * \param $chan
     *      The specific channel to accept/reject.
     *      The default (iErebotEventTargets::MATCH_ALL)
     *      accepts/rejects every channel depending on
     *      the order set during construction or with
     *      the iErebotEventTargets::setOrder() method.
     */
    public function addRule(
        $type,
        $nick = self::MATCH_ALL,
        $chan = self::MATCH_ALL
    );

    /**
     * Removes a matching rule from this instance.
     *
     * \param $type
     *      The type of rule to remove (allow or deny).
     *      Pass one of iErebotEventTargets::TYPE_ALLOW
     *      or iErebotEventTargets::TYPE_DENY for this
     *      parameter.
     *
     * \param $nick
     *      The specific nickname which was accepted/rejected.
     *
     * \param $chan
     *      The specific channel which was accepted/rejected.
     *
     * \note
     *      You MUST call iErebotEventTargets::removeRule()
     *      with exactly the same parameters you passed when
     *      calling iErebotEventTargets::addRule() for it to
     *      have any effect.
     */
    public function removeRule(
        $type,
        $nick = self::MATCH_ALL,
        $chan = self::MATCH_ALL
    );
}

