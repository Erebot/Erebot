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
 *      An abstract Event with a source and a target.
 */
abstract class  ErebotEventWithSourceAndTarget
extends         ErebotEvent
implements      iErebotEventSource,
                iErebotEventTarget
{
    protected $_source;
    protected $_target;

    public function __construct(
        iErebotConnection  &$connection,
                            $source,
                            $target
    )
    {
        parent::__construct($connection);
        $this->_source  =   ErebotUtils::extractNick($source, FALSE);
        $this->_target  =&  $target;
    }
    
    // Documented in the interface.
    public function & getSource()
    {
        return $this->_source;
    }

    // Documented in the interface.
    public function & getTarget()
    {
        return $this->_target;
    }
}

