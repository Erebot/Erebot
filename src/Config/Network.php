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

namespace Erebot\Config;

/**
 * \brief
 *      This class contains the configuration for an IRC network.
 *
 * This class deals with settings which apply for a whole IRC network,
 * such as its name.
 * It also contains references to instances that implement the
 * Erebot::Interfaces::Config::Server or Erebot::Interfaces::Config::Channel
 * interfaces and apply to this IRC network.
 */
class Network extends \Erebot\Config\Proxy implements \Erebot\Interfaces\Config\Network
{
    /// Main configuration this object depends on.
    protected $maincfg;

    /// The name of this IRC network.
    protected $name;

    /// A list of server configurations which apply to this network.
    protected $servers;

    /// A list of channel configurations which apply to this network.
    protected $channels;

    /**
     * Creates a new configuration object for an IRC network.
     *
     * \param Erebot::Interfaces::Config::Main $mainCfg
     *      A reference to the main configuration for the bot.
     *
     * \param SimpleXMLElement $xml
     *      An XML node containing the configuration data
     *      for this network.
     */
    public function __construct(
        \Erebot\Interfaces\Config\Main $mainCfg,
        \SimpleXMLElement $xml
    ) {
        parent::__construct($mainCfg, $xml);
        $this->maincfg  = $mainCfg;
        $this->servers  = array();
        $this->channels = array();
        $this->name     = (string) $xml['name'];

        foreach ($xml->servers->server as $serverCfg) {
            /// @TODO use dependency injection instead.
            $newConfig  = new \Erebot\Config\Server($this, $serverCfg);
            $uris       = $newConfig->getConnectionURI();
            $uri        = new \Erebot\URI($uris[count($uris) - 1]);
            $this->servers[(string) $uri] = $newConfig;
            unset($newConfig);
        }

        if (isset($xml->channels->channel)) {
            foreach ($xml->channels->channel as $channelCfg) {
                /// @TODO use dependency injection instead.
                $newConfig = new \Erebot\Config\Channel($this, $channelCfg);
                $this->channels[$newConfig->getName()] = $newConfig;
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
            $this->servers,
            $this->maincfg
        );
        parent::__destruct();
    }

    /// \copydoc Erebot::Interfaces::Config::Network::getName()
    public function getName()
    {
        return $this->name;
    }

    /// \copydoc Erebot::Interfaces::Config::Network::getServerCfg()
    public function getServerCfg($server)
    {
        if (!isset($this->servers[$server])) {
            throw new \Erebot\NotFoundException('No such server');
        }
        return $this->servers[$server];
    }

    /// \copydoc Erebot::Interfaces::Config::Network::getServers()
    public function getServers()
    {
        return $this->servers;
    }

    /// \copydoc Erebot::Interfaces::Config::Network::getChannelCfg()
    public function getChannelCfg($channel)
    {
        if (!isset($this->channels[$channel])) {
            throw new \Erebot\NotFoundException('No such channel');
        }
        return $this->channels[$channel];
    }

    /// \copydoc Erebot::Interfaces::Config::Network::getChannels()
    public function getChannels()
    {
        return $this->channels;
    }
}
