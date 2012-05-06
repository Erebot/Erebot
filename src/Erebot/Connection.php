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
 *      Handles a (possibly encrypted) connection to an IRC server.
 */
class       Erebot_Connection
implements  Erebot_Interface_ModuleContainer,
            Erebot_Interface_EventDispatcher,
            Erebot_Interface_BidirectionalConnection,
            Erebot_Interface_Collated
{
    /**
     * A configuration object implementing
     * the Erebot_Interface_Config_Server interface.
     */
    protected $_config;
    /// A bot object implementing the Erebot_Interface_Core interface.
    protected $_bot;

    /// The underlying socket, represented as a stream.
    protected $_socket;
    /// A FIFO queue for outgoing messages.
    protected $_sndQueue;
    /// A FIFO queue for incoming messages.
    protected $_rcvQueue;
    /// A raw buffer for incoming data.
    protected $_incomingData;

    /// Maps channels to their loaded modules.
    protected $_channelModules;
    /// Maps modules names to modules instances.
    protected $_plainModules;

    /// A list of rawHandlers.
    protected $_raws;
    /// A list of eventHandlers.
    protected $_events;

    /// Whether this connection is actually... well, connected.
    protected $_connected;

    protected $_uriFactory;

    protected $_depFactory;

    protected $_rawProfileLoader;

    protected $_collator;

    protected $_eventsProducer;

    /**
     * Constructs the object which will hold a connection.
     *
     * \param Erebot_Interface_Core $bot
     *      A bot instance.
     *
     * \note
     *      There is no actual connection until
     *      Erebot_Interface_Connection::connect()
     *      is called.
     */
    public function __construct(
        Erebot_Interface_Core   $bot,
                                $config = NULL,
                                $events = array()
    )
    {
        $this->_config      = $config;
        $this->_bot         = $bot;

        $this->_channelModules  = array();
        $this->_plainModules    = array();
        $this->_raws            = array();
        $this->_events          = array();
        $this->_sndQueue        = array();
        $this->_rcvQueue        = array();
        $this->_incomingData    = '';
        $this->_connected       = FALSE;
        $this->_collator        = new Erebot_IrcCollator_RFC1459();
        $this->_eventsProducer  = new Erebot_IrcParser($this);
        /// @FIXME: this should really be done in some other way.
        $this->_eventsProducer->setEventClasses($events);

        $this->setURIFactory('Erebot_URI');
        $this->setRawProfileLoader(
            new Erebot_RawProfileLoader(
                array(
                    'Erebot_Interface_RawProfile_RFC2812',
                    'Erebot_Interface_RawProfile_005',
                    // Technically, ISON is an optionnal feature from RFC 1459,
                    // but we need to load it by default so that some modules
                    // (eg. Erebot_Module_WatchList) work properly.
                    'Erebot_Interface_RawProfile_ISON',
                )
            )
        );

        $this->addEventHandler(
            new Erebot_EventHandler(
                new Erebot_Callable(array($this, 'handleCapabilities')),
                new Erebot_Event_Match_InstanceOf(
                    'Erebot_Event_ServerCapabilities'
                )
            )
        );

        $this->addEventHandler(
            new Erebot_EventHandler(
                new Erebot_Callable(array($this, 'handleConnect')),
                new Erebot_Event_Match_InstanceOf(
                    'Erebot_Interface_Event_Connect'
                )
            )
        );
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        $this->_socket = NULL;
        unset(
            $this->_events,
            $this->_raws,
            $this->_config,
            $this->_bot,
            $this->_channelModules,
            $this->_plainModules,
            $this->_uriFactory,
            $this->_depFactory
        );
    }

    /**
     * Returns the name of the class used
     * to parse Uniform Resource Identifiers.
     *
     * \retval string
     *      Name of the class used to parse an URI.
     */
    public function getURIFactory()
    {
        return $this->_uriFactory;
    }

    /**
     * Sets the class to use as a factory
     * to parse Uniform Resource Identifiers.
     *
     * \param string $factory
     *      Name of the class to use to parse an URI.
     */
    public function setURIFactory($factory)
    {
        $reflector = new ReflectionClass($factory);
        if (!$reflector->implementsInterface('Erebot_Interface_URI')) {
            throw new Erebot_InvalidValueException(
                'The factory must implement Erebot_Interface_URI'
            );
        }
        $this->_uriFactory = $factory;
    }

    public function getRawProfileLoader()
    {
        return $this->_rawProfileLoader;
    }

    public function setRawProfileLoader(
        Erebot_Interface_RawProfileLoader $loader
    )
    {
        $this->_rawProfileLoader = $loader;
    }

    public function reload(Erebot_Interface_Config_Server $config)
    {
        $this->_loadModules(
            $config,
            Erebot_Module_Base::RELOAD_ALL
        );
        $this->_config = $config;
    }

    protected function _loadModules(
        Erebot_Interface_Config_Server  $config,
                                        $flags
    )
    {
        $logging        = Plop::getInstance();
        $logger         = $logging->getLogger(__FILE__);

        $channelModules = $this->_channelModules;
        $plainModules   = $this->_plainModules;

        $newNetCfg      = $config->getNetworkCfg();
        $newChannels    = $newNetCfg->getChannels();

        $oldNetCfg      = $this->_config->getNetworkCfg();
        $oldChannels    = $oldNetCfg->getChannels();

        // Keep whatever can be kept from the old
        // channels-related module configurations.
        foreach ($oldChannels as $chan => $oldChanCfg) {
            try {
                $newChanCfg = $newNetCfg->getChannelCfg($chan);
                $newModules = $newChanCfg->getModules(FALSE);
                foreach ($oldChanCfg->getModules(FALSE) as $module) {
                    if (!in_array($module, $newModules))
                        unset($this->_channelModules[$chan][$module]);
                    else if (isset($this->_channelModules[$chan][$module]))
                        $this->_channelModules[$chan][$module] =
                            clone $this->_channelModules[$chan][$module];
                }
            }
            catch (Erebot_NotFoundException $e) {
                unset($this->_channelModules[$chan]);
            }
        }

        // Keep whatever can be kept from the old
        // generic module configurations.
        $newModules = $config->getModules(TRUE);
        foreach ($this->_config->getModules(TRUE) as $module) {
            if (!in_array($module, $newModules))
                unset($this->_plainModules[$module]);
            else if (isset($this->_plainModules[$module]))
                $this->_plainModules[$module] =
                    clone $this->_plainModules[$module];
        }

        // Configure new modules, both channel-related
        // and generic ones.
        foreach ($newChannels as $chanCfg) {
            $modules    = $chanCfg->getModules(FALSE);
            $chan       = $chanCfg->getName();
            foreach ($modules as $module) {
                try {
                    $this->_loadModule(
                        $module, $chan, $flags,
                        $this->_plainModules,
                        $this->_channelModules
                    );
                }
                catch (Erebot_StopException $e) {
                    throw $e;
                }
                catch (Exception $e) {
                    $logger->warning($e->getMessage());
                }
            }
        }

        foreach ($newModules as $module) {
            try {
                $this->_loadModule(
                    $module, NULL, $flags,
                    $this->_plainModules,
                    $this->_channelModules
                );
            }
            catch (Erebot_StopException $e) {
                throw $e;
            }
            catch (Exception $e) {
                    $logger->warning($e->getMessage());
            }
        }

        // Unload old module instances.
        foreach ($channelModules as $modules)
            foreach ($modules as $module)
                $module->unload();
        foreach ($plainModules as $module)
            $module->unload();
    }

    protected function _unloadModule($module)
    {
        var_dump(gettype($module));
    }

    /// \copydoc Erebot_Interface_Connection::isConnected()
    public function isConnected()
    {
        return $this->_connected;
    }

    /// \copydoc Erebot_Interface_Connection::connect()
    public function connect()
    {
        if ($this->_connected)
            return FALSE;

        $logging    = Plop::getInstance();
        $logger     = $logging->getLogger(__FILE__);

        $uris           = $this->_config->getConnectionURI();
        $serverUri      = new Erebot_URI($uris[count($uris) - 1]);
        $this->_socket  = NULL;

        $logger->info(
            $this->_bot->gettext('Loading required modules for "%s"...'),
            $serverUri
        );
        $this->_loadModules(
            $this->_config,
            Erebot_Module_Base::RELOAD_ALL |
            Erebot_Module_Base::RELOAD_INIT
        );

        try {
            $nbTunnels      = count($uris);
            $factory        = $this->_uriFactory;
            for ($i = 0; $i < $nbTunnels; $i++) {
                $uri        = new $factory($uris[$i]);
                $scheme     = $uri->getScheme();
                $upScheme   = strtoupper($scheme);

                if ($i + 1 == $nbTunnels)
                    $cls = 'Erebot_Proxy_EndPoint_'.$upScheme;
                else
                    $cls = 'Erebot_Proxy_'.$upScheme;

                if ($scheme == 'base' || !class_exists($cls))
                    throw new Erebot_InvalidValueException('Invalid class');

                $port = $uri->getPort();
                if ($port === NULL)
                    $port = getservbyname($scheme, 'tcp');
                if (!is_int($port) || $port <= 0 || $port > 65535)
                    throw new Erebot_InvalidValueException('Invalid port');

                if ($this->_socket === NULL) {
                    $this->_socket = stream_socket_client(
                        'tcp://'.$uri->getHost().':'.$port,
                        $errno, $errstr,
                        ini_get('default_socket_timeout'),
                        STREAM_CLIENT_CONNECT
                    );

                    if ($this->_socket === FALSE)
                        throw new Erebot_Exception('Could not connect');
                }

                // We're not the last link of the chain.
                if ($i + 1 < $nbTunnels) {
                    $proxy  = new $cls($this->_socket);
                    if (!($proxy instanceof Erebot_Proxy_Base))
                        throw new Erebot_InvalidValueException('Invalid class');

                    $next   = new $factory($uris[$i + 1]);
                    $proxy->proxify($uri, $next);
                    $logger->debug(
                        "Successfully established connection ".
                        "through proxy '%s'",
                        $uri->toURI(FALSE, FALSE)
                    );
                }
                // That's the endpoint.
                else {
                    $endPoint   = new $cls();
                    if (!($endPoint instanceof Erebot_Interface_Proxy_EndPoint))
                        throw new Erebot_InvalidValueException('Invalid class');

                    $query      = $uri->getQuery();
                    $params     = array();
                    if ($query !== NULL)
                        parse_str($query, $params);

                    stream_context_set_option(
                        $this->_socket,
                        'ssl', 'verify_peer',
                        isset($params['verify_peer'])
                        ? Erebot_Config_Proxy::_parseBool(
                            $params['verify_peer']
                        )
                        : TRUE
                    );

                    stream_context_set_option(
                        $this->_socket,
                        'ssl', 'allow_self_signed',
                        isset($params['allow_self_signed'])
                        ? Erebot_Config_Proxy::_parseBool(
                            $params['allow_self_signed']
                        )
                        : TRUE
                    );

                    stream_context_set_option(
                        $this->_socket,
                        'ssl', 'ciphers',
                        isset($params['ciphers'])
                        ? $params['ciphers']
                        : 'HIGH'
                    );

                    // Avoid unnecessary buffers
                    // and activate TLS encryption if required.
                    stream_set_write_buffer($this->_socket, 0);
                    if ($endPoint->requiresSSL()) {
                        stream_socket_enable_crypto(
                            $this->_socket, TRUE,
                            STREAM_CRYPTO_METHOD_TLS_CLIENT
                        );
                    }
                }
            }
        }
        catch (Exception $e) {
            if ($this->_socket)
                fclose($this->_socket);

            throw new Erebot_ConnectionFailureException(
                sprintf(
                    "Unable to connect to '%s' (%s)",
                    $uris[count($uris) - 1], $e->getMessage()
                )
            );
        }

        $this->dispatch($this->_eventsProducer->makeEvent('!Logon'));
        return TRUE;
    }

    /// \copydoc Erebot_Interface_Connection::disconnect()
    public function disconnect($quitMessage = NULL)
    {
        $logging    = Plop::getInstance();
        $logger     = $logging->getLogger(__FILE__);
        $uris       = $this->_config->getConnectionURI();
        $logger->info("Disconnecting from '%s' ...", $uris[count($uris) - 1]);

        // Purge send queue and send QUIT message to notify server.
        $this->_sndQueue = array();
        $quitMessage =
            Erebot_Utils::stringifiable($quitMessage)
            ? ' :'.$quitMessage
            : '';
        $this->pushLine('QUIT'.$quitMessage);

        // Send any pending data in the outgoing buffer.
        while (1) {
            $this->processOutgoingData();
            if ($this->emptySendQueue())
                break;
            usleep(50000); // Sleep for 50ms.
        }

        // Then kill the connection for real.
        $this->_bot->removeConnection($this);
        if (is_resource($this->_socket))
            fclose($this->_socket);
        $this->_socket      = NULL;
        $this->_connected   = FALSE;
    }

    /// \copydoc Erebot_Interface_SendingConnection::pushLine()
    public function pushLine($line)
    {
        $chars = array("\r", "\n");
        foreach ($chars as $char) {
            if (strpos($line, $char) !== FALSE) {
                throw new Erebot_InvalidValueException(
                    'Line contains forbidden characters'
                );
            }
        }
        $this->_sndQueue[] = $line;
    }

    /// \copydoc Erebot_Interface_Connection::getConfig()
    public function getConfig($chan)
    {
        if ($chan === NULL)
            return $this->_config;

        try {
            $netCfg     = $this->_config->getNetworkCfg();
            $chanCfg    = $netCfg->getChannelCfg($chan);
            unset($netCfg);
            return $chanCfg;
        }
        catch (Erebot_NotFoundException $e) {
            return $this->_config;
        }
    }

    /// \copydoc Erebot_Interface_Connection::getSocket()
    public function getSocket()
    {
        return $this->_socket;
    }

    /// \copydoc Erebot_Interface_ReceivingConnection::emptyReadQueue()
    public function emptyReadQueue()
    {
        return (count($this->_rcvQueue) == 0);
    }

    /// \copydoc Erebot_Interface_SendingConnection::emptySendQueue()
    public function emptySendQueue()
    {
        return (count($this->_sndQueue) == 0);
    }

    /**
     * Retrieves a single line of text from the incoming buffer
     * and puts it in the incoming FIFO.
     *
     * \retval TRUE
     *      Whether a line could be fetched from the buffer.
     *
     * \retval FALSE
     *      ... or not.
     *
     * \note
     *      Lines fetched by this method are always UTF-8 encoded.
     */
    protected function _getSingleLine()
    {
        $pos = strpos($this->_incomingData, "\r\n");
        if ($pos === FALSE)
            return FALSE;

        $line = Erebot_Utils::toUTF8(substr($this->_incomingData, 0, $pos));
        $this->_incomingData    = substr($this->_incomingData, $pos + 2);
        $this->_rcvQueue[]      = $line;

        $logging    = Plop::getInstance();
        $logger     = $logging->getLogger(
            __FILE__ . DIRECTORY_SEPARATOR . 'input'
        );
        $logger->debug("%s", addcslashes($line, "\000..\037"));
        return TRUE;
    }

    /// \copydoc Erebot_Interface_ReceivingConnection::processIncomingData()
    public function processIncomingData()
    {
        $received   = fread($this->_socket, 4096);
        if ($received === FALSE || feof($this->_socket)) {
            $event = $this->_eventsProducer->makeEvent('!Disconnect');
            $this->dispatch($event);

            if (!$event->preventDefault()) {
                $logging    = Plop::getInstance();
                $logger     = $logging->getLogger(__FILE__);
                $logger->error('Disconnected');
                throw new Erebot_ConnectionFailureException('Disconnected');
            }
            return;
        }

        $this->_incomingData .= $received;
        while ($this->_getSingleLine())
            ;   // Read messages.
    }

    /// \copydoc Erebot_Interface_SendingConnection::processOutgoingData()
    public function processOutgoingData()
    {
        if ($this->emptySendQueue()) {
            throw new Erebot_NotFoundException(
                'No outgoing data needs to be handled'
            );
        }

        $line       = array_shift($this->_sndQueue);
        $logging    = Plop::getInstance();
        $logger     = $logging->getLogger(
            __FILE__ . DIRECTORY_SEPARATOR . 'output'
        );

        try {
            /// @TODO:  use some variable from the configuration instead
            //          or having the module's name hard-coded like that.
            $rateLimiter = $this->getModule(
                'Erebot_Module_RateLimiter',
                NULL, FALSE
            );

            try {
                // Ask politely if we can send our message.
                if (!$rateLimiter->canSend()) {
                    // Put back what we took before.
                    array_unshift($this->_sndQueue, $line);
                    return;
                }
            }
            catch (Exception $e) {
                $logger->exception(
                    $this->_bot->gettext(
                        'Got an exception from the rate-limiter module. '.
                        'Assuming implicit approval to send the message.'
                    ),
                    $e
                );
            }
        }
        catch (Erebot_NotFoundException $e) {
            // No rate-limit in effect, send away!
        }

        // Make sure we send the whole line,
        // with a trailing CR LF sequence.
        $line .= "\r\n";
        for (
            $written = 0, $len = strlen($line);
            $written < $len;
            $written += $fwrite
        ) {
            $fwrite = @fwrite($this->_socket, substr($line, $written));
            if ($fwrite === FALSE)
                return FALSE;
        }
        $logger->debug("%s", addcslashes(substr($line, 0, -2), "\000..\037"));
        return $written;
    }

    /// \copydoc Erebot_Interface_ReceivingConnection::processQueuedData()
    public function processQueuedData()
    {
        if (!count($this->_rcvQueue))
            return;

        while (count($this->_rcvQueue))
            $this->_eventsProducer->parseLine(array_shift($this->_rcvQueue));
    }

    /// \copydoc Erebot_Interface_Connection::getBot()
    public function getBot()
    {
        return $this->_bot;
    }

    protected function _loadModule(
        $module,
        $chan,
        $flags,
       &$plainModules,
       &$channelModules
    )
    {
        if ($chan !== NULL) {
            if (isset($channelModules[$chan][$module]))
                return $channelModules[$chan][$module];
        }

        else if (isset($plainModules[$module]))
            return $plainModules[$module];

        $logging    = Plop::getInstance();
        $logger     = $logging->getLogger(__FILE__);

        if (!class_exists($module, TRUE)) {
            throw new Erebot_InvalidValueException("No such class '$module'");
        }

        if (!is_subclass_of($module, 'Erebot_Module_Base')) {
            throw new Erebot_InvalidValueException(
                "Invalid module! Not a subclass of Erebot_Module_Base."
            );
        }

        $instance = new $module($chan);
        if ($chan === NULL)
            $plainModules[$module] = $instance;
        else
            $channelModules[$chan][$module] = $instance;

        $instance->reload($this, $flags);
        $logger->info(
            $this->_bot->gettext("Successfully loaded module '%s'"),
            $module
        );
        return $instance;
    }

    /// \copydoc Erebot_Interface_ModuleContainer::loadModule()
    public function loadModule($module, $chan = NULL)
    {
        return $this->_loadModule(
            $module, $chan,
            Erebot_Module_Base::RELOAD_ALL,
            $this->_plainModules,
            $this->_channelModules
        );
    }

    /// \copydoc Erebot_Interface_ModuleContainer::getModules()
    public function getModules($chan = NULL)
    {
        if ($chan !== NULL) {
            $chanModules =  isset($this->_channelModules[$chan])
                            ? $this->_channelModules[$chan]
                            : array();
            return $chanModules + $this->_plainModules;
        }
        return $this->_plainModules;
    }

    /// \copydoc Erebot_Interface_ModuleContainer::getModule()
    public function getModule($name, $chan = NULL, $autoload = TRUE)
    {
        if ($chan !== NULL) {
            if (isset($this->_channelModules[$chan][$name]))
                return $this->_channelModules[$chan][$name];

            $netCfg     = $this->_config->getNetworkCfg();
            $chanCfg    = $netCfg->getChannel($chan);
            $modules    = $chanCfg->getModules(FALSE);
            if (in_array($name, $modules, TRUE)) {
                if (!$autoload)
                    throw new Erebot_NotFoundException('No instance found');
                return $this->loadModule($name, $chan);
            }
        }

        if (isset($this->_plainModules[$name]))
            return $this->_plainModules[$name];

        $modules = $this->_config->getModules(TRUE);
        if (!in_array($name, $modules, TRUE) || !$autoload)
            throw new Erebot_NotFoundException('No instance found');

        return $this->loadModule($name, NULL);
    }

    /// \copydoc Erebot_Interface_EventDispatcher::addRawHandler()
    public function addRawHandler(Erebot_Interface_RawHandler $handler)
    {
        $this->_raws[] = $handler;
    }

    /// \copydoc Erebot_Interface_EventDispatcher::removeRawHandler()
    public function removeRawHandler(Erebot_Interface_RawHandler $handler)
    {
        $key = array_search($handler, $this->_raws);
        if ($key === FALSE)
            throw new Erebot_NotFoundException('No such raw handler');
        unset($this->_raws[$key]);
    }

    /// \copydoc Erebot_Interface_EventDispatcher::addEventHandler()
    public function addEventHandler(Erebot_Interface_EventHandler $handler)
    {
        $this->_events[] = $handler;
    }

    /// \copydoc Erebot_Interface_EventDispatcher::removeEventHandler()
    public function removeEventHandler(Erebot_Interface_EventHandler $handler)
    {
        $key = array_search($handler, $this->_events);
        if ($key === FALSE)
            throw new Erebot_NotFoundException('No such event handler');
        unset($this->_events[$key]);
    }

    /**
     * Dispatches the given event to handlers
     * which have been registered for this type of event.
     *
     * \param Erebot_Interface_Event_Base_Generic $event
     *      An event to dispatch.
     */
    protected function _dispatchEvent(
        Erebot_Interface_Event_Base_Generic $event
    )
    {
        $logging    = Plop::getInstance();
        $logger     = $logging->getLogger(__FILE__);
//        $logger->debug(
//            $this->_bot->gettext('Dispatching "%s" event.'),
//            get_class($event)
//        );
        try {
            foreach ($this->_events as $handler) {
                if ($handler->handleEvent($event) === FALSE)
                    break;
            }
        }
        // This should help make the code a little more "bug-free" (TM).
        catch (Erebot_ErrorReportingException $e) {
            $logger->exception($this->_bot->gettext('Code is not clean!'), $e);
            $this->disconnect($e->getMessage());
        }
    }

    /**
     * Dispatches the given raw to handlers
     * which have been registered for this type of raw.
     *
     * \param Erebot_Interface_Event_Raw $raw
     *      A raw message to dispatch.
     */
    protected function _dispatchRaw(Erebot_Interface_Event_Raw $raw)
    {
        $logging    = Plop::getInstance();
        $logger     = $logging->getLogger(__FILE__);
//        $logger->debug(
//            $this->_bot->gettext('Dispatching raw #%s.'),
//            sprintf('%03d', $raw->getRaw())
//        );
        try {
            foreach ($this->_raws as $handler) {
                if ($handler->handleRaw($raw) === FALSE)
                    break;
            }
        }
        // This should help make the code a little more "bug-free" (TM).
        catch (Erebot_ErrorReportingException $e) {
            $logger->exception($this->_bot->gettext('Code is not clean!'), $e);
            $this->disconnect($e->getMessage());
        }
    }

    /// \copydoc Erebot_Interface_EventDispatcher::dispatch()
    public function dispatch(Erebot_Interface_Event_Base_Generic $event)
    {
        if ($event instanceof Erebot_Interface_Event_Raw)
            return $this->_dispatchRaw($event);
        return $this->_dispatchEvent($event);
    }

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
    public function isChannel($chan)
    {
        try {
            $capabilities = $this->getModule(
                'Erebot_Module_ServerCapabilities',
                NULL, FALSE
            );
            return $capabilities->isChannel($chan);
        }
        catch (Erebot_NotFoundException $e) {
            // Ignore silently.
        }

        if (!Erebot_Utils::stringifiable($chan)) {
            throw new Erebot_InvalidValueException(
                $this->_bot->gettext('Bad channel name')
            );
        }

        $chan = (string) $chan;
        if (!strlen($chan))
            return FALSE;

        // Restricted characters in channel names,
        // as per RFC 2811 - (2.1) Namespace.
        foreach (array(' ', ',', "\x07", ':') as $token)
            if (strpos($token, $chan) !== FALSE)
                return FALSE;

        if (strlen($chan) > 50)
            return FALSE;

        // As per RFC 2811 - (2.1) Namespace.
        return (strpos('#&+!', $chan[0]) !== FALSE);
    }

    public function handleCapabilities(
        Erebot_Interface_EventHandler   $handler,
        Erebot_Event_ServerCapabilities $event
    )
    {
        $module = $event->getModule();
        $cmds   = array(
            'WATCH'     => '!WATCH',
            'ISON'      => '!ISON',
            'JUPE'      => '!JUPE',
            'MAP'       => '!MAP',
            'DCCLIST'   => '!DCCLIST',
            'GLIST'     => '!GLIST',
            'RULES'     => '!RULES',
            'SILENCE'   => '!SILENCE',
            'STARTTLS'  => '!STARTTLS',
        );

        foreach ($cmds as $cmd => $profile) {
            if ($module->hasCommand($cmd))
                $this->_rawProfileLoader[] =
                    str_replace('!', 'Erebot_Interface_RawProfile_', $profile);
        }

        $validMappings  = array(
            // This is already the default value, but we still define it
            // in case setCollator() was called to change the default.
            'rfc1459'           => 'Erebot_IrcCollator_RFC1459',
            'strict-rfc1459'    => 'Erebot_IrcCollator_StrictRFC1459',
            'ascii'             => 'Erebot_IrcCollator_ASCII',
        );
        $caseMapping    = strtolower($module->getCaseMapping());
        if (in_array($caseMapping, array_keys($validMappings))) {
            $cls = $validMappings[$caseMapping];
            $this->_collator = new $cls();
        }
    }

    public function handleConnect(
        Erebot_Interface_EventHandler   $handler,
        Erebot_Interface_Event_Connect  $raw
    )
    {
        $this->_connected = TRUE;
    }

    public function setCollator(Erebot_Interface_IrcCollator $collator)
    {
        $this->_collator = $collator;
    }

    public function getCollator()
    {
        return $this->_collator;
    }

    public function getEventsProducer()
    {
        return $this->_eventsProducer;
    }
}

