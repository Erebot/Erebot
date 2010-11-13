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
 *      An abstract Event whose target is an IRC mask (nick!ident\@host).
 */
abstract class  ErebotEventChanUserModeMaskBase
extends         ErebotEventChanUserModeBase
implements      iErebotEventTargetNick
{
    // Documented in the interface.
    public function getTargetNick()
    {
        $nick = ErebotUtils::extractNick($this->target);
        return  (strpos($nick, '?') === FALSE &&
                strpos($nick, '*') === FALSE) ?
                $nick : NULL;
    }
}

