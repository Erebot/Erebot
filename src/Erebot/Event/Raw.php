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
    /// Whether the default action should be prevented or not.
    protected $_halt;

    /**
     * Constructs a raw message.
     *
     * \param Erebot_Interface_Connection $connection
     *      The connection this message came from.
     *
     * \param int $raw
     *      The raw numeric code.
     *
     * \param string $source
     *      The source of the raw message. This will generally be
     *      the name of an IRC server.
     *
     * \param string $target
     *      The target of the raw message. This will generally be
     *      the bot's nickname.
     *
     * \param string $text
     *      The raw content of the message.
     *
     * \note
     *      No attempt is made at parsing the content of the message.
     */
    public function __construct(
        Erebot_Interface_Connection $connection,
                                    $raw,
                                    $source,
                                    $target,
                                    $text
    )
    {
        $this->_halt        = FALSE;
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

