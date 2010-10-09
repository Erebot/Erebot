<?php

/**
 * \brief
 *      An abstract Event.
 */
abstract class  ErebotEvent
implements      iErebotEvent
{
    /// @TODO add support for these events or remove them...
#    const ON_CHAT           = 30;
#    const ON_CONNECTFAIL    = 50;
#    const ON_DCCSERVER      = 80;
#    const ON_ERROR          = 130;
#    const ON_FILERCVD       = 150;
#    const ON_FILESENT       = 160;
#    const ON_GETFAIL        = 170;
#    const ON_MODE           = 240;  // *
#    const ON_NOSOUND        = 260;
#    const ON_SENDFAIL       = 350;
#    const ON_SERV           = 360;
#    const ON_SERVERMODE     = 370;
#    const ON_SERVEROP       = 380;
#    const ON_SNOTICE        = 390;
#    const ON_WALLOPS        = 440;

    protected $halt;
    protected $connection;

    public function __construct(ErebotConnection &$connection)
    {
        $this->halt         =   FALSE;
        $this->connection   =&  $connection;
    }

    public function & getConnection()
    {
        return $this->connection;
    }

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
}

/**
 * \brief
 *      An abstract Event containing some text.
 */
abstract class  ErebotEventWithText
extends         ErebotEvent
implements      iErebotEventText
{
    protected $text;

    public function __construct(ErebotConnection &$connection, $text)
    {
        parent::__construct($connection);
        $this->text = new ErebotTextWrapper($text);
    }

    public function & getText()
    {
        return $this->text;
    }
}

/**
 * \brief
 *      An abstract Event which has a source and applies to a channel.
 */
abstract class  ErebotEventWithChanAndSource
extends         ErebotEvent
implements      iErebotEventChan,
                iErebotEventSource
{
    protected $chan;
    protected $source;

    public function __construct(ErebotConnection &$connection, $chan, $source)
    {
        parent::__construct($connection);
        $this->chan     =&  $chan;
        $this->source   =   ErebotUtils::extractNick($source, FALSE);
    }

    public function & getChan()
    {
        return $this->chan;
    }

    public function & getSource()
    {
        return $this->source;
    }
}

/**
 * \brief
 *      An abstract Event containing some text, having a source
 *      and applying to a channel.
 */
abstract class  ErebotEventWithChanSourceAndText
extends         ErebotEventWithChanAndSource
implements      iErebotEventText
{
    protected $text;

    public function __construct(ErebotConnection &$connection, $chan, $source, $text)
    {
        parent::__construct($connection, $chan, $source);
        $this->text = new ErebotTextWrapper($text);
    }

    public function & getText()
    {
        return $this->text;
    }
}

/**
 * \brief
 *      An abstract CTCP Event which applies to a channel
 *      and contains some text.
 */
abstract class  ErebotEventWithChanSourceAndCtcp
extends         ErebotEventWithChanSourceAndText
implements      iErebotEventCtcp
{
    protected $ctcpType;
    
    public function __construct(ErebotConnection &$connection, $chan, $source, $ctcpType, $text)
    {
        parent::__construct($connection, $chan, $source, $text);
        $this->ctcpType =& $ctcpType;
    }

    public function & getCtcpType()
    {
        return $this->ctcpType;
    }
}

/**
 * \brief
 *      An abstract Event which has a source and contains some text.
 */
abstract class  ErebotEventWithSourceAndText
extends         ErebotEventWithText
implements      iErebotEventSource
{
    protected $source;
    
    public function __construct(ErebotConnection &$connection, $source, $text)
    {
        parent::__construct($connection, $text);
        $this->source = ErebotUtils::extractNick($source, FALSE);
    }

    public function & getSource()
    {
        return $this->source;
    }
}

/**
 * \brief
 *      An abstract CTCP Event which applies to the bot
 *      and contains some text.
 */
