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
 *      A class representing a raw numeric event.
 */
class       Erebot_Event_Raw
implements  Erebot_Interface_Event_Raw
{
    protected $_connection;
    protected $_raw;
    protected $_source;
    protected $_target;
    protected $_text;

    // Documented in the interface.
    public function __construct(
        Erebot_Interface_Connection $connection,
                                    $raw,
                                    $source,
                                    $target,
                                    $text
    )
    {
        $this->_connection  = $connection;
        $this->_raw         = $raw;
        $this->_source      = $source;
        $this->_target      = $target;
        $this->_text        = new Erebot_TextWrapper((string) $text);
    }

    public function __destruct()
    {
    }

    // Documented in the interface.
    public function getConnection()
    {
        return $this->_connection;
    }

    // Documented in the interface.
    public function getRaw()
    {
        return $this->_raw;
    }

    // Documented in the interface.
    public function getSource()
    {
        return $this->_source;
    }

    // Documented in the interface.
    public function getTarget()
    {
        return $this->_target;
    }

    // Documented in the interface.
    public function getText()
    {
        return $this->_text;
    }
}

