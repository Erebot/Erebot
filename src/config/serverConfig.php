<?php

/**
 * \brief
 *      This class contains the configuration for an IRC server.
 *
 * This class stores settings which are specific to an IRC server,
 * such as a connection URL and a default quit message.
 */
class   ErebotServerConfig
extends ErebotConfigProxy
{
    /// A URL used to connect to this IRC(S) server.
    protected $connectionURL;

    /// A default quit message for this server.
    protected $quitMessage;

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
    public function __construct(ErebotNetworkConfig &$netCfg, SimpleXMLElement &$xml)
    {
        parent::__construct($netCfg, $xml);
        $this->connectionURL    =   (string) $xml['url'];
        $this->quitMessage      =   isset($xml['quit_message']) ?
                                        ((string) $xml['quit_message']) :
                                        NULL;
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
     * Returns the default quit message for this server.
     *
     * \return
     *      The default quit message, as a string.
     */
    public function getQuitMessage()
    {
        return $this->quitMessage;
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
