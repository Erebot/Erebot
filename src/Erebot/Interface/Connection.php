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
 *      Interface for IRC(S) connections.
 *
 * This interface provides the necessary methods
 * to handle an IRC(S) connection.
 */
interface Erebot_Interface_Connection
{
    /// Indicates that a given mode is to be added.
    const MODE_ADD          = 0;
    /// Indicates that a given mode is to be removed.
    const MODE_REMOVE       = 1;

    /**
     * Constructs the object which will hold a connection.
     *
     * \param Erebot_Interface_Core $bot
     *      A bot instance.
     *
     * \param Erebot_Interface_Config_Server $config
     *      A server configuration instance.
     *
     * \note
     *      There is no actual connection until Erebot_Connection::connect()
     *      is used.
     */
    public function __construct(
        Erebot_Interface_Core           $bot,
        Erebot_Interface_Config_Server  $config
    );

    /**
     * Returns whether this connection object
     * is currently connected to a server.
     *
     * \retval bool
     *      TRUE if the connection is really connected,
     *      FALSE otherwise.
     */
    public function isConnected();

    /**
     * Makes the actual connection to an IRC server,
     * using the configuration data passed to the
     * constructor.
     *
     * \throw Erebot_ConnectionFailureException
     *      Thrown whenever the bot fails to establish
     *      a connection to the given server.
     */
    public function connect();

    /**
     * Disconnects the bot from that particular IRC server.
     *
     * \param string $quitMessage
     *      (optional) A message which will be visible
     *      by other users when the bot gets disconnected.
     *      If no message is given, the IrcConnector module
     *      is probed for its "quit_message" parameter.
     *      If no message is available, the bot quits with
     *      an empty string as its quit message.
     */
    public function disconnect($quitMessage = NULL);

    /**
     * Adds a given line to the outgoing FIFO.
     *
     * \param string $line
     *      The line of text to send.
     *
     * \throw Erebot_InvalidValueException
     *      Thrown if the $line contains invalid characters.
     *
     * \warning
     *      This method should be considered private (part
     *      of the implementation). In particular, modules
     *      should not call it directly, but use their own
     *      sendMessage() or sendCommand() method which will
     *      convey the message/command correctly to the IRC
     *      server.
     */
    public function pushLine($line);

    /**
     * Retrieves the configuration for a given channel.
     *
     * \param NULL|string $chan
     *      The name of the IRC channel for which a configuration
     *      must be retrieved. If $chan is NULL, the ErebotServerConfig
     *      instance associated with this object is returned instead.
     *
     * \retval Erebot_Interface_Config_Channel
     *      The configuration for the given channel, if there is one.
     *
     * \retval Erebot_Interface_Config_Server
     *      Otherwise, the configuration for the associated IRC server.
     *
     * \throw Erebot_NotFoundException
     *      No ErebotChannelConfig object exists for the given channel.
     */
    public function getConfig($chan);

    /**
     * Returns the underlying transport implementation
     * for this connection.
     *
     * \retval stream
     *      Returns this connection's socket, as a PHP stream.
     *
     * \note
     *      You generally don't need any sort of access to this
     *      stream, but it may be useful in cases where you need
     *      to do a select() on the connection.
     */
    public function getSocket();

    /**
     * Returns a boolean indicating whether the incoming FIFO
     * is empty or not.
     *
     * \retval TRUE
     *      The FIFO for incoming messages is empty.
     *
     * \retval FALSE
     *      The FIFO for incoming messages is NOT empty.
     */
    public function emptyReadQueue();

    /**
     * Returns a boolean indicating whether the outgoing FIFO
     * is empty or not.
     *
     * \retval TRUE
     *      The FIFO for outgoing messages is empty.
     *
     * \retval FALSE
     *      The FIFO for outgoing messages is NOT empty.
     */
    public function emptySendQueue();

    /**
     * Processes data from the incoming buffer.
     *
     * Once this method has been called, all lines awaiting
     * processing in the incoming buffer have been transferred
     * to the incoming FIFO.
     * You must call Erebot_Connection::processQueuedData()
     * after that in order to process the lines in the FIFO.
     * This is done so that a throttling policy may be put
     * in place if needed (eg. for an anti-flood system).
     */
    public function processIncomingData();