abstract class  ErebotEventWithSourceAndCtcp
extends         ErebotEventWithSourceAndText
implements      iErebotEventCtcp
{
    protected $ctcpType;
    
    public function __construct(ErebotConnection &$connection, $source, $ctcpType, $text)
    {
        parent::__construct($connection, $source, $text);
        $this->ctcpType =& $ctcpType;
    }

    public function & getCtcpType()
    {
        return $this->ctcpType;
    }
}

/**
 * \brief
 *      An abstract Event with a source and a target.
 */
abstract class  ErebotEventWithSourceAndTarget
extends         ErebotEvent
implements      iErebotEventSource,
                iErebotEventTarget
{
    protected $source;
    protected $target;

    public function __construct(ErebotConnection &$connection, $source, $target)
    {
        parent::__construct($connection);
        $this->source   =   ErebotUtils::extractNick($source, FALSE);
        $this->target   =&  $target;
    }
    
    public function & getSource()
    {
        return $this->source;
    }

    public function & getTarget()
    {
        return $this->target;
    }
}

/**
 * \brief
 *      An abstract Event with a source, a target and some text.
 */
abstract class  ErebotEventWithSourceTargetAndText
extends         ErebotEventWithSourceAndTarget
implements      iErebotEventText
{
    protected $text;

    public function __construct(ErebotConnection &$connection, $source, $target, $text)
    {
        parent::__construct($connection, $source, $target);
        $this->text = new ErebotTextWrapper($text);
    }
    
    public function & getText()
    {
        return $this->text;
    }
}

/**
 * \brief
 *      An abstract Event which applies to a channel
 *      and has a source and a target.
 */
abstract class  ErebotEventWithChanSourceAndTarget
extends         ErebotEventWithSourceAndTarget
implements      iErebotEventChan
{
    protected $chan;

    public function __construct(ErebotConnection &$connection, $chan, $source, $target)
    {
        parent::__construct($connection, $source, $target);
        $this->chan     =& $chan;
    }

    public function & getChan()
    {
        return $this->chan;
    }
}

/**
 * \brief
 *      An abstract Event which applies to a channel,
 *      has a source, a target and even some text.
 */
abstract class  ErebotEventWithChanSourceTargetAndText
extends         ErebotEventWithChanSourceAndTarget
implements      iErebotEventText
{
    protected $text;

    public function __construct(ErebotConnection &$connection, $chan, $source, $target, $text)
    {
        parent::__construct($connection, $chan, $source, $target);
        $this->text = new ErebotTextWrapper($text);
    }

    public function & getText()
    {
        return $this->text;
    }
}

/**
 * \brief
 *      An abstract Event which represents a usermode change
 *      on a channel.
 */
abstract class  ErebotEventChanUserModeBase
extends         ErebotEventWithChanSourceAndTarget
{
    public function getMode()
    {
        return static::MODE_PREFIX . static::MODE_LETTER;
    }
}

/**
 * \brief
 *      An abstract Event whose target is an IRC mask (nick!ident\@host).
 */
abstract class  ErebotEventChanUserModeMaskBase
extends         ErebotEventChanUserModeBase
implements      iErebotEventTargetNick
{
    public function getTargetNick()
    {
        $nick = ErebotUtils::extractNick($this->target);
        return  (strpos($nick, '?') === FALSE &&
                strpos($nick, '*') === FALSE) ?
                $nick : NULL;
    }
}

/**
 * \brief
 *      An abstract Event for WATCH list notifications.
 */
abstract class  ErebotEventWatchNotification
extends         ErebotEventWithSourceAndText
{
    protected $ident;
    protected $host;
    protected $timestamp;

    public function __construct(ErebotConnection &$connection, $source,
                                $ident, $host, $timestamp, $text)
    {
        parent::__construct($connection, $source, $text);
        $this->ident        = $ident;
        $this->host         = $host;
        $this->timestamp    = $timestamp;
    }

    public function getIdent()
    {
        return $this->ident;
    }

    public function getHostname()
    {
        return $this->host;
    }

    public function getMask()
    {
        return $this->source.'!'.$this->ident.'@'.$this->host;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }
}

?>
