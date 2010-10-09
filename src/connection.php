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

include_once('src/config/mainConfig.php');
include_once('src/events/events.php');
include_once('src/events/rawHandler.php');
include_once('src/events/eventHandler.php');
include_once('src/ifaces/connection.php');

/**
 * \brief
 *      Handles a (possibly encrypted) connection to an IRC server.
 */
class       ErebotConnection
implements  iErebotConnection
{
    /// A configuration object implementing the iErebotServerConfig interface.
    protected $_config;
    /// A bot object implementing the iErebot interface.
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

    // Documented in the interface.
    public function __construct(iErebot &$bot, iErebotServerConfig &$config)
    {
        $this->_config  =&  $config;
        $this->_bot     =&  $bot;

        $this->_channelModules  = array();
        $this->_plainModules    = array();
        $this->_moduleClasses   = array();
        $this->_raws            = array();
        $this->_events          = array();
        $this->_sndQueue        = array();
        $this->_rcvQueue        = array();
        $this->_incomingData    = '';

        $this->_loadGeneralModules();
        $this->_loadChannelModules();
    }

    /**
     * Loads modules which are shared by all channels.
     * This means that all modules associated to this instance's $config
     * (an ErebotServerConfig) and its parents (an ErebotNetworkConfig
     * and an ErebotMainConfig) get loaded by this method.
     *
     * \note
     *      This method uses the modules metadata to take dependencies
     *      into account.
     *
     * \exception EErebotNotFound
     *      Thrown whenever an unsatisfied module dependency is discovered,
     *      such as when a module depends on another which is not loaded
     *      by the current configuration.
     */
    protected function _loadGeneralModules()
    {
        $logging    =&  Plop::getInstance();
        $logger     =   $logging->getLogger(__FILE__);

        // First pass: try to load them.
        $failed    = $this->_config->getModules(TRUE);
        do {
            $modules    = $failed;
            $failed     = array();

            foreach ($modules as $module) {
                try {
                    $this->loadModule($module, NULL);
                }
                catch (EErebotNotFound $e) {
                    $logger->debug(
                        "Could not load '%(module)s' module, ".
                        "putting it on hold for now...",
                        array(
                            'module'    => $module,
                        )
                    );
                    $failed[] = $module;
                }
            }
        } while (count($modules) != count($failed));

        // Second pass: display unsatisfied dependencies.
        if (count($failed)) {
            foreach ($failed as $module) {
                try {
                    $this->loadModule($module, NULL);
                    $logger->critical("An exception was expected");
                }
                catch (EErebotNotFound $e) {
                    $logger->error(
                        "Unmet dependency for module '%(module)s': ".
                        "%(dependency)s",
                        array(
                            'module'        => $module,
                            'dependency'    => $e->getMessage(),
                        )
                    );
                }
            }
            $this->_bot->stop();
            throw new EErebotNotFound('There are unmet dependencies');
        }
    }

    /**
     * Loads modules which are specific to some channel.
     *
     * \note
     *      This method uses the modules metadata to take dependencies
     *      into account.
     *
     * \note
     *      You may override the configuration of a shared module for a
     *      given channel by simply adding a new configuration for that
     *      module under that channel's XML tag.
     *
     * \exception EErebotNotFound
     *      Thrown whenever an unsatisfied module dependency is discovered,
     *      such as when a module depends on another which is not loaded
     *      by the current configuration.
     */
    protected function _loadChannelModules()
    {
        $logging    =&  Plop::getInstance();
        $logger     =   $logging->getLogger(__FILE__);

        $netCfg     =&  $this->_config->getNetworkCfg();
        $channels   =   $netCfg->getChannels();
        foreach ($channels as &$chanCfg) {
            // First pass.
            $failed = $chanCfg->getModules(FALSE);
            $chan   = $chanCfg->getName();
            do {
                $modules    = $failed;
                $failed     = array();

                foreach ($modules as $module) {
                    try {
                        $this->loadModule($module, $chan);
                    }
                    catch (EErebotNotFound $e) {
                        $logger->debug(
                            "Could not load '%(module)s' module, ".
                            "putting it on hold for now...",
                            array(
                                'module'    => $module,
                            )
                        );
                        $failed[] = $module;
                    }
                }
            } while (count($modules) != count($failed));

            // Second pass.
            if (count($failed)) {
                foreach ($failed as $module) {
                    try {
                        $this->loadModule($module, $chan);
                        $logger->critical("An exception was expected");
                    }
                    catch (EErebotNotFound $e) {
                        $logger->error(
                            "Unmet dependency for module ".
                            "'%(module)s' on %(channel)s: %(dependency)s",
                            array(
                                'channel'       => $chan,
                                'module'        => $module,
                                'dependency'    => $e->getMessage(),
                            )
                        );
                    }
                }
                $this->_bot->stop();
                throw new EErebotNotFound('There are unmet dependencies');
            }
        }
        unset($chanCfg);
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset(
            $this->_events,
            $this->_raws,
            $this->_config,
            $this->_bot,
            $this->_channelModules,
            $this->_plainModules
        );
    }

    // Documented in the interface.
    public function connect()
    {
        $url = $this->_config->getConnectionURL();

        try {
            $this->_socket = fopen($url, 'r+b');
        }
        catch (EErebotErrorReporting $e) {
            throw new EErebotConnectionFailure(
                sprintf("Unable to connect to '%s'", $url));
        }

        $event = new ErebotEventLogon($this);
        $this->dispatchEvent($event);
    }

    // Documented in the interface.
    public function disconnect($quitMessage = NULL)
    {
        $logging    =&  Plop::getInstance();
        $logger     =   $logging->getLogger(__FILE__);
        $url        =   $this->_config->getConnectionURL();
        $logger->info("Disconnecting from '%s' ...", $url);

        // Purge send queue and send QUIT message to notify server.
        $this->_sndQueue = array();
        if ($quitMessage === NULL) {
            try {
                $config =& $this->getConfig(NULL);
                $quitMessage = $config->parseString(
                    'IrcConnector',
                    'quit_message'
                );
            }
            catch (EErebot $e) {
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
        $this->_socket = NULL;
    }

    // Documented in the interface.
    public function pushLine($line)
    {
        $chars = array("\r", "\n");
        foreach ($chars as $char)
            if (strpos($line, $char) !== FALSE)
                throw new EErebotInvalidValue(
                    'Line contains forbidden characters');
        $this->_sndQueue[] =& $line;
    }

    // Documented in the interface.
    public function & getConfig($chan)
    {
        if ($chan === NULL)
            return $this->_config;

        try {
            $netCfg     =&  $this->_config->getNetworkCfg();
            $chanCfg    =&  $netCfg->getChannelCfg($chan);
            unset($netCfg);
            return $chanCfg;
        }
        catch (EErebotNotFound $e) {
            return $this->_config;
        }
    }

    // Documented in the interface.
    public function & getSocket()
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
     * \return
     *      A boolean indicating whether a line could be fetched
     *      from the buffer (TRUE) or not (FALSE).
     *
     * \note
     *      Lines fetched by this method are always UTF-8 encoded.
     */
    protected function _getSingleLine()
    {
        $pos = strpos($this->_incomingData, "\r\n");
        if ($pos === FALSE)
            return FALSE;

        $line = ErebotUtils::toUTF8(substr($this->_incomingData, 0, $pos));
        $this->_incomingData    = substr($this->_incomingData, $pos + 2);
        $this->_rcvQueue[]      = $line;

        $logging    =&  Plop::getInstance();
        $logger     =   $logging->getLogger(
            __FILE__ . DIRECTORY_SEPARATOR . 'input'
        );
        $logger->info("%s", addcslashes($line, "\000..\037"));
        return TRUE;
    }

    // Documented in the interface.
    public function processIncomingData()
    {
        $received   = fread($this->_socket, 4096);
        if ($received === FALSE || feof($this->_socket)) {
            $event = new ErebotEventDisconnect($this);
            $this->dispatchEvent($event);

            if (!$event->preventDefault())
                throw new EErebotConnectionFailure('Disconnected');
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
            throw new EErebotNotFound('No outgoing data needs to be handled');
        $line       =   array_shift($this->_sndQueue);
        $logging    =&  Plop::getInstance();
        $logger     =   $logging->getLogger(
            __FILE__ . DIRECTORY_SEPARATOR . 'output'
        );
        $logger->info("%s", addcslashes($line, "\000..\037"));
        return fwrite($this->_socket, $line."\r\n");
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
     * \param $msg
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
            $event = new ErebotEventPing($this, $msg);
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
            $event = new ErebotEventError($this, $source, $msg);
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
                $event = new ErebotEventInvite($this, $msg, $source, $target);
                $this->dispatchEvent($event);
                break;

            case 'JOIN':    // :nick1!ident@host JOIN :#chan
                $event = new ErebotEventJoin($this, $target, $source);
                $this->dispatchEvent($event);
                break;

            case 'KICK':    // :nick1!ident@host KICK #chan nick2 :Reason
                $pos    = strcspn($msg, " ");
                $nick   = substr($msg, 0, $pos);
                $msg    = substr($msg, $pos + 1);
                if (strlen($msg) && $msg[0] == ':')
                    $msg = substr($msg, 1);

                $event  = new ErebotEventKick(
                    $this,
                    $target,
                    $source,
                    $nick,
                    $msg
                );
                $this->dispatchEvent($event);
                break;

            case 'KILL':    // :nick1!ident@host KILL nick2 :Reason
                $event  = new ErebotEventKill($this, $target, $source, $msg);
                $this->dispatchEvent($event);
                break;

            case 'MODE':    // :nick1!ident@host MODE <nick2/#chan> modes
                $capabilities = $this->getModule(
                    'ServerCapabilities',
                    self::MODULE_BY_NAME
                );
                if (!$capabilities->isChannel($target)) {
                    $event = new ErebotEventUserMode(
                        $this,
                        $source,
                        $target,
                        $msg
                    );
                    $this->dispatchEvent($event);
                    break;
                }

                $event  = new ErebotEventRawMode($this, $target, $source, $msg);
                $this->dispatchEvent($event);

                if ($event->preventDefault(TRUE)) {
                    break;
                }

                $modes  = ErebotUtils::gettok($msg, 0, 1);
                $len    = strlen($modes);
                $mode   = self::MODE_ADD;
                $k      = 1;

                $priv     = array(
                    self::MODE_ADD =>
                        array(
                            'o' => 'ErebotEventOp',
                            'h' => 'ErebotEventHalfop',
                            'v' => 'ErebotEventVoice',
                            'a' => 'ErebotEventProtect',
                            'q' => 'ErebotEventOwner',
                            'b' => 'ErebotEventBan',
                            'e' => 'ErebotEventExcept',
                        ),
                    self::MODE_REMOVE =>
                        array(
                            'o' => 'ErebotEventDeOp',
                            'h' => 'ErebotEventDeHalfop',
                            'v' => 'ErebotEventDeVoice',
                            'a' => 'ErebotEventDeProtect',
                            'q' => 'ErebotEventDeOwner',
                            'b' => 'ErebotEventUnban',
                            'e' => 'ErebotEventUnexcept',
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
                            $tnick  = ErebotUtils::gettok($msg, $k++, 1);
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
                                    $remains[] = $this->gettok($msg, $k++, 1);
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
                $event = new ErebotEventNick($this, $source, $target);
                $this->dispatchEvent($event);
                break;

            case 'NOTICE':    // :nick1!ident@host NOTICE <nick2/#chan> :Message
                $capabilities = $this->getModule(
                    'ServerCapabilities',
                    self::MODULE_BY_NAME
                );
                if (($len = strlen($msg)) > 1 &&
                    $msg[$len-1] == "\x01" &&
                    $msg[0] == "\x01") {

                    $msg    = substr($msg, 1, -1);
                    $pos    = strcspn($msg, " ");
                    $ctcp   = strtoupper(substr($msg, 0, $pos));
                    $msg    = substr($msg, $pos + 1);

                    if ($capabilities->isChannel($target))
                        $event = new ErebotEventCtcpReplyChan(
                            $this,
                            $target,
                            $source,
                            $ctcp,
                            $msg
                        );
                    else
                        $event = new ErebotEventCtcpReplyPrivate(
                            $this,
                            $source,
                            $ctcp,
                            $msg
                        );
                    $this->dispatchEvent($event);
                    break;
                }

                if ($capabilities->isChannel($target))
                    $event = new ErebotEventNoticeChan(
                        $this,
                        $target,
                        $source,
                        $msg
                    );
                else
                    $event = new ErebotEventNoticePrivate($this, $source, $msg);
                $this->dispatchEvent($event);
                break;

            case 'PART':    // :nick1!ident@host PART #chan :Reason
                $event = new ErebotEventPart($this, $target, $source, $msg);
                $this->dispatchEvent($event);
                break;

            /* We sent a PING and got a PONG! :) */
            case 'PONG':    // :origin PONG origin target
                $event = new ErebotEventPong($this, $source, $msg);
                $this->dispatchEvent($event);
                break;

            case 'PRIVMSG':    // :nick1!ident@host PRIVMSG <nick2/#chan> :Msg
                $capabilities = $this->getModule(
                    'ServerCapabilities',
                    self::MODULE_BY_NAME
                );
                if (($len = strlen($msg)) > 1 &&
                    $msg[$len-1] == "\x01" &&
                    $msg[0] == "\x01") {

                    $msg    = substr($msg, 1, -1);
                    $pos    = strcspn($msg, " ");
                    $ctcp   = strtoupper(substr($msg, 0, $pos));
                    $msg    = substr($msg, $pos + 1);

                    if ($ctcp == "ACTION") {
                        if ($capabilities->isChannel($target))
                            $event = new ErebotEventActionChan(
                                $this,
                                $target,
                                $source,
                                $msg
                            );
                        else
                            $event = new ErebotEventActionPrivate(
                                $this,
                                $source,
                                $msg
                            );
                        $this->dispatchEvent($event);
                        break;
                    }

                    if ($capabilities->isChannel($target))
                        $event = new ErebotEventCtcpChan(
                            $this,
                            $target,
                            $source,
                            $ctcp,
                            $msg
                        );
                    else
                        $event = new ErebotEventCtcpPrivate(
                            $this,
                            $source,
                            $ctcp,
                            $msg
                        );
                    $this->dispatchEvent($event);
                    break;
                }

                if ($capabilities->isChannel($target))
                    $event = new ErebotEventTextChan(
                        $this,
                        $target,
                        $source,
                        $msg
                    );
                else
                    $event = new ErebotEventTextPrivate($this, $source, $msg);
                $this->dispatchEvent($event);
                break;

            case 'TOPIC':    // :nick1!ident@host TOPIC #chan :New topic
                $event = new ErebotEventTopic($this, $target, $source, $msg);
                $this->dispatchEvent($event);
                break;

            default:        // :server numeric parameters
                /* RAW (numeric) events */
                if (ctype_digit($type)) {
                    $type   = intval($type, 10);
                    switch ($type) {
                        case RPL_WELCOME:
                            $event = new ErebotEventConnect($this);
                            $this->dispatchEvent($event);
                            break;

                        case RPL_NOWON:
                        case RPL_NOWOFF:
                            $nick       = array_shift($parts);
                            $ident      = array_shift($parts);
                            $host       = array_shift($parts);
                            $timestamp  = intval(array_shift($parts), 10);
                            $text       = implode(' ', $parts);
                            if (substr($text, 0, 1) == ':')
                                $text = substr($text, 1);

                            $map    = array(
                                RPL_NOWON   => 'ErebotEventNotify',
                                RPL_NOWOFF  => 'ErebotEventUnNotify',
                            );
                            $cls    = $map[$type];
                            $event  = new $cls($this, $nick, $ident, $host,
                                                $timestamp, $text);
                            $this->dispatchEvent($event);
                            break;
                    }

                    $event  = new ErebotRaw(
                        $this,
                        $type,
                        $source,
                        $target,
                        $msg
                    );
                    $this->dispatchRaw($event);
                }
        } /* switch ($type) */
    }

    // Documented in the interface.
    public function & getBot()
    {
        return $this->_bot;
    }

    // Documented in the interface.
    public function & loadModule($module, $chan = NULL)
    {
        if ($chan !== NULL) {
            if (isset($this->_channelModules[$chan][$module]))
                return $this->_channelModules[$chan][$module];
        }

        else if (isset($this->_plainModules[$module]))
            return $this->_plainModules[$module];

        $logging    =&  Plop::getInstance();
        $logger     =   $logging->getLogger(__FILE__);

        $class      =   $this->_bot->loadModule($module);
        $instance   =   new $class($this, $chan);

        $metadata   = $instance::getMetadata();
        $depends = (isset($metadata['requires']) ?
                    $metadata['requires'] : array());

        foreach ($depends as $depend) {
            $depend = new ErebotDependency($depend);
            try {
                $depVer     = $depend->getVersion();
                $depended   = $this->getModule(
                    $depend->getName(),
                    self::MODULE_BY_NAME,
                    $chan
                );

                // Check version of the module.
                if ($depVer !== NULL) {
                    $metadata   = $depended::getMetadata();
                    $depEffVer = (isset($metadata['version']) ?
                                    $metadata['version'] : NULL);
                    if ($depEffVer === NULL ||
                        !version_compare(
                            $depEffVer,
                            $depVer,
                            $depend->getOperator()
                        ))
                        throw new EErebotNotFound();
                }
            }
            catch (EErebotNotFound $e) {
                unset($instance);
                // We raise a new exception with the full
                // dependency specification.
                throw new EErebotNotFound((string) $depend);
            }
        }

        try {
            $instance->reload(
                ErebotModuleBase::RELOAD_ALL |
                ErebotModuleBase::RELOAD_INIT
            );
        }
        catch (EErebotNotFound $e) {
            unset($instance);
            throw new EErebotNotFound('??? (missing dependency)');
        }

        if ($chan === NULL)
            $this->_plainModules[$module] = $instance;
        else
            $this->_channelModules[$chan][$module] = $instance;

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
    public function & getModule($name, $type, $chan = NULL)
    {
        if ($type == self::MODULE_BY_NAME)
            ;   // Nothing to do here.
        else if ($type == self::MODULE_BY_CLASS)
            $name = $this->_bot->moduleClassToName($name);
        else
            throw new EErebotInvalidValue('Invalid retrieval type');

        if ($chan !== NULL && isset($this->_channelModules[$chan][$name]))
            return $this->_channelModules[$chan][$name];

        if (isset($this->_plainModules[$name]))
            return $this->_plainModules[$name];

        throw new EErebotNotFound('No instance found');
    }

    // Documented in the interface.
    public function addRawHandler(iErebotRawHandler &$handler)
    {
        $this->_raws[] = $handler;
    }

    // Documented in the interface.
    public function removeRawHandler(iErebotRawHandler &$handler)
    {
        $key = array_search($handler, $this->_raws);
        if ($key === FALSE)
            throw new EErebotNotFound('No such raw handler');
        unset($this->_raws[$key]);
    }

    // Documented in the interface.
    public function addEventHandler(iErebotEventHandler &$handler)
    {
        $this->_events[] = $handler;
    }

    // Documented in the interface.
    public function removeEventHandler(iErebotEventHandler &$handler)
    {
        $key = array_search($handler, $this->_events);
        if ($key === FALSE)
            throw new EErebotNotFound('No such event handler');
        unset($this->_events[$key]);
    }

    // Documented in the interface.
    public function dispatchEvent(iErebotEvent &$event)
    {
        try {
            foreach ($this->_events as &$handler) {
                if ($handler->handleEvent($event) === FALSE)
                    break;
            }
        }
        // This should help make the code a little more "bug-free" (TM).
        catch (EErebotErrorReporting $e) {
            $logging    =&  Plop::getInstance();
            $logger     =   $logging->getLogger(__FILE__);
            $logger->error(
                $this->_bot->gettext(
                    '%(message)s in %(file)s on line %(line)d'
                ),
                array(
                    'message'   => $e->getMessage(),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),
                )
            );
            $this->disconnect($e->getMessage());
        }
    }

    // Documented in the interface.
    public function dispatchRaw(iErebotRaw &$raw)
    {
        try {
            foreach ($this->_raws as &$handler) {
                if ($handler->handleRaw($raw) === FALSE)
                    break;
            }
        }
        // This should help make the code a little more "bug-free" (TM).
        catch (EErebotErrorReporting $e) {
            $logging    =&  Plop::getInstance();
            $logger     =   $logging->getLogger(__FILE__);
            $logger->error(
                $this->_bot->gettext(
                    '%(message)s in %(file)s on line %(line)d'
                ),
                array(
                    'message'   => $e->getMessage(),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),
                )
            );
            $this->disconnect($e->getMessage());
        }
    }
}

