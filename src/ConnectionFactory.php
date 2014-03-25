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
 *      Connection factory.
 */
class ConnectionFactory implements \Erebot\Interfaces\ConnectionFactory
{
    /// Class to use to create connections.
    protected $connectionCls;

    /// Mapping of event interfaces to their factory.
    protected $eventClasses;

    /**
     * Initializes the factory.
     *
     * \param string $connectionCls
     *      The name of the class to use to create new connections.
     *
     * \param array $eventClasses
     *      An associative array which maps interface names
     *      to the class to use to create events with that
     *      interface.
     */
    public function __construct($connectionCls, $eventClasses)
    {
        $this->connectionCls    = $connectionCls;
        $this->eventClasses     = $eventClasses;
    }

    public function newConnection($bot, $config)
    {
        $connectionCls = $this->connectionCls;
        return new $connectionCls($bot, $config, $this->eventClasses);
    }
}
