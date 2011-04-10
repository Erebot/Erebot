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
 *      Interface for an event which has a source.
 */
interface   Erebot_Interface_Event_Base_Source
extends     Erebot_Interface_Event_Base_Generic
{
    /**
     * Returns the source of the current message.
     * This will generally be some user's nickname
     * or the name of an IRC server.
     *
     * \retval string
     *      The source of this message.
     */
    public function getSource();
}
