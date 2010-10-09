<?php

ErebotUtils::incl('../ifaces/serverConfig.php');

/**
 * \brief
 *      This class contains the configuration for an IRC server.
 *
 * This class stores settings which are specific to an IRC server,
 * such as a connection URL and a default quit message.
 */
class       ErebotServerConfig
extends     ErebotConfigProxy
implements  iErebotServerConfig
{
    /// A URL used to connect to this IRC(S) server.
    protected $connectionURL;

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
    public function __construct(iErebotNetworkConfig &$netCfg, SimpleXMLElement &$xml)
    {
        parent::__construct($netCfg, $xml);
        $this->connectionURL    =   (string) $xml['url'];
    }

    /**
     * Destructs ErebotServerConfig instances.
     */
    public function __destruct()
    {
    }

    /**
     * Returns the URL to use to connect to this IRC server.
     *
     * \return
     *      This server's connection URL, as a string.
     */
    public function getConnectionURL()
    {
        return $this->connectionURL;
    }

    /**
     * Returns the IRC network configuration upon which this
     * IRC server configuration depends.
     *
     * \return
     *      An instance of the ErebotNetworkConfig class.
     */
    public function & getNetworkCfg()
    {
        return $this->proxified;
    }

    /**
     * \XXX Not implemented yet.
     */
    public function export($indent = 0)
    {
    }
}

?>
