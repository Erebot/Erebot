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
implements  Erebot_Interface_Connection
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

    protected $_connected;

    /// Valid mappings for case-insensitive comparisons.
    static protected $_caseMappings = NULL;

    // Documented in the interface.
    public function __construct(
        Erebot_Interface_Core           $bot,
        Erebot_Interface_Config_Server  $config)
    {
        $this->_config  = $config;
        $this->_bot     = $bot;

        $this->_channelModules  = array();
        $this->_plainModules    = array();
        $this->_moduleClasses   = array();
        $this->_raws            = array();
        $this->_events          = array();
        $this->_sndQueue        = array();
        $this->_rcvQueue        = array();
        $this->_incomingData    = '';
        $this->_connected       = FALSE;

        // Build possible 
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

        $this->_loadModules();
    }

    protected function _loadModules()
    {
        $netCfg     = $this->_config->getNetworkCfg();
        $channels   = $netCfg->getChannels();
        foreach ($channels as $chanCfg) {
            $modules = $chanCfg->getModules(FALSE);
            $chan   = $chanCfg->getName();
            foreach ($modules as $module)
                $this->loadModule($module, $chan);
        }

        $modules    = $this->_config->getModules(TRUE);
        foreach ($modules as $module)
            $this->loadModule($module, NULL);
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        if (is_resource($this->_socket))
            fclose($this->_socket);
        $this->_socket = NULL;

        unset(
            $this->_events,
            $this->_raws,
            $this->_config,
            $this->_bot,
            $this->_channelModules,
            $this->_plainModules
        );
    }

    /**
     * Copy-constructor.
     */
    public function __clone()
    {
        throw new Exception("Cloning forbidden!");
    }

    // Documented in the interface.
    public function connect()
    {
        if ($this->_connected)
            return FALSE;

        $url = $this->_config->getConnectionURL();

        $url    = @parse_url($url);
        try {
            if ($url === FALSE          ||
                !isset($url['scheme'])  ||
                !isset($url['host'])) {
                throw new Erebot_InvalidValueException('Malformed URL');
            }

            $queryString = isset($url['query']) ? $url['query'] : '';
            parse_str($queryString, $params);

            $context = stream_context_get_default();
            $ctxOptions = stream_context_get_options($context);

            if (isset($params['verify_peer']))
                $ctxOptions['ssl']['verify_peer'] =
                    Erebot_Config_Proxy::_parseBool($params['verify_peer']);
            else if (!isset($ctxOptions['ssl']['verify_peer']))
                $ctxOptions['ssl']['verify_peer'] = TRUE;

            if (isset($params['allow_self_signed']))
                $ctxOptions['ssl']['allow_self_signed'] =
                    Erebot_Config_Proxy::_parseBool($params['allow_self_signed']);
            else if (!isset($ctxOptions['ssl']['allow_self_signed']))
                $ctxOptions['ssl']['allow_self_signed'] = TRUE;

            if (isset($params['ciphers']))
                $ctxOptions['ssl']['ciphers'] = $params['ciphers'];
            else if (!isset($options['ssl']['ciphers']))
                $ctxOptions['ssl']['ciphers'] = 'HIGH';

            $context  = stream_context_create($ctxOptions);

            if (!strcasecmp($url['scheme'], 'ircs')) {
                $port           = 994;
                $proto          = 'tls';
            }
            else if (!strcasecmp($url['scheme'], 'irc')) {
                $port       = 194;
                $proto      = 'tcp';
            }
            else
                throw new Erebot_InvalidValueException('Invalid scheme in URL');

            if (isset($url['port']))
                $port = $url['port'];

            $opened = $proto.'://'.$url['host'].':'.$port;
            $this->_socket = stream_socket_client(
                $opened, $errno, $errstr,
                ini_get('default_socket_timeout'),
                STREAM_CLIENT_CONNECT,
                $context
            );
            stream_set_write_buffer($this->_socket, 0);
        }
        catch (Exception $e) {
            throw new Erebot_ConnectionFailureException(
                sprintf("Unable to connect to '%s'", $url));
        }

        $event = new Erebot_Event_Logon($this);
        $this->dispatchEvent($event);
        return TRUE;
    }

    // Documented in the interface.
    public function disconnect($quitMessage = NULL)
    {
        $logging    = Plop::getInstance();
        $logger     = $logging->getLogger(__FILE__);
        $url        = $this->_config->getConnectionURL();
        $logger->info("Disconnecting from '%s' ...", $url);

        // Purge send queue and send QUIT message to notify server.
        $this->_sndQueue = array();
        if ($quitMessage === NULL) {
            try {
                $config         = $this->getConfig(NULL);
                $quitMessage    = $config->parseString(
                    'IrcConnector',
                    'quit_message'
                );
            }
            catch (Erebot_Exception $e) {
                // No default quit message configured.
            }
            unset($config);
        }
        $quitMessage    = ($quitMessage !== NULL ? ' :'.$quitMessage : '');
        $this->pushLine('QUIT'.$quitMessage);
        $this->processOutgoingData();

        // Then kill the connection for real.
        $this->_bot->removeConnection($this);
        if (is_resource($this->_socket))
            fclose($this->_socket);
        $this->_socket      = NULL;
        $this->_connected   = FALSE;
    }

    // Documented in the interface.
    public function pushLine($line)
    {
        $chars = array("\r", "\n");
        foreach ($chars as $char)
            if (strpos($line, $char) !== FALSE)
                throw new Erebot_InvalidValueException(
                    'Line contains forbidden characters');
        $this->_sndQueue[] = $line;
    }

    // Documented in the interface.
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

    // Documented in the interface.
    public function getSocket()
    {
        return $this->_socket;
    }

    // Documented in the interface.
    public function emptyReadQueue()
    {
        return (count($this->_rcvQueue) == 0);
    }

    // Documented in the interface.
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

    // Documented in the interface.
    public function processIncomingData()
    {
        $received   = fread($this->_socket, 4096);
        if ($received === FALSE || feof($this->_socket)) {
            $event = new Erebot_Event_Disconnect($this);
            $this->dispatchEvent($event);

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

    // Documented in the interface.
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

        $logger->debug("%s", addcslashes($line, "\000..\037"));

        // Make sure we send the whole line,
        // with a trailing CR LF sequence.
        $line .= "\r\n";
        for (
            $written = 0, $len = strlen($line);
            $written < $len;
            $written += $fwrite
        ) {
            $fwrite = fwrite($this->_socket, substr($line, $written));
            if ($fwrite === FALSE)
                return FALSE;
        }
        return $written;
    }

    // Documented in the interface.
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
            $event = new Erebot_Event_Ping($this, $msg);
            $this->dispatchEvent($event);
            return;
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
            $event = new Erebot_Event_Error($this, $source, $msg);
            $this->dispatchEvent($event);
            return;
        }

        $target = array_shift($parts);

        if (count($parts) && $parts[0][0] == ':')
            $parts[0] = substr($parts[0], 1);
        $msg = implode(' ', $parts);

        if (isset($target[0]) && $target[0] == ':')
            $target = substr($target, 1);

        switch ($type) {
            case 'INVITE':     // :nick1!ident@host INVITE nick2 :#chan
                $event = new Erebot_Event_Invite($this, $msg, $source, $target);
                $this->dispatchEvent($event);
                break;

            case 'JOIN':    // :nick1!ident@host JOIN :#chan
                $event = new Erebot_Event_Join($this, $target, $source);
                $this->dispatchEvent($event);
                break;

            case 'KICK':    // :nick1!ident@host KICK #chan nick2 :Reason
                $pos    = strcspn($msg, " ");
                $nick   = substr($msg, 0, $pos);
                $msg    = substr($msg, $pos + 1);
                if (strlen($msg) && $msg[0] == ':')
                    $msg = substr($msg, 1);

                $event  = new Erebot_Event_Kick(
                    $this,
                    $target,
                    $source,
                    $nick,
                    $msg
                );
                $this->dispatchEvent($event);
                break;

            case 'KILL':    // :nick1!ident@host KILL nick2 :Reason
                $event  = new Erebot_Event_Kill($this, $target, $source, $msg);
                $this->dispatchEvent($event);
                break;

            case 'MODE':    // :nick1!ident@host MODE <nick2/#chan> modes
                if (!$this->isChannel($target)) {
                    $event = new Erebot_Event_UserMode(
                        $this,
                        $source,
                        $target,
                        $msg
                    );
                    $this->dispatchEvent($event);
                    break;
                }

                $event  = new Erebot_Event_RawMode($this, $target, $source, $msg);
                $this->dispatchEvent($event);

                if ($event->preventDefault(TRUE)) {
                    break;
                }

                $wrappedMessage = new Erebot_TextWrapper($msg);
                $modes  = $wrappedMessage[0];
                $len    = strlen($modes);
                $mode   = self::MODE_ADD;
                $k      = 1;

                $priv     = array(
                    self::MODE_ADD =>
                        array(
                            'o' => 'Erebot_Event_Op',
                            'h' => 'Erebot_Event_Halfop',
                            'v' => 'Erebot_Event_Voice',
                            'a' => 'Erebot_Event_Protect',
                            'q' => 'Erebot_Event_Owner',
                            'b' => 'Erebot_Event_Ban',
                            'e' => 'Erebot_Event_Except',
                        ),
                    self::MODE_REMOVE =>
                        array(
                            'o' => 'Erebot_Event_DeOp',
                            'h' => 'Erebot_Event_DeHalfop',
                            'v' => 'Erebot_Event_DeVoice',
                            'a' => 'Erebot_Event_DeProtect',
                            'q' => 'Erebot_Event_DeOwner',
                            'b' => 'Erebot_Event_UnBan',
                            'e' => 'Erebot_Event_UnExcept',
                        ),
                );

                $remains = array();
                for ($i = 0; $i < $len; $i++) {
                    switch ($modes[$i]) {
                        case '+':
                            $mode = self::MODE_ADD;
                            break;
                        case '-':
                            $mode = self::MODE_REMOVE;
                            break;

                        case 'o':
                        case 'v':
                        case 'h':
                        case 'a':
                        case 'q':
                        case 'b':
                            $tnick  = $wrappedMessage[$k++];
                            $cls    = $priv[$mode][$modes[$i]];
                            $event  = new $cls($this, $target, $source, $tnick);
                            $this->dispatchEvent($event);
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
                $event = new Erebot_Event_Nick($this, $source, $target);
                $this->dispatchEvent($event);
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
                        $event = new Erebot_Event_ChanCtcpReply(
                            $this,
                            $target,
                            $source,
                            $ctcp,
                            $msg
                        );
                    else
                        $event = new Erebot_Event_PrivateCtcpReply(
                            $this,
                            $source,
                            $ctcp,
                            $msg
                        );
                    $this->dispatchEvent($event);
                    break;
                }

                if ($this->isChannel($target))
                    $event = new Erebot_Event_ChanNotice(
                        $this,
                        $target,
                        $source,
                        $msg
                    );
                else
                    $event = new Erebot_Event_PrivateNotice($this, $source, $msg);
                $this->dispatchEvent($event);
                break;

            case 'PART':    // :nick1!ident@host PART #chan :Reason
                $event = new Erebot_Event_Part($this, $target, $source, $msg);
                $this->dispatchEvent($event);
                break;

            /* We sent a PING and got a PONG! :) */
            case 'PONG':    // :origin PONG origin target
                $event = new Erebot_Event_Pong($this, $source, $msg);
                $this->dispatchEvent($event);
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
                            $event = new Erebot_Event_ChanAction(
                                $this,
                                $target,
                                $source,
                                $msg
                            );
                        else
                            $event = new Erebot_Event_PrivateAction(
                                $this,
                                $source,
                                $msg
                            );
                        $this->dispatchEvent($event);
                        break;
                    }

                    if ($this->isChannel($target))
                        $event = new Erebot_Event_ChanCtcp(
                            $this,
                            $target,
                            $source,
                            $ctcp,
                            $msg
                        );
                    else
                        $event = new Erebot_Event_PrivateCtcp(
                            $this,
                            $source,
                            $ctcp,
                            $msg
                        );
                    $this->dispatchEvent($event);
                    break;
                }

                if ($this->isChannel($target))
                    $event = new Erebot_Event_ChanText(
                        $this,
                        $target,
                        $source,
                        $msg
                    );
                else
                    $event = new Erebot_Event_PrivateText($this, $source, $msg);
                $this->dispatchEvent($event);
                break;

            case 'TOPIC':    // :nick1!ident@host TOPIC #chan :New topic
                $event = new Erebot_Event_Topic($this, $target, $source, $msg);
                $this->dispatchEvent($event);
                break;

            default:        // :server numeric parameters
                /* RAW (numeric) events */
                if (!ctype_digit($type))
                    break;

                $type   = intval($type, 10);
                switch ($type) {
                    /* We can't rely on RPL_WELCOME because we may need
                     * to detect the server's capabilities first.
                     * So, we delay detection of the connection for as
                     * long as we can (while keeping portability). */
                    case Erebot_Interface_Event_Raw::RPL_LUSERME:
                        if ($this->_connected)
                            break;
                        $event = new Erebot_Event_Connect($this);
                        $this->dispatchEvent($event);
                        $this->_connected = TRUE;
                        break;

                    case Erebot_Interface_Event_Raw::RPL_NOWON:
                    case Erebot_Interface_Event_Raw::RPL_NOWOFF:
                    case Erebot_Interface_Event_Raw::RPL_LOGON:
                    case Erebot_Interface_Event_Raw::RPL_LOGOFF:
                        $nick       = array_shift($parts);
                        $ident      = array_shift($parts);
                        $host       = array_shift($parts);
                        $timestamp  = intval(array_shift($parts), 10);
                        $timestamp  = new DateTime('@'.$timestamp);
                        $text       = implode(' ', $parts);
                        if (substr($text, 0, 1) == ':')
                            $text = substr($text, 1);

                        $map    = array(
                            Erebot_Interface_Event_Raw::RPL_NOWON   =>
                                'Erebot_Event_Notify',
                            Erebot_Interface_Event_Raw::RPL_LOGON   =>
                                'Erebot_Event_Notify',
                            Erebot_Interface_Event_Raw::RPL_NOWOFF  =>
                                'Erebot_Event_UnNotify',
                            Erebot_Interface_Event_Raw::RPL_LOGOFF  =>
                                'Erebot_Event_UnNotify',
                        );
                        $cls    = $map[$type];
                        $event  = new $cls($this, $nick, $ident, $host,
                                            $timestamp, $text);
                        $this->dispatchEvent($event);
                        break;
                }

                $event  = new Erebot_Event_Raw(
                    $this,
                    $type,
                    $source,
                    $target,
                    $msg
                );
                $this->dispatchRaw($event);
                break;
        } /* switch ($type) */
    }

    // Documented in the interface.
    public function getBot()
    {
        return $this->_bot;
    }

    // Documented in the interface.
    public function loadModule($module, $chan = NULL)
    {
        if ($chan !== NULL) {
            if (isset($this->_channelModules[$chan][$module]))
                return $this->_channelModules[$chan][$module];
        }

        else if (isset($this->_plainModules[$module]))
            return $this->_plainModules[$module];

        $logging    = Plop::getInstance();
        $logger     = $logging->getLogger(__FILE__);

        if (!is_subclass_of($module, 'Erebot_Module_Base'))
            throw new Erebot_InvalidValueException(
                "Invalid module! Not a subclass of Erebot_Module_Base.");

        $instance   =   new $module($this, $chan);

        if ($chan === NULL)
            $this->_plainModules[$module] = $instance;
        else
            $this->_channelModules[$chan][$module] = $instance;

        $metadata   = $instance->getMetadata($module);
        $depends = (isset($metadata['requires']) ?
                    $metadata['requires'] : array());

        foreach ($depends as $depend) {
            /// @TODO use dependency injection instead.
            $depend = new Erebot_Dependency($depend);
            try {
                $depVer     = $depend->getVersion();
                $depended   = $this->getModule($depend->getName(), $chan);

                // Check version of the module.
                if ($depVer !== NULL) {
                    $metadata   = $depended->getMetadata($depend->getName());
                    $depEffVer = (isset($metadata['version']) ?
                                    $metadata['version'] : NULL);
                    if ($depEffVer === NULL ||
                        !version_compare(
                            $depEffVer,
                            $depVer,
                            $depend->getOperator()
                        ))
                        throw new Erebot_NotFoundException();
                }
            }
            catch (Erebot_NotFoundException $e) {
                unset($instance);
                // We raise a new exception with the full
                // dependency specification.
                throw new Erebot_NotFoundException((string) $depend);
            }
        }

        $depends = (isset($metadata['recommends']) ?
                    $metadata['recommends'] : array());

        foreach ($depends as $depend) {
            /// @TODO use dependency injection instead.
            $depend = new Erebot_Dependency($depend);
            try {
                $depVer     = $depend->getVersion();
                $depended   = $this->getModule($depend->getName(), $chan);

                // Check version of the module.
                if ($depVer !== NULL) {
                    $metadata   = $depended->getMetadata($depend->getName());
                    $depEffVer = (isset($metadata['version']) ?
                                    $metadata['version'] : NULL);
                    if ($depEffVer === NULL ||
                        !version_compare(
                            $depEffVer,
                            $depVer,
                            $depend->getOperator()
                        ))
                        throw new Erebot_NotFoundException();
                }
            }
            catch (Erebot_NotFoundException $e) {
                // This is only a recommendation,
                // so we silently ignore failures.
            }
        }

        $instance->reload(
            Erebot_Module_Base::RELOAD_ALL |
            Erebot_Module_Base::RELOAD_INIT
        );

        $logger->info(
            $this->_bot->gettext("Successfully loaded module '%s'"),
            $module
        );
        return $instance;
    }

    // Documented in the interface.
    public function getModules($chan = NULL)
    {
        if ($chan !== NULL) {
            return  $this->_channelModules[$chan] +
                    $this->_plainModules;
        }
        return $this->_plainModules;
    }

    // Documented in the interface.
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

    // Documented in the interface.
    public function addRawHandler(Erebot_Interface_RawHandler $handler)
    {
        $this->_raws[] = $handler;
    }

    // Documented in the interface.
    public function removeRawHandler(Erebot_Interface_RawHandler $handler)
    {
        $key = array_search($handler, $this->_raws);
        if ($key === FALSE)
            throw new Erebot_NotFoundException('No such raw handler');
        unset($this->_raws[$key]);
    }

    // Documented in the interface.
    public function addEventHandler(Erebot_Interface_EventHandler $handler)
    {
        $this->_events[] = $handler;
    }

    // Documented in the interface.
    public function removeEventHandler(Erebot_Interface_EventHandler $handler)
    {
        $key = array_search($handler, $this->_events);
        if ($key === FALSE)
            throw new Erebot_NotFoundException('No such event handler');
        unset($this->_events[$key]);
    }

    // Documented in the interface.
    public function dispatchEvent(Erebot_Interface_Event_Generic $event)
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

    // Documented in the interface.
    public function dispatchRaw(Erebot_Interface_Event_Raw $raw)
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

    // Documented in the interface.
    public function irccmp($a, $b)
    {
        return strcmp($a, $b);
    }

    // Documented in the interface.
    public function ircncmp($a, $b, $len)
    {
        return strncmp($a, $b, $len);
    }

    // Documented in the interface.
    public function irccasecmp($a, $b, $mappingName = NULL)
    {
        return strcmp(
            $this->normalizeNick($a, $mappingName),
            $this->normalizeNick($b, $mappingName)
        );
    }

    // Documented in the interface.
    public function ircncasecmp($a, $b, $len, $mappingName = NULL)
    {
        return strncmp(
            $this->normalizeNick($a, $mappingName),
            $this->normalizeNick($b, $mappingName),
            $len
        );
    }

    // Documented in the interface.
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

        if (!is_string($chan) || !strlen($chan)) {
            throw new Erebot_InvalidValueException(
                $this->_bot->gettext('Bad channel name')
            );
        }

        // Restricted characters in channel names,
        // as per RFC 2811 - (2.1) Namespace.
        foreach (array(' ', ',', "\x07", ':') as $token)
            if (strpos($token, $target) !== FALSE)
                return FALSE;

        if (strlen($chan) > 50)
            return FALSE;

        // As per RFC 2811 - (2.1) Namespace.
        return (strpos('#&+!', $target[0]) !== FALSE);
    }

    // Documented in the interface.
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
}

