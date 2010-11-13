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
 *      Interface for an IRC server's configuration.
 *
 * This interface provides the necessary methods
 * to represent the configuration associated with
 * some IRC server.
 */
interface   Erebot_Interface_Config_Server
extends     Erebot_Interface_Config_Proxy
{
    /**
     * Creates a new ErebotServerConfig instance.
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
        Erebot_Interface_Config_Network &$netCfg,
        SimpleXMLElement                &$xml
    );

    /**
     * Returns the URL to use to connect to this IRC server.
     *
     * \retval string
     *      This server's connection URL.
     */
    public function getConnectionURL();

    /**
     * Returns the IRC network configuration upon which this
     * IRC server configuration depends.
     *
     * \retval ErebotNetworkConfig
     *      The network configuration associated with this server
     *      configuration.
     */
    public function & getNetworkCfg();
}

