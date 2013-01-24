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
 *      This class contains the configuration for an IRC server.
 *
 * This class stores settings which are specific to an IRC server,
 * such as a connection URL and a default quit message.
 */
class       Erebot_Config_Server
extends     Erebot_Config_Proxy
implements  Erebot_Interface_Config_Server
{
    /// An array of URI to follow to connect to this IRC(S) server.
    protected $_connectionURI;

    /**
     * Creates a new Erebot_Config_Server instance.
     *
     * \param Erebot_Interface_Config_Network $netCfg
     *      A reference to an object which contains the configuration
     *      for the IRC network this server is a part of.
     *
     * \param SimpleXMLElement $xml
     *      An XML node containing the configuration data
     *      for this server.
     */
    public function __construct(
        Erebot_Interface_Config_Network $netCfg,
        SimpleXMLElement                $xml
    )
    {
        parent::__construct($netCfg, $xml);
        $this->_connectionURI = array_filter(
            explode(' ', (string) $xml['url'])
        );
    }

    /// Destructor.
    public function __destruct()
    {
        parent::__destruct();
    }

    /// \copydoc Erebot_Interface_Config_Server::getConnectionURI()
    public function getConnectionURI()
    {
        return $this->_connectionURI;
    }

    /// \copydoc Erebot_Interface_Config_Server::getNetworkCfg()
    public function getNetworkCfg()
    {
        return $this->_proxified;
    }
}

