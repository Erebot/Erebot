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
 * \see
 *      RFC 2812 (http://www.faqs.org/rfcs/rfc2812.html)
 */
interface   Erebot_Interface_RawProfile_RFC2812
extends     Erebot_Interface_RawProfile_RFC1459
{
    /**
     *  \brief
     *      First raw sent to a client after its connection (welcome message).
     *
     *  \format{"Welcome to the Internet Relay Network <nick>!<user>@<host>"}
     */
    const RPL_WELCOME               =   1;

    /**
     *  \brief
     *      Gives the name/version of the server we're connected to.
     *
     *  \format{"Your host is <servername>\, running version <ver>"}
     */
    const RPL_YOURHOST              =   2;
    const RPL_YOURHOSTIS            =   2;

    /**
     *  \brief
     *      Last time the IRC server was restarted.
     *
     *  \format{"This server was created <date>"}
     */
    const RPL_CREATED               =   3;
    const RPL_SERVERCREATED         =   3;

    /**
     *  \brief
     *      Supported user and channel modes.
     * 
     *  \format{
     *      "<servername> <version> <available user modes>
     *      <available channel modes>"
     *  }
     */
    const RPL_MYINFO                =   4;
    const RPL_SERVERVERSION         =   4;

    /**
     *  \brief
     *      Unused raw.
     */
    const RPL_TRACERECONNECT        = 210;

    /**
     *  \TODO
     */
    const RPL_STATSBLINE            = 220;

    const RPL_STATSVLINE            = 227;

    /**
     *  \brief
     *      RPL_TRACEEND is sent to indicate the end of the list
     *      of replies to a TRACE command.
     *
     *  \format{"<server name> <version & debug level> :End of TRACE"}
     */
    const RPL_TRACEEND              = 262;
    const RPL_ENDOFTRACE            = 262;

    /**
     *  \brief
     *      When a server drops a command without processing it,
     *      it MUST use the reply RPL_TRYAGAIN to inform the
     *      originating client.
     *
     *  \format{"<command> :Please wait a while and try again."}
     */
    const RPL_TRYAGAIN              = 263;

    /**
     *  \brief
     *      Mostly an alias for
     *      Erebot_Interface_Event_Raw_RFC2812::RPL_TRYAGAIN.
     *
     *  \format{
     *      "<command> :Server load is temporarily too heavy.
     *      Please wait a while and try again."
     *  }
     *
     *  \note
     *      This is mostly the same as
     *      Erebot_Interface_Event_Raw_RFC2812::RPL_TRYAGAIN
     *      except the text is worded slightly differently.
     *
     *  \note
     *      Although the name and the text imply that it is the server's load
     *      that causes this reply, in practice it seems to be sent whenever
     *      a user attempts to request too much information from a server.
     *      For example, if you attempt too many STATS requests in a short
     *      period of time, you will get this error. 
     */
    const RPL_LOAD2HI               = 263;

    /**
     *  \TODO
     */
    const RPL_STATSDLINE            = 275;

    /**
     *  \TODO
     */
    const RPL_UNIQOPIS              = 325;

    /**
     *  \TODO
     */
    const RPL_INVITELIST            = 346;
    ///  Alias for Erebot_Interface_Event_Raw_RFC2812::RPL_INVITELIST.
    const RPL_INVEXLIST             = 346;

    /**
     *  \TODO
     */
    const RPL_ENDOFINVITELIST       = 347;
    ///  Alias for Erebot_Interface_Event_Raw_RFC2812::RPL_ENDOFINVITELIST.
    const RPL_ENDOFINVEXLIST        = 347;

    /**
     *  \TODO
     */
    const RPL_EXCEPTLIST            = 348;
    ///  Alias for Erebot_Interface_Event_Raw_RFC2812::RPL_EXCEPTLIST.
    const RPL_EXLIST                = 348;
    ///  Alias for Erebot_Interface_Event_Raw_RFC2812::RPL_EXCEPTLIST.
    const RPL_EXEMPTLIST            = 348;

    /**
     *  \TODO
     */
    const RPL_ENDOFEXCEPTLIST       = 349;
    ///  Alias for Erebot_Interface_Event_Raw_RFC2812::RPL_ENDOFEXCEPTLIST.
    const RPL_ENDOFEXLIST           = 349;
    ///  Alias for Erebot_Interface_Event_Raw_RFC2812::RPL_ENDOFEXCEPTLIST.
    const RPL_ENDOFEXEMPTLIST       = 349;

    /**
     *  \brief
     *      Sent by the server to a service upon successful
     *      registration.
     *
     *  \format{"You are service <servicename>"}
     */
    const RPL_YOURESERVICE          = 383;

    /**
     *  \brief
     *      Returned to a client which is attempting to send a SQUERY
     *      to a service which does not exist.
     *
     *  \format{"<service name> :No such service"}
     */
    const ERR_NOSUCHSERVICE         = 408;

    /**
     *  \brief
     *      Returned when an invalid mask was passed to
     *      <tt>"PRIVMSG $<server>"</tt> or <tt>"PRIVMSG #<host>"</tt>.
     *
     *  \format{"<mask> :Bad Server/host mask"}
     */
    const ERR_BADMASK               = 415;

    /**
     *  \TODO
     *
     *  \note
     *      Although this type of raw message is cited in RFC 2812,
     *      there is no associated numeric code. The value here
     *      seems to be the one used by at least IRCnet.
     */
    const ERR_TOOMANYMATCHES        = 416;

    /**
     *  \brief
     *      Returned when a resource needed to perform the given
     *      action is unavailable.
     *
     *  \format{"<nick/channel> :Nick/channel is temporarily unavailable"}
     *
     *  This error is:
     *  -   Returned by a server to a user trying to join a channel
     *      currently blocked by the channel delay mechanism.
     *
     *  -   Returned by a server to a user trying to change nickname
     *      when the desired nickname is blocked by the nick delay
     *      mechanism.
     */
    const ERR_UNAVAILRESOURCE       = 437;

    /**
     *  \brief
     *      Returned when attempting to set modes on a channel
     *      which does not support modes.
     *
     *  \format{"<channel> :Channel doesn't support modes"}
     */
    const ERR_NOCHANMODES           = 477;

    /**
     *  \brief
     *      Returned when attempting to add a ban on a channel
     *      for which the banlist is already full.
     *
     *  \format{"<channel> <char> :Channel list is full"}
     */
    const ERR_BANLISTFULL           = 478;

    /**
     *  \brief
     *      Sent by the server to a user upon connection to indicate
     *      the restricted nature of the connection (user mode "+r")
     *
     *  \format{":Your connection is restricted!"}
     */
    const ERR_RESTRICTED            = 484;

    /**
     *  \brief
     *      Any MODE requiring "channel creator" privileges will
     *      return this error if the client making the attempt is not
     *      a channel operator on the specified channel.
     *
     *  \format{":You're not the original channel operator"}
     */
    const ERR_UNIQOPPRIVSNEEDED     = 485;

}
