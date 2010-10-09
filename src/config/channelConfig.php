<?php

ErebotUtils::incl('../ifaces/channelConfig.php');

/**
 * \brief
 *      Contains the configuration for an IRC channel.
 *
 * This is mainly used to provide autojoin and advanced i18n
 * capabilities to the bot.
 */
class       ErebotChannelConfig
extends     ErebotConfigProxy
implements  iErebotChannelConfig
{
    /// The name of the channel this configuration refers to.
    protected $name;

    /// A reference to the ErebotNetworkConfig this instance depends on. 
    protected $netCfg;

    // Documented in the interface.
    public function __construct(iErebotNetworkConfig &$netCfg, SimpleXMLElement &$xml)
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

    // Documented in the interface.
    public function getName()
    {
        return $this->name;
    }

    // Documented in the interface.
    public function & getNetworkCfg()
    {
        return $this->netCfg;
    }
}

?>