    /**
     * Sends a single line of data from the outgoing FIFO
     * to the underlying socket.
     *
     * This method is misnamed, because it acts on a FIFO
     * rather than on raw data.
     * This method can be called multiple times (such as
     * in a loop) to send all lines in the outgoing FIFO.
     * This is done so that a throttling policy may be put
     * in place if needed (eg. for an anti-flood system).
     *
     * \throw Erebot_NotFoundException
     *      Thrown if the outgoing FIFO is empty.
     */
    public function processOutgoingData();

    /**
     * Processes all lines in the incoming FIFO.
     * This method will dispatch the proper events/raws
     * for each line in the FIFO.
     */
    public function processQueuedData();

    /**
     * Returns the bot instance this connection
     * is associated with.
     *
     * \retval Erebot_Interface_Core
     *      An instance of the core class (Erebot).
     */
    public function getBot();

    /**
     * Loads a module for a specific channel or for the whole connection.
     *
     * \param string $module
     *      The name of the module to load.
     *
     * \param NULL|string $chan
     *      (optional) An IRC channel name. If given, the module will be
     *      loaded and a specific instance will be created for that
     *      $chan. Otherwise, an instance will be created that will be
     *      shared across channels on the same connection.
     *
     * \retval Erebot_Module_Base
     *      An instance of the module.
     *
     * \note
     *      Only one instance of a module is ever created for a channel
     *      or the pool of shared modules. Therefore, it is safe to call
     *      this method multiple times with the same parameters.
     *
     * \throw Erebot_InvalidValueException
     *      Thrown when invalid values are found in the (meta)data
     *      of the module.
     *
     * \throw Erebot_NotFoundException
     *      Thrown when a required dependency could not be loaded.
     *      You may want to load the required dependency and then
     *      try to load the module again.
     */
    public function loadModule($module, $chan = NULL);

    /**
     * Returns the modules loaded for a given channel
     * or for the whole connection.
     *
     * \param string $chan
     *      (optional) An IRC channel name. If given, both the modules
     *      which were specifically loaded for that channel and the
     *      shared modules are returned. Otherwise, only the shared
     *      modules are returned.
     *
     * \retval list(Erebot_Module_Base)
     *      An array of module instances.
     */
    public function getModules($chan = NULL);

    /**
     * Returns an instance of a given module on a given channel.
     *
     * \param string $name
     *      The name of the module (ie. the name of the class
     *      implementing the feature we're interested in).
     *
     * \param string $chan
     *      (optional) An IRC channel name. If given, the bot will try
     *      to return an instance which is specific to that particular
     *      channel, before falling back to a shared instance.
     *      Otherwise, this method only looks for a shared instance.
     *
     * \param bool $autoload
     *      (optional) Whether the module should be autoloaded if it
     *      could not be found first time around (TRUE) or not (FALSE).
     *      The default is to autoload missing modules.
     *
     * \retval Erebot_Module_Base
     *      An instance of the given module.
     *
     * \throw Erebot_InvalidValueException
     *      Thrown when an invalid $type is passed.
     *
     * \throw Erebot_NotFoundException
     *      Thrown if no instance of the given module could be found.
     */
    public function getModule($name, $chan = NULL, $autoload = TRUE);

    /**
     * Registers a raw handler on this connection.
     *
     * \param Erebot_Interface_RawHandler $handler
     *      The handler to register.
     */
    public function addRawHandler(Erebot_Interface_RawHandler $handler);

    /**
     * Unregisters a raw handler on this connection.
     *
     * \param Erebot_Interface_RawHandler $handler
     *      The handler to unregister.
     *
     * \throw Erebot_NotFoundException
     *      Thrown when the given handler could not be found,
     *      such as when it was not registered on this connection.
     */
    public function removeRawHandler(Erebot_Interface_RawHandler $handler);

    /**
     * Registers an event handler on this connection.
     *
     * \param Erebot_Interface_EventHandler $handler
     *      The handler to register.
     */
    public function addEventHandler(Erebot_Interface_EventHandler $handler);

    /**
     * Unregisters an event handler on this connection.
     *
     * \param Erebot_Interface_EventHandler $handler
     *      The handler to unregister.
     *
     * \throw Erebot_NotFoundException
     *      Thrown when the given handler could not be found,
     *      such as when it was not registered on this connection.
     */
    public function removeEventHandler(Erebot_Interface_EventHandler $handler);

