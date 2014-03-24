<?php
/*
    This file is part of Erebot, a modular IRC bot written in PHP.

    Copyright © 2010 François Poirotte

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

namespace Erebot;

/**
 * \brief
 *      Handles a (possibly encrypted) connection to an IRC server.
 */
class IrcConnection implements \Erebot\Interfaces\IrcConnection
{
    /**
     * A configuration object implementing
     * the Erebot::Interfaces::Config::Server interface.
     */
    protected $_config;

    /// A bot object implementing the Erebot::Interfaces::Core interface.
    protected $_bot;

    /// The underlying socket, represented as a stream.
    protected $_socket;

    /// Maps channels to their loaded modules.
    protected $_channelModules;

    /// Maps modules names to modules instances.
    protected $_plainModules;

    /// A list of numeric handlers.
    protected $_numerics;

    /// A list of event handlers.
    protected $_events;

    /// Whether this connection is actually... well, connected.
    protected $_connected;

    /// Factory to use to parse URI.
    protected $_uriFactory;

    /// Numeric profile.
    protected $_numericProfile;

    /// Collator for IRC nicknames.
    protected $_collator;

    /// Class to use to parse IRC messages and produce events from them.
    protected $_eventsProducer;

    /// I/O manager for the socket.
    protected $_io;

