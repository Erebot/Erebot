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
 *      Triggered when the bot has determined
 *      what features the IRC server supports.
 */
class       Erebot_Event_ServerCapabilities
extends     Erebot_Event_Abstract
implements  Erebot_Interface_Event_ServerCapabilities
{
    /// Module containing the server's capabilities.
    protected $_module;

    /**
     * Constructs a new event dealing with
     * server capabilities.
     *
     * \param Erebot_Interface_Connection $connection
     *      The connection this event relates to.
     *
     * \param Erebot_Module_Base $module
     *      The module containing information about
     *      the server's capabilities.
     */
    public function __construct(
        Erebot_Interface_Connection $connection,
        Erebot_Module_Base          $module
    )
    {
        parent::__construct($connection);
        $this->_module = $module;
    }

    /// \copydoc Erebot_Interface_Event_ServerCapabilities::getModule()
    public function getModule()
    {
        return $this->_module;
    }
}

