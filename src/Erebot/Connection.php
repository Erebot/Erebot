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
            Erebot_Interface_IrcComparator,
            Erebot_Interface_EventDispatcher,
            Erebot_Interface_EventFactory,
            Erebot_Interface_BidirectionnalConnection
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

    /// Mappings from (lowercase) interface names to actual classes.
    protected $_eventsMapping;

    protected $_uriFactory;

    protected $_depFactory;

    protected $_rawProfileLoader;

    /// Valid mappings for case-insensitive comparisons.
    static protected $_caseMappings = NULL;

    /// \copydoc Erebot_Interface_Connection::__construct()
    public function __construct(Erebot_Interface_Core $bot, $config = NULL, $events = array())
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

        // Build case mappings for case-insensitive comparisons.
        if (self::$_caseMappings === NULL) {
            self::$_caseMappings = array(
                'ascii' => array_combine(
                    range('a', 'z'),
                    range('A', 'Z')
                ),
                'strict-rfc1459' => array_combine(
                    range('a', chr(125)),
                    range('A', chr(93))
                ),
                'rfc1459' => array_combine(
                    range('a', chr(126)),
                    range('A', chr(94))
                ),
            );
        }

        $this->_eventsMapping = array();
        $this->setEventClasses($events);
        $this->setURIFactory('Erebot_URI');
        $this->setRawProfileLoader(
            new Erebot_RawProfileLoader(
                array(
                    'Erebot_Interface_RawProfile_RFC2812',
                    'Erebot_Interface_RawProfile_005',
                )
            )
        );

        $this->addEventHandler(
            new Erebot_EventHandler(
                new Erebot_Callable(array($this, 'handleCapabilities')),
                new Erebot_Event_Match_InstanceOf('Erebot_Event_ServerCapabilities')
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
        if (!$reflector->implementsInterface('Erebot_Interface_URI'))
            throw new Erebot_InvalidValueException(
                'The factory must implement Erebot_Interface_URI');
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
        try {
            foreach ($newChannels as $chanCfg) {
                $modules    = $chanCfg->getModules(FALSE);
                $chan       = $chanCfg->getName();
                foreach ($modules as $module)
                    $this->_loadModule(
                        $module, $chan, $flags,
                        $this->_plainModules,
                        $this->_channelModules
                    );
            }

            foreach ($newModules as $module)
                $this->_loadModule(
                    $module, NULL, $flags,
                    $this->_plainModules,
                    $this->_channelModules
                );

            // Unload old module instances.
            foreach ($channelModules as $modules)
                foreach ($modules as $module)
                    $module->unload();
            foreach ($plainModules as $module)
                $module->unload();
        }

        // If something wrong happens, restore the previous configuration.
        catch (Exception $e) {
            $this->_plainModules    = $plainModules;
            $this->_channelModules  = $channelModules;
        }
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
                        "Successfully established connection through proxy '%s'",
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

        $this->dispatch($this->makeEvent('!Logon'));
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
        foreach ($chars as $char)
            if (strpos($line, $char) !== FALSE)
                throw new Erebot_InvalidValueException(
                    'Line contains forbidden characters');
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
            $event = $this->makeEvent('!Disconnect');
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
        if ($this->emptySendQueue())
            throw new Erebot_NotFoundException('No outgoing data needs to be handled');

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
            $this->_handleMessage(array_shift($this->_rcvQueue));
    }

    /**
     * Handles a single IRC message.
     *
     * \param string $msg
     *      The message to process.
     *
     * \note
     *      Events/raws are dispatched as necessary
     *      by this method.
     */
    protected function _handleMessage($msg)
    {
        $parts  = explode(' ', $msg);
        $source = array_shift($parts);

        // Ping message from the server.
        if (!strcasecmp($source, 'PING')) {
            $msg = implode(' ', $parts);
            if (substr($msg, 0, 1) == ':')
                $msg = substr($msg, 1);
            return $this->dispatch($this->makeEvent('!Ping', $msg));
        }

        $type   = array_shift($parts);
        if ($source == '' || !count($parts))
            return;

        if ($source[0] == ':')
            $source = substr($source, 1);

        $type = strtoupper($type);

        // ERROR usually happens when disconnecting from the server,
        // when using QUIT or getting KILLed, etc.
        if ($type == 'ERROR') {
            if (substr($parts[0], 0, 1) == ':')
                $parts[0] = substr($parts[0], 1);

            $msg = implode(' ', $parts);
            return $this->dispatch($this->makeEvent('!Error', $souce, $msg));
        }

        $target = array_shift($parts);

        if (count($parts) && $parts[0][0] == ':')
            $parts[0] = substr($parts[0], 1);
        $msg = implode(' ', $parts);

        if (isset($target[0]) && $target[0] == ':')
            $target = substr($target, 1);

        switch ($type) {
            case 'INVITE':     // :nick1!ident@host INVITE nick2 :#chan
                $this->dispatch($this->makeEvent('!Invite', $msg, $source, $target));
                break;

            case 'JOIN':    // :nick1!ident@host JOIN :#chan
                $this->dispatch($this->makeEvent('!Join', $target, $source));
                break;

            case 'KICK':    // :nick1!ident@host KICK #chan nick2 :Reason
                $pos    = strcspn($msg, " ");
                $nick   = substr($msg, 0, $pos);
                $msg    = substr($msg, $pos + 1);
                if (strlen($msg) && $msg[0] == ':')
                    $msg = substr($msg, 1);

                $this->dispatch($this->makeEvent('!Kick', $target, $source, $nick, $msg));
                break;

            case 'KILL':    // :nick1!ident@host KILL nick2 :Reason
                $this->dispatch($this->makeEvent('!Kill', $target, $source, $msg));
                break;

            case 'MODE':    // :nick1!ident@host MODE <nick2/#chan> modes
                if (!$this->isChannel($target)) {
                    $this->dispatch($this->makeEvent('!UserMode', $source, $target, $msg));
                    break;
                }

                $event = $this->makeEvent('!RawMode', $target, $source, $msg);
                $this->dispatch($event);
                if ($event->preventDefault(TRUE)) {
                    break;
                }

                $wrappedMessage = new Erebot_TextWrapper($msg);
                $modes  = $wrappedMessage[0];
                $len    = strlen($modes);
                $mode   = 'add';
                $k      = 1;

                $priv     = array(
                    'add' =>
                        array(
                            'o' => '!Op',
                            'h' => '!Halfop',
                            'v' => '!Voice',
                            'a' => '!Protect',
                            'q' => '!Owner',
                            'b' => '!Ban',
                            'e' => '!Except',
                        ),
                    'remove' =>
                        array(
                            'o' => '!DeOp',
                            'h' => '!DeHalfop',
                            'v' => '!DeVoice',
                            'a' => '!DeProtect',
                            'q' => '!DeOwner',
                            'b' => '!UnBan',
                            'e' => '!UnExcept',
                        ),
                );

                $remains = array();
                for ($i = 0; $i < $len; $i++) {
                    switch ($modes[$i]) {
                        case '+':
                            $mode = 'add';
                            break;
                        case '-':
                            $mode = 'remove';
                            break;

                        case 'o':
                        case 'v':
                        case 'h':
                        case 'a':
                        case 'q':
                        case 'b':
                            $tnick  = $wrappedMessage[$k++];
                            $cls    = $priv[$mode][$modes[$i]];
                            $this->dispatch($this->makeEvent($cls, $target, $source, $tnick));
                            break;

                        default:
/// @TODO fix this
/*
                            for ($j = 3; $j > 0; $j--) {
                                $pos = strpos(
                                    $this->chanModes[$j-1],
                                    $modes[$i]
                                );
                                if ($pos !== FALSE)
                                    break;
                            }
                            switch ($j) {
                                case 3:
                                    if ($mode == self::MODE_REMOVE)
                                        break;
                                case 1:
                                case 2:
                                    $remains[] = $wrappedMessage[$k++]
                                    break;
                            }
*/
                    }
                } // for each mode in $modes

                $remainingModes = str_replace(
                    array('o', 'h', 'v', 'b', '+', '-'),
                    '',
                    $modes
                );
                if ($remainingModes != '') {
                    if (count($remains))
                        $remains = ' '.implode(' ', $remains);
                    else $remains = '';

                    for ($i = strlen($modes) - 1; $i >= 0; $i--) {

                    }

# @TODO
#                    $event = new ErebotEvent($this, $source, $target,
#                        ErebotEvent::ON_MODE, $remainingModes.$remains);
#                    $this->dispatchEvent($event);
                }

                break; // ON_MODE

            case 'NICK':    // :oldnick!ident@host NICK newnick
                $this->dispatch($this->makeEvent('!Nick', $source, $target));
                break;

            case 'NOTICE':    // :nick1!ident@host NOTICE <nick2/#chan> :Message
                if (($len = strlen($msg)) > 1 &&
                    $msg[$len-1] == "\x01" &&
                    $msg[0] == "\x01") {

                    $msg    = substr($msg, 1, -1);
                    $pos    = strcspn($msg, " ");
                    $ctcp   = substr($msg, 0, $pos);
                    $msg    = (string) substr($msg, $pos + 1);

                    if ($this->isChannel($target))
                        $this->dispatch($this->makeEvent('!ChanCtcpReply', $target, $source, $ctcp, $msg));
                    else
                        $this->dispatch($this->makeEvent('!PrivateCtcpReply', $source, $ctcp, $msg));
                    break;
                }

                if ($this->isChannel($target))
                    $this->dispatch($this->makeEvent('!ChanNotice', $target, $source, $msg));
                else
                    $this->dispatch($this->makeEvent('!PrivateNotice', $source, $msg));
                break;

            case 'PART':    // :nick1!ident@host PART #chan :Reason
                $this->dispatch($this->makeEvent('!Part', $target, $source, $msg));
                break;

            /* We sent a PING and got a PONG! :) */
            case 'PONG':    // :origin PONG origin target
                $this->dispatch($this->makeEvent('!Pong', $source, $msg));
                break;

            case 'PRIVMSG':    // :nick1!ident@host PRIVMSG <nick2/#chan> :Msg
                if (($len = strlen($msg)) > 1 &&
                    $msg[$len-1] == "\x01" &&
                    $msg[0] == "\x01") {

                    $msg    = substr($msg, 1, -1);
                    $pos    = strcspn($msg, " ");
                    $ctcp   = substr($msg, 0, $pos);
                    $msg    = (string) substr($msg, $pos + 1);

                    if ($ctcp == "ACTION") {
                        if ($this->isChannel($target))
                            $this->dispatch($this->makeEvent('!ChanAction', $target, $source, $msg));
                        else
                            $this->dispatch($this->makeEvent('!PrivateAction', $source, $msg));
                        break;
                    }

                    if ($this->isChannel($target))
                        $this->dispatch($this->makeEvent('!ChanCtcp', $target, $source, $ctcp, $msg));
                    else
                        $this->dispatch($this->makeEvent('!PrivateCtcp', $source, $ctcp, $msg));
                    break;
                }

                if ($this->isChannel($target))
                    $this->dispatch($this->makeEvent('!ChanText', $target, $source, $msg));
                else
                    $this->dispatch($this->makeEvent('!PrivateText', $source, $msg));
                break;

            case 'TOPIC':    // :nick1!ident@host TOPIC #chan :New topic
                $this->dispatch($this->makeEvent('!Topic', $target, $source, $msg));
                break;

            default:        // :server numeric parameters
                /* RAW (numeric) events */
                if (!ctype_digit($type)) {
                    break;
                }

                $type   = intval($type, 10);
                switch ($type) {
                    /* We can't rely on RPL_WELCOME because we may need
                     * to detect the server's capabilities first.
                     * So, we delay detection of the connection for as
                     * long as we can (while keeping portability). */
                    case Erebot_Interface_RawProfile_RFC1459::RPL_LUSERME:
                        if ($this->_connected) {
                            break;
                        }
                        $this->dispatch($this->makeEvent('!Connect'));
                        $this->_connected = TRUE;
                        break;

                    case Erebot_Interface_RawProfile_WATCH::RPL_NOWON:
                    case Erebot_Interface_RawProfile_WATCH::RPL_NOWOFF:
                    case Erebot_Interface_RawProfile_WATCH::RPL_LOGON:
                    case Erebot_Interface_RawProfile_WATCH::RPL_LOGOFF:
                        $nick       = array_shift($parts);
                        $ident      = array_shift($parts);
                        $host       = array_shift($parts);
                        $timestamp  = intval(array_shift($parts), 10);
                        $timestamp  = new DateTime('@'.$timestamp);
                        $text       = implode(' ', $parts);
                        if (substr($text, 0, 1) == ':')
                            $text = substr($text, 1);

                        $map    = array(
                            Erebot_Interface_RawProfile_WATCH::RPL_NOWON   =>
                                '!Notify',
                            Erebot_Interface_RawProfile_WATCH::RPL_LOGON   =>
                                '!Notify',
                            Erebot_Interface_RawProfile_WATCH::RPL_NOWOFF  =>
                                '!UnNotify',
                            Erebot_Interface_RawProfile_WATCH::RPL_LOGOFF  =>
                                '!UnNotify',
                        );
                        $cls    = $map[$type];
                        $this->dispatch($this->makeEvent($cls, $nick, $ident, $host, $timestamp, $text));
                        break;
                }

                $this->dispatch($this->makeEvent('!Raw', $type, $source, $target, $msg));
                break;
        } /* switch ($type) */
    }

    /// \copydoc Erebot_Interface_Connection::getBot()
    public function getBot()
    {
        return $this->_bot;
    }

    protected function _loadModule($module, $chan, $flags, &$plainModules, &$channelModules)
    {
        if ($chan !== NULL) {
            if (isset($channelModules[$chan][$module]))
                return $channelModules[$chan][$module];
        }

        else if (isset($plainModules[$module]))
            return $plainModules[$module];

        $logging    = Plop::getInstance();
        $logger     = $logging->getLogger(__FILE__);

        if (!is_subclass_of($module, 'Erebot_Module_Base'))
            throw new Erebot_InvalidValueException(
                "Invalid module! Not a subclass of Erebot_Module_Base.");

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
            return  $this->_channelModules[$chan] +
                    $this->_plainModules;
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

    /// \copydoc Erebot_Interface_EventFactory::makeEvent()
    public function makeEvent($iface /* , ... */)
    {
        $args = func_get_args();

        // Shortcuts.
        $iface = str_replace('!', 'Erebot_Interface_Event_', $iface);
        $iface = strtolower($iface);

        if (!isset($this->_eventsMapping[$iface]))
            throw new Erebot_NotFoundException('No such declared interface');

        // Replace the first argument (interface) with a reference
        // to this connection, since all events require it anyway.
        // This simplifies calls to this method a little bit.
        $args[0]    = $this;
        $cls        = new ReflectionClass($this->_eventsMapping[$iface]);
        return $cls->newInstanceArgs($args);
    }

    /**
     * \copydoc
     *      Erebot_Interface_EventFactory::getEventClass($iface)
     *
     * \note
     *      As a special shortcut, you may use an exclamation
     *      point ("!") in the interface name, which will be
     *      replaced by the text "Erebot_Interface_Event_".
     *      Hence, to retrieve the class used to create events
     *      with the "Erebot_Interface_Event_Op" interface,
     *      it is enough to simply pass "!Op" as the value
     *      for $iface.
     */
    public function getEventClass($iface)
    {
        // Shortcuts.
        $iface = str_replace('!', 'Erebot_Interface_Event_', $iface);
        $iface = strtolower($iface);

        return isset($this->_eventsMapping[$iface])
            ? $this->_eventsMapping[$iface]
            : NULL;
    }

    public function getEventClasses()
    {
        return $this->_eventsMapping;
    }

    public function setEventClasses($events)
    {
        foreach ($events as $iface => $cls) {
            $this->setEventClass($iface, $cls);
        }
    }

    /**
     * \copydoc
     *      Erebot_Interface_EventFactory::setEventClass($iface, $cls)
     *
     * \note
     *      As a special shortcut, you may use an exclamation
     *      point ("!") in the interface name, which will be
     *      replaced by the text "Erebot_Interface_Event_".
     *      Hence, to change the class used to create events
     *      with the "Erebot_Interface_Event_Op" interface,
     *      it is enough to simply pass "!Op" as the value
     *      for $iface. The $cls is always left unaffected.
     */
    public function setEventClass($iface, $cls)
    {
        // Shortcuts.
        $iface = str_replace('!', 'Erebot_Interface_Event_', $iface);
        $iface = strtolower($iface);

        $reflector = new ReflectionClass($cls);
        if (!$reflector->implementsInterface($iface))
            throw new Erebot_InvalidValueException(
                'The given class does not implement that interface');
        $this->_eventsMapping[$iface] = $cls;
    }

    /**
     * Dispatches the given event to handlers
     * which have been registered for this type of event.
     *
     * \param Erebot_Interface_Event_Base_Generic $event
     *      An event to dispatch.
     */
    protected function _dispatchEvent(Erebot_Interface_Event_Base_Generic $event)
    {
        $logging    = Plop::getInstance();
        $logger     = $logging->getLogger(__FILE__);
#        $logger->debug(
#            $this->_bot->gettext('Dispatching "%s" event.'),
#            get_class($event)
#        );
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
#        $logger->debug(
#            $this->_bot->gettext('Dispatching raw #%s.'),
#            sprintf('%03d', $raw->getRaw())
#        );
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

    protected function _getMapping($mappingName = NULL)
    {
        if ($mappingName === NULL) {
            try {
                $capabilities = $this->getModule(
                    'Erebot_Module_ServerCapabilities',
                    NULL, FALSE
                );
                $mappingName = $capabilities->getCaseMapping();
            }
            catch (Erebot_NotFoundException $e) {
                // Fallback to a safe mapping.
                $mappingName = 'rfc1459';
            }
        }

        if (!is_string($mappingName))
            throw new Erebot_InvalidValueException(
                $this->_bot->gettext('Invalid mapping name')
            );

        $mappingName = strtolower($mappingName);
        if (!isset(self::$_caseMappings[$mappingName]))
            throw new Erebot_NotFoundException(
                $this->_bot->gettext('No such mapping exists')
            );
        return self::$_caseMappings[$mappingName];
    }

    /// \copydoc Erebot_Interface_IrcComparator::irccmp()
    public function irccmp($a, $b)
    {
        return strcmp($a, $b);
    }

    /// \copydoc Erebot_Interface_IrcComparator::ircncmp()
    public function ircncmp($a, $b, $len)
    {
        return strncmp($a, $b, $len);
    }

    /// \copydoc Erebot_Interface_IrcComparator::irccasecmp()
    public function irccasecmp($a, $b, $mappingName = NULL)
    {
        return strcmp(
            $this->normalizeNick($a, $mappingName),
            $this->normalizeNick($b, $mappingName)
        );
    }

    /// \copydoc Erebot_Interface_IrcComparator::ircncasecmp()
    public function ircncasecmp($a, $b, $len, $mappingName = NULL)
    {
        return strncmp(
            $this->normalizeNick($a, $mappingName),
            $this->normalizeNick($b, $mappingName),
            $len
        );
    }

    /// \copydoc Erebot_Interface_IrcComparator::isChannel()
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

    /// \copydoc Erebot_Interface_IrcComparator::normalizeNick()
    public function normalizeNick($nick, $mappingName = NULL)
    {
        $mapping = $this->_getMapping($mappingName);
        $pos = strpos($nick, '!');
        $suffix = '';
        if ($pos !== FALSE) {
            $suffix = substr($nick, $pos);
            $nick = substr($nick, 0, $pos);
            if ($nick === FALSE)
                throw new Erebot_InvalidValueException(
                    $this->_bot->gettext('Not a valid mask')
                );
        }

        $nick = strtr($nick, $mapping);
        return $nick.$suffix;
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
    }
}

