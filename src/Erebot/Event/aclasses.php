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

    protected $_halt;
    protected $_connection;

    public function __construct(iErebotConnection &$connection)
    {
        $this->_halt        =   FALSE;
        $this->_connection  =&  $connection;
    }

    // Documented in the interface.
    public function & getConnection()
    {
        return $this->_connection;
    }

    // Documented in the interface.
    public function preventDefault($prevent = NULL)
    {
        $res = $this->_halt;
        if ($prevent !== NULL) {
            if (!is_bool($prevent))
                throw new EErebotInvalidValue('Bad prevention value');

            $this->_halt = $prevent;
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
    protected $_text;

    public function __construct(iErebotConnection &$connection, $text)
    {
        parent::__construct($connection);
        $this->_text = new ErebotTextWrapper($text);
    }

    // Documented in the interface.
    public function & getText()
    {
        return $this->_text;
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
    protected $_chan;
    protected $_source;

    public function __construct(iErebotConnection &$connection, $chan, $source)
    {
        parent::__construct($connection);
        $this->_chan    =&  $chan;
        $this->_source  =   ErebotUtils::extractNick($source, FALSE);
    }

    // Documented in the interface.
    public function & getChan()
    {
        return $this->_chan;
    }

    // Documented in the interface.
    public function & getSource()
    {
        return $this->_source;
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
    protected $_text;

    public function __construct(
        iErebotConnection  &$connection,
                            $chan,
                            $source,
                            $text
    )
    {
        parent::__construct($connection, $chan, $source);
        $this->_text = new ErebotTextWrapper($text);
    }

    // Documented in the interface.
    public function & getText()
    {
        return $this->_text;
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
    protected $_ctcpType;
    
    public function __construct(
        iErebotConnection   &$connection,
                            $chan,
                            $source,
                            $ctcpType,
                            $text
    )
    {
        parent::__construct($connection, $chan, $source, $text);
        $this->ctcpType =& $_ctcpType;
    }

    // Documented in the interface.
    public function & getCtcpType()
    {
        return $this->_ctcpType;
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
    protected $_source;
    
    public function __construct(iErebotConnection &$connection, $source, $text)
    {
        parent::__construct($connection, $text);
        $this->_source = ErebotUtils::extractNick($source, FALSE);
    }

    // Documented in the interface.
    public function & getSource()
    {
        return $this->_source;
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
    protected $_ctcpType;
    
    public function __construct(
        iErebotConnection   &$connection,
                            $source,
                            $ctcpType,
                            $text
    )
    {
        parent::__construct($connection, $source, $text);
        $this->_ctcpType =& $ctcpType;
    }

    // Documented in the interface.
    public function & getCtcpType()
    {
        return $this->_ctcpType;
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
    protected $_source;
    protected $_target;

    public function __construct(
        iErebotConnection  &$connection,
                            $source,
                            $target
    )
    {
        parent::__construct($connection);
        $this->_source  =   ErebotUtils::extractNick($source, FALSE);
        $this->_target  =&  $target;
    }
    
    // Documented in the interface.
    public function & getSource()
    {
        return $this->_source;
    }

    // Documented in the interface.
    public function & getTarget()
    {
        return $this->_target;
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
    protected $_text;

    public function __construct(
        iErebotConnection   &$connection,
                            $source,
                            $target,
                            $text
    )
    {
        parent::__construct($connection, $source, $target);
        $this->_text = new ErebotTextWrapper($text);
    }
    
    // Documented in the interface.
    public function & getText()
    {
        return $this->_text;
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
    protected $_chan;

    public function __construct(
        iErebotConnection   &$connection,
                            $chan,
                            $source,
                            $target
    )
    {
        parent::__construct($connection, $source, $target);
        $this->_chan =& $chan;
    }

    // Documented in the interface.
    public function & getChan()
    {
        return $this->_chan;
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
    protected $_text;

    public function __construct(
        iErebotConnection   &$connection,
                            $chan,
                            $source,
                            $target,
                            $text
    )
    {
        parent::__construct($connection, $chan, $source, $target);
        $this->_text = new ErebotTextWrapper($text);
    }

    // Documented in the interface.
    public function & getText()
    {
        return $this->_text;
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
        return $this->MODE_PREFIX . $this->MODE_LETTER;
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
    // Documented in the interface.
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
    protected $_ident;
    protected $_host;
    protected $_timestamp;

    public function __construct(
        iErebotConnection   &$connection,
                            $source,
                            $ident,
                            $host,
                            $timestamp,
                            $text
    )
    {
        parent::__construct($connection, $source, $text);
        $this->_ident       = $ident;
        $this->_host        = $host;
        $this->_timestamp   = $timestamp;
    }

    public function getIdent()
    {
        return $this->_ident;
    }

    public function getHostname()
    {
        return $this->_host;
    }

    public function getMask()
    {
        return $this->_source.'!'.$this->_ident.'@'.$this->_host;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }
}

