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
 *      Triggered when the bot has determined
 *      what features the IRC server supports.
 */
class ServerCapabilities extends \Erebot\Event\AbstractEvent implements
    \Erebot\Interfaces\Event\ServerCapabilities
{
    /// Module containing the server's capabilities.
    protected $_module;

    /**
     * Constructs a new event dealing with
     * server capabilities.
     *
     * \param Erebot::Interfaces::Connection $connection
     *      The connection this event relates to.
     *
     * \param Erebot::Module::Base $module
     *      The module containing information about
     *      the server's capabilities.
     */
    public function __construct(
        \Erebot\Interfaces\Connection $connection,
        \Erebot\Module\Base $module
    ) {
        parent::__construct($connection);
        $this->_module = $module;
    }

    public function getModule()
    {
        return $this->_module;
    }
}
