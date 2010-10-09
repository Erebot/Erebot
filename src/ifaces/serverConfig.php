<?php

ErebotUtils::incl('configProxy.php');

/**
 * \brief
 *      Interface for an IRC server's configuration.
 *
 * This interface provides the necessary methods
 * to represent the configuration associated with
 * some IRC server.
 */
interface   iErebotServerConfig
extends     iErebotConfigProxy
{
    /**
     * Creates a new ErebotServerConfig instance.
     *
     * \param $netCfg
     *      A reference to an ErebotNetworkConfig object which contains the
     *      configuration for the IRC network this server is a part of.
     *
     * \param $xml
     *      A SimpleXMLElement node containing the configuration data
     *      for this server.
     */
    public function __construct(iErebotNetworkConfig &$netCfg, SimpleXMLElement &$xml);

    /**
     * Returns the URL to use to connect to this IRC server.
     *
     * \return
     *      This server's connection URL, as a string.
     */
    public function getConnectionURL();

    /**
     * Returns the IRC network configuration upon which this
     * IRC server configuration depends.
     *
     * \return
     *      An instance of the ErebotNetworkConfig class.
     */
    public function & getNetworkCfg();
}

?>
