<?php

/**
 * \brief
 *      Interface for a generic Event.
 */
interface iErebotEvent
{}

/**
 * \brief
 *      Interface for an event which applies to a channel.
 */
interface   iErebotEventChan
extends     iErebotEvent
{
    public function & getChan();
}

/**
 * \brief
 *      Interface for an event which has a source.
 */
interface   iErebotEventSource
extends     iErebotEvent
{
    public function & getSource();
}

/**
 * \brief
 *      Interface for an event which has a target.
 */
interface   iErebotEventTarget
extends     iErebotEvent
{
    public function & getTarget();
}

/**
 * \brief
 *      Interface for an event which contains some text.
 */
interface   iErebotEventText
extends     iErebotEvent
{
    public function & getText();
}

/**
 * \brief
 *      Interface for an event whose target is an IRC mask.
 *
 * Classes implementing this interface should do their best
 * to resolve the mask to a single nickname.
 */
interface   iErebotEventTargetNick
extends     iErebotEvent
{
    public function getTargetNick();
}

/**
 * \brief
 *      Interface for a CTCP event.
 */
interface   iErebotEventCtcp
extends     iErebotEvent
{
    public function & getCtcpType();
}

/**
 * \brief
 *      Interface for a mode which is given to someone.
 */
interface   iErebotEventChanModeGive
extends     iErebotEvent
{
    const MODE_PREFIX = '+';
}

/**
 * \brief
 *      Interface for a mode which is taken from someone.
 */
interface   iErebotEventChanModeTake
extends     iErebotEvent
{
    const MODE_PREFIX = '-';
}

/**
 * \brief
 *      Interface for BAN mode.
 */
interface   iErebotEventChanModeBan
extends     iErebotEvent
{
    const MODE_LETTER = 'b';
}

/**
 * \brief
 *      Interface for ban EXCEPTion mode.
 */
interface   iErebotEventChanModeExcept
extends     iErebotEvent
{
    const MODE_LETTER = 'e';
}

/**
 * \brief
 *      Interface for OPerator mode.
 */
interface   iErebotEventChanModeOp
extends     iErebotEvent
{
    const MODE_LETTER = 'o';
}

/**
 * \brief
 *      Interface for HALF-OPerator mode.
 */
interface   iErebotEventChanModeHalfop
extends     iErebotEvent
{
    const MODE_LETTER = 'h';
}

/**
 * \brief
 *      Interface for VOICE mode.
 */
interface   iErebotEventChanModeVoice
extends     iErebotEvent
{
    const MODE_LETTER = 'v';
}

/**
 * \brief
 *      Interface for PROTECTion mode.
 */
interface   iErebotEventChanModeProtect
extends     iErebotEvent
{
    const MODE_LETTER = 'a';
}

/**
 * \brief
 *      Interface for OWNER mode.
 */
interface   iErebotEventChanModeOwner
extends     iErebotEvent
{
    const MODE_LETTER = 'q';
}

/**
 * \brief
 *      Interface for an event capable of conveying a message.
 */
interface   iErebotEventMessageCapable
extends     iErebotEvent
{}

/**
 * \brief
 *      Interface for an event capable of conveying a text message.
 */
interface   iErebotEventMessageText
extends     iErebotEventMessageCapable
{}

/**
 * \brief
 *      Interface for an event capable of conveying a NOTICE.
 */
interface   iErebotEventMessageNotice
extends     iErebotEventMessageCapable
{}

/**
 * \brief
 *      Interface for an event capable of conveying an ACTION.
 */
interface   iErebotEventMessageAction
extends     iErebotEventMessageCapable
{}

/**
 * \brief
 *      Interface for an event capable of conveying a CTCP query.
 */
interface   iErebotEventMessageCtcp
extends     iErebotEventMessageCapable
{}

/**
 * \brief
 *      Interface for an event capable of conveying a CTCP reply.
 */
interface   iErebotEventMessageCtcpReply
extends     iErebotEventMessageCapable
{}

/**
 * \brief
 *      Interface for an event which occurs in a PRIVATE query.
 */
interface   iErebotEventPrivate
extends     iErebotEvent
{}

?>
