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

class       Erebot_Event_Match_Chan
implements  Erebot_Interface_Event_Match,
            Erebot_Interface_Event_Chan
{
    protected $_chan;

    public function __construct($chan = NULL)
    {
        if ($chan !== NULL && !is_string($chan))
            throw new Erebot_InvalidValueException('Not a channel');

        $this->_chan = $chan;
    }

    public function & getChan()
    {
        return $this->_chan;
    }

    public function match(
        Erebot_Interface_Config_Main   &$config,
        Erebot_Interface_Event_Generic &$event
    )
    {
        if (!($event instanceof Erebot_Interface_Event_Chan))
            return FALSE;

        if ($this->_chan === NULL)
            return TRUE;

        return ($event->getChan() == $this->_chan);
    }
}

