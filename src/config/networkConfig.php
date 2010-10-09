<?php

ErebotUtils::incl('serverConfig.php');
ErebotUtils::incl('channelConfig.php');
ErebotUtils::incl('../ifaces/networkConfig.php');

/**
 * \brief
 *      This class contains the configuration for an IRC network.
 *
 * This class deals with settings which apply for a whole IRC network,
 * such as its name.
 * It also contains references to instances of the ErebotServerConfig
 * and ErebotChannelConfig classes which apply on this IRC network.
 */
class       ErebotNetworkConfig
extends     ErebotConfigProxy
implements  iErebotNetworkConfig
{
    /// A reference to the ErebotMainConfig this instance depends on.
    protected $maincfg;

    /// The name of this IRC network.
    protected $name;

    /// A list of ErebotServerConfig objects which apply on this network.
    protected $servers;

    /// A list of ErebotChannelConfig objects which apply on this network.
    protected $channels;

    // Documented in the interface.
    public function __construct(iErebotMainConfig &$mainCfg, SimpleXMLElement &$xml)
    {
        parent::__construct($mainCfg, $xml);
        $this->maincfg      =& $mainCfg;
        $this->servers      = array();
        $this->channels     = array();
        $this->name         = (string) $xml['name'];

        foreach ($xml->servers->server as $serverCfg) {
            $newConfig = new ErebotServerConfig($this, $serverCfg);
            $this->servers[$newConfig->getConnectionURL()] =& $newConfig;
            unset($newConfig);
        }

        if (isset($xml->channels->channel)) {
            foreach ($xml->channels->channel as $channelCfg) {
                $newConfig = new ErebotChannelConfig($this, $channelCfg);
                $this->channels[$newConfig->getName()] =& $newConfig;
                unset($newConfig);
            }
        }
    }

    /**
     * Destructs ErebotNetworkConfig instances.
     */
    public function __destruct()
    {
        unset($this->servers, $this->maincfg);
    }

    // Documented in the interface.
    public function getName()
    {
        return $this->name;
    }

    // Documented in the interface.
    public function & getServerCfg($server)
    {
        if (!isset($this->servers[$server]))
            throw new EErebotNotFound('No such server');
        return $this->servers[$server];
    }

    // Documented in the interface.
    public function getServers()
    {
        return $this->servers;
    }

    // Documented in the interface.
    public function & getChannelCfg($channel)
    {
        if (!isset($this->channels[$channel]))
            throw new EErebotNotFound('No such channel');
        return $this->channels[$channel];
    }

    // Documented in the interface.
    public function getChannels()
    {
        return $this->channels;
    }
}

?>
