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
 *      An abstract Event which represents a usermode change
 *      on a channel.
 */
abstract class  Erebot_Event_ChanUserModeAbstract
extends         Erebot_Event_WithChanSourceTargetAbstract
{
    /**
     * Returns a description of the mode this event
     * refers to, including any leading operator.
     *
     * \retval string
     *      Textual representation of this mode,
     *      eg. "+o".
     */
    public function getMode()
    {
        return self::MODE_PREFIX . self::MODE_LETTER;
    }
}

