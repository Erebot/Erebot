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
    /// The connection object this raw event came from.
    protected $_connection;
    /// Raw numeric code.
    protected $_raw;
    /// Source of the raw event.
    protected $_source;
    /// Target of the raw event; this is usually the bot.
    protected $_target;
    /// Content of the raw event.
    protected $_text;

    /// \copydoc Erebot_Interface_Event_Raw::__construct()
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

    /// Destructor.
    public function __destruct()
    {
    }

    /// \copydoc Erebot_Interface_Event_Raw::getConnection()
    public function getConnection()
    {
        return $this->_connection;
    }

    /// \copydoc Erebot_Interface_Event_Raw::getRaw()
    public function getRaw()
    {
        return $this->_raw;
    }

    /// \copydoc Erebot_Interface_Event_Raw::getSource()
    public function getSource()
    {
        return $this->_source;
    }

    /// \copydoc Erebot_Interface_Event_Raw::getTarget()
    public function getTarget()
    {
        return $this->_target;
    }

    /// \copydoc Erebot_Interface_Event_Raw::getText()
    public function getText()
    {
        return $this->_text;
    }
}

