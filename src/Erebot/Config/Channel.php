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
 *      Contains the configuration for an IRC channel.
 *
 * This is mainly used to provide autojoin and advanced i18n
 * capabilities to the bot.
 */
class       Erebot_Config_Channel
extends     Erebot_Config_Proxy
implements  Erebot_Interface_Config_Channel
{
    /// The name of the channel this configuration refers to.
    protected $_name;

    /// Netword configuration configuration this object depends on.
    protected $_netCfg;

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
        Erebot_Interface_Config_Network $netCfg,
        SimpleXMLElement                $xml
    )
    {
        parent::__construct($this, $xml);
        $this->_name     = (string) $xml['name'];
        $this->_netCfg   = $netCfg;
    }

    /// Destructor.
    public function __destruct()
    {
        parent::__destruct();
    }

    /// \copydoc Erebot_Interface_Config_Channel::getName()
    public function getName()
    {
        return $this->_name;
    }

    /// \copydoc Erebot_Interface_Config_Channel::getNetworkCfg()
    public function getNetworkCfg()
    {
        return $this->_netCfg;
    }
}

