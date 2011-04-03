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
     * \param NULL|Erebot_Interface_Event_Match $filter
     *      (optional) A filter which must be matched for the callback
     *      associated with this handler to be called.
     */
    public function __construct(
                                        $callback,
        Erebot_Interface_Event_Match    $filter = NULL
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
     * Sets the filter associated with this event handler.
     *
     * \param $filter
     *      The new filter associated with this event handler.
     *      Its criterion must sucessfully match the contents of
     *      an event for that event to trigger this event handler's
     *      callback.
     */
    public function setFilter(Erebot_Interface_Event_Match $filter = NULL);

    /**
     * Returns the filter currently associated with this event handler.
     *
     * \retval Erebot_Interface_Event_Match
     *      The current filter associated with this event handler,
     *      or NULL if no filter has been set yet.
     */
    public function getFilter();

    /**
     * Given an event, this method does its best to handler it.
     *
     * \param Erebot_Interface_Event_Base_Generic $event
     *      An event to try to handle.
     *
     * \note
     *      It is this method's responsability to make appropriate
     *      checks and act upon the result of those checks.
     *      It may for example check that the event matches the
     *      filters (on type, target and/or content) associated
     *      with the handler.
     */
    public function handleEvent(Erebot_Interface_Event_Base_Generic $event);
}

