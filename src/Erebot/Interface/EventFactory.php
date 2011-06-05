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
 *      Interface for a class that can be used
 *      to create events.
 */
interface Erebot_Interface_EventFactory
{
    public function getEventClasses();

    /**
     * Returns the name of the class used to create
     * events for a certain interface.
     *
     * \param string $iface
     *      The name of the interface describing
     *      the type of event.
     *
     * \retval string
     *      Name of the class to use to create events
     *      for the given interface.
     *
     * \retval NULL
     *      Returned when no class has been registered yet
     *      to create events for the given interface.
     *
     * \note
     *      The name of the interface is case-insensitive.
     */
    public function getEventClass($iface);

    public function setEventClasses($events);

    /**
     * Sets the class to use when creating events
     * for a certain interface.
     *
     * \param string $iface
     *      Interface to associate the class with.
     *
     * \param string $cls
     *      Class to use when creating events for that interface.
     *
     * \throw Erebot_InvalidValueException
     *      The given class does not implement the given
     *      interface and therefore cannot be used as a
     *      factory.
     *
     * \note
     *      The name of the interface is case-insensitive.
     */
    public function setEventClass($iface, $cls);

    /**
     * Factory to create an event matching the given interface,
     * passing any additional parameters given to this method
     * to the constructor for that event.
     *
     * \param string $iface
     *      Name of the interface describing
     *      the type of event to create.
     *
     * \note
     *      You may pass additional parameters to this method.
     *      They will be passed as is to the event's constructor.
     *
     * \note
     *      It is not necessary to pass "$this" explicitely
     *      as the first additional parameter to this method,
     *      this factory already takes care of adding it
     *      automatically as all event types require it.
     *
     * \note
     *      This method can also use the same shortcuts as
     *      Erebot_Connection::getEventClass().
     *
     * \note
     *      The name of the interface to use is case-insensitive.
     */
    public function makeEvent($iface /* , ... */);
}
