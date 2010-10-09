<?php

ErebotUtils::incl('config/mainConfig.php');
ErebotUtils::incl('events/events.php');
ErebotUtils::incl('events/rawHandler.php');
ErebotUtils::incl('events/eventHandler.php');
ErebotUtils::incl('ifaces/connection.php');

class       ErebotConnection
implements  iErebotConnection
{
    const MODULE_BY_NAME    = 0;
    const MODULE_BY_CLASS   = 1;

    const MODE_ADD          = 0;
    const MODE_REMOVE       = 1;

    protected $config;
    protected $bot;

    protected $socket;
    protected $sndQueue;
    protected $rcvQueue;
    protected $incomingData;

    protected $channelModules;
    protected $plainModules;

    protected $raws;
    protected $events;

    public function __construct(iErebot &$bot, iErebotServerConfig &$config)
    {
        $this->config   =&  $config;
        $this->bot      =&  $bot;

        $this->channelModules   = array();
        $this->plainModules     = array();
        $this->moduleClasses    = array();
        $this->raws             = array();
        $this->events           = array();
        $this->sndQueue         = array();
        $this->rcvQueue         = array();
        $this->incomingData     = '';

        $this->loadGeneralModules();
        $this->loadChannelModules();
    }

    protected function loadGeneralModules()
    {
        $logging    =&  ErebotLogging::getInstance();
        $logger     =   $logging->getLogger(__FILE__);

        // First pass: try to load them.
        $failed    = $this->config->getModules(TRUE);
        do {
            $modules    = $failed;
            $failed     = array();

            foreach ($modules as $module) {
                try {
                    $this->loadModule($module, NULL);
                }
                catch (EErebotNotFound $e) {
                    $logger->debug("Could not load '%(module)s' module, ".
                                    "putting it on hold for now...",
                                    array(
                                        'module'    => $module,
                                    ));
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
                    $logger->error("Unmet dependency for module '%(module)s': ".
                                    "%(dependency)s",
                                    array(
                                        'module'        => $module,
                                        'dependency'    => $e->getMessage(),
                                    ));
                }
            }
            $this->bot->stop();
            throw new EErebotNotFound('There are unmet dependencies');
        }
    }

    protected function loadChannelModules()
    {
        $logging    =&  ErebotLogging::getInstance();
        $logger     =   $logging->getLogger(__FILE__);

        $netCfg     =&  $this->config->getNetworkCfg();
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
                        $logger->debug("Could not load '%(module)s' module, ".
                                        "putting it on hold for now...",
                                        array(
                                            'module'    => $module,
                                        ));
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
                        $logger->error("Unmet dependency for module ".
                            "'%(module)s' on %(channel)s: %(dependency)s",
                            array(
                                'channel'       => $chan,
                                'module'        => $module,
                                'dependency'    => $e->getMessage(),
                            ));
                    }
                }
                $this->bot->stop();
                throw new EErebotNotFound('There are unmet dependencies');
            }
        }
        unset($chanCfg);
    }

    public function __destruct()
    {
        unset(
            $this->events,
            $this->raws,
            $this->config,
            $this->bot,
            $this->channelModules,
            $this->plainModules
        );
    }

    public function connect()
    {
        $url        =   $this->config->getConnectionURL();
        $netCfg     =&  $this->config->getNetworkCfg();
        $network    =   $netCfg->getName();

        try {
            $this->socket = fopen($url, 'r+b');
        }
        catch (EErebotErrorReporting $e) {
            throw new EErebotConnectionFailure(
                "Unable to connect to '".$url."'");
        }

        $event = new ErebotEventLogon($this);
        $this->dispatchEvent($event);
    }

    public function disconnect($quitMessage = NULL)
    {
        $logging    =&  ErebotLogging::getInstance();
        $logger     =   $logging->getLogger(__FILE__);
        $logger->info("Disconnecting...");

        // Purge send queue and send QUIT message to notify server.
        $this->sndQueue = array();
        if ($quitMessage === NULL) {
            try {
                $config =& $this->getConfig(NULL);
                $quitMessage = $config->parseString(
                                    'IrcConnector',
                                    'quit_message');
            }
            catch (EErebot $e) { }
            unset($config);
        }
        $quitMessage    = ($quitMessage !== NULL ? ' :'.$quitMessage : '');
        $this->pushLine('QUIT'.$quitMessage);
        $this->processOutgoingData();

        // Then kill the connection for real.
        $this->bot->removeConnection($this);
        if (is_resource($this->socket))
            fclose($this->socket);
        $this->socket = NULL;
    }

    public function pushLine($line)
    {
        $chars = array("\r", "\n");
        foreach ($chars as $char)
            if (strpos($line, $char) !== FALSE)
                throw new EErebotInvalidValue(
                    'Line contains forbidden characters');
        $this->sndQueue[] =& $line;
    }

    public function & getConfig($chan)
    {
        if ($chan === NULL)
            return $this->config;

        try {
            $netCfg     =&  $this->config->getNetworkCfg();
            $chanCfg    =&  $netCfg->getChannelCfg($chan);
            unset($netCfg);
            return $chanCfg;
        }
        catch (EErebotNotFound $e) {
            return $this->config;
        }
    }

    public function & getSocket()
    {
        return $this->socket;
    }

    public function emptyReadQueue()
    {
        return (count($this->rcvQueue) == 0);
    }

    public function emptySendQueue()
    {
        return (count($this->sndQueue) == 0);
    }

    protected function getSingleLine()
    {
        $pos = strpos($this->incomingData, "\r\n");
        if ($pos === FALSE)
            return FALSE;

        $line = ErebotUtils::toUTF8(substr($this->incomingData, 0, $pos));
        $this->incomingData = substr($this->incomingData, $pos + 2);
        $this->rcvQueue[]   = $line;

        $logging    =&  ErebotLogging::getInstance();
        $logger     =   $logging->getLogger(__FILE__.
                            DIRECTORY_SEPARATOR.'input');
        $logger->info("%s", addcslashes($line, "\000..\037"));
        return TRUE;
    }

    public function processIncomingData()
    {
        $received   = fread($this->socket, 4096);
        if ($received === FALSE || feof($this->socket)) {
            $event = new ErebotEventDisconnect($this);
            $this->dispatchEvent($event);

            if (!$event->preventDefault())
                throw new EErebotConnectionFailure('Disconnected');
            return;
        }

        $this->incomingData .= $received;
        while ($this->getSingleLine())
            ;   // Read messages.
    }

    public function processOutgoingData()
    {
        if ($this->emptySendQueue())
            throw new EErebotNotFound('No outgoing data needs to be handled');
        $line       =   array_shift($this->sndQueue);
        $logging    =&  ErebotLogging::getInstance();
        $logger     =   $logging->getLogger(__FILE__.
                            DIRECTORY_SEPARATOR.'output');
        $logger->info("%s", addcslashes($line, "\000..\037"));
        return fwrite($this->socket, $line."\r\n");
    }

    public function processQueuedData()
    {
        if (!count($this->rcvQueue))
            return NULL;

        while (count($this->rcvQueue))
            $this->handleMessage(array_shift($this->rcvQueue));
    }

    protected function handleMessage($msg)
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

                $event  = new ErebotEventKick($this, $target, $source, $nick, $msg);
                $this->dispatchEvent($event);
                break;

            case 'KILL':    // :nick1!ident@host KILL nick2 :Reason
                $event  = new ErebotEventKill($this, $target, $source, $msg);
                $this->dispatchEvent($event);
                break;

            case 'MODE':    // :nick1!ident@host MODE <nick2/#chan> modes
                $capabilities = $this->getModule('ServerCapabilities', self::MODULE_BY_NAME);
                if (!$capabilities->isChannel($target)) {
                    $event = new ErebotEventUserMode($this, $source, $target, $msg);
                    $this->dispatchEvent($event);
                    break;
                }

                $event  = new ErebotEventRawMode($this, $target, $source, $msg);
                $this->dispatchEvent($event);

                if ($event->preventDefault(TRUE))
                    break;

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
                            for ($j = 3; $j > 0; $j--)
                                if (strpos($this->chanModes[$j-1], $modes[$i]) !== FALSE)
                                    break;
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

                $remaining_modes = str_replace(array('o', 'h', 'v', 'b', '+', '-'), '', $modes);
                if ($remaining_modes != '') {
                    if (count($remains))
                        $remains = ' '.implode(' ', $remains);
                    else $remains = '';

                    for ($i = strlen($modes) - 1; $i >= 0; $i--) {

                    }

# @TODO
#                    $event = new ErebotEvent($this, $source, $target,
#                        ErebotEvent::ON_MODE, $remaining_modes.$remains);
#                    $this->dispatchEvent($event);
                }

                break; // ON_MODE

            case 'NICK':    // :oldnick!ident@host NICK newnick
                $event = new ErebotEventNick($this, $source, $target);
                $this->dispatchEvent($event);
                break;

            case 'NOTICE':    // :nick1!ident@host NOTICE <nick2/#chan> :Message
                $capabilities = $this->getModule('ServerCapabilities', self::MODULE_BY_NAME);
                if (($len = strlen($msg)) > 1 &&
                    $msg[$len-1] == "\x01" &&
                    $msg[0] == "\x01") {

                    $msg    = substr($msg, 1, -1);
                    $pos    = strcspn($msg, " ");
                    $ctcp   = strtoupper(substr($msg, 0, $pos));
                    $msg    = substr($msg, $pos + 1);

                    if ($capabilities->isChannel($target))
                        $event = new ErebotEventCtcpReplyChan($this, $target, $source, $ctcp, $msg);
                    else
                        $event = new ErebotEventCtcpReplyPrivate($this, $source, $ctcp, $msg);
                    $this->dispatchEvent($event);
                    break;
                }

                if ($capabilities->isChannel($target))
                    $event = new ErebotEventNoticeChan($this, $target, $source, $msg);
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

            case 'PRIVMSG':    // :nick1!ident@host PRIVMSG <nick2/#chan> :Message
                $capabilities = $this->getModule('ServerCapabilities', self::MODULE_BY_NAME);
                if (($len = strlen($msg)) > 1 &&
                    $msg[$len-1] == "\x01" &&
                    $msg[0] == "\x01") {

                    $msg    = substr($msg, 1, -1);
                    $pos    = strcspn($msg, " ");
                    $ctcp   = strtoupper(substr($msg, 0, $pos));
                    $msg    = substr($msg, $pos + 1);

                    if ($ctcp == "ACTION") {
                        if ($capabilities->isChannel($target))
                            $event = new ErebotEventActionChan($this, $target, $source, $msg);
                        else
                            $event = new ErebotEventActionPrivate($this, $source, $msg);
                        $this->dispatchEvent($event);
                        break;
                    }

                    if ($capabilities->isChannel($target))
                        $event = new ErebotEventCtcpChan($this, $target, $source, $ctcp, $msg);
                    else
                        $event = new ErebotEventCtcpPrivate($this, $source, $ctcp, $msg);
                    $this->dispatchEvent($event);
                    break;
                }

                if ($capabilities->isChannel($target))
                    $event = new ErebotEventTextChan($this, $target, $source, $msg);
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

                    $event  = new ErebotRaw($this, $type, $source, $target, $msg);
                    $this->dispatchRaw($event);
                }
        } /* switch ($type) */
    }

    public function & getBot()
    {
        return $this->bot;
    }

    public function & loadModule($module, $chan = NULL)
    {
        if ($chan !== NULL) {
            if (isset($this->channelModules[$chan][$module]))
                return $this->channelModules[$chan][$module];
        }

        else if (isset($this->plainModules[$module]))
            return $this->plainModules[$module];

        $logging    =&  ErebotLogging::getInstance();
        $logger     =   $logging->getLogger(__FILE__);

        $class      =   $this->bot->loadModule($module);
        $instance   =   new $class($this, $chan);
        $instance->reload(  ErebotModuleBase::RELOAD_METADATA |
                            ErebotModuleBase::RELOAD_INIT);

        $opTokens   = ' !<>=';
        $operators  =   array(
                            '<', 'lt',
                            '<=', 'le',
                            '>', 'gt',
                            '>=', 'ge',
                            '==', '=', 'eq',
                            '!=', '<>', 'ne',
                        );

        $depends    = $instance->getMetadata(ErebotModuleBase::META_DEPENDS);
        foreach ($depends as &$depend) {
            $depNameEnd     = strcspn($depend, $opTokens);
            $depName        = substr($depend, 0, $depNameEnd);

            $len = strlen($depend);
            if ($depNameEnd == $len)
                $depOp = $depVer = NULL;
            else {
                $depVerStart    = $len - strcspn(strrev($depend, $opTokens));
                if ($depVerStart <= $depNameEnd)
                    throw new Exception('Invalid dependency specification');
                $depVer         = strtolower(substr($depend, $depVerStart));
                $depOp          = strtolower(trim(substr($depend,
                                    $depNameEnd, $depVerStart), ' '));
                if (!in_array($depOp, $operators))
                    throw new Exception('Invalid dependency operator');
            }

            try {
                $depended   = $this->getModule($depName,
                                self::MODULE_BY_NAME, $chan);

                // Check version of the module.
                if ($depVer !== NULL) {
                    $depEffVer  = $depended->getMetadata(
                                    ErebotModuleBase::META_VERSION);
                    if ($depEffVer === NULL ||
                        !version_compare($depEffVer, $depVer, $depOp))
                        throw new EErebotNotFound();
                }
            }
            catch (EErebotNotFound $e) {
                unset($instance);
                // We raise a new exception with the full
                // dependency specification.
                throw new EErebotNotFound($depend);
            }
        }
        unset($depend);
        try {
            $instance->reload( (ErebotModuleBase::RELOAD_ALL |
                                ErebotModuleBase::RELOAD_INIT) ^
                                ErebotModuleBase::RELOAD_METADATA);
        }
        catch (EErebotNotFound $e) {
            unset($instance);
            throw new EErebotNotFound('??? (missing dependency)');
        }
        if ($chan === NULL)
            $this->plainModules[$module] = $instance;
        else
            $this->channelModules[$chan][$module] = $instance;

        $logger->info("Successfully loaded module '%s'", $module);
        return $instance;
    }

    public function getModules($chan = NULL)
    {
        if ($chan !== NULL) {
            return  $this->channelModules[$chan] +
                    $this->plainModules;
        }
        return $this->plainModules;
    }

    public function & getModule($name, $type, $chan = NULL)
    {
        if ($type == self::MODULE_BY_NAME)
            ;   // Nothing to do here.
        else if ($type == self::MODULE_BY_CLASS)
            $name = $this->bot->moduleClassToName($name);
        else
            throw new EErebotInvalidValue('Invalid retrieval type');

        if ($chan !== NULL && isset($this->channelModules[$chan][$name]))
            return $this->channelModules[$chan][$name];

        if (isset($this->plainModules[$name]))
            return $this->plainModules[$name];

        throw new EErebotNotFound('No such instance');
    }

    public function addRawHandler(iErebotRawHandler &$handler)
    {
        $this->raws[]   = $handler;
    }

    public function removeRawHandler(iErebotRawHandler &$handler)
    {
        $key = array_search($handler, $this->raws);
        if ($key === FALSE)
            throw new EErebotNotFound('No such raw handler');
        unset($this->raws[$key]);
    }

    public function addEventHandler(iErebotEventHandler &$handler)
    {
        $this->events[] = $handler;
    }

    public function removeEventHandler(iErebotEventHandler &$handler)
    {
        $key = array_search($handler, $this->events);
        if ($key === FALSE)
            throw new EErebotNotFound('No such event handler');
        unset($this->events[$key]);
    }

    public function dispatchEvent(iErebotEvent &$event)
    {
        try {
            foreach ($this->events as &$handler) {
                if ($handler->handleEvent($event) === FALSE)
                    break;
            }
        }
        // This should help make the code a little more "bug-free" (TM).
        catch (EErebotErrorReporting $e) {
            $logging    =&  ErebotLogging::getInstance();
            $logger     =   $logging->getLogger(__FILE__);
            $logger->error('%(message)s in %(file)s on line %(line)d', array(
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ));
            $this->disconnect($e->getMessage());
        }
    }

    public function dispatchRaw(iErebotRaw &$raw)
    {
        try {
            foreach ($this->raws as &$handler) {
                if ($handler->handleRaw($raw) === FALSE)
                    break;
            }
        }
        // This should help make the code a little more "bug-free" (TM).
        catch (EErebotErrorReporting $e) {
            $logging    =&  ErebotLogging::getInstance();
            $logger     =   $logging->getLogger(__FILE__);
            $logger->error('%(message)s in %(file)s on line %(line)d', array(
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
            ));
            $this->disconnect($e->getMessage());
        }
    }
}

?>