    /**
     * Factory to create an event matching the given interface,
     * passing any additional parameters given to this method
     * to the constructor for that event.
     *
     * \param string $iface
     *      Name of the interface describing
     *      the type of event to create.
     *
     * \note
     *      You may pass additional parameters to this method.
     *      They will be passed as is to the event's constructor.
     *
     * \note
     *      It is not necessary to pass "$this" explicitely
     *      as the first additional parameter to this method,
     *      this factory already takes care of adding it
     *      automatically as all event types require it.
     *
     * \note
     *      This method can also use the same shortcuts as
     *      Erebot_Connection::getEventClass().
     *
     * \note
     *      The name of the interface to use is case-insensitive.
     */
    public function makeEvent($iface /* , ... */);

    /**
     * Dispatches the given event to handlers
     * which have been registered for this type of event.
     *
     * \param Erebot_Interface_Event_Base_Generic $event
     *      An event to dispatch.
     *
     * \note
     *      For the purposes of this method, raw messages (objects
     *      implementing the Erebot_Interface_Event_Raw interface)
     *      are also considered to be "events".
     */
    public function dispatch(Erebot_Interface_Event_Base_Generic $event);

    /**
     * Compares two string in a case-sensitive fashion (like strcmp).
     *
     * \param $a
     *      First string to compare.
     *
     * \param $b
     *      Second string to compare.
     *
     * \retval int
     *      An integer less than, equal to or greater than zero if
     *      $a is found, respectively, to be less than, to match,
     *      or be greater than $b.
     */
    public function irccmp($a, $b);

    /**
     * Compares two string in a case-sensitive fashion
     * up to a given length (like strncmp).
     *
     * \param $a
     *      First string to compare.
     *
     * \param $b
     *      Second string to compare.
     *
     * \param $len
     *      Limit the comparison to only the first $len bytes
     *      of each string.
     *
     * \retval int
     *      An integer less than, equal to or greater than zero if
     *      the first $len bytes of $a are found, respectively,
     *      to be less than, to match, or be greater than the first
     *      $len bytes of $b.
     */
    public function ircncmp($a, $b, $len);

    /**
     * Compares two string in a case-insensitive fashion
     * (like strcasecmp).
     *
     * \param $a
     *      First string to compare.
     *
     * \param $b
     *      Second string to compare.
     *
     * \param $mappingName
     *      (optional) Name of the mapping which will be used to
     *      normalize the two strings. Possible values include
     *      "strict-rfc1459", "rfc1459" and "ascii".
     *      The default is to determine the right mapping to use
     *      automatically.
     *
     * \retval int
     *      An integer less than, equal to or greater than zero if
     *      $a is found, respectively, to be less than, to match,
     *      or be greater than $b.
     *
     * \throw Erebot_InvalidValueException
     *      An invalid $mappingName was given.
     *
     * \throw Erebot_NotFoundException
     *      No mapping could be found matching $mappingName.
     */
    public function irccasecmp($a, $b, $mappingName = NULL);

    /**
     * Compares two string in a case-insensitive fashion
     * up to a given length (like strncasecmp).
     *
     * \param $a
     *      First string to compare.
     *
     * \param $b
     *      Second string to compare.
     *
     * \param $len
     *      Limit the comparison to only the first $len bytes
     *      of each string.
     *
     * \param $mappingName
     *      (optional) Name of the mapping which will be used to
     *      normalize the two strings. Possible values include
     *      "strict-rfc1459", "rfc1459" and "ascii".
     *      The default is to determine the right mapping to use
     *      automatically.
     *
     * \retval int
     *      An integer less than, equal to or greater than zero if
     *      the first $len bytes of $a are found, respectively,
     *      to be less than, to match, or be greater than the first
     *      $len bytes of $b.
     *
     * \throw Erebot_InvalidValueException
     *      An invalid $mappingName was given.
     *
     * \throw Erebot_NotFoundException
     *      No mapping could be found matching $mappingName.
     */
    public function ircncasecmp($a, $b, $len, $mappingName = NULL);

    /**
     * Determines if the given string is a valid channel name or not.
     * A channel name usually starts with the hash symbol (#).
     * Valid characters for the rest of the name vary between IRC networks.
     *
     * \param $chan
     *      Tentative channel name.
     *
     * \retval bool
     *      TRUE if $chan is a valid channel name, FALSE otherwise.
     *
     * \throw Erebot_InvalidValueException
     *      $chan is not a string or is empty.
     */
    public function isChannel($chan);

    public function normalizeNick($nick, $mappingName = NULL);
}

