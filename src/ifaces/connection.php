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
interface iErebotConnection
{
    /// Retrieves an instance of a module by its name.
    const MODULE_BY_NAME    = 0;
    /// Retrieves an instance of a module using that module's class name.
    const MODULE_BY_CLASS   = 1;

    /// Indicates that a given mode is to be added.
    const MODE_ADD          = 0;
    /// Indicates that a given mode is to be removed.
    const MODE_REMOVE       = 1;

    /**
     * Constructs the object which will hold a connection.
     *
     * \param $bot
     *      A bot instance.
     *
     * \param $config
     *      A server configuration instance.
     *
     * \note
     *      There is no actual connection until ErebotConnection::connect()
     *      is used.
     */
    public function __construct(iErebot &$bot, iErebotServerConfig &$config);

    /**
     * Makes the actual connection to an IRC server,
     * using the configuration data passed to the
     * constructor.
     *
     * \throw EErebotConnectionFailure
     *      Thrown whenever the bot fails to establish
     *      a connection to the given server.
     */
    public function connect();

    /**
     * Disconnects the bot from that particular IRC server.
     *
     * \param $quitMessage
     *      An optional message which will be visible
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
     * \param $line
     *      The line of text to send.
     *
     * \throw EErebotInvalidValue
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
     * \param $chan
     *      The name of the IRC channel for which a configuration
     *      must be retrieved. If $chan is NULL, the ErebotServerConfig
     *      instance associated with this object is returned instead.
     *
     * \return
     *      An ErebotChannelConfig or ErebotServerConfig, depending
     *      on its availability and the value of $chan.
     *
     * \throw EErebotNotFound
     *      No ErebotChannelConfig object exists for the given channel.
     */
    public function & getConfig($chan);

    /**
     * Returns the underlying transport implementation
     * for this connection.
     *
     * \return
     *      Returns this connection's socket, as a PHP stream.
     *
     * \note
     *      You generally don't need any sort of access to this
     *      stream, but it may be useful in cases where you need
     *      to do a select() on the connection.
     */
    public function & getSocket();

    /**
     * Returns a boolean indicating whether the incoming FIFO
     * is empty or not.
     *
     * \return
     *      A boolean indicating whether the FIFO for incoming
     *      messages is empty (\b{TRUE}) or not (\b{FALSE}).
     */
    public function emptyReadQueue();

    /**
     * Returns a boolean indicating whether the outgoing FIFO
     * is empty or not.
     *
     * \return
     *      A boolean indicating whether the FIFO for outgoing
     *      messages is empty (\b{TRUE}) or not (\b{FALSE}).
     */
    public function emptySendQueue();

    /**
     * Processes data from the incoming buffer.
     *
     * Once this method has been called, all lines awaiting
     * processing in the incoming buffer have been transferred
     * to the incoming FIFO.
     * You must call ErebotConnection::processQueuedData()
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
     * \throw EErebotNotFound
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
     * \return
     *      An Erebot instance.
     */
    public function & getBot();

    /**
     * Loads a module for a specific channel or for the whole connection.
     *
     * \param $module
     *      The name of the module to load.
     *
     * \param $chan
     *      An optional IRC channel name. If given, the module will be
     *      loaded and a specific instance will be created for that
     *      $chan. Otherwise, an instance will be created that will be
     *      shared across channels on the same connection.
     *
     * \return
     *      An instance of the module.
     *
     * \note
     *      Only one instance of a module is ever created for a channel
     *      or the pool of shared modules. Therefore, it is safe to call
     *      this method multiple times with the same parameters.
     *
     * \throw EErebotInvalidValue
     *      Thrown when invalid values are found in the (meta)data
     *      of the module.
     *
     * \throw EErebotNotFound
     *      Thrown when a required dependency could not be loaded.
     *      You may want to load the required dependency and then
     *      try to load the module again.
     */
    public function & loadModule($module, $chan = NULL);

    /**
     * Returns the modules loaded for a given channel
     * or for the whole connection.
     *
     * \param $chan
     *      An optional IRC channel name. If given, both the modules
     *      which were specifically loaded for that channel and the
     *      shared modules are returned. Otherwise, only the shared
     *      modules are returned.
     *
     * \return
     *      An array of module instances.
     */
    public function getModules($chan = NULL);

    /**
     * Returns an instance of a given module on a given channel.
     *
     * \param $name
     *      Depending on the $type parameter, this is either
     *      the name of the module or the name of the class
     *      implementing the module we're interested in.
     *
     * \param $type
     *      Either ErebotConnection::MODULE_BY_NAME to search for the
     *      module by its name, or ErebotConnection::MODULE_BY_CLASS
     *      to look for the module by its class.
     *
     * \param $chan
     *      An optional IRC channel name. If given, the bot will try
     *      to return an instance which is specific to that particular
     *      channel, before falling back to a shared instance.
     *      Otherwise, this method only looks for a shared instance.
     *
     * \return
     *      An instance of the given module.
     *
     * \throw EErebotInvalidValue
     *      Thrown when an invalid $type is passed.
     *
     * \throw EErebotNotFound
     *      Thrown if no instance of the given module could be found.
     */
    public function & getModule($name, $type, $chan = NULL);

    /**
     * Registers a raw handler on this connection.
     *
     * \param $handler
     *      The ErebotRawHandler to register.
     */
    public function addRawHandler(iErebotRawHandler &$handler);

    /**
     * Unregisters a raw handler on this connection.
     *
     * \param $handler
     *      The ErebotRawHandler to unregister.
     *
     * \throw EErebotNotFound
     *      Thrown when the given handler could not be found,
     *      such as when it was not registered on this connection.
     */
    public function removeRawHandler(iErebotRawHandler &$handler);

    /**
     * Registers an event handler on this connection.
     *
     * \param $handler
     *      The ErebotEventHandler to register.
     */
    public function addEventHandler(iErebotEventHandler &$handler);

    /**
     * Unregisters an event handler on this connection.
     *
     * \param $handler
     *      The ErebotEventHandler to unregister.
     *
     * \throw EErebotNotFound
     *      Thrown when the given handler could not be found,
     *      such as when it was not registered on this connection.
     */
    public function removeEventHandler(iErebotEventHandler &$handler);

    /**
     * Dispatches the given event to handlers
     * which have registered for this type of event.
     *
     * \param $event
     *      An object to dispatch, which implements
     *      the iErebotEvent interface.
     */
    public function dispatchEvent(iErebotEvent &$event);

    /**
     * Dispatches the given raw to handlers
     * which have registered for this type of raw.
     *
     * \param $raw
     *      An object to dispatch, which implements
     *      the iErebotRaw interface.
     */
    public function dispatchRaw(iErebotRaw &$raw);
}

