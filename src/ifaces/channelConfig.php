<?php

ErebotUtils::incl('configProxy.php');

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
    public function __construct(iErebotNetworkConfig &$netCfg, SimpleXMLElement &$xml);

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

?>
