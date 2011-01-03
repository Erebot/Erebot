<?php
/*
    This file is part of Erebot.

    Erebot is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Erebot is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Erebot.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * \brief
 *      Interface for the main (general) configuration.
 *
 * This interface provides the necessary methods
 * to represent the general configuration associated
 * with an instance of the bot.
 */
interface   Erebot_Interface_Config_Main
extends     Erebot_Interface_Config_Proxy
{
    /// Indicates that the configuration must be loaded from a file.
    const LOAD_FROM_FILE = 1;

    /// Indicates that the configuration must be loaded from a string.
    const LOAD_FROM_STRING = 2;

    /**
     * Creates a new instance of the ErebotMainConfig class.
     *
     * \param string $configData
     *      Either a (relative or absolute) path to the configuration file
     *      to load or a string representation of the configuration, 
     *      depending on the value of the $source parameter.
     *
     * \param opaque $source
     *      Erebot_Interface_Config_Main::LOAD_FROM_FILE or
     *      Erebot_Interface_Config_Main::LOAD_FROM_STRING, depending on
     *      whether $configData contains a filename or the string
     *      representation of the configuration data, respectively.
     *
     * \throw Erebot_InvalidValueException
     *      The configuration file did not exist or contained invalid values.
     *      This exception is also thrown when the $source parameter contains
     *      an invalid value.
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
     * \param string $configData
     *      Either a (relative or absolute) path to the configuration file
     *      to load or a string representation of the configuration, 
     *      depending on the value of the $source parameter.
     *
     * \param opaque $source
     *      Erebot_Interface_Config_Main::LOAD_FROM_FILE or
     *      Erebot_Interface_Config_Main::LOAD_FROM_STRING, depending on
     *      whether $configData contains a filename or the string
     *      representation of the configuration data, respectively.
     *
     * \throw Erebot_InvalidValueException
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
     * \param string $network
     *      The name of the IRC network whose configuration
     *      we're interested in.
     *
     * \retval Erebot_Interface_Config_Network
     *      The configuration object for that network.
     *
     * \throw Erebot_NotFoundException
     *      No such network has been configured on the bot.
     */
    public function & getNetworkCfg($network);

    /**
     * Returns all IRC network configurations.
     *
     * \retval list(Erebot_Interface_Config_Network)
     *       A list of network configurations.
     */
    public function getNetworks();

    /**
     * Returns the bot's version string.
     *
     * \retval string
     *      The bot's version, such as '0.20-pre'.
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
     * \retval string
     *      The bot's current timezone, such as 'Europe/Paris'.
     */
    public function getTimezone();

    /**
     * Returns the prefix used by commands.
     *
     * \retval string
     *      The prefix for commands, such as '!'.
     */
    public function getCommandsPrefix();

    /**
     * Returns the name of the currently loaded configuration file.
     *
     * \retval string
     *      Currently loaded configuration file.
     *
     * \retval NULL
     *      No configuration file has been loaded or the configuration was
     *      loaded using Erebot_Interface_Config_Main::LOAD_FROM_STRING.
     */
    public function getConfigFile();

    /// @TODO: Document those properly.
    public function mustDaemonize();
    public function getGroupIdentity();
    public function getUserIdentity();
    public function getPidfile();
}

