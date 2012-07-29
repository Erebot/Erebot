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
 *      A class representing a numeric event.
 */
class       Erebot_Event_Numeric
implements  Erebot_Interface_Event_Numeric
{
    /// The connection object this numeric event came from.
    protected $_connection;
    /// Numeric code.
    protected $_numeric;
    /// Source of the numeric event.
    protected $_source;
    /// Target of the numeric event; this is usually the bot.
    protected $_target;
    /// Content of the numeric event.
    protected $_text;
    /// Whether the default action should be prevented or not.
    protected $_halt;

    /**
     * Constructs a numeric event.
     *
     * \param Erebot_Interface_Connection $connection
     *      The connection this message came from.
     *
     * \param int $numeric
     *      The numeric code for the message.
     *
     * \param string $source
     *      The source of the numeric message. This will generally be
     *      the name of an IRC server.
     *
     * \param string $target
     *      The target of the numeric message. This will generally be
     *      the bot's nickname.
     *
     * \param string $text
     *      The numeric content of the message.
     *
     * \note
     *      No attempt is made at parsing the content of the message.
     */
    public function __construct(
        Erebot_Interface_Connection $connection,
                                    $numeric,
                                    $source,
                                    $target,
                                    $text
    )
    {
        $this->_halt        = FALSE;
        $this->_connection  = $connection;
        $this->_numeric     = $numeric;
        $this->_source      = $source;
        $this->_target      = $target;
        $this->_text        = new Erebot_TextWrapper((string) $text);
    }

    /// Destructor.
    public function __destruct()
    {
    }

    /// \copydoc Erebot_Interface_Event_Numeric::getConnection()
    public function getConnection()
    {
        return $this->_connection;
    }

    /// \copydoc Erebot_Interface_Event_Numeric::getCode()
    public function getCode()
    {
        return $this->_numeric;
    }

    /// \copydoc Erebot_Interface_Event_Numeric::getSource()
    public function getSource()
    {
        return $this->_source;
    }

    /// \copydoc Erebot_Interface_Event_Numeric::getTarget()
    public function getTarget()
    {
        return $this->_target;
    }

    /// \copydoc Erebot_Interface_Event_Numeric::getText()
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

