<?php

/**
 * \brief
 *      Contains the configuration for an IRC channel.
 *
 * This is mainly used to provide autojoin and advanced i18n
 * capabilities to the bot.
 */
class   ErebotChannelConfig
extends ErebotConfigProxy
{
    /// The name of the channel this configuration refers to.
    protected $name;

    /// A reference to the ErebotNetworkConfig this instance depends on. 
    protected $netCfg;

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
    public function __construct(ErebotNetworkConfig &$netCfg, SimpleXMLElement &$xml)
    {
        parent::__construct($this, $xml);
        $this->name     =   (string) $xml['name'];
        $this->netCfg   =&  $netCfg;
    }

    /**
     * Destructs ErebotChannelConfig instances.
     */
    public function __destruct()
    {
    }

    /**
     * Returns the name of this IRC channel.
     *
     * \return
     *      The name of this IRC channel, as a string.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the IRC network configuration upon which this
     * IRC channel configuration depends.
     *
     * \return
     *      An instance of the ErebotNetworkConfig class.
     */
    public function & getNetworkCfg()
    {
        return $this->netCfg;
    }

    /**
     * \XXX Not yet implemented.
     */
    public function export($indent = 0)
    {
    }
}

?>
