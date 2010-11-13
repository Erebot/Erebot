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
 *      An abstract Event which applies to a channel,
 *      has a source, a target and even some text.
 */
abstract class  ErebotEventWithChanSourceTargetAndText
extends         ErebotEventWithChanSourceAndTarget
implements      iErebotEventText
{
    protected $_text;

    public function __construct(
        iErebotConnection   &$connection,
                            $chan,
                            $source,
                            $target,
                            $text
    )
    {
        parent::__construct($connection, $chan, $source, $target);
        $this->_text = new ErebotTextWrapper($text);
    }

    // Documented in the interface.
    public function & getText()
    {
        return $this->_text;
    }
}

