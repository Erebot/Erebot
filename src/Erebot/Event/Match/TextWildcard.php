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

class   Erebot_Event_Match_TextWildcard
extends Erebot_Event_Match_TextAbstract
{
    protected function _match($prefix, $text)
    {
        $translationTable = array(
            '\\*'   => '.*',
            '\\?'   => '.',
            '\\\\&' => '&',
            '&'     => '[^\\040]+',
        );
        $text       = preg_replace('/\s+/', ' ', $text);
        $pattern    = preg_replace('/\s+/', ' ', $this->_pattern);
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

