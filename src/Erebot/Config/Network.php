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
 *      This class contains the configuration for an IRC network.
 *
 * This class deals with settings which apply for a whole IRC network,
 * such as its name.
 * It also contains references to instances of the ErebotServerConfig
 * and ErebotChannelConfig classes which apply on this IRC network.
 */
class       Erebot_Config_Network
extends     Erebot_Config_Proxy
implements  Erebot_Interface_Config_Network
{
    /// A reference to the ErebotMainConfig this instance depends on.
    protected $_maincfg;

    /// The name of this IRC network.
    protected $_name;

    /// A list of ErebotServerConfig objects which apply on this network.
    protected $_servers;

    /// A list of ErebotChannelConfig objects which apply on this network.
    protected $_channels;

    // Documented in the interface.
    public function __construct(
        Erebot_Interface_Config_Main    &$mainCfg,
        SimpleXMLElement                &$xml
    )
    {
        parent::__construct($mainCfg, $xml);
        $this->_maincfg     =& $mainCfg;
        $this->_servers     = array();
        $this->_channels    = array();
        $this->_name        = (string) $xml['name'];

        foreach ($xml->servers->server as $serverCfg) {
            /// @TODO: use dependency injection instead.
            $newConfig = new Erebot_Config_Server($this, $serverCfg);
            $this->_servers[$newConfig->getConnectionURL()] =& $newConfig;
            unset($newConfig);
        }

        if (isset($xml->channels->channel)) {
            foreach ($xml->channels->channel as $channelCfg) {
                /// @TODO: use dependency injection instead.
                $newConfig = new Erebot_Config_Channel($this, $channelCfg);
                $this->_channels[$newConfig->getName()] =& $newConfig;
                unset($newConfig);
            }
        }
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset(
            $this->_servers,
            $this->_maincfg
        );
    }

    // Documented in the interface.
    public function getName()
    {
        return $this->_name;
    }

    // Documented in the interface.
    public function & getServerCfg($server)
    {
        if (!isset($this->_servers[$server]))
            throw new Erebot_NotFoundException('No such server');
        return $this->_servers[$server];
    }

    // Documented in the interface.
    public function getServers()
    {
        return $this->_servers;
    }

    // Documented in the interface.
    public function & getChannelCfg($channel)
    {
        if (!isset($this->_channels[$channel]))
            throw new Erebot_NotFoundException('No such channel');
        return $this->_channels[$channel];
    }

    // Documented in the interface.
    public function getChannels()
    {
        return $this->_channels;
    }
}

