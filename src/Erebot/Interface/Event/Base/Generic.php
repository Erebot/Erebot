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
 *      Interface for a generic Event.
 */
interface Erebot_Interface_Event_Base_Generic
{
    /**
     * Returns the connection this event came from.
     * This is the same object as that passed during construction.
     *
     * \retval Erebot_Interface_Connection
     *      The connection this event came from.
     */
    public function getConnection();

    /**
     * Prevents the default action associated
     * with this type of event from occuring.
     *
     * \param mixed $prevent
     *      (optional) Whether the default action should be prevented (TRUE)
     *      or not (FALSE). You may also pass the value NULL to retrieve
     *      the current value without modifying it.
     *
     * \retval bool
     *      Previous value (if modified by this call), or current value
     *      (if the value NULL was passed to $prevent).
     */
    public function preventDefault($prevent = NULL);
}

