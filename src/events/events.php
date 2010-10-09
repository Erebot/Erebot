<?php

ErebotUtils::incl('eventTargets.php');
ErebotUtils::incl('../textWrapper.php');
ErebotUtils::incl('interfaces.php');
ErebotUtils::incl('aclasses.php');

/**
 * \brief
 *      Triggered when the bot's usermodes are changed.
 */
class   ErebotEventUserMode
extends ErebotEventWithSourceTargetAndText
{}

/**
 * \brief
 *      Triggered when a channel's modes are changed.
 *
 * For common channel mode changes, you'll probably prefer
 * other more specific events, such as ErebotEventBan, etc.
 * The content of the mode change is passed unparsed.
 */
class   ErebotEventRawMode
extends ErebotEventWithChanSourceAndText
{}

/**
 * \brief
 *      Triggered when the topic of a channel the bot is on changes.
 */
class   ErebotEventTopic
extends ErebotEventWithChanSourceAndText
{}

/**
 * \brief
 *      Triggered when someone on a common channel with the bot quits IRC.
 */
class   ErebotEventQuit
extends ErebotEventWithSourceAndText
{}

/**
 * \brief
 *      Triggered when someone on the watch list signs on IRC.
 */
class   ErebotEventNotify
extends ErebotEventWatchNotification
{}

/**
 * \brief
 *      Triggered when someone on the watch list signs off IRC.
 */
class   ErebotEventUnNotify
extends ErebotEventWatchNotification
{}

/**
 * \brief
 *      Triggered when someone joins a channel the bot is already on.
 */
class   ErebotEventJoin
extends ErebotEventWithChanAndSource
{}

/**
 * \brief
 *      Triggered when someone leaves a channel the bot is on.
 */
class   ErebotEventPart
extends ErebotEventWithChanSourceAndText
{}

/**
 * \brief
 *      Triggered when the bot receives a PING message from a server.
 */
class   ErebotEventPing
extends ErebotEventWithText
{}

/**
 * \brief
 *      Triggered when the bot receives a PONG message from a server.
 */
class   ErebotEventPong
extends ErebotEventWithSourceAndText
{}

/**
 * \brief
 *      Triggered right after a connection is opened to an IRC server.
 *
 * This event can be used to send credentials, negociate options, etc.
 */
class   ErebotEventLogon
extends ErebotEvent
{}

/**
 * \brief
 *      Triggered when the bot is considered as being connected to a new server.
 *
 * Actually, the bot is considered as being connected to a new server
 * when a predefined message (raw #001) is received from that server.
 * Therefore, this event is only a convenient shortcut for the underlying
 * raw event.
 */
class   ErebotEventConnect
extends ErebotEvent
{}

/**
 * \brief
 *      Triggered when the connection to a server gets dropped.
 */
class   ErebotEventDisconnect
extends ErebotEvent
{}

/**
 * \brief
 *      Triggered when the bot receives an ERROR message from the server.
 *
 * This usually happen when the bot QUITs or is KILLed, etc.
 */
class   ErebotEventError
extends ErebotEventWithText
{}

/**
 * \brief
 *      Triggered when the bot receives a KILL message from the server.
 */
class   ErebotEventKill
extends ErebotEventWithSourceTargetAndText
{}

/**
 * \brief
 *      Triggered when someone asks the bot to shutdown (from the command-line).
 */
class   ErebotEventExit
extends ErebotEvent
{}

/**
 * \brief
 *      Triggered when someone gets invited on a channel.
 */
class   ErebotEventInvite
extends ErebotEventWithChanSourceAndTarget
{}

/**
 * \brief
 *      Triggered when someone changes their IRC nickname.
 */
class   ErebotEventNick
extends ErebotEventWithSourceAndTarget
{}

/**
 * \brief
 *      Triggered when someone gets kicked out a channel the bot is on.
 */
class   ErebotEventKick
extends ErebotEventWithChanSourceTargetAndText
{}

/**
 * \brief
 *      Triggered when a private message is received.
 */
class       ErebotEventTextPrivate
extends     ErebotEventWithSourceAndText
implements  iErebotEventMessageText,
            iErebotEventPrivate
{}

/**
 * \brief
 *      Triggered when a message is received on a channel the bot is on.
 */
class       ErebotEventTextChan
extends     ErebotEventWithChanSourceAndText
implements  iErebotEventMessageText
{}

/**
 * \brief
 *      Triggered when an action is received in private.
 */
class       ErebotEventActionPrivate
extends     ErebotEventWithSourceAndText
implements  iErebotEventMessageAction,
            iErebotEventPrivate
{}

/**
 * \brief
 *      Triggered when an action is received on a channel the bot is on.
 */
class       ErebotEventActionChan
extends     ErebotEventWithChanSourceAndText
implements  iErebotEventMessageAction
{}

/**
 * \brief
 *      Triggered when a private notice is received.
 */
class       ErebotEventNoticePrivate
extends     ErebotEventWithSourceAndText
implements  iErebotEventMessageNotice,
            iErebotEventPrivate
{}

/**
 * \brief
 *      Triggered when a notice is received on a channel the bot is on.
 */
class       ErebotEventNoticeChan
extends     ErebotEventWithChanSourceAndText
implements  iErebotEventMessageNotice
{}

