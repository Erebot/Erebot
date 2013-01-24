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

/**
 * \brief
 *      Connection factory.
 */
class       Erebot_ConnectionFactory
implements  Erebot_Interface_ConnectionFactory
{
    /// Class to use to create connections.
    protected $_connectionCls;

    /// Mapping of event interfaces to their factory.
    protected $_eventClasses;

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
        $this->_connectionCls   = $connectionCls;
        $this->_eventClasses    = $eventClasses;
    }

    /// \copydoc Erebot_Interface_ConnectionFactory::newConnection()
    public function newConnection($bot, $config)
    {
        $connectionCls = $this->_connectionCls;
        return new $connectionCls($bot, $config, $this->_eventClasses);
    }
}
