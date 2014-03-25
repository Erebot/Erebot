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
    protected $config;

    /// A bot object implementing the Erebot::Interfaces::Core interface.
    protected $bot;

    /// The underlying socket, represented as a stream.
    protected $socket;

    /// Maps channels to their loaded modules.
    protected $channelModules;

    /// Maps modules names to modules instances.
    protected $plainModules;

    /// A list of numeric handlers.
    protected $numerics;

    /// A list of event handlers.
    protected $events;

    /// Whether this connection is actually... well, connected.
    protected $connected;

    /// Factory to use to parse URI.
    protected $uriFactory;

    /// Numeric profile.
    protected $numericProfile;

    /// Collator for IRC nicknames.
    protected $collator;

    /// Class to use to parse IRC messages and produce events from them.
    protected $eventsProducer;

    /// I/O manager for the socket.
    protected $io;

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
        \Erebot\Interfaces\Config\Server $config = null,
        $events = array()
    ) {
        $this->config           = $config;
        $this->bot              = $bot;
        $this->channelModules   = array();
        $this->plainModules     = array();
        $this->numerics         = array();
        $this->events           = array();
        $this->connected        = false;
        $this->io               = new \Erebot\LineIO(\Erebot\LineIO::EOL_WIN);
        $this->collator         = new \Erebot\IrcCollator_RFC1459();
        $this->eventsProducer   = new \Erebot\IrcParser($this);
        /// @FIXME: this should really be done in some other way.
        $this->eventsProducer->setEventClasses($events);
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
        $this->socket = null;
        unset(
            $this->events,
            $this->numerics,
            $this->config,
            $this->bot,
            $this->channelModules,
            $this->plainModules,
            $this->uriFactory
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
        return $this->uriFactory;
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
        $reflector = new \ReflectionClass($factory);
        if (!$reflector->implementsInterface('\\Erebot\\URIInterface')) {
            throw new \Erebot\InvalidValueException(
                'The factory must implement \\Erebot\\URIInterface'
            );
        }
        $this->uriFactory = $factory;
    }

    public function getNumericProfile()
    {
        return $this->numericProfile;
    }

    public function setNumericProfile(\Erebot\NumericProfile\Base $profile)
    {
        $this->numericProfile = $profile;
    }

    /**
     * Reloads/sets the configuration for this connection.
     *
     * \param Erebot::Interfaces::Config::Server $config
     *      The new configuration for this connection.
     */
    public function reload(\Erebot\Interfaces\Config\Server $config)
    {
        $this->loadModules(
            $config,
            \Erebot\Module\Base::RELOAD_ALL
        );
        $this->config = $config;
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
    protected function loadModules(
        \Erebot\Interfaces\Config\Server $config,
        $flags
    ) {
        $logger         = \Plop::getInstance();

        $channelModules = $this->channelModules;
        $plainModules   = $this->plainModules;

        $newNetCfg      = $config->getNetworkCfg();
        $newChannels    = $newNetCfg->getChannels();

        $oldNetCfg      = $this->config->getNetworkCfg();
        $oldChannels    = $oldNetCfg->getChannels();

        // Keep whatever can be kept from the old
        // channels-related module configurations.
        foreach ($oldChannels as $chan => $oldChanCfg) {
            try {
                $newChanCfg = $newNetCfg->getChannelCfg($chan);
                $newModules = $newChanCfg->getModules(false);
                foreach ($oldChanCfg->getModules(false) as $module) {
                    if (!in_array($module, $newModules)) {
                        unset($this->channelModules[$chan][$module]);
                    } elseif (isset($this->channelModules[$chan][$module])) {
                        $this->channelModules[$chan][$module] =
                            clone $this->channelModules[$chan][$module];
                    }
                }
            } catch (\Erebot\NotFoundException $e) {
                unset($this->channelModules[$chan]);
            }
        }

        // Keep whatever can be kept from the old
        // generic module configurations.
        $newModules = $config->getModules(true);
        foreach ($this->config->getModules(true) as $module) {
            if (!in_array($module, $newModules)) {
                unset($this->plainModules[$module]);
            } elseif (isset($this->plainModules[$module])) {
                $this->plainModules[$module] =
                    clone $this->plainModules[$module];
            }
        }

        // Configure new modules, both channel-related
        // and generic ones.
        foreach ($newChannels as $chanCfg) {
            $modules    = $chanCfg->getModules(false);
            $chan       = $chanCfg->getName();
            foreach ($modules as $module) {
                try {
                    $this->loadModule(
                        $module,
                        $chan,
                        $flags,
                        $this->plainModules,
                        $this->channelModules
                    );
                } catch (\Erebot\StopException $e) {
                    throw $e;
                } catch (\Exception $e) {
                    $logger->warning($e->getMessage());
                }
            }
        }

        foreach ($newModules as $module) {
            try {
                $this->loadModule(
                    $module,
                    null,
                    $flags,
                    $this->plainModules,
                    $this->channelModules
                );
            } catch (\Erebot\StopException $e) {
                throw $e;
            } catch (\Exception $e) {
                $logger->warning($e->getMessage());
            }
        }

        // Unload old module instances.
        foreach ($channelModules as $modules) {
            foreach ($modules as $module) {
                $module->unloadModule();
            }
        }
        foreach ($plainModules as $module) {
            $module->unloadModule();
        }
    }

    public function isConnected()
    {
        return $this->connected;
    }

    public function connect()
    {
        if ($this->connected) {
            return false;
        }

        $logger         = \Plop::getInstance();
        $uris           = $this->config->getConnectionURI();
        $serverUri      = new \Erebot\URI($uris[count($uris) - 1]);
        $this->socket  = null;

        $logger->info(
            $this->bot->gettext('Loading required modules for "%(uri)s"...'),
            array('uri' => $serverUri)
        );
        $this->loadModules(
            $this->config,
            \Erebot\Module\Base::RELOAD_ALL |
            \Erebot\Module\Base::RELOAD_INIT
        );

        try {
            $nbTunnels      = count($uris);
            $factory        = $this->uriFactory;
            for ($i = 0; $i < $nbTunnels; $i++) {
                $uri        = new $factory($uris[$i]);
                $scheme     = $uri->getScheme();
                $upScheme   = strtoupper($scheme);

                if ($i + 1 == $nbTunnels) {
                    $cls = '\\Erebot\\Proxy\\EndPoint\\'.$upScheme;
                } else {
                    $cls = '\\Erebot\\Proxy\\'.$upScheme;
                }

                if ($scheme == 'base' || !class_exists($cls)) {
                    throw new \Erebot\InvalidValueException('Invalid class');
                }

                $port = $uri->getPort();
                if ($port === null) {
                    $port = getservbyname($scheme, 'tcp');
                }
                if (!is_int($port) || $port <= 0 || $port > 65535) {
                    throw new \Erebot\InvalidValueException('Invalid port');
                }

                if ($this->socket === null) {
                    $this->socket = @stream_socket_client(
                        'tcp://' . $uri->getHost() . ':' . $port,
                        $errno,
                        $errstr,
                        ini_get('default_socket_timeout'),
                        STREAM_CLIENT_CONNECT
                    );

                    if ($this->socket === false) {
                        throw new \Erebot\Exception('Could not connect');
                    }
                }

                // We're not the last link of the chain.
                if ($i + 1 < $nbTunnels) {
                    $proxy = new $cls($this->socket);
                    if (!($proxy instanceof \Erebot\Proxy\Base)) {
                        throw new \Erebot\InvalidValueException('Invalid class');
                    }

                    $next   = new $factory($uris[$i + 1]);
                    $proxy->proxify($uri, $next);
                    $logger->debug(
                        "Successfully established connection ".
                        "through proxy '%(uri)s'",
                        array('uri' => $uri->toURI(false, false))
                    );
                } else {
                    // That's the endpoint.
                    $endPoint = new $cls();
                    if (!($endPoint instanceof \Erebot\Interfaces\Proxy\EndPoint)) {
                        throw new \Erebot\InvalidValueException('Invalid class');
                    }

                    $query      = $uri->getQuery();
                    $params     = array();
                    if ($query !== null) {
                        parse_str($query, $params);
                    }

                    stream_context_set_option(
                        $this->socket,
                        'ssl',
                        'verify_peer',
                        isset($params['verify_peer'])
                        ? \Erebot\Config\Proxy::_parseBool(
                            $params['verify_peer']
                        )
                        : true
                    );

                    stream_context_set_option(
                        $this->socket,
                        'ssl',
                        'allow_self_signed',
                        isset($params['allow_self_signed'])
                        ? \Erebot\Config\Proxy::_parseBool(
                            $params['allow_self_signed']
                        )
                        : true
                    );

                    stream_context_set_option(
                        $this->socket,
                        'ssl',
                        'ciphers',
                        isset($params['ciphers'])
                        ? $params['ciphers']
                        : 'HIGH'
                    );

                    // Avoid unnecessary buffers
                    // and activate TLS encryption if required.
                    stream_set_write_buffer($this->socket, 0);
                    if ($endPoint->requiresSSL()) {
                        stream_socket_enable_crypto(
                            $this->socket,
                            true,
                            STREAM_CRYPTO_METHOD_TLS_CLIENT
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            if ($this->socket) {
                fclose($this->socket);
            }

            throw new \Erebot\ConnectionFailureException(
                sprintf(
                    "Unable to connect to '%s' (%s)",
                    $uris[count($uris) - 1],
                    $e->getMessage()
                )
            );
        }

        $this->io->setSocket($this->socket);
        $this->dispatch($this->eventsProducer->makeEvent('!Logon'));
        return true;
    }

    public function disconnect($quitMessage = null)
    {
        $logger     = \Plop::getInstance();
        $uris       = $this->config->getConnectionURI();
        $logger->info(
            "Disconnecting from '%(uri)s' ...",
            array('uri' => $uris[count($uris) - 1])
        );

        // Purge send queue and send QUIT message to notify server.
        $this->io->setSocket($this->socket);
        $quitMessage =
            \Erebot\Utils::stringifiable($quitMessage)
            ? ' :'.$quitMessage
            : '';
        $this->io->push('QUIT'.$quitMessage);

        // Send any pending data in the outgoing buffer.
        while ($this->io->inWriteQueue()) {
            $this->io->write();
            usleep(50000); // Sleep for 50ms.
        }

        // Then kill the connection for real.
        $this->bot->removeConnection($this);
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }

        $this->io->setSocket(null);
        $this->socket      = null;
        $this->connected   = false;
    }

    public function getConfig($chan)
    {
        if ($chan === null) {
            return $this->config;
        }

        try {
            $netCfg     = $this->config->getNetworkCfg();
            $chanCfg    = $netCfg->getChannelCfg($chan);
            unset($netCfg);
            return $chanCfg;
        } catch (\Erebot\NotFoundException $e) {
            return $this->config;
        }
    }

    public function getSocket()
    {
        return $this->socket;
    }

    public function getBot()
    {
        return $this->bot;
    }

    public function getIO()
    {
        return $this->io;
    }

    public function read()
    {
        $res = $this->io->read();
        if ($res === false) {
            $event = $this->eventsProducer->makeEvent('!Disconnect');
            $this->dispatch($event);

            if (!$event->preventDefault()) {
                $logger = \Plop::getInstance();
                $logger->error('Disconnected');
                throw new \Erebot\ConnectionFailureException('Disconnected');
            }
        }
        return $res;
    }

    /// Processes commands queued in the input buffer.
    public function process()
    {
        for ($i = $this->io->inReadQueue(); $i > 0; $i--) {
            $this->eventsProducer->parseLine($this->io->pop());
        }
    }

    public function write()
    {
        if (!$this->io->inWriteQueue()) {
            throw new \Erebot\NotFoundException(
                'No outgoing data needs to be handled'
            );
        }

        $logger     = \Plop::getInstance();

        try {
            /// @TODO:  use some variable from the configuration instead
            //          or having the module's name hard-coded like that.
            $rateLimiter = $this->getModule('\\Erebot\\Module\\RateLimiter', null, false);

            try {
                // Ask politely if we can send our message.
                if (!$rateLimiter->canSend()) {
                    return false;
                }
            } catch (\Exception $e) {
                $logger->exception(
                    $this->bot->gettext(
                        'Got an exception from the rate-limiter module. '.
                        'Assuming implicit approval to send the message.'
                    ),
                    $e
                );
            }
        } catch (\Erebot\NotFoundException $e) {
            // No rate-limit in effect, send away!
        }

        return $this->io->write();
    }

    /**
     * Load a single module for this connection.
     *
     * \param string $module
     *      Name of the module to load. If the module
     *      is already loaded, nothing will happen.
     *
     * \param string|null $chan
     *      Name of the IRC channel for which this module
     *      is being loaded. Pass \b null to load a module
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
    protected function realLoadModule(
        $module,
        $chan,
        $flags,
        &$plainModules,
        &$channelModules
    ) {
        if ($chan !== null) {
            if (isset($channelModules[$chan][$module])) {
                return $channelModules[$chan][$module];
            }
        } elseif (isset($plainModules[$module])) {
            return $plainModules[$module];
        }

        if (!class_exists($module, true)) {
            throw new \Erebot\InvalidValueException("No such class '$module'");
        }

        if (!is_subclass_of($module, '\\Erebot\\Module\\Base')) {
            throw new \Erebot\InvalidValueException(
                "Invalid module! Not a subclass of \\Erebot\\Module\\Base."
            );
        }

        $reflector = new \ReflectionClass($module);
        $instance = new $module($chan);
        if ($chan === null) {
            $plainModules[$module] = $instance;
        } else {
            $channelModules[$chan][$module] = $instance;
        }

        $instance->reloadModule($this, $flags);
        $logger = \Plop::getInstance();
        $logger->info(
            $this->bot->gettext("Successfully loaded module '%(module)s' [%(source)s]"),
            array(
                'module' => $module,
                'source' => (substr($reflector->getFileName(), 0, 7) == 'phar://')
                            ? $this->bot->gettext('PHP archive')
                            : $this->bot->gettext('regular file'),
            )
        );
        return $instance;
    }

    public function loadModule($module, $chan = null)
    {
        return $this->realLoadModule(
            $module,
            $chan,
            \Erebot\Module\Base::RELOAD_ALL,
            $this->plainModules,
            $this->channelModules
        );
    }

    public function getModules($chan = null)
    {
        if ($chan !== null) {
            $chanModules =  isset($this->channelModules[$chan])
                            ? $this->channelModules[$chan]
                            : array();
            return $chanModules + $this->plainModules;
        }
        return $this->plainModules;
    }

    public function getModule($name, $chan = null, $autoload = true)
    {
        if ($chan !== null) {
            if (isset($this->channelModules[$chan][$name])) {
                return $this->channelModules[$chan][$name];
            }

            $netCfg     = $this->config->getNetworkCfg();
            $chanCfg    = $netCfg->getChannelCfg($chan);
            $modules    = $chanCfg->getModules(false);
            if (in_array($name, $modules, true)) {
                if (!$autoload) {
                    throw new \Erebot\NotFoundException('No instance found');
                }
                return $this->loadModule($name, $chan);
            }
        }

        if (isset($this->plainModules[$name])) {
            return $this->plainModules[$name];
        }

        $modules = $this->config->getModules(true);
        if (!in_array($name, $modules, true) || !$autoload) {
            throw new \Erebot\NotFoundException('No instance found');
        }

        return $this->loadModule($name, null);
    }

    public function addNumericHandler(\Erebot\Interfaces\NumericHandler $handler)
    {
        $this->numerics[] = $handler;
    }

    public function removeNumericHandler(\Erebot\Interfaces\NumericHandler $handler)
    {
        $key = array_search($handler, $this->numerics);
        if ($key === false) {
            throw new \Erebot\NotFoundException('No such numeric handler');
        }
        unset($this->numerics[$key]);
    }

    public function addEventHandler(\Erebot\Interfaces\EventHandler $handler)
    {
        $this->events[] = $handler;
    }

    public function removeEventHandler(\Erebot\Interfaces\EventHandler $handler)
    {
        $key = array_search($handler, $this->events);
        if ($key === false) {
            throw new \Erebot\NotFoundException('No such event handler');
        }
        unset($this->events[$key]);
    }

    /**
     * Dispatches the given event to handlers
     * which have been registered for this type of event.
     *
     * \param Erebot::Interfaces::Event::Base::Generic $event
     *      An event to dispatch.
     */
    protected function dispatchEvent(\Erebot\Interfaces\Event\Base\Generic $event)
    {
        $logger = \Plop::getInstance();
        $logger->debug(
            $this->bot->gettext('Dispatching "%(type)s" event.'),
            array('type' => get_class($event))
        );
        try {
            foreach ($this->events as $handler) {
                if ($handler->handleEvent($event) === false) {
                    break;
                }
            }
        } catch (\Erebot\ErrorReportingException $e) {
            // This should help make the code a little more "bug-free" (TM).
            $logger->exception($this->bot->gettext('Code is not clean!'), $e);
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
    protected function dispatchNumeric(\Erebot\Interfaces\Event\Numeric $numeric)
    {
        $logger = \Plop::getInstance();
        $logger->debug(
            $this->bot->gettext('Dispatching numeric %(code)s.'),
            array('code' => sprintf('%03d', $numeric->getCode()))
        );
        try {
            foreach ($this->numerics as $handler) {
                if ($handler->handleNumeric($numeric) === false) {
                    break;
                }
            }
        } catch (\Erebot\ErrorReportingException $e) {
            // This should help make the code a little more "bug-free" (TM).
            $logger->exception($this->bot->gettext('Code is not clean!'), $e);
            $this->disconnect($e->getMessage());
        }
    }

    public function dispatch(\Erebot\Interfaces\Event\Base\Generic $event)
    {
        if ($event instanceof \Erebot\Interfaces\Event\Numeric) {
            return $this->dispatchNumeric($event);
        }
        return $this->dispatchEvent($event);
    }

    public function isChannel($chan)
    {
        try {
            $capabilities = $this->getModule('\\Erebot\\Module\\ServerCapabilities', null, false);
            return $capabilities->isChannel($chan);
        } catch (\Erebot\NotFoundException $e) {
            // Ignore silently.
        }

        if (!\Erebot\Utils::stringifiable($chan)) {
            throw new \Erebot\InvalidValueException(
                $this->bot->gettext('Bad channel name')
            );
        }

        $chan = (string) $chan;
        if (!strlen($chan)) {
            return false;
        }

        // Restricted characters in channel names,
        // as per RFC 2811 - (2.1) Namespace.
        foreach (array(' ', ',', "\x07", ':') as $token) {
            if (strpos($token, $chan) !== false) {
                return false;
            }
        }

        if (strlen($chan) > 50) {
            return false;
        }

        // As per RFC 2811 - (2.1) Namespace.
        return (strpos('#&+!', $chan[0]) !== false);
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
            $this->collator = new $cls();
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
    ) {
        $this->connected = true;
    }

    /**
     * Sets the collector to use for this connection.
     *
     * \param Erebot::Interfaces::Collator $collator
     *      The new collator to use for this connection.
     */
    public function setCollator(\Erebot\Interfaces\IrcCollator $collator)
    {
        $this->collator = $collator;
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
        return $this->collator;
    }

    public function getEventsProducer()
    {
        return $this->eventsProducer;
    }
}
