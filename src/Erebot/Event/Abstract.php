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
 *      An abstract Event.
 */
abstract class  Erebot_Event_Abstract
implements      Erebot_Interface_Event_Base_Generic
{
    /// @TODO add support for these events or remove them...
#    const ON_CHAT           = 30;
#    const ON_CONNECTFAIL    = 50;
#    const ON_DCCSERVER      = 80;
#    const ON_ERROR          = 130;
#    const ON_FILERCVD       = 150;
#    const ON_FILESENT       = 160;
#    const ON_GETFAIL        = 170;
#    const ON_MODE           = 240;  // *
#    const ON_NOSOUND        = 260;
#    const ON_SENDFAIL       = 350;
#    const ON_SERV           = 360;
#    const ON_SERVERMODE     = 370;
#    const ON_SERVEROP       = 380;
#    const ON_SNOTICE        = 390;
#    const ON_WALLOPS        = 440;

    /// Whether the default action should be prevented or not.
    protected $_halt;
    /// Connection the event originated from.
    protected $_connection;

    public function __construct(Erebot_Interface_Connection $connection)
    {
        $this->_halt        = FALSE;
        $this->_connection  = $connection;
    }

    /// \copydoc Erebot_Interface_Event_Base_Generic::getConnection()
    public function getConnection()
    {
        return $this->_connection;
    }

    /// \copydoc Erebot_Interface_Event_Base_Generic::preventDefault()
    public function preventDefault($prevent = NULL)
    {
        $res = $this->_halt;
        if ($prevent !== NULL) {
            if (!is_bool($prevent))
                throw new Erebot_InvalidValueException('Bad prevention value');

            $this->_halt = $prevent;
        }
        return $res;
    }
}

