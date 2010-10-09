<?php

ErebotUtils::incl('eventTargets.php');

class ErebotTextWrapper
{
    protected $text;

    public function __construct($text)
    {
        $this->text = $text;
    }

    public function getTokens($start, $length = 0, $separator = ' ')
    {
        return ErebotUtils::gettok($this->text, $start, $length, $separator);
    }

    public function countTokens($separator = ' ')
    {
        return ErebotUtils::numtok($this->text, $separator);
    }

    public function __toString()
    {
        return $this->text;
    }
}

class ErebotEvent
{
    const ON_ACTION         =  10;  // *
    const ON_BAN            =  20;  // *
    const ON_CHAT           =  30;
    const ON_CONNECT        =  40;  // *
    const ON_CONNECTFAIL    =  50;
    const ON_CTCP           =  60;  // *
    const ON_CTCPREPLY      =  70;  // *
    const ON_DCCSERVER      =  80;
    const ON_DEHALFOP       =  90;  // *
    const ON_DEHELP         =  90;  // *
    const ON_DEOWN          =  95;
    const ON_DEOP           = 100;  // *
    const ON_DEPROTECT      = 105;
    const ON_DEVOICE        = 110;  // *
    const ON_DISCONNECT     = 120;  // *
    const ON_ERROR          = 130;
    const ON_EXIT           = 140;
    const ON_FILERCVD       = 150;
    const ON_FILESENT       = 160;
    const ON_GETFAIL        = 170;
    const ON_HALFOP         = 180;  // *
    const ON_HELP           = 180;  // *
    const ON_INVITE         = 200;  // *
    const ON_JOIN           = 210;  // *
    const ON_KICK           = 220;  // *
    const ON_LOGON          = 230;  // *
    const ON_MODE           = 240;  // *
    const ON_NICK           = 250;  // *
    const ON_NOSOUND        = 260;
    const ON_NOTICE         = 270;  // *
    const ON_OWNER          = 280;
    const ON_OP             = 290;  // *
    const ON_PART           = 300;  // *
    const ON_PING           = 310;  // *
    const ON_PONG           = 320;  // *
    const ON_PROTECT        = 325;
    const ON_QUIT           = 330;  // *
    const ON_RAWMODE        = 340;  // *
    const ON_SENDFAIL       = 350;
    const ON_SERV           = 360;
    const ON_SERVERMODE     = 370;
    const ON_SERVEROP       = 380;
    const ON_SNOTICE        = 390;
    const ON_TEXT           = 400;  // *
    const ON_TOPIC          = 410;  // *
    const ON_UNBAN          = 420;  // *
    const ON_USERMODE       = 425;  // *
    const ON_VOICE          = 430;  // *
    const ON_WALLOPS        = 440;

    private $connection;
    private $source;
    private $destination;
    private $type;
    private $text;
    private $halt;
    private $more;

    public function __construct(
        ErebotConnection &$connection,
        $source,
        $destination,
        $type,
        $text,
        $more = NULL
    )
    {
        $this->connection   = $connection;
        $this->source       = ErebotUtils::extractNick($source, FALSE);
        $this->destination  = $destination;
        $this->type         = $type;
        $this->text         = new ErebotTextWrapper($text);
        $this->halt         = FALSE;
        $this->more         = $more;
    }

    protected function getNick($events)
    {
        return in_array($this->type, $events) ? $this->text : NULL;
    }

    public function getSource() { return $this->source; }

    public function getKickNick()
    {
        return ($this->type == self::ON_KICK) ?
            $this->more : NULL;
    }

    public function getBanNick()
    {
        $nick = ErebotUtils::extractNick($this->text());
        return (($this->type == self::ON_BAN ||
                $this->type == self::ON_UNBAN) &&
                strpos($nick, '?') === FALSE &&
                strpos($nick, '*') === FALSE) ?
                $nick : NULL;
    }

    public function getBanMask()    { return $this->getNick(array(self::ON_BAN,     self::ON_UNBAN)); }
    public function getOpNick()     { return $this->getNick(array(self::ON_OP,      self::ON_DEOP)); }
    public function getHalfNick()   { return $this->getNick(array(self::ON_HELP,    self::ON_DEHELP)); }
    public function getVoiceNick()  { return $this->getNick(array(self::ON_VOICE,   self::ON_DEVOICE)); }
    public function getNewNick()    { return $this->getNick(array(self::ON_NICK)); }

    public function getChan()
    {
        $capabilities =& $this->connection->getModule('ServerCapabilities', ErebotConnection::MODULE_BY_NAME);
        return $capabilities->isChannel($this->destination) ? $this->destination : NULL;
    }

    public function getTarget()
    {
        $module     =&  $this->connection->getModule('IrcConnector', ErebotConnection::MODULE_BY_NAME);
        $botNick    =   $module->getBotNickname();

        return $this->destination !== NULL &&
            strcasecmp($this->destination, $botNick) ?
            $this->destination : $this->source;
    }

    public function & getConnection()
    {
        return $this->connection;
    }

    public function getType()           { return $this->type; }
    public function getText()           { return $this->text; }

    public function preventDefault($prevent = NULL)
    {
        $res = $this->halt;
        if ($prevent !== NULL) {
            if (!is_bool($prevent))
                throw new EErebotInvalidValue('Bad prevention value');

            $this->halt = $prevent;
        }
        return $res;
    }

    public function __toString() {
        return $this->type." ".$this->source." ".$this->destination." ".$this->text;
    }

    public static function getEvents()
    {
        $reflect    = new ReflectionClass(__CLASS__);
        $constants  = $reflect->getConstants();

        if (!count($constants)) return;
        $events = array();

        foreach ($constants as $name => $val) {
            if (substr($name, 0, 3) == 'ON_')
                $events[$name] = $val;
        }

        return $events;
    }
}

?>
