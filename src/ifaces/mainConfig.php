<?php

ErebotUtils::incl('configProxy.php');

/**
 * \brief
 *      Interface for the main (general) configuration.
 *
 * This interface provides the necessary methods
 * to represent the general configuration associated
 * with an instance of the bot.
 */
interface   iErebotMainConfig
extends     iErebotConfigProxy
{
    /// Indicates that the configuration must be loaded from a file.
    const LOAD_FROM_FILE = 1;

    /// Indicates that the configuration must be loaded from a string.
    const LOAD_FROM_STRING = 2;

    /**
     * Creates a new instance of the ErebotMainConfig class.
     *
     * Do not call this class' constructor directly. Instead, use
     * ErebotMainConfig::getInstance() to retrieve an instance of
     * this class.
     */
    public function __construct($configData, $source);

    /**
     * Prevents cloning of this class to avoid escape from the
     * singleton design pattern.
     */
    public function __clone();

    /**
     * (Re)loads a configuration file.
     *
     * \param $configData
     *      Either a (relative or absolute) path to the configuration file
     *      to load or a string representation of the configuration, 
     *      depending on the value of the $source parameter.
     *
     * \param $source
     *      Whether $configData contains a filename or the string
     *      representation of the configuration. data
     *
     * \throw EErebotInvalidValue
     *      The configuration file did not exist or contained invalid values.
     *      This exception is also thrown when the $source parameter contains
     *      an invalid value.
     *
     * \note
     *      Each time this method is called, all previous settings will be
     *      discarded.
     */
    public function load($configData, $source);

    /**
     * Returns the configuration object for a particular IRC network.
     *
     * \param $network
     *      The name of the IRC network whose configuration we're interested in.
     *
     * \return
     *      The ErebotNetworkConfig object for that network.
     *
     * \throw EErebotNotFound
     *      No such network has been configured on the bot.
     */
    public function & getNetworkCfg($network);

    /**
     * Returns all IRC network configurations.
     *
     * \return
     *       A list of ErebotNetworkConfig instances.
     */
    public function getNetworks();

    /**
     * Returns the bot's version string.
     *
     * \return
     *      A string representing the bot's version,
     *      such as '0.20-pre'.
     *
     * \note
     *      This version string is compatible with PHP's versioning scheme.
     *      Therefore, you may use PHP's version_compare() function to compare
     *      the version strings for different releases of the bot.
     */
    public function getVersion();

    /**
     * Returns the bot's timezone.
     *
     * \return
     *      A string describing the bot's current timezone,
     *      such as 'Europe/Paris'.
     */
    public function getTimezone();
}

?>
