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
 *      A filter that compares the content of events
 *      with some static string and matches when
 *      the two are equal.
 */
class TextStatic extends \Erebot\Event\Match\TextAbstract
{
    protected function realMatch($prefix, $text)
    {
        $text       = preg_replace('/\s+/', ' ', $text);
        $pattern    = preg_replace('/\s+/', ' ', (string) $this->pattern);

        // Prefix forbidden.
        if ($this->requirePrefix === false) {
            return ($pattern == $text);
        }

        $matched    = ($text == $prefix.$pattern);
        // Prefix required.
        if ($this->requirePrefix === true) {
            return $matched;
        }

        // Prefix allowed.
        return ($matched || $pattern == $text);
    }
}
