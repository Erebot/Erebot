<?php
/*
    This file is part of Erebot, a modular IRC bot written in PHP.

    Copyright © 2010 François Poirotte

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

namespace Erebot\Event;

/**
 * \brief
 *      A class representing a numeric event.
 */
class Numeric implements \Erebot\Interfaces\Event\Numeric
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
     * \param Erebot::Interfaces::Connection $connection
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
        \Erebot\Interfaces\Connection $connection,
        $numeric,
        $source,
        $target,
        $text
    ) {
        $this->_halt        = FALSE;
        $this->_connection  = $connection;
        $this->_numeric     = $numeric;
        $this->_source      = $source;
        $this->_target      = $target;
        $this->_text        = new \Erebot\TextWrapper((string) $text);
    }

    /// Destructor.
    public function __destruct()
    {
    }

    public function getConnection()
    {
        return $this->_connection;
    }

    public function getCode()
    {
        return $this->_numeric;
    }

    public function getSource()
    {
        return $this->_source;
    }

    public function getTarget()
    {
        return $this->_target;
    }

    public function getText()
    {
        return $this->_text;
    }

    public function preventDefault($prevent = NULL)
    {
        $res = $this->_halt;
        if ($prevent !== NULL) {
            if (!is_bool($prevent))
                throw new \Erebot\InvalidValueException('Bad prevention value');

            $this->_halt = $prevent;
        }
        return $res;
    }
}
