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

namespace Erebot;

/**
 * \brief
 *      A class that holds a reference to an IRC numeric.
 *
 * This class holds a reference to an IRC numeric using
 * its name (eg. "RPL_WELCOME"). The actual numeric code
 * associated with this reference may vary dynamically depending
 * on the numeric profile currently loaded for the connection.
 */
class NumericReference implements \Erebot\Interfaces\NumericReference
{
    /// IRC connection this reference is valid for.
    protected $connection;

    /// Name of the numeric (eg. "RPL_WELCOME").
    protected $name;

    /**
     * Constructs a new reference to a numeric.
     *
     * \param Erebot::Interfaces::Connection $connection
     *      Connection object this reference is valid for.
     *
     * \param string $name
     *      Name associated with the numeric this object
     *      should reference (such as "RPL_WELCOME").
     */
    public function __construct(
        \Erebot\Interfaces\Connection $connection,
        $name
    ) {
        $this->connection  = $connection;
        $this->name        = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        $profile = $this->connection->getNumericProfile();
        return $profile[$this->name];
    }
}
