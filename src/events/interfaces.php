<?php

/**
 * Generic interface for an Event.
 */
interface iErebotEvent
{}

/**
 * Interface for an event which applies to a channel.
 */
interface   iErebotEventChan
extends     iErebotEvent
{
    public function & getChan();
}

/**
 * Interface for an event which has a source.
 */
interface   iErebotEventSource
extends     iErebotEvent
{
    public function & getSource();
}

/**
 * Interface for an event which has a target.
 */
interface   iErebotEventTarget
extends     iErebotEvent
{
    public function & getTarget();
}

/**
 * Interface for an event which contains some text.
 */
interface   iErebotEventText
extends     iErebotEvent
{
    public function & getText();
}

/**
 * Interface for an event whose target is an IRC mask.
 * Classes implementing this interface should try their best
 * to resolve the mask to a single nickname.
 */
interface   iErebotEventTargetNick
extends     iErebotEvent
{
    public function getTargetNick();
}

/**
 * Interface for a CTCP event.
 */
interface   iErebotEventCtcp
extends     iErebotEvent
{
    public function & getCtcpType();
}

/**
 * Interface for a mode which is given to someone.
 */
interface   iErebotEventChanModeGive
extends     iErebotEvent
{
    const MODE_PREFIX = '+';
}

/**
 * Interface for a mode which is taken from someone.
 */
interface   iErebotEventChanModeTake
extends     iErebotEvent
{
    const MODE_PREFIX = '-';
}

/**
 * Interface for BAN mode.
 */
interface   iErebotEventChanModeBan
extends     iErebotEvent
{
    const MODE_LETTER = 'b';
}

/**
 * Interface for ban EXCEPTion mode.
 */
interface   iErebotEventChanModeExcept
extends     iErebotEvent
{
    const MODE_LETTER = 'e';
}

/**
 * Interface for OPerator mode.
 */
interface   iErebotEventChanModeOp
extends     iErebotEvent
{
    const MODE_LETTER = 'o';
}

/**
 * Interface for HALF-OPerator mode.
 */
interface   iErebotEventChanModeHalfop
extends     iErebotEvent
{
    const MODE_LETTER = 'h';
}

/**
 * Interface for VOICE mode.
 */
interface   iErebotEventChanModeVoice
extends     iErebotEvent
{
    const MODE_LETTER = 'v';
}

/**
 * Interface for PROTECTion mode.
 */
interface   iErebotEventChanModeProtect
extends     iErebotEvent
{
    const MODE_LETTER = 'a';
}

/**
 * Interface for OWNER mode.
 */
interface   iErebotEventChanModeOwner
extends     iErebotEvent
{
    const MODE_LETTER = 'q';
}

/**
 * Interface for an event capable of conveying a message.
 */
interface   iErebotEventMessageCapable
extends     iErebotEvent
{}

/**
 * Interface for an event capable of conveying a text message.
 */
interface   iErebotEventMessageText
extends     iErebotEventMessageCapable
{}

/**
 * Interface for an event capable of conveying a NOTICE.
 */
interface   iErebotEventMessageNotice
extends     iErebotEventMessageCapable
{}

/**
 * Interface for an event capable of conveying an ACTION.
 */
interface   iErebotEventMessageAction
extends     iErebotEventMessageCapable
{}

/**
 * Interface for an event capable of conveying a CTCP query.
 */
interface   iErebotEventMessageCtcp
extends     iErebotEventMessageCapable
{}

/**
 * Interface for an event capable of conveying a CTCP reply.
 */
interface   iErebotEventMessageCtcpReply
extends     iErebotEventMessageCapable
{}

/**
 * Interface for an event which occurs in PRIVATE.
 */
interface   iErebotEventPrivate
extends     iErebotEvent
{}

?>
