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
 *      Interface to represent a raw numeric message.
 *
 * This interface provides the necessary methods
 * to represent a raw numeric message from an IRC server.
 *
 * \see
 *      RFC 1459 (http://www.faqs.org/rfcs/rfc1459.html)
 * \see
 *      RFC 2812 (http://www.faqs.org/rfcs/rfc2812.html)
 * \see
 *      InspIRCd's wiki on the STARTTLS feature
 *      (http://wiki.inspircd.org/STARTTLS_Documentation)
 */
interface   Erebot_Interface_Event_Raw
extends     Erebot_Interface_Event_Base_Generic,
            Erebot_Interface_Event_Base_Source,
            Erebot_Interface_Event_Base_Target,
            Erebot_Interface_Event_Base_Text
{
    //   List of supported raw (numeric) messages.

    /**
     *  \brief
     *      First raw sent to a client after its connection (welcome message).
     *
     *  \par Format
     *  <tt>
     *      "Welcome to the Internet Relay Network <nick>!<user>@<host>"
     *  </tt>
     */
    const RPL_WELCOME               =   1;

    /**
     *  \brief
     *      Gives the name/version of the server we're connected to.
     *
     *  \par Format
     *  <tt>
     *      "Your host is <servername>, running version <ver>"
     *  </tt>
     */
    const RPL_YOURHOST              =   2;

    /**
     *  \brief
     *      Last time the IRC server was restarted.
     *
     *  \par Format
     *  <tt>
     *      "This server was created <date>"
     *  </tt>
     */
    const RPL_CREATED               =   3;

    /**
     *  \brief
     *      Supported user and channel modes.
     * 
     *  \par Format
     *  <tt>
     *      "<servername> <version> <available user modes>
     *      <available channel modes>"
     *  </tt>
     */
    const RPL_MYINFO                =   4;

    /// Specific commands/options supported by the server.
    const RPL_ISUPPORT              =   5;

    /// Active PROTOcol ConTroL flags (obsolete).
    const RPL_PROTOCTL              =   5;

    /**
     *  \brief
     *      Obsolete raw used to redirect users to another server.
     *
     *  \par Format
     *  <tt>
     *      "Try server <server name>, port <port number>"
     *  </tt>
     */
    const RPL_BOUNCE                =   5;

    /**
     *  \TODO
     */
    const RPL_MAP                   =   6;

    /// Alias for Erebot_Interface_Event_Raw::RPL_MAP.
    const RPL_MAPMORE               =   6;

    /**
     *  \TODO
     */
    const RPL_MAPEND                =   7;

    /**
     *  \TODO
     */
    const RPL_SNOMASK               =   8;

    /**
     *  \TODO
     */
    const RPL_STATMEM               =  10;

    /**
     *  \TODO
     */
    const RPL_STATMEMTOT            =  10;

    /**
     *  \TODO
     */
    const RPL_YOURCOOKIE            =  14;

    /**
     *  \TODO
     */
    const RPL_YOURID                =  42;

    /**
     *  \TODO
     */
    const RPL_SAVENICK              =  43;

    /**
     *  \TODO
     */
    const RPL_ATTEMPTINGJUNC        =  50;

    /**
     *  \TODO
     */
    const RPL_ATTEMPTINGREROUTE     =  51;

    /**
     *  \TODO
     */
    const RPL_TRACELINK             = 200;

    /**
     *  \TODO
     */
    const RPL_TRACECONNECTING       = 201;

    /**
     *  \TODO
     */
    const RPL_TRACEHANDSHAKE        = 202;

    /**
     *  \TODO
     */
    const RPL_TRACEUNKNOWN          = 203;

    /**
     *  \TODO
     */
    const RPL_TRACEOPERATOR         = 204;

    /**
     *  \TODO
     */
    const RPL_TRACEUSER             = 205;

    /**
     *  \TODO
     */
    const RPL_TRACESERVER           = 206;

    /**
     *  \TODO
     */
    const RPL_TRACESERVICE          = 207;

    /**
     *  \TODO
     */
    const RPL_TRACENEWTYPE          = 208;

    /**
     *  \TODO
     */
    const RPL_TRACECLASS            = 209;

    /**
     *  \TODO
     */
    const RPL_TRACECONNECT          = 210;

    /**
     *  \brief
     *      Reports statistics on a connection.
     *
     *  \par Format
     *  <tt>
     *      "\<linkname\> \<sendq\> \<sent messages\>
     *      \<sent Kbytes\> \<received messages\>
     *      \<received Kbytes\> \<time open\>"
     *  </tt>
     *
     *  \<linkname\> identifies the particular connection,
     *  \<sendq\> is the amount of data that is queued and
     *  waiting to be sent \<sent messages\> the number of
     *  messages sent, and \<sent Kbytes\> the amount of
     *  data sent, in Kbytes.
     *  \<received messages\> and \<received Kbytes\> are the
     *  equivalent of \<sent messages\> and \<sent Kbytes\>
     *  for received data, respectively.
     *  \<time open\> indicates how long ago the connection
     *  was opened, in seconds.
     */
    const RPL_STATSLINKINFO         = 211;

    /**
     *  \brief
     *      Reports statistics on commands usage.
     *
     *  \par Format
     *  <tt>
     *      "<command> <count> <byte count> <remote count>"
     *  </tt>
     */
    const RPL_STATSCOMMANDS         = 212;

    /**
     *  \TODO
     */
    const RPL_STATSCLINE            = 213;

    /**
     *  \TODO
     */
    const RPL_STATSNLINE            = 214;

    /**
     *  \TODO
     */
    const RPL_STATSOLDNLINE         = 214;

    /**
     *  \TODO
     */
    const RPL_STATSILINE            = 215;

    /**
     *  \TODO
     */
    const RPL_STATSKLINE            = 216;

    /**
     *  \TODO
     */
    const RPL_STATSPLINE            = 217;

    /**
     *  \TODO
     */
    const RPL_STATSQLINE            = 217;

    /**
     *  \TODO
     */
    const RPL_STATSYLINE            = 218;

    /**
     *  \TODO
     */
    const RPL_ENDOFSTATS            = 219;

    /**
     *  \TODO
     */
    const RPL_STATSBLINE            = 220;

    /**
     *  \brief
     *      To answer a query about a client's own mode,
     *      RPL_UMODEIS is sent back.
     *
     *  \par Format
     *  <tt>
     *      "<user mode string>"
     *  </tt>
     */
    const RPL_UMODEIS               = 221;

    /**
     *  \TODO
     */
    const RPL_SQLINE_NICK           = 222;

    /**
     *  \TODO
     */
    const RPL_STATS_E               = 223;

    /**
     *  \TODO
     */
    const RPL_STATS_D               = 224;

    /**
     *  \TODO
     */
    const RPL_STATSCLONE            = 225;

    /**
     *  \TODO
     */
    const RPL_STATSCOUNT            = 226;

    /**
     *  \TODO
     */
    const RPL_STATSGLINE            = 227;

    /**
     *  \TODO
     */
    const RPL_SERVICEINFO           = 231;

    /**
     *  \TODO
     */
    const RPL_ENDOFSERVICES         = 232;

    /**
     *  \TODO
     */
    const RPL_SERVICE               = 233;

    /**
     *  \TODO
     */
    const RPL_SERVLIST              = 234;

    /**
     *  \TODO
     */
    const RPL_SERVLISTEND           = 235;

    /**
     *  \TODO
     */
    const RPL_STATSLLINE            = 241;

    /**
     *  \brief
     *      Reports the server uptime.
     *
     *  \par Format
     *  <tt>
     *      ":Server Up %d days %d:%02d:%02d"
     *  </tt>
     */
    const RPL_STATSUPTIME           = 242;

    /**
     *  \brief
     *      Reports the allowed hosts from where
     *      users may become IRC operators.
     *
     *  \par Format
     *  <tt>
     *      "O <hostmask> * <name>"
     *  </tt>
     */
    const RPL_STATSOLINE            = 243;

    /**
     *  \TODO
     */
    const RPL_STATSHLINE            = 244;

    /**
     *  \TODO
     */
    const RPL_STATSSLINE            = 245;

    /**
     *  \TODO
     */
    const RPL_STATSXLINE            = 246;

    /**
     *  \TODO
     */
    const RPL_STATSULINE            = 248;

    /**
     *  \TODO
     */
    const RPL_STATSDEBUG            = 249;

    /**
     *  \TODO
     */
    const RPL_STATSCONN             = 250;

    /**
     *  \TODO
     */
    const RPL_LUSERCLIENT           = 251;

    /**
     *  \TODO
     */
    const RPL_LUSEROP               = 252;

    /**
     *  \TODO
     */
    const RPL_LUSERUNKNOWN          = 253;

    /**
     *  \TODO
     */
    const RPL_LUSERCHANNELS         = 254;

    /**
     *  \TODO
     */
    const RPL_LUSERME               = 255;

    /**
     *  \TODO
     */
    const RPL_ADMINME               = 256;

    /**
     *  \TODO
     */
    const RPL_ADMINLOC1             = 257;

    /**
     *  \TODO
     */
    const RPL_ADMINLOC2             = 258;

    /**
     *  \TODO
     */
    const RPL_ADMINEMAIL            = 259;

    /**
     *  \TODO
     */
    const RPL_TRACELOG              = 261;

    /**
     *  \TODO
     */
    const RPL_TRACEPING             = 262;

    /**
     *  \brief
     *      When a server drops a command without processing it,
     *      it MUST use the reply RPL_TRYAGAIN to inform the
     *      originating client.
     *
     *  \par Format
     *  <tt>
     *      "<command> :Please wait a while and try again."
     *  </tt>
     */
    const RPL_TRYAGAIN              = 263;

    /**
     *  \brief
     *      Mostly an alias for Erebot_Interface_Event_Raw::RPL_TRYAGAIN.
     *
     *  \par Format
     *  <tt>
     *      "<command> :Server load is temporarily too heavy.
     *      Please wait a while and try again."
     *  </tt>
     *
     *  \note
     *      This is mostly the same as Erebot_Interface_Event_Raw::RPL_TRYAGAIN
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
    const RPL_LOCALUSERS            = 265;

    /**
     *  \TODO
     */
    const RPL_LUSERSC               = 265;

    /**
     *  \TODO
     */
    const RPL_GLOBALUSERS           = 266;

    /**
     *  \TODO
     */
    const RPL_LUSERSG               = 266;

    /**
     *  \TODO
     */
    const RPL_SILELIST              = 271;

    /**
     *  \TODO
     */
    const RPL_ENDOFSILELIST         = 272;

    /**
     *  \TODO
     */
    const RPL_STATSDLINE            = 275;

    /**
     *  \TODO
     */
    const RPL_GLIST                 = 280;

    /**
     *  \TODO
     */
    const RPL_ENDOFGLIST            = 281;

    /**
     *  \TODO
     */
    const RPL_HELPHDR               = 290;

    /**
     *  \TODO
     */
    const RPL_HELPOP                = 291;

    /**
     *  \TODO
     */
    const RPL_HELPTLR               = 292;

    /**
     *  \TODO
     */
    const RPL_HELPHLP               = 293;

    /**
     *  \TODO
     */
    const RPL_HELPFWD               = 294;

    /**
     *  \TODO
     */
    const RPL_HELPIGN               = 295;

    /**
     *  \TODO
     */
    const RPL_NEWNICK               = 298;

    /// Dummy reply number. Not used.
    const RPL_NONE                  = 300;

    /**
     *  \brief
     *      RPL_AWAY is sent to any client sending a
     *      PRIVMSG to a client which is away.
     *
     *  \par Format
     *  <tt>
     *      "<nick> :<away message>"
     *  </tt>
     *
     * \note
     *      RPL_AWAY is only sent by the server
     *      to which the client is connected.
     */
    const RPL_AWAY                  = 301;

    /**
     *  \brief
     *      Reply format used by USERHOST to list replies to the query list.
     *
     *  \par Format
     *  <tt>
     *      ":*1<reply> *( " " <reply> )"
     *  </tt>
     *
     * The reply string is composed as follows:
     *
     * <tt>reply = nickname [ "*" ] "=" ( "+" / "-" ) hostname</tt>
     *
     * The '*' indicates whether the client has registered
     * as an Operator.  The '-' or '+' characters represent
     * whether the client has set an AWAY message or not
     * respectively.
     */
    const RPL_USERHOST              = 302;

    /**
     *  \brief
     *      Reply format used by ISON to list replies to the query list.
     *
     *  \par Format
     *  <tt>
     *      ":*1<nick> *( " " <nick> )"
     *  </tt>
     */
    const RPL_ISON                  = 303;

    /**
     *  \brief
     *      Sent went the client removes an AWAY message.
     *
     *  \par Format
     *  <tt>
     *      ":You are no longer marked as being away"
     *  </tt>
     */
    const RPL_UNAWAY                = 305;

    /**
     *  \brief
     *      Sent when the client sets an AWAY message.
     *
     *  \par Format
     *  <tt>
     *      ":You have been marked as being away"
     *  </tt>
     */
    const RPL_NOWAWAY               = 306;

    /**
     *  \TODO
     */
    const RPL_USERIP                = 307;

    /**
     *  \TODO
     */
    const RPL_WHOISREGNICK          = 307;

    /**
     *  \TODO
     */
    const RPL_WHOISADMIN            = 308;

    /**
     *  \TODO
     */
    const RPL_WHOISSADMIN           = 309;

    /**
     *  \TODO
     */
    const RPL_WHOISSVCMSG           = 310;

    /**
     *  \TODO
     */
    const RPL_WHOISHELPOP           = 310;

    /**
     *  \brief
     *      Sent in response to a WHOIS, giving
     *      a few information on the target user.
     *
     *  \par Format
     *  <tt>
     *      "<nick> <user> <host> * :<real name>"
     *  </tt>
     *
     *  \note
     *      The '*' in RPL_WHOISUSER is there as the
     *      literal character and not as a wild card.
     */
    const RPL_WHOISUSER             = 311;

    /**
     *  \TODO
     */
    const RPL_WHOISSERVER           = 312;

    /**
     *  \brief
     *      Sent in response to a WHOIS, indicating
     *      that the target user is an IRC operator.
     *
     *  \par Format
     *  <tt>
     *      "<nick> :is an IRC operator"
     *  </tt>
     */
    const RPL_WHOISOPERATOR         = 313;

    /**
     *  \brief
     *      Sent in response to a WHOWAS, giving
     *      information on the target user.
     *
     *  \par Format
     *  <tt>
     *      "<nick> <user> <host> * :<real name>"
     *  </tt>
     */
    const RPL_WHOWASUSER            = 314;

    /**
     *  \TODO
     */
    const RPL_ENDOFWHO              = 315;

    /**
     *  \TODO
     */
    const RPL_WHOISCHANOP           = 316;

    /**
     *  \brief
     *      Sent in response to a WHOIS, indicating
     *      how much time the target user has spent idle.
     *
     *  \par Format
     *  <tt>
     *      "<nick> <integer> :seconds idle"
     *  </tt>
     */
    const RPL_WHOISIDLE             = 317;

    /**
     *  \brief
     *      The RPL_ENDOFWHOIS reply is used to mark
     *      the end of processing a WHOIS message.
     *
     *  \par Format
     *  <tt>
     *      "<nick> :End of WHOIS list"
     *  </tt>
     */
    const RPL_ENDOFWHOIS            = 318;

    /**
     *  \brief
     *      Sent in response to a WHOIS, listing
     *      the public channels the target user is on.
     *
     *  \par Format
     *  <tt>
     *      "<nick> :*( ( "@" / "+" ) <channel> " " )"
     *  </tt>
     *
     *  \note
     *      For each reply set, RPL_WHOISCHANNELS may appear
     *      more than once (for long lists of channel names).
     *
     *  \note
     *      The '@' and '+' characters next to the channel name
     *      indicate whether a client is a channel operator or
     *      has been granted permission to speak on a moderated
     *      channel.
     */
    const RPL_WHOISCHANNELS         = 319;

    /**
     *  \brief
     *      Obsolete raw used to mark the beginning
     *      of a reply to a LIST command.
     */
    const RPL_LISTSTART             = 321;

    /**
     *  \brief
     *      Sent in response to a LIST command,
     *      contains the actual response data.
     *
     *  \par Format
     *  <tt>
     *      "<channel> <# visible> :<topic>"
     *  </tt>
     */
    const RPL_LIST                  = 322;

    /**
     *  \brief
     *      Sent in response to a LIST command,
     *      marks the end of the server's response.
     *
     *  \par Format
     *  <tt>
     *      ":End of LIST"
     *  </tt>
     *
     *  \note
     *      If there are no channels available to return,
     *      only Erebot_Interface_Event_Raw::RPL_LISTEND
     *      will be sent.
     */
    const RPL_LISTEND               = 323;

    /**
     *  \TODO
     */
    const RPL_CHANNELMODEIS         = 324;

    /**
     *  \TODO
     */
    const RPL_UNIQOPIS              = 325;

    /**
     *  \TODO
     */
    const RPL_CREATIONTIME          = 329;

    /**
     *  \brief
     *      Sent when joining a channel or issuing
     *      a TOPIC command and not topic has been
     *      set yet.
     *
     *  \par Format
     *  <tt>
     *      "<channel> :No topic is set"
     *  </tt>
     */
    const RPL_NOTOPIC               = 331;

    /**
     *  \brief
     *      Sent when joining a channel or issuing
     *      a TOPIC command; contains the current
     *      topic.
     *
     *  \par Format
     *  <tt>
     *      "<channel> :<topic>"
     *  </tt>
     */
    const RPL_TOPIC                 = 332;

    /**
     *  \TODO
     */
    const RPL_TOPICWHOTIME          = 333;

    /**
     *  \TODO
     */
    const RPL_COMMANDSYNTAX         = 334;

    /**
     *  \TODO
     */
    const RPL_WHOISTEXT             = 337;

    /**
     *  \TODO
     */
    const RPL_WHOISACTUALLY         = 338;

    /**
     *  \brief
     *      Returned by the server to indicate that the
     *      attempted INVITE message was successful and is
     *      being passed onto the end client.
     *
     *  \par Format
     *  <tt>
     *      "<channel> <nick>"
     *  </tt>
     */
    const RPL_INVITING              = 341;

    /**
     *  \brief
     *      Returned by a server answering a SUMMON message to
     *      indicate that it is summoning that user.
     *
     *  \par Format
     *  <tt>
     *      "<user> :Summoning user to IRC"
     *  </tt>
     */
    const RPL_SUMMONING             = 342;

    /**
     *  \TODO
     */
    const RPL_INVITELIST            = 346;

    /**
     *  \TODO
     */
    const RPL_ENDOFINVITELIST       = 347;

    /**
     *  \TODO
     */
    const RPL_EXCEPTLIST            = 348;

    ///  Alias for Erebot_Interface_Event_Raw::RPL_EXCEPTLIST.
    const RPL_EXEMPTLIST            = 348;

    /**
     *  \TODO
     */
    const RPL_ENDOFEXCEPTLIST       = 349;

    ///  Alias for Erebot_Interface_Event_Raw::RPL_ENDOFEXCEPTLIST.
    const RPL_ENDOFEXEMPTLIST       = 349;

    /**
     *  \brief
     *      Reply by the server showing its version details.
     *
     *  \par Format
     *  <tt>
     *      "<version>.<debuglevel> <server> :<comments>"
     *  </tt>
     *
     *  The <version> is the version of the software being
     *  used (including any patchlevel revisions) and the
     *  <debuglevel> is used to indicate if the server is
     *  running in "debug mode".
     *
     *  The "comments" field may contain any comments about
     *  the version or further version details.
     */
    const RPL_VERSION               = 351;

    /**
     *  \TODO
     */
    const RPL_WHOREPLY              = 352;

    /**
     *  \TODO
     */
    const RPL_NAMEREPLY             = 353;

    /**
     * Alias for Erebot_Interface_Event_Raw::RPL_NAMEREPLY,
     * which is often mispelled in documentation.
     */
    const RPL_NAMREPLY              = 353;

    /**
     *  \TODO
     */
    const RPL_RWHOREPLY             = 354;

    /**
     *  \TODO
     */
    const RPL_CLOSING               = 362;

    /**
     *  \TODO
     */
    const RPL_CLOSEEND              = 363;

    /**
     *  \TODO
     */
    const RPL_LINKS                 = 364;

    /**
     *  \TODO
     */
    const RPL_ENDOFLINKS            = 365;

    /**
     *  \TODO
     */
    const RPL_ENDOFNAMES            = 366;

    /**
     *  \TODO
     */
    const RPL_BANLIST               = 367;

    /**
     *  \TODO
     */
    const RPL_ENDOFBANLIST          = 368;

    /**
     *  \brief
     *      Sent in response to a WHOWAS, marks
     *      the end of the WHOWAS message processing.
     *
     *  \par Format
     *  <tt>
     *      "<nick> :End of WHOWAS"
     *  </tt>
     */
    const RPL_ENDOFWHOWAS           = 369;

    /**
     *  \TODO
     */
    const RPL_INFO                  = 371;

    /**
     *  \TODO
     */
    const RPL_MOTD                  = 372;

    /**
     *  \TODO
     */
    const RPL_ENDOFINFO             = 374;

    /**
     *  \TODO
     */
    const RPL_MOTDSTART             = 375;

    /**
     *  \TODO
     */
    const RPL_ENDOFMOTD             = 376;

    /**
     *  \TODO
     */
    const RPL_ISASERVICE            = 377;

    /**
     *  \TODO
     */
    const RPL_WHOISHOST             = 378;

    /**
     *  \brief
     *      RPL_YOUREOPER is sent back to a client which has
     *      just successfully issued an OPER message and gained
     *      operator status.
     *
     *  \par Format
     *  <tt>
     *      ":You are now an IRC operator"
     *  </tt>
     */
    const RPL_YOUREOPER             = 381;

    /**
     *  \brief
     *      If the REHASH option is used and an operator sends
     *      a REHASH message, an RPL_REHASHING is sent back to
     *      the operator.
     *
     *  \par Format
     *  <tt>
     *      "<config file> :Rehashing"
     *  </tt>
     */
    const RPL_REHASHING             = 382;

    /**
     *  \brief
     *      Sent by the server to a service upon successful
     *      registration.
     *
     *  \par Format
     *  <tt>
     *      "You are service <servicename>"
     *  </tt>
     */
    const RPL_YOURESERVICE          = 383;

    /**
     *  \TODO
     */
    const RPL_MYPORTIS              = 384;

    /**
     *  \brief
     *      When replying to the TIME message, a server MUST send
     *      the reply using the RPL_TIME format below.
     *
     *  \par Format
     *  <tt>
     *      "<server> :<string showing server's local time>"
     *  </tt>
     *
     *  \note
     *      The string showing the time need only contain the correct
     *      time there. There is no further requirement for the day
     *      and time string.
     */
    const RPL_TIME                  = 391;

    /**
     *  \TODO
     */
    const RPL_USERSSTART            = 392;

    /**
     *  \TODO
     */
    const RPL_USERS                 = 393;

    /**
     *  \TODO
     */
    const RPL_ENDOFUSERS            = 394;

    /**
     *  \TODO
     */
    const RPL_NOUSERS               = 395;

    /**
     *  \brief
     *      Used to indicate the nickname parameter
     *      supplied to a command is currently unused.
     *
     *  \par Format
     *  <tt>
     *      "<nickname> :No such nick/channel"
     *  </tt>
     */
    const ERR_NOSUCHNICK            = 401;

    /**
     *  \brief
     *      Used to indicate the server name given
     *      currently doesn't exist.
     *
     *  \par Format
     *  <tt>
     *      "<server name> :No such server"
     *  </tt>
     */
    const ERR_NOSUCHSERVER          = 402;

    /**
     *  \brief
     *      Used to indicate the given channel name
     *      is invalid.
     *
     *  \par Format
     *  <tt>
     *      "<channel name> :No such channel"
     *  </tt>
     */
    const ERR_NOSUCHCHANNEL         = 403;

    /**
     *  \brief
     *      Sent by the server when attempting to send
     *      a PRIVMSG on a channel when you're not allowed
     *      to do so.
     *
     *  \par Format
     *  <tt>
     *      "<channel name> :Cannot send to channel"
     *  </tt>
     *
     *  Sent to a user who is either (a) not on a channel
     *  which is mode +n or (b) not a chanop (or mode +v) on
     *  a channel which has mode +m set or where the user is
     *  banned and is trying to send a PRIVMSG message to
     *  that channel.
     */
    const ERR_CANNOTSENDTOCHAN      = 404;

    /**
     *  \brief
     *      Sent to a user when they have joined the maximum
     *      number of allowed channels and they try to join
     *      another channel.
     *
     *  \par Format
     *  <tt>
     *      "<channel name> :You have joined too many channels"
     *  </tt>
     */
    const ERR_TOOMANYCHANNELS       = 405;

    /**
     *  \brief
     *      Returned by WHOWAS to indicate there is no history
     *      information for that nickname.
     *
     *  \par Format
     *  <tt>
     *      "<nickname> :There was no such nickname"
     *  </tt>
     */
    const ERR_WASNOSUCHNICK         = 406;

    /**
     *  \brief
     *      Used when several targets match the given parameters
     *      for a command.
     *
     *  \par Format
     *  <tt>
     *      "<target> :<error code> recipients. <abort message>"
     *  </tt>
     *
     *  There are several occasions when this raw may be used:
     *  -   Returned to a client which is attempting to send a
     *      PRIVMSG/NOTICE using the user@host destination format
     *      and for a user@host which has several occurrences.
     *
     *  -   Returned to a client which trying to send a
     *      PRIVMSG/NOTICE to too many recipients.
     *
     *  -   Returned to a client which is attempting to JOIN a safe
     *      channel using the shortname when there are more than one
     *      such channel.
     *
     *  \note
     *      RFC 1459 defines a slightly different (less meaningful) message:
     *      <tt>"<target> :Duplicate recipients. No message delivered"</tt>
     */
    const ERR_TOOMANYTARGETS        = 407;

    /**
     *  \brief
     *      Returned to a client which is attempting to send a SQUERY
     *      to a service which does not exist.
     *
     *  \par Format
     *  <tt>
     *      "<service name> :No such service"
     *  </tt>
     */
    const ERR_NOSUCHSERVICE         = 408;

    /**
     *  \TODO
     */
    const ERR_NOCTRLSONCHAN         = 408;

    /**
     *  \brief
     *      PING or PONG message missing the originator parameter.
     *
     *  \par Format
     *  <tt>
     *      ":No origin specified"
     *  </tt>
     */
    const ERR_NOORIGIN              = 409;

    /**
     *  \brief
     *      Used to indicate a recipient was expected
     *      for the given command.
     *
     *  \par Format
     *  <tt>
     *      ":No recipient given (<command>)"
     *  </tt>
     */
    const ERR_NORECIPIENT           = 411;

    /**
     *  \brief
     *      Sent when a command did not receive any text when it was
     *      expecting some.
     *
     *  \par Format
     *  <tt>
     *      ":No text to send"
     *  </tt>
     */
    const ERR_NOTEXTTOSEND          = 412;

    /**
     *  \brief
     *      Returned when an invalid use of "PRIVMSG $<server>"
     *      or "PRIVMSG #<host>" is attempted (when it doesn't
     *      contain top-level domain).
     *
     *  \par Format
     *  <tt>
     *      "<mask> :No toplevel domain specified"
     *  </tt>
     */
    const ERR_NOTOPLEVEL            = 413;

    /**
     *  \brief
     *      Returned when an invalid use of "PRIVMSG $<server>"
     *      or "PRIVMSG #<host>" is attempted (when the top-level
     *      domain contains wildcard characters).
     *
     *  \par Format
     *  <tt>
     *      "<mask> :Wildcard in toplevel domain"
     *  </tt>
     */
    const ERR_WILDTOPLEVEL          = 414;

    /**
     *  \brief
     *      Returned when an invalid mask was passed to
     *      "PRIVMSG $<server>" or "PRIVMSG #<host>".
     *
     *  \par Format
     *  <tt>
     *      "<mask> :Bad Server/host mask"
     *  </tt>
     */
    const ERR_BADMASK               = 415;

    /**
     *  \TODO
     */
    const ERR_QUERYTOOLONG          = 416;

    /**
     *  \brief
     *      Returned to a registered client to indicate that the
     *      command sent is unknown by the server.
     *
     *  \par Format
     *  <tt>
     *      "<command> :Unknown command"
     *  </tt>
     */
    const ERR_UNKNOWNCOMMAND        = 421;

    /**
     *  \brief
     *      Server's MOTD file could not be opened by the server.
     *
     *  \par Format
     *  <tt>
     *      ":MOTD File is missing"
     *  </tt>
     */
    const ERR_NOMOTD                = 422;

    /**
     *  \brief
     *      Returned by a server in response to an ADMIN message
     *      when there is an error in finding the appropriate
     *      information.
     *
     *  \par Format
     *  <tt>
     *      "<server> :No administrative info available"
     *  </tt>
     */
    const ERR_NOADMININFO           = 423;

    /**
     *  \brief
     *      Generic error message used to report a failed file
     *      operation during the processing of a message.
     *
     *  \par Format
     *  <tt>
     *      ":File error doing <file op> on <file>"
     *  </tt>
     */
    const ERR_FILEERROR             = 424;

    /**
     *  \TODO
     */
    const ERR_TOOMANYAWAY           = 429;

    /**
     *  \brief
     *      Returned when a nickname parameter is expected
     *      for a command and isn't found.
     *
     *  \par Format
     *  <tt>
     *      ":No nickname given"
     *  </tt>
     */
    const ERR_NONICKNAMEGIVEN       = 431;

    /**
     *  \brief
     *      Returned after receiving a NICK message which contains
     *      characters which do not fall in the defined set.
     *
     *  \par Format
     *  <tt>
     *      "<nick> :Erroneous nickname"
     *  </tt>
     */
    const ERR_ERRONEUSNICKNAME      = 432;

    /**
     *  \brief
     *      Returned when a NICK message is processed that results
     *      in an attempt to change to a currently existing
     *      nickname.
     *
     *  \par Format
     *  <tt>
     *      "<nick> :Nickname is already in use"
     *  </tt>
     */
    const ERR_NICKNAMEINUSE         = 433;

    /**
     *  \TODO
     */
    const ERR_BANONCHAN             = 435;

    /**
     *  \brief
     *      Returned by a server to a client when it detects
     *      a nickname collision (registered of a NICK that
     *      already exists by another server).
     *
     *  \par Format
     *  <tt>
     *      "<nick> :Nickname collision KILL from <user>@<host>"
     *  </tt>
     */
    const ERR_NICKCOLLISION         = 436;

    /**
     *  \brief
     *      Returned when a resource needed to perform the given
     *      action is unavailable.
     *
     *  \par Format
     *  <tt>
     *      "<nick/channel> :Nick/channel is temporarily unavailable"
     *  </tt>
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
     *  \TODO
     */
    const ERR_BANNICKCHANGE         = 437;

    /**
     *  \TODO
     */
    const ERR_NICKTOOFAST           = 438;

    /**
     *  \TODO
     */
    const ERR_TARGETTOOFAST         = 439;

    /**
     *  \TODO
     */
    const ERR_SERVICESDOWN          = 440;

    /**
     *  \brief
     *      Returned by the server to indicate that the target
     *      user of the command is not on the given channel.
     *
     *  \par Format
     *  <tt>
     *      "<nick> <channel> :They aren't on that channel"
     *  </tt>
     */
    const ERR_USERNOTINCHANNEL      = 441;

    /**
     *  \brief
     *      Returned by the server whenever a client tries to
     *      perform a channel affecting command for which the
     *      client isn't a member.
     *
     *  \par Format
     *  <tt>
     *      "<channel> :You're not on that channel"
     *  </tt>
     */
    const ERR_NOTONCHANNEL          = 442;

    /**
     *  \brief
     *      Returned when a client tries to invite a user to
     *      a channel they are already on.
     *
     *  \par Format
     *  <tt>
     *      "<user> <channel> :is already on channel"
     *  </tt>
     */
    const ERR_USERONCHANNEL         = 443;

    /**
     *  \brief
     *      Returned by the summon after a SUMMON command for a
     *      user was unable to be performed since they were not
     *      logged in.
     *
     *  \par Format
     *  <tt>
     *      "<user> :User not logged in"
     *  </tt>
     */
    const ERR_NOLOGIN               = 444;

    /**
     *  \brief
     *      Returned by any server which does not support the SUMMON command,
     *      either because it was not implemented or it was disabled (in the
     *      configuration).
     *
     *  \par Format
     *  <tt>
     *      ":SUMMON has been disabled"
     *  </tt>
     */
    const ERR_SUMMONDISABLED        = 445;

    /**
     *  \brief
     *      Returned by any server which does not support the USERS command,
     *      either because it was not implemented or it was disabled (in the
     *      configuration).
     *
     *  \par Format
     *  <tt>
     *      ":USERS has been disabled"
     *  </tt>
     */
    const ERR_USERSDISABLED         = 446;

    /**
     *  \brief
     *      Returned by the server to indicate that the client
     *      must be registered before the server will allow it
     *      to be parsed in detail.
     *
     *  \par Format
     *  <tt>
     *      ":You have not registered"
     *  </tt>
     */
    const ERR_NOTREGISTERED         = 451;

    /**
     *  \TODO
     */
    const ERR_HOSTILENAME           = 455;

    /**
     *  \brief
     *      Returned by the server by numerous commands to
     *      indicate to the client that it didn't supply
     *      enough parameters.
     *
     *  \par Format
     *  <tt>
     *      "<command> :Not enough parameters"
     *  </tt>
     */
    const ERR_NEEDMOREPARAMS        = 461;

    /**
     *  \brief
     *      Returned by the server to any link which tries to
     *      change part of the registered details (such as
     *      password or user details from second USER message).
     *
     *  \par Format
     *  <tt>
     *      ":Unauthorized command (already registered)"
     *  </tt>
     */
    const ERR_ALREADYREGISTRED      = 462;

    /**
     *  \brief
     *      Returned to a client which attempts to register
     *      with a server which does not been setup to allow
     *      connections from the host the attempted connection
     *      is tried.
     *
     *  \par Format
     *  <tt>
     *      ":Your host isn't among the privileged"
     *  </tt>
     */
    const ERR_NOPERMFORHOST         = 463;

    /**
     *  \brief
     *      Returned to indicate a failed attempt at registering
     *      a connection for which a password was required and
     *      was either not given or incorrect.
     *
     *  \par Format
     *  <tt>
     *      ":Password incorrect"
     *  </tt>
     */
    const ERR_PASSWDMISMATCH        = 464;

    /**
     *  \brief
     *      Returned after an attempt to connect and register
     *      yourself with a server which has been setup to
     *      explicitly deny connections to you.
     *
     *  \par Format
     *  <tt>
     *      ":You are banned from this server"
     *  </tt>
     */
    const ERR_YOUREBANNEDCREEP      = 465;

    /**
     *  \brief
     *      Sent by a server to a user to inform that access to the
     *      server will soon be denied.
     */
    const ERR_YOUWILLBEBANNED       = 466;

    /**
     *  \brief
     *      Sent when attempting to set a key for a channel
     *      which already has one.
     *
     *  \par Format
     *  <tt>
     *      "<channel> :Channel key already set"
     *  </tt>
     */
    const ERR_KEYSET                = 467;

    /**
     *  \TODO
     */
    const ERR_INVALIDUSERNAME       = 468;

    /**
     *  \TODO
     */
    const ERR_ONLYSERVERSCANCHANGE  = 468;

    /**
     *  \brief
     *      Returned when trying to JOIN a channel for which
     *      a limit has been set and reached.
     *
     *  \par Format
     *  <tt>
     *      "<channel> :Cannot join channel (+l)"
     *  </tt>
     */
    const ERR_CHANNELISFULL         = 471;

    /**
     *  \brief
     *      Returned when trying to set a mode which is not
     *      recognized by the server on a channel.
     *
     *  \par Format
     *  <tt>
     *      "<char> :is unknown mode char to me for <channel>"
     *  </tt>
     *
     * Sent when the client sets an AWAY message.
     */
    const ERR_UNKNOWNMODE           = 472;

    /**
     *  \brief
     *      Returned when trying to JOIN a channel which requires
     *      an invitation and you've not been invited.
     *
     *  \par Format
     *  <tt>
     *      "<channel> :Cannot join channel (+i)"
     *  </tt>
     */
    const ERR_INVITEONLYCHAN        = 473;

    /**
     *  \brief
     *      Returned when trying to JOIN a channel from which
     *      you've been banned.
     *
     *  \par Format
     *  <tt>
     *      "<channel> :Cannot join channel (+b)"
     *  </tt>
     */
    const ERR_BANNEDFROMCHAN        = 474;

    /**
     *  \brief
     *      Returned when trying to JOIN a channel for which
     *      a key was set and was either not given or incorrect.
     *
     *  \par Format
     *  <tt>
     *      "<channel> :Cannot join channel (+k)"
     *  </tt>
     */
    const ERR_BADCHANNELKEY         = 475;

    /**
     *  \TODO
     */
    const ERR_BADCHANMASK           = 476;

    /**
     *  \brief
     *      Returned when attempting to set modes on a channel
     *      which does not support modes.
     *
     *  \par Format
     *  <tt>
     *      "<channel> :Channel doesn't support modes"
     *  </tt>
     */
    const ERR_NOCHANMODES           = 477;

    /**
     *  \TODO
     */
    const ERR_NEEDREGGEDNICK        = 477;

    /**
     *  \brief
     *      Returned when attempting to add a ban on a channel
     *      for which the banlist is already full.
     *
     *  \par Format
     *  <tt>
     *      "<channel> <char> :Channel list is full"
     *  </tt>
     */
    const ERR_BANLISTFULL           = 478;

    /**
     *  \TODO
     */
    const ERR_BADCHANNAME           = 479;

    /**
     *  \brief
     *      Any command requiring operator privileges to operate
     *      will return this error to indicate the attempt was
     *      unsuccessful.
     *
     *  \par Format
     *  <tt>
     *      ":Permission Denied- You're not an IRC operator"
     *  </tt>
     */
    const ERR_NOPRIVILEGES          = 481;

    /**
     *  \brief
     *      Any command requiring 'chanop' privileges (such as
     *      MODE messages) will return this error if the client
     *      making the attempt is not a channel operator on the
     *      specified channel.
     *
     *  \par Format
     *  <tt>
     *      <channel> :You're not channel operator"
     *  </tt>
     */
    const ERR_CHANOPPRIVSNEEDED     = 482;

    /**
     *  \brief
     *      Any attempts to use the KILL command on a server
     *      will be refused and this error returned directly
     *      to the client.
     *
     *  \par Format
     *  <tt>
     *      ":You can't kill a server!"
     *  </tt>
     */
    const ERR_CANTKILLSERVER        = 483;

    /**
     *  \brief
     *      Sent by the server to a user upon connection to indicate
     *      the restricted nature of the connection (user mode "+r")
     *
     *  \par Format
     *  <tt>
     *      ":Your connection is restricted!"
     *  </tt>
     */
    const ERR_RESTRICTED            = 484;

    /**
     *  \TODO
     */
    const ERR_ISCHANSERVICE         = 484;

    /**
     *  \brief
     *      Any MODE requiring "channel creator" privileges will
     *      return this error if the client making the attempt is not
     *      a channel operator on the specified channel.
     *
     *  \par Format
     *  <tt>
     *      ":You're not the original channel operator"
     *  </tt>
     */
    const ERR_UNIQOPPRIVSNEEDED     = 485;

    /**
     *  \TODO
     */
    const ERR_CHANBANREASON         = 485;

    /**
     *  \TODO
     */
    const ERR_NONONREG              = 486;

    /**
     *  \TODO
     */
    const ERR_MSGSERVICES           = 487;

    /**
     *  \brief
     *      If a client sends an OPER message and the server
     *      has not been configured to allow connections from
     *      the client's host as an operator, this error will
     *      be returned.
     *
     *  \par Format
     *  <tt>
     *      ":No O-lines for your host"
     *  </tt>
     */
    const ERR_NOOPERHOST            = 491;

    /**
     *  \TODO
     */
    const ERR_OWNMODE               = 494;

    /**
     *  \brief
     *      Returned by the server to indicate that a MODE
     *      message was sent with a nickname parameter and
     *      that the a mode flag sent was not recognized.
     *
     *  \par Format
     *  <tt>
     *      ":Unknown MODE flag"
     *  </tt>
     */
    const ERR_UMODEUNKNOWNFLAG      = 501;

    /**
     *  \brief
     *      Error sent to any user trying to view or change
     *      the user mode for a user other than themselves.
     *
     *  \par Format
     *  <tt>
     *      ":Cannot change mode for other users"
     *  </tt>
     */
    const ERR_USERSDONTMATCH        = 502;

    /**
     *  \TODO
     */
    const ERR_SILELISTFULL          = 511;

    /**
     *  \TODO
     */
    const ERR_NOSUCHGLINE           = 512;

    /**
     *  \TODO
     */
    const ERR_TOOMANYWATCH          = 512;

    /**
     *  \TODO
     */
    const ERR_BADPING               = 513;

    /**
     *  \TODO
     */
    const ERR_TOOMANYDCC            = 514;

    /**
     *  \TODO
     */
    const ERR_LISTSYNTAX            = 521;

    /**
     *  \TODO
     */
    const ERR_WHOSYNTAX             = 522;

    /**
     *  \TODO
     */
    const ERR_WHOLIMEXCEED          = 523;

    /**
     *  \TODO
     */
    const RPL_LOGON                 = 600;

    /**
     *  \TODO
     */
    const RPL_LOGOFF                = 601;

    /**
     *  \TODO
     */
    const RPL_WATCHOFF              = 602;

    /**
     *  \TODO
     */
    const RPL_WATCHSTAT             = 603;

    /**
     *  \TODO
     */
    const RPL_NOWON                 = 604;

    /**
     *  \TODO
     */
    const RPL_NOWOFF                = 605;

    /**
     *  \TODO
     */
    const RPL_WATCHLIST             = 606;

    /**
     *  \TODO
     */
    const RPL_ENDOFWATCHLIST        = 607;

    /**
     *  \TODO
     */
    const RPL_DCCSTATUS             = 617;

    /**
     *  \TODO
     */
    const RPL_DCCLIST               = 618;

    /**
     *  \TODO
     */
    const RPL_ENDOFDCCLIST          = 619;

    /**
     *  \TODO
     */
    const RPL_DCCINFO               = 620;


    // Those are unofficial raws for InspIRCd's STARTTLS extension.

    /**
     *  \brief
     *      Returned to a client after a STARTTLS command to indicate
     *      that the server is ready to proceed with data encrypted
     *      using the SSL/TLS protocol.
     *
     *  \par Format
     *  <tt>
     *      ":STARTTLS successful, go ahead with TLS handshake"
     *  </tt>
     *
     *  \note
     *      Upon receiving this message, the client should proceed
     *      with a TLS handshake. Once the handshake is completed,
     *      data may be exchanged securely between the server and
     *      the client.
     */
    const RPL_STARTTLSOK            = 670;

    /**
     *  \brief
     *      Returned to a client after STARTTLS command to indicate
     *      that the attempt to negotiate a secure channel for the
     *      communication to take place has failed.
     *
     *  \par Format
     *  <tt>
     *      ":STARTTLS failure"
     *  </tt>
     *
     *  \note
     *      Upon receiving this message, the client may proceed with
     *      the communication (even though data will be exchanged in
     *      plain text), or it may choose to close the connection
     *      entirely.
     */
    const ERR_STARTTLSFAIL          = 691;


    /**
     * Constructs a raw message.
     *
     * \param Erebot_Interface_Connection $connection
     *      The connection this message came from.
     *
     * \param int $raw
     *      The raw numeric code.
     *
     * \param string $source
     *      The source of the raw message. This will generally be
     *      the name of an IRC server.
     *
     * \param string $target
     *      The target of the raw message. This will generally be
     *      the bot's nickname.
     *
     * \param string $text
     *      The raw content of the message.
     *
     * \note
     *      No attempt is made at parsing the content of the message.
     */
    public function __construct(
        Erebot_Interface_Connection $connection,
                                    $raw,
                                    $source,
                                    $target,
                                    $text
    );

    /**
     * Returns the raw numeric code associated with
     * the current message.
     *
     * \retval int
     *      The raw numeric code of this message.
     *
     * \see
     *      You may compare the value returned by this method with one
     *      of the constants of the Erebot_Interface_Event_Raw interface.
     *
     * \note
     *      Multiple constants may point to the same code
     *      as the same code may have different interpretations
     *      depending on the server (IRCd) where it is used.
     */
    public function getRaw();
}

