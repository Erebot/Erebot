<?php

ErebotUtils::incl('serverConfig.php');
ErebotUtils::incl('channelConfig.php');

/**
 * \brief
 *      This class contains the configuration for an IRC network.
 *
 * This class deals with settings which apply for a whole IRC network,
 * such as its name.
 * It also contains references to instances of the ErebotServerConfig
 * and ErebotChannelConfig classes which apply on this IRC network.
 */
class   ErebotNetworkConfig
extends ErebotConfigProxy
{
    /// A reference to the ErebotMainConfig this instance depends on.
    protected $maincfg;

    /// The name of this IRC network.
    protected $name;

    /// A list of ErebotServerConfig objects which apply on this network.
    protected $servers;

    /// A list of ErebotChannelConfig objects which apply on this network.
    protected $channels;

    /**
     * Creates a new ErebotNetworkConfig instance.
     *
     * \param $mainCfg
     *      A reference to an ErebotMainConfig object which contains the
     *      general configuration for the bot.
     *
     * \param $xml
     *      A SimpleXMLElement node containing the configuration data
     *      for this network.
     */
    public function __construct(ErebotMainConfig &$mainCfg, SimpleXMLElement &$xml)
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

    /**
     * Returns the name of this IRC network.
     *
     * \return
     *      The name of this IRC network, as a string.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the configuration object for a particular IRC server.
     *
     * \param $server
     *      The name of the IRC server whose configuration we're interested in.
     *
     * \return
     *      The ErebotServerConfig object for that server.
     *
     * \throw EErebotNotFound
     *      No such server has been configured on this IRC network.
     */
    public function & getServerCfg($server)
    {
        if (!isset($this->servers[$server]))
            throw new EErebotNotFound('No such server');
        return $this->servers[$server];
    }

    /**
     * Returns all IRC server configurations.
     *
     * \return
     *      A list of ErebotServerConfig instances.
     */
    public function getServers()
    {
        return $this->servers;
    }

    /**
     * Returns the configuration object for a particular IRC channel.
     *
     * \param $channel
     *      The name of the IRC channel whose configuration we're interested in.
     *
     * \return
     *      The ErebotChannelConfig object for that channel.
     *
     * \throw EErebotNotFound
     *      No such channem has been configured on this IRC network.
     */
    public function & getChannelCfg($channel)
    {
        if (!isset($this->channels[$channel]))
            throw new EErebotNotFound('No such channel');
        return $this->channels[$channel];
    }

    /**
     * Returns all IRC channel configurations.
     *
     * \return
     *      A list of ErebotChannelConfig instances.
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * \XXX Not implemented yet.
     */
    public function export($indent = 0)
    {
    }
}

?>
