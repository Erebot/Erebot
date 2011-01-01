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

class       Erebot_Event_Match_InstanceOf
implements  Erebot_Interface_Event_Match
{
    protected $_type;

    public function __construct($type)
    {
        if (is_object($type))
            $type = get_class($type);

        if (!is_string($type))
            throw new Erebot_InvalidValueException('Not a valid type');

        $this->_type = $type;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function match(
        Erebot_Interface_Config_Main   &$config,
        Erebot_Interface_Event_Generic &$event
    )
    {
        return ($event instanceof $this->_type);
    }
}

