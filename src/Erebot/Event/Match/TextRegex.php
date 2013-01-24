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

/**
 * \brief
 *      A filter that matches the content of an event
 *      based on a regular expression.
 */
class   Erebot_Event_Match_TextRegex
extends Erebot_Event_Match_TextAbstract
{
    /// \copydoc Erebot_Event_Match_TextAbstract::_match()
    protected function _match($prefix, $text)
    {
        return (preg_match((string) $this->_pattern, $text) == 1);
    }
}

