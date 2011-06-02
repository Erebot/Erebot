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
 */
interface Erebot_Interface_EventDispatcher
{
    /**
     * Registers a raw handler on this connection.
     *
     * \param Erebot_Interface_RawHandler $handler
     *      The handler to register.
     */
    public function addRawHandler(Erebot_Interface_RawHandler $handler);

    /**
     * Unregisters a raw handler on this connection.
     *
     * \param Erebot_Interface_RawHandler $handler
     *      The handler to unregister.
     *
     * \throw Erebot_NotFoundException
     *      Thrown when the given handler could not be found,
     *      such as when it was not registered on this connection.
     */
    public function removeRawHandler(Erebot_Interface_RawHandler $handler);

    /**
     * Registers an event handler on this connection.
     *
     * \param Erebot_Interface_EventHandler $handler
     *      The handler to register.
     */
    public function addEventHandler(Erebot_Interface_EventHandler $handler);

    /**
     * Unregisters an event handler on this connection.
     *
     * \param Erebot_Interface_EventHandler $handler
     *      The handler to unregister.
     *
     * \throw Erebot_NotFoundException
     *      Thrown when the given handler could not be found,
     *      such as when it was not registered on this connection.
     */
    public function removeEventHandler(Erebot_Interface_EventHandler $handler);

    /**
     * Dispatches the given event to handlers
     * which have been registered for this type of event.
     *
     * \param Erebot_Interface_Event_Base_Generic $event
     *      An event to dispatch.
     *
     * \note
     *      For the purposes of this method, raw messages (objects
     *      implementing the Erebot_Interface_Event_Raw interface)
     *      are also considered to be "events".
     */
    public function dispatch(Erebot_Interface_Event_Base_Generic $event);
}
