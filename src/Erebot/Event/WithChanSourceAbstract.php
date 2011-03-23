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
 *      An abstract Event which has a source and applies to a channel.
 */
abstract class  Erebot_Event_WithChanSourceAbstract
extends         Erebot_Event_Abstract
implements      Erebot_Interface_Event_Chan,
                Erebot_Interface_Event_Source
{
    protected $_chan;
    protected $_source;

    public function __construct(
        Erebot_Interface_Connection    &$connection,
                                        $chan,
                                        $source
    )
    {
        parent::__construct($connection);
        $this->_chan    = $chan;
        $this->_source  = new Erebot_Identity($source);
    }

    // Documented in the interface.
    public function getChan()
    {
        return $this->_chan;
    }

    // Documented in the interface.
    public function getSource()
    {
        return $this->_source;
    }
}

