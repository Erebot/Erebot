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
 *      An abstract Event for WATCH list notifications.
 */
abstract class  Erebot_Event_NotificationAbstract
extends         Erebot_Event_WithSourceTextAbstract
{
    protected $_ident;
    protected $_host;
    protected $_timestamp;

    public function __construct(
        Erebot_Interface_Connection    &$connection,
                                        $source,
                                        $ident,
                                        $host,
                                        $timestamp,
                                        $text
    )
    {
        parent::__construct($connection, $source, $text);
        $this->_ident       = $ident;
        $this->_host        = $host;
        $this->_timestamp   = $timestamp;
    }

    public function getIdent()
    {
        return $this->_ident;
    }

    public function getHostname()
    {
        return $this->_host;
    }

    public function getMask()
    {
        return $this->_source.'!'.$this->_ident.'@'.$this->_host;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }
}

