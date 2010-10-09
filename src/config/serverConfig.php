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

    // Documented in the interface.
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

    // Documented in the interface.
    public function getConnectionURL()
    {
        return $this->connectionURL;
    }

    // Documented in the interface.
    public function & getNetworkCfg()
    {
        return $this->proxified;
    }
}

?>
