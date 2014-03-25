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
    protected $connection;
    /// Numeric code.
    protected $numeric;
    /// Source of the numeric event.
    protected $source;
    /// Target of the numeric event; this is usually the bot.
    protected $target;
    /// Content of the numeric event.
    protected $text;
    /// Whether the default action should be prevented or not.
    protected $halt;

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
        $this->halt         = false;
        $this->connection   = $connection;
        $this->numeric      = $numeric;
        $this->source       = $source;
        $this->target       = $target;
        $this->text         = new \Erebot\TextWrapper((string) $text);
    }

    /// Destructor.
    public function __destruct()
    {
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function getCode()
    {
        return $this->numeric;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function getText()
    {
        return $this->text;
    }

    public function preventDefault($prevent = null)
    {
        $res = $this->halt;
        if ($prevent !== null) {
            if (!is_bool($prevent)) {
                throw new \Erebot\InvalidValueException('Bad prevention value');
            }

            $this->halt = $prevent;
        }
        return $res;
    }
}
