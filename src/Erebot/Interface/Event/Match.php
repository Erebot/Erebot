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
 *      Interface for an event filter.
 *
 * Subclasses should implement the match() method to provide
 * an algorithm which decides if a given event matches or not.
 */
interface Erebot_Interface_Event_Match
{
    /**
     * Decides whether the given event passes the filter or not.
     *
     * \param $event
     *      An event to try to match to the criterion expressed
     *      by this filter.
     *
     * \retval bool
     *      TRUE if the event matches the criterion, FALSE otherwise.
     */
    public function match(Erebot_Interface_Event_Base_Generic $event);
}

