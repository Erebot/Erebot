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
 *      Interface for an IRC channel's configuration.
 *
 * This interface provides the necessary methods
 * to represent the configuration associated with
 * some IRC channel.
 */
interface   Erebot_Interface_Config_Channel
extends     Erebot_Interface_Config_Proxy
{
    /**
     * Creates a new configuration object for an IRC channel.
     *
     * \param Erebot_Interface_Config_Network $netCfg
     *      An object which contains the network configuration
     *      for this channel.
     *
     * \param SimpleXMLElement $xml
     *      An XML node containing the configuration data
     *      for this network.
     */
    public function __construct(
        Erebot_Interface_Config_Network &$netCfg,
        SimpleXMLElement                &$xml
    );

    /**
     * Returns the name of this IRC channel.
     *
     * \retval string
     *      The name of this IRC channel.
     */
    public function getName();

    /**
     * Returns the IRC network configuration upon which this
     * IRC channel configuration depends.
     *
     * \retval Erebot_Interface_Config_Network
     *      The IRC network configuration for this IRC channel.
     */
    public function & getNetworkCfg();
}
