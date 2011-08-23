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
 *      Triggered when the ServerCapabilities finishes determining
 *      what features the IRC server supports.
 */
class       Erebot_Event_ServerCapabilities
extends     Erebot_Event_Abstract
implements  Erebot_Interface_Event_ServerCapabitilies
{
    protected $_module;

    public function __construct(
        Erebot_Interface_Connection $connection,
        Erebot_Module_Base          $module
    )
    {
        parent::__construct($connection);
        $this->_module = $module;
    }

    public function getModule()
    {
        return $this->_module;
    }
}

