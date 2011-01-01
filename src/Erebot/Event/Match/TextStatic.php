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

class   Erebot_Event_Match_TextStatic
extends Erebot_Event_Match_TextAbstract
{
    public function match(
        Erebot_Interface_Config_Main   &$config,
        Erebot_Interface_Event_Generic &$event
    )
    {
        if (!($event instanceof Erebot_Interface_Event_Text))
            return FALSE;

        $text       = preg_replace('/\s+/', ' ', $event->getText());
        $pattern    = preg_replace('/\s+/', ' ', $this->_pattern);

        // Prefix forbidden.
        if ($this->_requirePrefix === FALSE)
            return ($pattern == $text);

        $matched    = ($text == $config->getCommandsPrefix().$pattern);
        // Prefix required.
        if ($this->_requirePrefix === TRUE)
            return $matched;

        // Prefix allowed.
        return ($matched || $pattern == $text);
    }
}

