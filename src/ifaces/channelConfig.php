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

include_once('src/ifaces/configProxy.php');

/**
 * \brief
 *      Interface for an IRC channel's configuration.
 *
 * This interface provides the necessary methods
 * to represent the configuration associated with
 * some IRC channel.
 */
interface   iErebotChannelConfig
extends     iErebotConfigProxy
{
    /**
     * Creates a new ErebotChannelConfig instance.
     *
     * \param $netCfg
     *      A reference to the ErebotNetworkConfig object which contains
     *      the network configuration for this channel.
     *
     * \param $xml
     *      A SimpleXMLElement node containing the configuration data
     *      for this network.
     */
    public function __construct(
        iErebotNetworkConfig   &$netCfg,
        SimpleXMLElement       &$xml
    );

    /**
     * Returns the name of this IRC channel.
     *
     * \return
     *      The name of this IRC channel, as a string.
     */
    public function getName();

    /**
     * Returns the IRC network configuration upon which this
     * IRC channel configuration depends.
     *
     * \return
     *      An instance of the ErebotNetworkConfig class.
     */
    public function & getNetworkCfg();
}

