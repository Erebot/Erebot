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
 *      An abstract Event with a source, a target and some text.
 */
abstract class  Erebot_Event_WithSourceTargetTextAbstract
extends         Erebot_Event_WithSourceTargetAbstract
implements      Erebot_Interface_Event_Base_Text
{
    /// Content of this event.
    protected $_text;

    public function __construct(
        Erebot_Interface_Connection $connection,
                                    $source,
                                    $target,
                                    $text
    )
    {
        parent::__construct($connection, $source, $target);
        $this->_text = new Erebot_TextWrapper((string) $text);
    }
    
    /// \copydoc Erebot_Interface_Event_Base_Text::getText()
    public function getText()
    {
        return $this->_text;
    }
}