    /**
     * Constructs the object which will hold a connection.
     *
     * \param Erebot::Interfaces::Core $bot
     *      A bot instance.
     *
     * \param Erebot::Interfaces::Config::Server $config
     *      The connfiguration for this connection.
     *
     * \param array $events
     *      (optional) A mapping of event interface names
     *      to the class that must be used to produce such
     *      events (factory).
     *
     * \note
     *      No connection (in the socket sense) is actually
     *      created until Erebot::Interfaces::Connection::connect()
     *      is called.
     */
    public function __construct(
        \Erebot\Interfaces\Core $bot,
        \Erebot\Interfaces\Config\Server $config = NULL,
        $events = array()
    )
    {
        $this->_config          = $config;
        $this->_bot             = $bot;

        $this->_channelModules  = array();
        $this->_plainModules    = array();
        $this->_numerics        = array();
        $this->_events          = array();
        $this->_connected       = FALSE;
        $this->_io              = new \Erebot\LineIO(\Erebot\LineIO::EOL_WIN);
        $this->_collator        = new \Erebot\IrcCollator_RFC1459();
        $this->_eventsProducer  = new \Erebot\IrcParser($this);
        /// @FIXME: this should really be done in some other way.
        $this->_eventsProducer->setEventClasses($events);
        $this->setURIFactory('\\Erebot\\URI');
        $this->setNumericProfile(new \Erebot\NumericProfile_RFC2812());

        $this->addEventHandler(
            new \Erebot\EventHandler(
                new \Erebot\CallableWrapper(array($this, 'handleCapabilities')),
                new \Erebot\Event\Match\Type(
                    '\\Erebot\\Event\\ServerCapabilities'
                )
            )
        );

        $this->addEventHandler(
            new \Erebot\EventHandler(
                new \Erebot\CallableWrapper(array($this, 'handleConnect')),
                new \Erebot\Event\Match\Type(
                    '\\Erebot\\Interface\\Event\\Connect'
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
            $this->_numerics,
            $this->_config,
            $this->_bot,
            $this->_channelModules,
            $this->_plainModules,
            $this->_uriFactory
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
        if (!$reflector->implementsInterface('\\Erebot\\URIInterface')) {
            throw new \Erebot\InvalidValueException(
                'The factory must implement \\Erebot\\URIInterface'
            );
        }
        $this->_uriFactory = $factory;
    }

    public function getNumericProfile()
    {
        return $this->_numericProfile;
    }

    public function setNumericProfile(\Erebot\NumericProfile\Base $profile)
    {
        $this->_numericProfile = $profile;
    }

    /**
     * Reloads/sets the configuration for this connection.
     *
     * \param Erebot::Interfaces::Config::Server $config
     *      The new configuration for this connection.
     */
    public function reload(\Erebot\Interfaces\Config\Server $config)
    {
        $this->_loadModules(
            $config,
            \Erebot\Module\Base::RELOAD_ALL
        );
        $this->_config = $config;
    }

    /**
     * (Re)Load the modules for this connection.
     *
     * \param Erebot::Interfaces::Config::Server $config
     *      A server configuration that describes the
     *      modules to load.
     *
     * \param int $flags
     *      Flags that will be passed to the reload()
     *      method of every module that needs reloading.
     *      This is a bitwise-OR of the RELOAD_* constants
     *      defined in Erebot::Module::Base.
     */
    protected function _loadModules(
        \Erebot\Interfaces\Config\Server $config,
        $flags
    )
    {
        $logger         = Plop::getInstance();

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
            catch (\Erebot\NotFoundException $e) {
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
                catch (\Erebot\StopException $e) {
                    throw $e;
                }
                catch (\Exception $e) {
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
            catch (\Erebot\StopException $e) {
                throw $e;
            }
            catch (\Exception $e) {
                $logger->warning($e->getMessage());
            }
        }

        // Unload old module instances.
        foreach ($channelModules as $modules)
            foreach ($modules as $module)
                $module->unloadModule();
        foreach ($plainModules as $module)
            $module->unloadModule();
    }

    public function isConnected()
    {
        return $this->_connected;
    }

    public function connect()
    {
        if ($this->_connected)
            return FALSE;

        $logger         = Plop::getInstance();
        $uris           = $this->_config->getConnectionURI();
        $serverUri      = new \Erebot\URI($uris[count($uris) - 1]);
        $this->_socket  = NULL;

        $logger->info(
            $this->_bot->gettext('Loading required modules for "%(uri)s"...'),
            array('uri' => $serverUri)
        );
        $this->_loadModules(
            $this->_config,
            \Erebot\Module\Base::RELOAD_ALL |
            \Erebot\Module\Base::RELOAD_INIT
        );

        try {
            $nbTunnels      = count($uris);
            $factory        = $this->_uriFactory;
            for ($i = 0; $i < $nbTunnels; $i++) {
                $uri        = new $factory($uris[$i]);
                $scheme     = $uri->getScheme();
                $upScheme   = strtoupper($scheme);

                if ($i + 1 == $nbTunnels)
                    $cls = '\\Erebot\\Proxy\\EndPoint\\'.$upScheme;
                else
                    $cls = '\\Erebot\\Proxy\\'.$upScheme;

                if ($scheme == 'base' || !class_exists($cls))
                    throw new \Erebot\InvalidValueException('Invalid class');

                $port = $uri->getPort();
                if ($port === NULL)
                    $port = getservbyname($scheme, 'tcp');
                if (!is_int($port) || $port <= 0 || $port > 65535)
                    throw new \Erebot\InvalidValueException('Invalid port');

                if ($this->_socket === NULL) {
                    $this->_socket = @stream_socket_client(
                        'tcp://'.$uri->getHost().':'.$port,
                        $errno, $errstr,
                        ini_get('default_socket_timeout'),
                        STREAM_CLIENT_CONNECT
                    );

                    if ($this->_socket === FALSE)
                        throw new \Erebot\Exception('Could not connect');
                }

                // We're not the last link of the chain.
                if ($i + 1 < $nbTunnels) {
                    $proxy  = new $cls($this->_socket);
                    if (!($proxy instanceof \Erebot\Proxy\Base))
                        throw new \Erebot\InvalidValueException('Invalid class');

                    $next   = new $factory($uris[$i + 1]);
                    $proxy->proxify($uri, $next);
                    $logger->debug(
                        "Successfully established connection ".
                        "through proxy '%(uri)s'",
                        array('uri' => $uri->toURI(FALSE, FALSE))
                    );
                }
                // That's the endpoint.
                else {
                    $endPoint   = new $cls();
                    if (!($endPoint instanceof \Erebot\Interfaces\Proxy\EndPoint))
                        throw new \Erebot\InvalidValueException('Invalid class');

                    $query      = $uri->getQuery();
                    $params     = array();
                    if ($query !== NULL)
                        parse_str($query, $params);

                    stream_context_set_option(
                        $this->_socket,
                        'ssl', 'verify_peer',
                        isset($params['verify_peer'])
                        ? \Erebot\Config\Proxy::_parseBool(
                            $params['verify_peer']
                        )
                        : TRUE
                    );

                    stream_context_set_option(
                        $this->_socket,
                        'ssl', 'allow_self_signed',
                        isset($params['allow_self_signed'])
                        ? \Erebot\Config\Proxy::_parseBool(
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

            throw new \Erebot\ConnectionFailureException(
                sprintf(
                    "Unable to connect to '%s' (%s)",
                    $uris[count($uris) - 1], $e->getMessage()
                )
            );
        }

        $this->_io->setSocket($this->_socket);
        $this->dispatch($this->_eventsProducer->makeEvent('!Logon'));
        return TRUE;
    }

    public function disconnect($quitMessage = NULL)
    {
        $logger     = Plop::getInstance();
        $uris       = $this->_config->getConnectionURI();
        $logger->info(
            "Disconnecting from '%(uri)s' ...",
            array('uri' => $uris[count($uris) - 1])
        );

        // Purge send queue and send QUIT message to notify server.
        $this->_io->setSocket($this->_socket);
        $quitMessage =
            \Erebot\Utils::stringifiable($quitMessage)
            ? ' :'.$quitMessage
            : '';
        $this->_io->push('QUIT'.$quitMessage);

        // Send any pending data in the outgoing buffer.
        while ($this->_io->inWriteQueue()) {
            $this->_io->write();
            usleep(50000); // Sleep for 50ms.
        }

        // Then kill the connection for real.
        $this->_bot->removeConnection($this);
        if (is_resource($this->_socket))
            fclose($this->_socket);
        $this->_io->setSocket(NULL);
        $this->_socket      = NULL;
        $this->_connected   = FALSE;
    }

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
        catch (\Erebot\NotFoundException $e) {
            return $this->_config;
        }
    }

    public function getSocket()
    {
        return $this->_socket;
    }

    public function getBot()
    {
        return $this->_bot;
    }

    public function getIO()
    {
        return $this->_io;
    }

    public function read()
    {
        $res = $this->_io->read();
        if ($res === FALSE) {
            $event = $this->_eventsProducer->makeEvent('!Disconnect');
            $this->dispatch($event);

            if (!$event->preventDefault()) {
                $logger     = Plop::getInstance();
                $logger->error('Disconnected');
                throw new \Erebot\ConnectionFailureException('Disconnected');
            }
        }
        return $res;
    }

    /// Processes commands queued in the input buffer.
    public function process()
    {
        for ($i = $this->_io->inReadQueue(); $i > 0; $i--)
            $this->_eventsProducer->parseLine($this->_io->pop());
    }

    public function write()
    {
        if (!$this->_io->inWriteQueue()) {
            throw new \Erebot\NotFoundException(
                'No outgoing data needs to be handled'
            );
        }

        $logger     = \Plop::getInstance();

        try {
            /// @TODO:  use some variable from the configuration instead
            //          or having the module's name hard-coded like that.
            $rateLimiter = $this->getModule(
                '\\Erebot\\Module\\RateLimiter',
                NULL, FALSE
            );

            try {
                // Ask politely if we can send our message.
                if (!$rateLimiter->canSend()) {
                    return FALSE;
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
        catch (\Erebot\NotFoundException $e) {
            // No rate-limit in effect, send away!
        }

        return $this->_io->write();
    }

    /**
     * Load a single module for this connection.
     *
     * \param string $module
     *      Name of the module to load. If the module
     *      is already loaded, nothing will happen.
     *
     * \param string|NULL $chan
     *      Name of the IRC channel for which this module
     *      is being loaded. Pass NULL to load a module
     *      globally (for the whole connection) rather than
     *      for a specific IRC channel.
     *
     * \param opaque $flags
     *      Bitwise-OR combination of flags to pass to
     *      the module's initialization method.
     *
     * \param array $plainModules
     *      An associative array containing the global modules
     *      currently loaded. This array will be updated if it
     *      needs to be once the module has been successfully
     *      loaded.
     *
     * \param array $channelModules
     *      An associative array containing the modules currently
     *      loaded for the given IRC channel. This array will be
     *      updated if it needs to be once the module has been
     *      successfully loaded.
     *
     * \retval Erebot::Module::Base
     *      An instance of the module with the given name.
     */
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

        if (!class_exists($module, TRUE)) {
            throw new \Erebot\InvalidValueException("No such class '$module'");
        }

        if (!is_subclass_of($module, '\\Erebot\\Module\\Base')) {
            throw new \Erebot\InvalidValueException(
                "Invalid module! Not a subclass of \\Erebot\\Module\\Base."
            );
        }

        $reflector = new ReflectionClass($module);
        $instance = new $module($chan);
        if ($chan === NULL)
            $plainModules[$module] = $instance;
        else
            $channelModules[$chan][$module] = $instance;

        $instance->reloadModule($this, $flags);
        $logger = Plop::getInstance();
        $logger->info(
            $this->_bot->gettext("Successfully loaded module '%(module)s' [%(source)s]"),
            array(
                'module' => $module,
                'source' => (substr($reflector->getFileName(), 0, 7) == 'phar://')
                            ? $this->_bot->gettext('PHP archive')
                            : $this->_bot->gettext('regular file'),
            )
        );
        return $instance;
    }

    public function loadModule($module, $chan = NULL)
    {
        return $this->_loadModule(
            $module, $chan,
            \Erebot\Module\Base::RELOAD_ALL,
            $this->_plainModules,
            $this->_channelModules
        );
    }

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

    public function getModule($name, $chan = NULL, $autoload = TRUE)
    {
        if ($chan !== NULL) {
            if (isset($this->_channelModules[$chan][$name]))
                return $this->_channelModules[$chan][$name];

            $netCfg     = $this->_config->getNetworkCfg();
            $chanCfg    = $netCfg->getChannelCfg($chan);
            $modules    = $chanCfg->getModules(FALSE);
            if (in_array($name, $modules, TRUE)) {
                if (!$autoload)
                    throw new \Erebot\NotFoundException('No instance found');
                return $this->loadModule($name, $chan);
            }
        }

        if (isset($this->_plainModules[$name]))
            return $this->_plainModules[$name];

        $modules = $this->_config->getModules(TRUE);
        if (!in_array($name, $modules, TRUE) || !$autoload)
            throw new \Erebot\NotFoundException('No instance found');

        return $this->loadModule($name, NULL);
    }

    public function addNumericHandler(\Erebot\Interfaces\NumericHandler $handler)
    {
        $this->_numerics[] = $handler;
    }

    public function removeNumericHandler(\Erebot\Interfaces\NumericHandler $handler)
    {
        $key = array_search($handler, $this->_numerics);
        if ($key === FALSE)
            throw new \Erebot\NotFoundException('No such numeric handler');
        unset($this->_numerics[$key]);
    }

    public function addEventHandler(\Erebot\Interfaces\EventHandler $handler)
    {
        $this->_events[] = $handler;
    }

    public function removeEventHandler(\Erebot\Interfaces\EventHandler $handler)
    {
        $key = array_search($handler, $this->_events);
        if ($key === FALSE)
            throw new \Erebot\NotFoundException('No such event handler');
        unset($this->_events[$key]);
    }

    /**
     * Dispatches the given event to handlers
     * which have been registered for this type of event.
     *
     * \param Erebot::Interfaces::Event::Base::Generic $event
     *      An event to dispatch.
     */
    protected function _dispatchEvent(\Erebot\Interfaces\Event\Base\Generic $event)
    {
        $logger = Plop::getInstance();
        $logger->debug(
            $this->_bot->gettext('Dispatching "%(type)s" event.'),
            array('type' => get_class($event))
        );
        try {
            foreach ($this->_events as $handler) {
                if ($handler->handleEvent($event) === FALSE)
                    break;
            }
        }
        // This should help make the code a little more "bug-free" (TM).
        catch (\Erebot\ErrorReportingException $e) {
            $logger->exception($this->_bot->gettext('Code is not clean!'), $e);
            $this->disconnect($e->getMessage());
        }
    }

    /**
     * Dispatches the given numeric event to handlers
     * which have been registered for this type of numeric.
     *
     * \param Erebot::Interfaces::Event::Numeric $numeric
     *      A numeric message to dispatch.
     */
    protected function _dispatchNumeric(\Erebot\Interfaces\Event\Numeric $numeric)
    {
        $logger = Plop::getInstance();
        $logger->debug(
            $this->_bot->gettext('Dispatching numeric %(code)s.'),
            array('code' => sprintf('%03d', $numeric->getCode()))
        );
        try {
            foreach ($this->_numerics as $handler) {
                if ($handler->handleNumeric($numeric) === FALSE)
                    break;
            }
        }
        // This should help make the code a little more "bug-free" (TM).
        catch (\Erebot\ErrorReportingException $e) {
            $logger->exception($this->_bot->gettext('Code is not clean!'), $e);
            $this->disconnect($e->getMessage());
        }
    }

    public function dispatch(\Erebot\Interfaces\Event\Base\Generic $event)
    {
        if ($event instanceof \Erebot\Interfaces\Event\Numeric)
            return $this->_dispatchNumeric($event);
        return $this->_dispatchEvent($event);
    }

    public function isChannel($chan)
    {
        try {
            $capabilities = $this->getModule(
                '\\Erebot\\Module\\ServerCapabilities',
                NULL, FALSE
            );
            return $capabilities->isChannel($chan);
        }
        catch (\Erebot\NotFoundException $e) {
            // Ignore silently.
        }

        if (!\Erebot\Utils::stringifiable($chan)) {
            throw new \Erebot\InvalidValueException(
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

    /**
     * Handles the "ServerCapabilities" event.
     *
     * \param Erebot::Interfaces::EventHandler $handler
     *      The event handler responsible for calling
     *      this method.
     *
     * \param Erebot::Event::ServerCapabilities $event
     *      The "ServerCapabilities" event to process.
     */
    public function handleCapabilities(
        \Erebot\Interfaces\EventHandler $handler,
        \Erebot\Event\ServerCapabilities $event
    ) {
        $module = $event->getModule();
        $validMappings  = array(
            // This is already the default value, but we still define it
            // in case setCollator() was called to change the default.
            'rfc1459'           => '\\Erebot\\IrcCollator\\RFC1459',
            'strict-rfc1459'    => '\\Erebot\\IrcCollator\\StrictRFC1459',
            'ascii'             => '\\Erebot\\IrcCollator\\ASCII',
        );
        $caseMapping    = strtolower($module->getCaseMapping());
        if (in_array($caseMapping, array_keys($validMappings))) {
            $cls = $validMappings[$caseMapping];
            $this->_collator = new $cls();
        }
    }

    /**
     * Handles the "Connect" event.
     *
     * \param Erebot::Interfaces::EventHandler $handler
     *      The event handler responsible for calling
     *      this method.
     *
     * \param Erebot::Interfaces::Event::Connect $event
     *      The "Connect" event to process.
     */
    public function handleConnect(
        \Erebot\Interfaces\EventHandler $handler,
        \Erebot\Interfaces\Event\Connect $event
    )
    {
        $this->_connected = TRUE;
    }

    /**
     * Sets the collector to use for this connection.
     *
     * \param Erebot::Interfaces::Collator $collator
     *      The new collator to use for this connection.
     */
    public function setCollator(\Erebot\Interfaces\IrcCollator $collator)
    {
        $this->_collator = $collator;
    }

    /**
     * Returns the collator associated with
     * this connection.
     *
     * \retval Erebot::Interfaces::IrcCollator
     *      The collator for this connection.
     */
    public function getCollator()
    {
        return $this->_collator;
    }

    public function getEventsProducer()
    {
        return $this->_eventsProducer;
    }
}

