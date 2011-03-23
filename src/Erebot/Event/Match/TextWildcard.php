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
 *      A filter that matches the content of an event
 *      based on some wildcard pattern.
 *
 * \note
 *      Valid wildcard characters are "?" (any character),
 *      "*" (any string, even an empty one), "&" (any word).
 *
 * \note
 *      For the purpose of this filter, a "word" is a
 *      sequence of characters that starts at the beginning
 *      of the string or is preceded with a space (U+0020)
 *      and stops at the first space (U+0020) or at the end
 *      of the string (whichever comes first).
 */
class   Erebot_Event_Match_TextWildcard
extends Erebot_Event_Match_TextAbstract
{
    // Documented in the parent class.
    protected function _match($prefix, $text)
    {
        $translationTable = array(
            '\\*'   => '.*',
            '\\?'   => '.',
            '\\\\&' => '&',
            '&'     => '[^\\040]+',
        );
        $text       = preg_replace('/\s+/', ' ', $text);
        $pattern    = preg_replace('/\s+/', ' ', (string) $this->_pattern);
        $prefixPattern = '';
        if ($this->_requirePrefix !== FALSE) {
            $prefixPattern = '(?:'.preg_quote($prefix).')';
            if ($this->_requirePrefix === NULL)
                $prefixPattern .= '?';
        }
        $pattern    =   "#^".$prefixPattern.strtr(
            preg_quote($pattern, '#'),
            $translationTable
        )."$#i";
        return (preg_match($pattern, $text) == 1);
    }
}

