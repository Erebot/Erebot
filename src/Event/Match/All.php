<?php
/*
    This file is part of Erebot, a modular IRC bot written in PHP.

    Copyright © 2010 François Poirotte

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

namespace Erebot\Event\Match;

/**
 * \brief
 *      A filter that groups several (sub-)filters together
 *      and only matches when all sub-filters match.
 */
class All extends \Erebot\Event\Match\CollectionAbstract
{
    public function match(\Erebot\Interfaces\Event\Base\Generic $event)
    {
        foreach ($this->_submatchers as $match) {
            if (!$match->match($event)) {
                return false;
            }
        }
        return true;
    }
}
