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

define('EREBOT_VERSION', '0.3.2-dev1');

/**
 * \brief
 *      Interface for core features.
 *
 * This interface provides the necessary methods
 * to get a basic instance of the bot running.
 */
interface iErebot
{
    /**
     * Version information regarding this release of the bot.
     * This information is also available as a global constant called
     * EREBOT_VERSION.
     */
    const VERSION = EREBOT_VERSION;

    /**
     * Creates a new Erebot instance.
     *
     * \param iErebotMainConfig $config
     *      The (main) configuration to use.
     */
    public function __construct(iErebotMainConfig $config);

    /**
     * Returns a list of all connections handled by the bot.
     *
     * \retval list(iErebotConnection)
     *      A list of connections handled by this instance.
     *
     * \note
     *      There is not much use for this method actually. The only
     *      case where you might need it is when you're willing to
     *      broadcast a message/command to all connections (such as
     *      to signal the bot shutting down).
     */
    public function getConnections();

    /**
     * Starts the bot.
     *
     * \param string $connectionCls
     *      The name of the class to use to create new connections.
     *      This class must implement the iErebotConnection interface.
     *
     * \note
     *      If no connection class is given, it defaults to ErebotConnection.
     *
     * \throw EErebotInvalidValue
     *      The given value for $connectionCls is invalid (not a class name,
     *      a class with a bad interface, etc.).
     *
     * \attention
     *      This method never returns. Therefore, this MUST be the last
     *      method you call in your script.
     */
    public function start($connectionCls = NULL);

    /**
     * Stops the bot.
     */
    public function stop();

    /**
     * Returns a list of all \link ErebotTimer ErebotTimers\endlink registered.
     *
     * \retval list(iErebotTimer)
     *      Returns a list of timers registered for this instance.
     */
    public function getTimers();

    /**
     * Registers a timer for this instance.
     *
     * \param iErebotTimer $timer
     *      A timer to register.
     */
    public function addTimer(iErebotTimer &$timer);

    /**
     * Unregisters a timer.
     *
     * \param iErebotTimer $timer
     *      A timer to unregister.
     */
    public function removeTimer(iErebotTimer &$timer);

    /**
     * Retrieves the bot's version information.
     *
     * \retval string
     *      A string containing formatted version information about the bot.
     *
     * \see
     *      The constant Erebot::VERSION contains the raw version information.
     */
    public static function getVersion();

    /**
     * Loads a module.
     *
     * \param string $module
     *      The name of the module to load.
     *
     * \retval string
     *      Returns the name of the class to use to create instances of this
     *      module.
     *
     * \throw EErebotInvalidValue
     *      The given \a $module name does not define a valid module.
     *
     * \note
     *      There should be a folder going by the name of the \a $module
     *      itself containing a file called \a $module.php in the
     *      "modules/" folder. The file must contain a single class derived
     *      from ErebotModuleBase for it to be considered a valid module.
     *      You're free to give that class whatever name you like.
     */
    public function loadModule($module);

    /**
     * Returns the class associated with a given module.
     *
     * \param string $modName
     *      Name of the (loaded) module whose class we're interested in.
     *
     * \retval string
     *      Upon success, the name of the class to use to create
     *      new instances of this module is returned.
     *
     * \throw EErebotNotFound
     *      No module with the given name has been loaded.
     *
     * \see
     *      iErebot::moduleClassToName which does the opposite conversion.
     */
    public function moduleNameToClass($modName);

    /**
     * Returns the name of the module associated with a given class.
     *
     * \param string $className
     *      Name of the class whose module we're interested in.
     *
     * \retval string
     *      Upon success, the name of the module that the given class
     *      is used to create is returned.
     *
     * \throw EErebotNotFound
     *      No module using the given class name has been loaded.
     *
     * \see
     *      iErebot::moduleNameToClass which does the opposite conversion.
     */
    public function moduleClassToName($className);

    /**
     * Adds a (new) connection to the bot.
     *
     * Once a new connection has been created, use this method to add
     * it to the pool of connections the bot must process.
     * This enables the connection to send and receive messages.
     *
     * \param iErebotConnection $connection
     *      Adds a connection to the list of connections handled by
     *      this instance of the bot.
     *
     * \throw EErebotInvalidValue
     *      This connection is already part of the connection pool
     *      handled by this instance of the bot.
     */
    public function addConnection(iErebotConnection &$connection);

    /**
     * Removes a connection from the bot.
     *
     * Use this method to remove a connection from the pool of connections
     * the bot must process, such as when the connection is lost with the
     * remote IRC server.
     *
     * \param iErebotConnection $connection
     *      Removes a connection from the list of connections handled by
     *      this instance of the bot.
     *
     * \throw EErebotNotFound
     *      The given connection is not part of the connection pool
     *      handled by this instance of the bot.
     */
    public function removeConnection(iErebotConnection &$connection);

    /**
     * Returns the translation of a message in the primary language.
     *
     * Use this method to get a translated message in the primary language.
     * That is, using the language defined in the "language" attribute of
     * the "configuration" tag in your XML configuration file.
     *
     * \param string $message
     *      The original message to translate, in english.
     *
     * \retval string
     *      The translation for this message or the original (english)
     *      message if no translation is available.
     */
    public function gettext($message);
}