/**
 * \brief
 *      Triggered when a CTCP request is received in private.
 */
class       ErebotEventCtcpPrivate
extends     ErebotEventWithSourceAndCtcp
implements  iErebotEventMessageCtcp,
            iErebotEventPrivate
{}

/**
 * \brief
 *      Triggered when a CTCP request is received on a channel the bot is on.
 */
class       ErebotEventCtcpChan
extends     ErebotEventWithChanSourceAndCtcp
implements  iErebotEventMessageCtcp
{}

/**
 * \brief
 *      Triggered when a CTCP reply is received in private.
 */
class       ErebotEventCtcpReplyPrivate
extends     ErebotEventWithSourceAndCtcp
implements  iErebotEventMessageCtcpReply,
            iErebotEventPrivate
{}

/**
 * \brief
 *      Triggered when a CTCP reply is received on a channel the bot is on.
 */
class       ErebotEventCtcpReplyChan
extends     ErebotEventWithChanSourceAndCtcp
implements  iErebotEventMessageCtcpReply
{}

# Ban (+b)
/**
 * \brief
 *      Triggered when a ban is set on a channel.
 */
class       ErebotEventBan
extends     ErebotEventChanUserModeMaskBase
implements  iErebotEventChanModeGive,
            iErebotEventChanModeBan
{}

/**
 * \brief
 *      Triggered when a ban is removed from a channel.
 */
class       ErebotEventUnban
extends     ErebotEventChanUserModeMaskBase
implements  iErebotEventChanModeTake,
            iErebotEventChanModeBan
{}

# Except (+e)
/**
 * \brief
 *      Triggered when a ban exception is set on a channel.
 */
class       ErebotEventExcept
extends     ErebotEventChanUserModeMaskBase
implements  iErebotEventChanModeGive,
            iErebotEventChanModeExcept
{}

/**
 * \brief
 *      Triggered when a ban exception is removed from a channel.
 */
class       ErebotEventUnexcept
extends     ErebotEventChanUserModeMaskBase
implements  iErebotEventChanModeTake,
            iErebotEventChanModeExcept
{}

# Op (+o)
/**
 * \brief
 *      Triggered when someone receives OPerator priviledges on a channel.
 */
class       ErebotEventOp
extends     ErebotEventChanUserModeBase
implements  iErebotEventChanModeGive,
            iErebotEventChanModeOp
{}

/**
 * \brief
 *      Triggered when someone loses OPerator priviledges on a channel.
 */
class       ErebotEventDeOp
extends     ErebotEventChanUserModeBase
implements  iErebotEventChanModeTake,
            iErebotEventChanModeOp
{}

# Halfop (+h)
/**
 * \brief
 *      Triggered when someone receives HALF-OPerator priviledges on a channel.
 */
class       ErebotEventHalfop
extends     ErebotEventChanUserModeBase
implements  iErebotEventChanModeGive,
            iErebotEventChanModeHalfop
{}

/**
 * \brief
 *      Triggered when someone loses HALF-OPerator priviledges on a channel.
 */
class       ErebotEventDeHalfop
extends     ErebotEventChanUserModeBase
implements  iErebotEventChanModeTake,
            iErebotEventChanModeHalfop
{}

# Voice (+v)
/**
 * \brief
 *      Triggered when someone receives VOICE priviledges on a channel.
 */
class       ErebotEventVoice
extends     ErebotEventChanUserModeBase
implements  iErebotEventChanModeGive,
            iErebotEventChanModeVoice
{}

/**
 * \brief
 *      Triggered when someone loses VOICE priviledges on a channel.
 */
class       ErebotEventDeVoice
extends     ErebotEventChanUserModeBase
implements  iErebotEventChanModeTake,
            iErebotEventChanModeVoice
{}

# Protect (+a)
/**
 * \brief
 *      Triggered when someone receives PROTECTion priviledges on a channel.
 */
class       ErebotEventProtect
extends     ErebotEventChanUserModeBase
implements  iErebotEventChanModeGive,
            iErebotEventChanModeProtect
{}

/**
 * \brief
 *      Triggered when someone loses PROTECTion priviledges on a channel.
 */
class       ErebotEventDeProtect
extends     ErebotEventChanUserModeBase
implements  iErebotEventChanModeTake,
            iErebotEventChanModeProtect
{}

# Owner (+q)
/**
 * \brief
 *      Triggered when someone receives OWNER priviledges on a channel.
 */
class       ErebotEventOwner
extends     ErebotEventChanUserModeBase
implements  iErebotEventChanModeGive,
            iErebotEventChanModeOwner
{}

/**
 * \brief
 *      Triggered when someone loses OWNER priviledges on a channel.
 */
class       ErebotEventDeOwner
extends     ErebotEventChanUserModeBase
implements  iErebotEventChanModeTake,
            iErebotEventChanModeOwner
{}

# Other chan modes
/**
 * \brief
 *      Triggered when someone sets a mode on a channel.
 */
class       ErebotEventModeGive
extends     ErebotEventWithChanSourceAndText
implements  iErebotEventChanModeGive
{}

/**
 * \brief
 *      Triggered when someone removes a mode from a channel.
 */
class       ErebotEventModeTake
extends     ErebotEventWithChanSourceAndText
implements  iErebotEventChanModeTake
{}

?>
