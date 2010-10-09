<?php

ErebotUtils::incl('config/mainConfig.php');
ErebotUtils::incl('events/rawHandler.php');
ErebotUtils::incl('events/eventHandler.php');

class       ErebotConnection
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

    protected $channelModules;
    protected $plainModules;

    protected $raws;
    protected $events;

    public function __construct(Erebot &$bot, ErebotServerConfig &$config)
    {
        $this->config   =&  $config;
        $this->bot      =&  $bot;

        $this->channelModules   = array();
        $this->plainModules     = array();
        $this->moduleClasses    = array();
        $this->raws             = array();
        $this->events           = array();

        $modules = $config->getModules(TRUE);
        foreach ($modules as &$module)
            $this->loadModule($module, NULL);
        unset($module);

        $netCfg     =&  $config->getNetworkCfg();
        $channels   =   $netCfg->getChannels();
        foreach ($channels as &$chanCfg) {
            $modules    =   $chanCfg->getModules(FALSE);
            foreach ($modules as &$module) {
                $this->loadModule($module, $chanCfg->getName());
            }
            unset($module);
        }
        unset($chanCfg);

        $this->connect();
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    public function connect()
    {
        $this->sndQueue         = array();
        $this->rcvQueue         = array();

        $url        =   $this->config->getConnectionURL();
        $netCfg     =&  $this->config->getNetworkCfg();
        $network    =   $netCfg->getName();

        $this->socket = @fopen($url, 'r+t');
        if ($this->socket === FALSE)
            throw new EErebotConnectionFailure(
                "Unable to connect to '".$url."'");

        $event = new ErebotEvent($this, NULL, NULL,
                                ErebotEvent::ON_LOGON, NULL);
        $this->dispatchEvent($event);
    }

    public function disconnect($quitMessage = NULL)
    {
        // Purge send queue and send QUIT message to notify server.
        $this->sndQueue = array();
        if ($quitMessage === NULL)
            $quitMessage = $this->config->getQuitMessage();
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
                throw new EErebotInvalidValue('Line contains forbidden characters');
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

    public function processIncomingData()
    {
        $i = 0;
        do {
            $line = fread($this->socket, 4096);

            if (feof($this->socket)) {
                $event = new ErebotEvent($this, NULL, NULL, ErebotEvent::ON_DISCONNECT, NULL, NULL);
                $this->dispatchEvent($event);

                if (!$event->preventDefault())
                    throw new EErebotConnectionFailure('Disconnected');
                else break;
            }

            if ($line == '')
                break;

            $line = ErebotUtils::toUTF8(substr($line, 0, -2));
            $this->rcvQueue[]   = $line;
echo date('<- [H:i:s] ').addcslashes($line, "\000..\037")."\n";
        } while ($i < 10);
    }

    public function processOutgoingData()
    {
        if ($this->emptySendQueue())
            throw new EErebotNotFound('No outgoing data needs to be handled');
        $line = array_shift($this->sndQueue);
echo date('-> [H:i:s] ').addcslashes($line, "\000..\037")."\n";
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
        $type   = array_shift($parts);

        if (!strcasecmp($source, 'PING')) {
            $msg = implode(' ', $parts);
            $event = new ErebotEvent($this, $type, NULL, ErebotEvent::ON_PING, $msg);
            $this->dispatchEvent($event);
            return;
        }

        if ($source == '' || !count($parts))
            return;

        if ($source[0] == ':')
            $source = substr($source, 1);

        $type = strtoupper($type);

        /* ON_QUIT is handled separately because
         * it doesn't have a $target... */
        if ($type == 'QUIT') {
            if ($parts[0][0] == ':')
                $parts[0] = substr($parts[0], 1);

            $msg = implode(' ', $parts);
            $event = new ErebotEvent($this, $source, NULL, ErebotEvent::ON_QUIT, $msg);
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
                $event = new ErebotEvent($this, $source, $msg, ErebotEvent::ON_INVITE, NULL);
                $this->dispatchEvent($event);
                break;

            case 'JOIN':    // :nick1!ident@host JOIN :#chan
                $event = new ErebotEvent($this, $source, $target, ErebotEvent::ON_JOIN, NULL);
                $this->dispatchEvent($event);
                break;

            case 'KICK':    // :nick1!ident@host KICK #chan nick2 :Reason
                $pos    = strcspn($msg, " ");
                $nick   = substr($msg, 0, $pos);
                $msg    = substr($msg, $pos + 1);
                if (strlen($msg) && $msg[0] == ':')
                    $msg = substr($msg, 1);

                $event  = new ErebotEvent($this, $source, $target, ErebotEvent::ON_KICK, $msg, $nick);
                $this->dispatchEvent($event);
                break;

            case 'MODE':    // :nick1!ident@host MODE <nick2/#chan> modes
                $capabilities = $this->getModule('ServerCapabilities', self::MODULE_BY_NAME);
                if (!$capabilities->isChannel($target)) {
                    $event = new ErebotEvent($this, $source, $target, ErebotEvent::ON_USERMODE, $msg);
                    $this->dispatchEvent($event);
                    break;
                }

                $event  = new ErebotEvent($this, $source, $target, ErebotEvent::ON_RAWMODE, $msg);
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
                            'o' => ErebotEvent::ON_OP,
                            'h' => ErebotEvent::ON_HELP,
                            'v' => ErebotEvent::ON_VOICE,
                            'b' => ErebotEvent::ON_BAN,
                        ),
                    self::MODE_REMOVE =>
                        array(
                            'o' => ErebotEvent::ON_DEOP,
                            'h' => ErebotEvent::ON_DEHELP,
                            'v' => ErebotEvent::ON_DEVOICE,
                            'b' => ErebotEvent::ON_UNBAN,
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
                        case 'b':
                            $tnick = ErebotUtils::gettok($msg, $k++, 1);
                            $event = new ErebotEvent($this, $source, $target,
                                $priv[$mode][$modes[$i]], $tnick);
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

                    $event = new ErebotEvent($this, $source, $target,
                        ErebotEvent::ON_MODE, $remaining_modes.$remains);
                    $this->dispatchEvent($event);
                }

                break; // ON_MODE

            case 'NICK':    // :oldnick!ident@host NICK newnick
                $event = new ErebotEvent($this, $source, NULL, ErebotEvent::ON_NICK, $target);
                $this->dispatchEvent($event);
                break;

            case 'NOTICE':    // :nick1!ident@host NOTICE <nick2/#chan> :Message
                $ev     = ErebotEvent::ON_NOTICE;
                $ctcp   = NULL;

                if (($len = strlen($msg)) > 1 &&
                    $msg[$len-1] == "\x01" &&
                    $msg[0] == "\x01") {

                    $msg    = substr($msg, 1, -1);
                    $ctcp   = strtoupper(substr($msg, 0, strcspn($msg, " ")));
                    $ev     = ErebotEvent::ON_CTCPREPLY;
                }

                $event = new ErebotEvent($this, $source, $target, $ev, $msg, $ctcp);
                $this->dispatchEvent($event);
                break;

            case 'PART':    // :nick1!ident@host PART #chan :Reason
                $event = new ErebotEvent($this, $source, $target, ErebotEvent::ON_PART, $msg);
                $this->dispatchEvent($event);
                break;

            /* PING? PONG! */
            case 'PING':    // :origin PING nick1 ???
                $event = new ErebotEvent($this, $source, $target, ErebotEvent::ON_PING, $msg);
                $this->dispatchEvent($event);
                break;

            /* We sent a PING and got a PONG! :) */
            case 'PONG':    // :origin PONG nick1 ???
                $event = new ErebotEvent($this, $source, $target, ErebotEvent::ON_PONG, $msg);
                $this->dispatchEvent($event);
                break;

            case 'PRIVMSG':    // :nick1!ident@host PRIVMSG <nick2/#chan> :Message
                $ev     = ErebotEvent::ON_TEXT;
                $ctcp   = NULL;

                if (($len = strlen($msg)) > 1 &&
                    $msg[$len-1] == "\x01" &&
                    $msg[0] == "\x01") {

                    $msg    = substr($msg, 1, -1);
                    $ctcp   = strtoupper(substr($msg, 0, strcspn($msg, " ")));

                    if ($ctcp == "ACTION") {
                        $ev     = ErebotEvent::ON_ACTION;
                        $msg    = substr($msg, strlen($ctcp) + 1);
                    }
                    else $ev = ErebotEvent::ON_CTCP;
                }
                $event = new ErebotEvent($this, $source, $target, $ev, $msg, $ctcp);
                $this->dispatchEvent($event);

            case 'TOPIC':    // nick1!ident@host TOPIC #chan :New topic
                $event = new ErebotEvent($this, $source, $target, ErebotEvent::ON_TOPIC, $msg);
                $this->dispatchEvent($event);
                break;

            default:
                /* RAW (numeric) events */
                if (ctype_digit($type)) {
                    $type   = intval($type, 10);

                    if ($type == RPL_WELCOME) {
                        $event = new ErebotEvent($this, $source, $target, ErebotEvent::ON_CONNECT, $msg);
                        $this->dispatchEvent($event);
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

        $class = $this->bot->loadModule($module);
echo "Module '$module' loaded\n";

        if ($chan !== NULL) {
            $instance = new $class($this, $chan);
            $this->channelModules[$chan][$module] = $instance;
            return $instance;
        }

        $instance = new $class($this, NULL);
        $this->plainModules[$module] = $instance;
        return $instance;
    }

    public function getModules($chan = NULL)
    {
        if ($chan !== NULL)
            return $this->channelModules[$chan];
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

        if ($chan !== NULL) {
            if (!isset($this->channelModules[$chan][$name]))
                throw new EErebotNotFound('No such instance');
            return $this->channelModules[$chan][$name];
        }

        if (!isset($this->plainModules[$name]))
            throw new EErebotNotFound('No such instance');
        return $this->plainModules[$name];
    }

    public function addRawHandler(ErebotRawHandler &$handler)
    {
        $this->raws[]   = $handler;
    }

    public function removeRawHandler(ErebotRawHandler &$handler)
    {
        $key = array_search($handler, $this->raws);
        if ($key === FALSE)
            throw new EErebotNotFound('No such raw handler');
        unset($this->raws[$key]);
    }

    public function addEventHandler(ErebotEventHandler &$handler)
    {
        $this->events[] = $handler;
    }

    public function removeEventHandler(ErebotEventHandler &$handler)
    {
        $key = array_search($handler, $this->events);
        if ($key === FALSE)
            throw new EErebotNotFound('No such event handler');
        unset($this->events[$key]);
    }

    public function dispatchEvent(ErebotEvent &$event)
    {
        foreach ($this->events as &$handler) {
            if ($handler->match($event)) {
                $res = call_user_func($handler->getCallback(), $event);
                if ($res === FALSE)
                    break;
            }
        }
    }

    public function dispatchRaw(ErebotRaw &$raw)
    {
        foreach ($this->raws as &$handler) {
            if ($handler->match($raw)) {
                $res = call_user_func($handler->getCallback(), $raw);
                if ($res === FALSE)
                    break;
            }
        }
    }
}

?>
