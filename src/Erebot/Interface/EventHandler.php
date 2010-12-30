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
 *      Interface for event handlers.
 *
 * This interface provides the necessary methods to represent
 * a structure capable of handling events from an IRC server.
 */
interface Erebot_Interface_EventHandler
{
    /**
     * Constructs an event handler.
     *
     * \param callback $callback
     *      The callback function/method which will be called
     *      when an event is received which meets the $constraints,
     *      is part of valid $targets and passed the $filters
     *      successfully.
     *
     * \param string|list(string) $constraints
     *      Either a string of array of strings containing
     *      the names of classes/interfaces which should be
     *      considered acceptable events for this handler
     *      to treat. Therefore, it's a list of constraints
     *      on the event's type.
     *
     * \param NULL|Erebot_Interface_EventTarget $targets
     *      (optional) A description of the targets this event handler
     *      will consider valid. This is complementary to the $constraints
     *      parameter and can be used to build whitelists/blacklists
     *      (eg. make the bot react to an event only if it comes from
     *      a trusted source like the bot's administrator).
     *      See the documentation on Erebot_Interface_EventTarget
     *      for more information.
     *      If this is set to NULL (the default), any target is
     *      considered valid.
     *
     * \param NULL|Erebot_Interface_TextFilter|array $filters
     *      (optional) An object or array of objects used to filter events
     *      based on their text (in any). See the documentation on
     *      Erebot_Interface_TextFilter for more information.
     *      If this is set to NULL (the default) or an empty array, any text
     *      is considered valid (ie: no filtering is done). Otherwise, the
     *      event's text must match the criteria defined by that object (or
     *      by any of the objects in case an array was passed).
     */
    public function __construct(
        $callback,
        $constraints,
        Erebot_Interface_EventTarget    $targets    = NULL,
                                        $filters    = NULL
    );

    /**
     * Returns a reference to the callback which was associated
     * with this handler during construction.
     *
     * \retval callback
     *      The callback associated with this handler.
     */
    public function & getCallback();

    /**
     * Returns the constraints on the event's type associated
     * with this handler during construction.
     *
     * \retval mixed
     *      Type constraints for this handler, either
     *      as a string or an array of strings (whichever
     *      was used during construction).
     */
    public function getConstraints();

    /**
     * Returns the constraints on the event's target associated
     * with this handler during construction.
     *
     * \retval Erebot_Interface_EventTarget
     *      An object expressing constraints on targets.
     */
    public function & getTargets();

    /**
     * Returns the constraints on the event's text associated
     * with this handler during construction.
     *
     * \retval Erebot_Interface_TextFilter
     *      An object expressing constraints on an event's text.
     */
    public function & getFilters();

    /**
     * Given an event, this method does its best to handler it.
     *
     * \param Erebot_Interface_Config_Main $config
     *      An object containing the global configuration for the bot.
     *
     * \param Erebot_Interface_Event_Generic $event
     *      An event to try to handle.
     *
     * \note
     *      It is this method's responsability to make appropriate
     *      checks and act upon the result of those checks.
     *      It may for example check that the event matches the
     *      constraints (on type, target and/or content) expressed
     *      by the current handler.
     */
    public function handleEvent(
        Erebot_Interface_Config_Main   &$config,
        Erebot_Interface_Event_Generic &$event
    );
}

