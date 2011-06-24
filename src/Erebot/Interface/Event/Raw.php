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

    /// Specific commands/options supported by the server.
    const RPL_ISUPPORT              =   5;

    /// Active PROTOcol ConTroL flags (obsolete).
    const RPL_PROTOCTL              =   5;

    /**
     *  \brief
     *      Obsolete raw used to redirect users to another server.
     *
     *  \format{"Try server <server name>\, port <port number>"}
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
    const RPL_ENDMAP                =   7;

    /**
     *  \TODO
     */
    const RPL_SNOMASK               =   8;
    const RPL_SNOMASKIS             =   8;

    /**
     *  \TODO
     */
    const RPL_STATMEM               =  10;

    /**
     *  \TODO
     */
    const RPL_STATMEMTOT            =  10;

    const RPL_REDIR                 =  10;  // UnrealIRCd

    /**
     *  \TODO
     */
    const RPL_YOURCOOKIE            =  14;

    /**
     *  \TODO
     */
    const RPL_YOURID                =  42;
    const RPL_YOURUUID              =  42;  // InspIRCd, ircnet

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

    const RPL_REMOTEISUPPORT        = 105;

    /**
     *  \brief
     *      RPL_TRACELINK is sent by any server which handles
     *      a TRACE message and has to pass it on to another
     *      server.
     * 
     *  \format{
     *      "Link \<version & debug level\> \<destination\>
     *      \<next server\> V\<protocol version\>
     *      \<link uptime in seconds\> \<backstream sendq\>
     *      \<upstream sendq\>"
     *  }
     */
    const RPL_TRACELINK             = 200;

    /**
     *  \brief
     *      Used when tracing connections which have not been
     *      fully established and are still attempting to connect.
     * 
     *  \format{"Try. <class> <server>"}
     */
    const RPL_TRACECONNECTING       = 201;

    /**
     *  \brief
     *      Used when tracing connections which have not been
     *      fully established and are in the process of completing
     *      the "server handshake".
     * 
     *  \format{"H.S. <class> <server>"}
     */
    const RPL_TRACEHANDSHAKE        = 202;

    /**
     *  \brief
     *      Used when tracing connections which have not been
     *      fully established and are unknown.
     * 
     *  \format{"???? <class> [<client IP address in dot form>]"}
     */
    const RPL_TRACEUNKNOWN          = 203;

    /**
     *  \brief
     *      Used when tracing connections to give information
     *      on IRC operators.
     * 
     *  \format{"Oper <class> <nick>"}
     */
    const RPL_TRACEOPERATOR         = 204;

    /**
     *  \brief
     *      Used when tracing connections to give information
     *      on (non-operator) IRC clients.
     * 
     *  \format{"User <class> <nick>"}
     */
    const RPL_TRACEUSER             = 205;

    /**
     *  \brief
     *      Used when tracing connections to give information
     *      on IRC servers.
     * 
     *  \format{
     *      "Serv <class> <int>S <int>C <server>
     *      <nick!user|*!*>@<host|server> V<protocol version>"
     *  }
     */
    const RPL_TRACESERVER           = 206;

    /**
     *  \brief
     *      Used when tracing connections to give information
     *      on IRC services.
     * 
     *  \format{"Service <class> <name> <type> <active type>"}
     */
    const RPL_TRACESERVICE          = 207;

    /**
     *  \brief
     *      RPL_TRACENEWTYPE is to be used for any connection
     *      which does not fit in the other categories but is
     *      being displayed anyway.
     * 
     *  \format{"<newtype> 0 <client name>"}
     */
    const RPL_TRACENEWTYPE          = 208;

    /**
     *  \brief
     *      Used when tracing connections to give information
     *      on a class of connections.
     * 
     *  \format{"Class <class> <count>"}
     */
    const RPL_TRACECLASS            = 209;

    /**
     *  \brief
     *      Unused raw.
     */
    const RPL_TRACERECONNECT        = 210;

    const RPL_STATSHELP             = 210;  // UnrealIRCd

    /**
     *  \brief
     *      Reports statistics on a connection.
     *
     *  \format{
     *      "\<linkname\> \<sendq\> \<sent messages\>
     *      \<sent Kbytes\> \<received messages\>
     *      \<received Kbytes\> \<time open\>"
     *  }
     *
     *  <tt>\<linkname\></tt> identifies the particular connection,
     *  <tt>\<sendq\></tt> is the amount of data that is queued and
     *  waiting to be sent <tt>\<sent messages\></tt> the number of
     *  messages sent, and <tt>\<sent Kbytes\></tt> the amount of
     *  data sent, in Kbytes.
     *  <tt>\<received messages\></tt> and <tt>\<received Kbytes\></tt> are
     *  the equivalent of <tt>\<sent messages\></tt> and <tt>\<sent Kbytes\></tt>
     *  for received data, respectively.
     *  <tt>\<time open\></tt> indicates how long ago the connection
     *  was opened, in seconds.
     */
    const RPL_STATSLINKINFO         = 211;

    /**
     *  \brief
     *      Reports statistics on commands usage.
     *
     *  \format{"<command> <count> <byte count> <remote count>"}
     */
    const RPL_STATSCOMMANDS         = 212;

    /**
     *  \TODO
     *  "C <address> * <server> <port> <class>"
     *
     *  \note
     *      The "*" is treated as a litteral, not some
     *      wildcard character.
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
     *  \brief
     *      Marks the end of the STATS report.
     *
     *  \format{"<stats letter> :End of STATS report"}
     */
    const RPL_ENDOFSTATS            = 219;

    /**
     *  \TODO
     */
    const RPL_STATSBLINE            = 220;

    const RPL_NMODEIS               = 220;

    /**
     *  \brief
     *      To answer a query about a client's own mode,
     *      RPL_UMODEIS is sent back.
     *
     *  \format{"<user mode string>"}
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

    const RPL_STATSELINE            = 223;  // Bahamut

    /**
     *  \TODO
     */
    const RPL_STATS_D               = 224;

    const RPL_STATSTLINE            = 224;  // UnrealIRCd

    const RPL_STATSFLINE            = 224;  // UltimateIRCd

    /**
     *  \TODO
     */
    const RPL_STATSCLONE            = 225;

    const RPL_STATSZLINE            = 225;  // UltimateIRCd

    /**
     *  \TODO
     */
    const RPL_STATSCOUNT            = 226;

    /**
     *  \TODO
     */
    const RPL_STATSGLINE            = 227;

    const RPL_STATSVLINE            = 227;  // UnrealIRCd

    const RPL_STATSBANVER           = 228;  // UnrealIRCd

    const RPL_STATSSPAMF            = 229;  // UnrealIRCd

    const RPL_STATSEXCEPTTKL        = 230;  // UnrealIRCd

    /**
     *  \TODO
     */
    const RPL_SERVICEINFO           = 231;

    /**
     *  \TODO
     */
    const RPL_ENDOFSERVICES         = 232;

    const RPL_RULES                 = 232;  // UnrealIRCd

    /**
     *  \TODO
     */
    const RPL_SERVICE               = 233;

    /**
     *  \brief
     *      When listing services in reply to a SERVLIST message,
     *      a separate RPL_SERVLIST is sent for each service.
     *
     *  \format{"<name> <server> <mask> <type> <hopcount> <info>"}
     */
    const RPL_SERVLIST              = 234;

    /**
     *  \brief
     *      Marks the end of the list of services,
     *      sent in response to a SERVLIST message.
     *
     *  \format{"<mask> <type> :End of service listing"}
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
     *  \format{":Server Up %d days %d:%02d:%02d"}
     */
    const RPL_STATSUPTIME           = 242;

    /**
     *  \brief
     *      Reports the allowed hosts from where
     *      users may become IRC operators.
     *
     *  \format{"O <hostmask> * <name>"}
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
     *  \brief
     *      In processing an LUSERS message, the server
     *      sends this raw to indicate how many clients
     *      and servers are connected (global count).
     *
     *  \format{
     *      ":There are <integer> users and <integer>
     *      services on <integer> servers"
     *  }
     */
    const RPL_LUSERCLIENT           = 251;

    /**
     *  \brief
     *      Sent in response to a LUSERS message to indicate
     *      how many IRC operators are currently connected,
     *      if any.
     *
     *  \format{"<integer> :operator(s) online"}
     */
    const RPL_LUSEROP               = 252;

    /**
     *  \brief
     *      Sent in response to a LUSERS message to indicate
     *      how many unknown connections there are, if any.
     *
     *  \format{"<integer> :unknown connection(s)"}
     */
    const RPL_LUSERUNKNOWN          = 253;

    /**
     *  \brief
     *      Sent in response to a LUSERS message to indicate
     *      how many IRC channels have been formed, if any.
     *
     *  \format{"<integer> :channels formed"}
     */
    const RPL_LUSERCHANNELS         = 254;

    /**
     *  \brief
     *      In processing an LUSERS message, the server
     *      sends this raw to indicate how many clients
     *      and servers are connected (local count).
     *
     *  \format{":I have <integer> clients and <integer> servers"}
     */
    const RPL_LUSERME               = 255;

    /**
     *  \brief
     *      Returned as the first raw in response to an
     *      ADMIN message.
     *
     *  \format{"<server> :Administrative info"}
     */
    const RPL_ADMINME               = 256;

    /**
     *  \brief
     *      Returned in response to an ADMIN message,
     *      usually giving information on the city,
     *      state and country where the server is located.
     *
     *  \format{":<admin info>"}
     */
    const RPL_ADMINLOC1             = 257;

    /**
     *  \brief
     *      Returned in response to an ADMIN message,
     *      usually giving information on the institution
     *      hosting the server.
     *
     *  \format{":<admin info>"}
     */
    const RPL_ADMINLOC2             = 258;

    /**
     *  \brief
     *      Returned as the last raw in response to an
     *      ADMIN message, giving an email where the server's
     *      administrator can be reached.
     *
     *  \format{":<admin info>"}
     *
     *  \note
     *      RFC 2812 makes it a requirement that this raw
     *      contain a valid email address.
     */
    const RPL_ADMINEMAIL            = 259;

    /**
     *  \brief
     *      Used to indicate that TRACE information is being logged
     *      to a file on the IRC server.
     *
     *  \format{"File <logfile> <debug level>"}
     */
    const RPL_TRACELOG              = 261;

    /**
     *  \brief
     *      RPL_TRACEEND is sent to indicate the end of the list
     *      of replies to a TRACE command.
     *
     *  \format{"<server name> <version & debug level> :End of TRACE"}
     */
    const RPL_TRACEEND              = 262;

    const RPL_ENDOFTRACE            = 262; // Bahamut

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
     *      Mostly an alias for Erebot_Interface_Event_Raw::RPL_TRYAGAIN.
     *
     *  \format{
     *      "<command> :Server load is temporarily too heavy.
     *      Please wait a while and try again."
     *  }
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

    const RPL_MAPUSERS              = 270;

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
    const RPL_USINGSSL              = 275;

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
     *  \format{"<nick> :<away message>"}
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
     *  \format{":*1<reply> *( " " <reply> )"}
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
     *  \format{":*1<nick> *( " " <nick> )"}
     */
    const RPL_ISON                  = 303;

    /**
     *  \TODO
     */
    const RPL_TEXT                  = 304;

    const RPL_SYNTAX                = 304;  // InspIRCd

    /**
     *  \brief
     *      Sent went the client removes an AWAY message.
     *
     *  \format{":You are no longer marked as being away"}
     */
    const RPL_UNAWAY                = 305;

    /**
     *  \brief
     *      Sent when the client sets an AWAY message.
     *
     *  \format{":You have been marked as being away"}
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

    const RPL_RULESTART             = 308;  // InspIRCd
    const RPL_RULESSTART            = 308;  // UnrealIRCd

    /**
     *  \TODO
     */
    const RPL_WHOISSADMIN           = 309;

    const RPL_ENDOFRULES            = 309;  // UnrealIRCd
    const RPL_RULESEND              = 309;  // InspIRCd

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
     *  \format{"<nick> <user> <host> * :<real name>"}
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
     *  \format{"<nick> :is an IRC operator"}
     */
    const RPL_WHOISOPERATOR         = 313;

    /**
     *  \brief
     *      Sent in response to a WHOWAS, giving
     *      information on the target user.
     *
     *  \format{"<nick> <user> <host> * :<real name>"}
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
     *  \format{"<nick> <integer> :seconds idle"}
     */
    const RPL_WHOISIDLE             = 317;

    /**
     *  \brief
     *      The RPL_ENDOFWHOIS reply is used to mark
     *      the end of processing a WHOIS message.
     *
     *  \format{"<nick> :End of WHOIS list"}
     */
    const RPL_ENDOFWHOIS            = 318;

    /**
     *  \brief
     *      Sent in response to a WHOIS, listing
     *      the public channels the target user is on.
     *
     *  \format{"\<nick\> :*( ( "@" / "+" ) \<channel\> " " )"}
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

    const RPL_WHOISSPECIAL          = 320;  // UnrealIRCd

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
     *  \format{"<channel> <# visible> :<topic>"}
     */
    const RPL_LIST                  = 322;

    /**
     *  \brief
     *      Sent in response to a LIST command,
     *      marks the end of the server's response.
     *
     *  \format{":End of LIST"}
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
    const RPL_CHANNELCREATED        = 329;

    /**
     *  \brief
     *      Sent when joining a channel or issuing
     *      a TOPIC command and no topic has been
     *      set yet.
     *
     *  \format{"<channel> :No topic is set"}
     */
    const RPL_NOTOPIC               = 331;
    const RPL_NOTOPICSET            = 331;

    /**
     *  \brief
     *      Sent when joining a channel or issuing
     *      a TOPIC command; contains the current
     *      topic.
     *
     *  \format{"<channel> :<topic>"}
     */
    const RPL_TOPIC                 = 332;

    /**
     *  \TODO
     */
    const RPL_TOPICWHOTIME          = 333;
    const RPL_TOPICTIME             = 333;

    /**
     *  \TODO
     */
    const RPL_COMMANDSYNTAX         = 334;
    const RPL_LISTSYNTAX            = 334;

    const RPL_WHOISBOT              = 335;

    /**
     *  \TODO
     */
    const RPL_WHOISTEXT             = 337;

    /**
     *  \TODO
     */
    const RPL_WHOISACTUALLY         = 338;

    const RPL_USERIP                = 340;

    /**
     *  \brief
     *      Returned by the server to indicate that the
     *      attempted INVITE message was successful and is
     *      being passed onto the end client.
     *
     *  \format{"<channel> <nick>"}
     */
    const RPL_INVITING              = 341;

    /**
     *  \brief
     *      Returned by a server answering a SUMMON message to
     *      indicate that it is summoning that user.
     *
     *  \format{"<user> :Summoning user to IRC"}
     */
    const RPL_SUMMONING             = 342;

    /**
     *  \TODO
     */
    const RPL_INVITELIST            = 346;
    const RPL_INVEXLIST             = 346;  // UnrealIRCd

    /**
     *  \TODO
     */
    const RPL_ENDOFINVITELIST       = 347;
    const RPL_ENDOFINVEXLIST        = 347;  // UnrealIRCd

    /**
     *  \TODO
     */
    const RPL_EXCEPTLIST            = 348;
    const RPL_EXLIST                = 348;  // UnrealIRCd

    ///  Alias for Erebot_Interface_Event_Raw::RPL_EXCEPTLIST.
    const RPL_EXEMPTLIST            = 348;

    /**
     *  \TODO
     */
    const RPL_ENDOFEXCEPTLIST       = 349;
    const RPL_ENDOFEXLIST           = 349;  // UnrealIRCd

    ///  Alias for Erebot_Interface_Event_Raw::RPL_ENDOFEXCEPTLIST.
    const RPL_ENDOFEXEMPTLIST       = 349;

    /**
     *  \brief
     *      Reply by the server showing its version details.
     *
     *  \format{"<version>.<debuglevel> <server> :<comments>"}
     *
     *  The <tt>\<version\></tt> is the version of the software being
     *  used (including any patchlevel revisions) and the
     *  <tt>\<debuglevel\></tt> is used to indicate if the server is
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
    const RPL_KILLDONE              = 361;

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
     *  \format{"<nick> :End of WHOWAS"}
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
    const RPL_INFOSTART             = 373;

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

    const RPL_WHOISMODES            = 379;  // UnrealIRCd

    /**
     *  \brief
     *      RPL_YOUREOPER is sent back to a client which has
     *      just successfully issued an OPER message and gained
     *      operator status.
     *
     *  \format{":You are now an IRC operator"}
     */
    const RPL_YOUREOPER             = 381;
    const RPL_YOUAREOPER            = 381;

    /**
     *  \brief
     *      If the REHASH option is used and an operator sends
     *      a REHASH message, an RPL_REHASHING is sent back to
     *      the operator.
     *
     *  \format{"<config file> :Rehashing"}
     */
    const RPL_REHASHING             = 382;

    /**
     *  \brief
     *      Sent by the server to a service upon successful
     *      registration.
     *
     *  \format{"You are service <servicename>"}
     */
    const RPL_YOURESERVICE          = 383;

    /**
     *  \TODO
     */
    const RPL_MYPORTIS              = 384;

    /**
     *  \TODO
     */
    const RPL_NOTOPERANYMORE        = 385;

    const RPL_QLIST                 = 386;  // UnrealIRCd

    const RPL_ENDOFQLIST            = 387;  // UnrealIRCd

    const RPL_ALIST                 = 388;  // UnrealIRCd

    const RPL_ENDOFALIST            = 389;  // UnrealIRCd

    /**
     *  \brief
     *      When replying to the TIME message, a server MUST send
     *      the reply using the RPL_TIME format below.
     *
     *  \format{"<server> :<string showing server's local time>"}
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

    const RPL_YOURDISPLAYEDHOST     = 396;  // charybdis

    /**
     *  \brief
     *      Used to indicate the nickname parameter
     *      supplied to a command is currently unused.
     *
     *  \format{"<nickname> :No such nick/channel"}
     */
    const ERR_NOSUCHNICK            = 401;

    /**
     *  \brief
     *      Used to indicate the server name given
     *      currently doesn't exist.
     *
     *  \format{"<server name> :No such server"}
     */
    const ERR_NOSUCHSERVER          = 402;

    /**
     *  \brief
     *      Used to indicate the given channel name
     *      is invalid.
     *
     *  \format{"<channel name> :No such channel"}
     */
    const ERR_NOSUCHCHANNEL         = 403;

    /**
     *  \brief
     *      Sent by the server when attempting to send
     *      a PRIVMSG on a channel when you're not allowed
     *      to do so.
     *
     *  \format{"<channel name> :Cannot send to channel"}
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
     *  \format{"<channel name> :You have joined too many channels"}
     */
    const ERR_TOOMANYCHANNELS       = 405;

    /**
     *  \brief
     *      Returned by WHOWAS to indicate there is no history
     *      information for that nickname.
     *
     *  \format{"<nickname> :There was no such nickname"}
     */
    const ERR_WASNOSUCHNICK         = 406;

    /**
     *  \brief
     *      Used when several targets match the given parameters
     *      for a command.
     *
     *  \format{"<target> :<error code> recipients. <abort message>"}
     *
     *  There are several occasions when this raw may be used:
     *  -   Returned to a client which is attempting to send a
     *      PRIVMSG/NOTICE using the user\@host destination format
     *      and for a user\@host which has several occurrences.
     *
     *  -   Returned to a client which tries to send a
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
     *  \format{"<service name> :No such service"}
     */
    const ERR_NOSUCHSERVICE         = 408;

    const ERR_NOCOLORSONCHAN        = 408;  // UltimateIRCd

    /**
     *  \TODO
     */
    const ERR_NOCTRLSONCHAN         = 408;

    /**
     *  \brief
     *      PING or PONG message missing the originator parameter.
     *
     *  \format{":No origin specified"}
     */
    const ERR_NOORIGIN              = 409;

    const ERR_INVALIDCAPSUBCOMMAND  = 410;

    /**
     *  \brief
     *      Used to indicate a recipient was expected
     *      for the given command.
     *
     *  \format{":No recipient given (<command>)"}
     */
    const ERR_NORECIPIENT           = 411;

    /**
     *  \brief
     *      Sent when a command did not receive any text when it was
     *      expecting some.
     *
     *  \format{":No text to send"}
     */
    const ERR_NOTEXTTOSEND          = 412;

    /**
     *  \brief
     *      Returned when an invalid use of <tt>"PRIVMSG $<server>"</tt>
     *      or <tt>"PRIVMSG #<host>"</tt> is attempted (when it doesn't
     *      contain a top-level domain).
     *
     *  \format{"<mask> :No toplevel domain specified"}
     */
    const ERR_NOTOPLEVEL            = 413;

    /**
     *  \brief
     *      Returned when an invalid use of <tt>"PRIVMSG $<server>"</tt>
     *      or <tt>"PRIVMSG #<host>"</tt> is attempted (when the top-level
     *      domain contains wildcard characters).
     *
     *  \format{"<mask> :Wildcard in toplevel domain"}
     */
    const ERR_WILDTOPLEVEL          = 414;

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
     */
    const ERR_QUERYTOOLONG          = 416;

    /**
     *  \brief
     *      Returned to a registered client to indicate that the
     *      command sent is unknown by the server.
     *
     *  \format{"<command> :Unknown command"}
     */
    const ERR_UNKNOWNCOMMAND        = 421;

    /**
     *  \brief
     *      Server's MOTD file could not be opened by the server.
     *
     *  \format{":MOTD File is missing"}
     */
    const ERR_NOMOTD                = 422;

    /**
     *  \brief
     *      Returned by a server in response to an ADMIN message
     *      when there is an error in finding the appropriate
     *      information.
     *
     *  \format{"<server> :No administrative info available"}
     */
    const ERR_NOADMININFO           = 423;

    /**
     *  \brief
     *      Generic error message used to report a failed file
     *      operation during the processing of a message.
     *
     *  \format{":File error doing <file op> on <file>"}
     */
    const ERR_FILEERROR             = 424;

    const ERR_NOOPERMOTD            = 425;  // UnrealIRCd

    /**
     *  \TODO
     */
    const ERR_TOOMANYAWAY           = 429;

    /**
     *  \brief
     *      Returned when a nickname parameter is expected
     *      for a command and isn't found.
     *
     *  \format{":No nickname given"}
     */
    const ERR_NONICKNAMEGIVEN       = 431;

    /**
     *  \brief
     *      Returned after receiving a NICK message which contains
     *      characters which do not fall in the defined set.
     *
     *  \format{"<nick> :Erroneous nickname"}
     */
    const ERR_ERRONEUSNICKNAME      = 432;

    /**
     *  \brief
     *      Returned when a NICK message is processed that results
     *      in an attempt to change to a currently existing
     *      nickname.
     *
     *  \format{"<nick> :Nickname is already in use"}
     */
    const ERR_NICKNAMEINUSE         = 433;

    const ERR_NORULES               = 434;  // InspIRCd

    /**
     *  \TODO
     */
    const ERR_BANONCHAN             = 435;

    const ERR_SERVICECONFUSED       = 435;  // UnrealIRCd

    /**
     *  \brief
     *      Returned by a server to a client when it detects
     *      a nickname collision (registered of a NICK that
     *      already exists by another server).
     *
     *  \format{"<nick> :Nickname collision KILL from <user>@<host>"}
     */
    const ERR_NICKCOLLISION         = 436;

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
     *  \TODO
     */
    const ERR_BANNICKCHANGE         = 437;

    /**
     *  \TODO
     */
    const ERR_NICKTOOFAST           = 438;
    const ERR_NCHANGETOOFAST        = 438;

    /**
     *  \TODO
     */
    const ERR_TARGETTOOFAST         = 439;
    const ERR_TARGETTOFAST          = 439;  // Bahamut

    /**
     *  \TODO
     */
    const ERR_SERVICESDOWN          = 440;

    /**
     *  \brief
     *      Returned by the server to indicate that the target
     *      user of the command is not on the given channel.
     *
     *  \format{"<nick> <channel> :They aren't on that channel"}
     */
    const ERR_USERNOTINCHANNEL      = 441;

    /**
     *  \brief
     *      Returned by the server whenever a client tries to
     *      perform a channel affecting command for which the
     *      client isn't a member.
     *
     *  \format{"<channel> :You're not on that channel"}
     */
    const ERR_NOTONCHANNEL          = 442;

    /**
     *  \brief
     *      Returned when a client tries to invite a user to
     *      a channel they are already on.
     *
     *  \format{"<user> <channel> :is already on channel"}
     */
    const ERR_USERONCHANNEL         = 443;

    /**
     *  \brief
     *      Returned by the summon after a SUMMON command for a
     *      user was unable to be performed since they were not
     *      logged in.
     *
     *  \format{"<user> :User not logged in"}
     */
    const ERR_NOLOGIN               = 444;

    /**
     *  \brief
     *      Returned by any server which does not support the SUMMON command,
     *      either because it was not implemented or it was disabled (in the
     *      configuration).
     *
     *  \format{":SUMMON has been disabled"}
     */
    const ERR_SUMMONDISABLED        = 445;

    /**
     *  \brief
     *      Returned by any server which does not support the USERS command,
     *      either because it was not implemented or it was disabled (in the
     *      configuration).
     *
     *  \format{":USERS has been disabled"}
     */
    const ERR_USERSDISABLED         = 446;

    const ERR_CANTCHANGENICK        = 447;
    const ERR_NONICKCHANGE          = 447;  // UnrealIRCd

    /**
     *  \brief
     *      Returned by the server to indicate that the client
     *      must be registered before the server will allow it
     *      to be parsed in detail.
     *
     *  \format{":You have not registered"}
     */
    const ERR_NOTREGISTERED         = 451;

    /**
     *  \TODO
     */
    const ERR_HOSTILENAME           = 455;

    const ERR_NOHIDING              = 459;  // UnrealIRCd

    const ERR_NOTFORHALFOPS         = 460;  // UnrealIRCd

    /**
     *  \brief
     *      Returned by the server by numerous commands to
     *      indicate to the client that it didn't supply
     *      enough parameters.
     *
     *  \format{"<command> :Not enough parameters"}
     */
    const ERR_NEEDMOREPARAMS        = 461;

    /**
     *  \brief
     *      Returned by the server to any link which tries to
     *      change part of the registered details (such as
     *      password or user details from second USER message).
     *
     *  \format{":Unauthorized command (already registered)"}
     */
    const ERR_ALREADYREGISTRED      = 462;
    const ERR_ALREADYREGISTERED     = 462;  // InspIRCd

    /**
     *  \brief
     *      Returned to a client which attempts to register
     *      with a server which does not been setup to allow
     *      connections from the host the attempted connection
     *      is tried.
     *
     *  \format{":Your host isn't among the privileged"}
     */
    const ERR_NOPERMFORHOST         = 463;

    /**
     *  \brief
     *      Returned to indicate a failed attempt at registering
     *      a connection for which a password was required and
     *      was either not given or incorrect.
     *
     *  \format{":Password incorrect"}
     */
    const ERR_PASSWDMISMATCH        = 464;

    /**
     *  \brief
     *      Returned after an attempt to connect and register
     *      yourself with a server which has been setup to
     *      explicitly deny connections to you.
     *
     *  \format{":You are banned from this server"}
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
     *  \format{"<channel> :Channel key already set"}
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

    const ERR_LINKSET               = 469;  // UnrealIRCd

    const ERR_LINKCHANNEL           = 470;  // UnrealIRCd

    /**
     *  \brief
     *      Returned when trying to JOIN a channel for which
     *      a limit has been set and reached.
     *
     *  \format{"<channel> :Cannot join channel (+l)"}
     */
    const ERR_CHANNELISFULL         = 471;

    /**
     *  \brief
     *      Returned when trying to set a mode which is not
     *      recognized by the server on a channel.
     *
     *  \format{"<char> :is unknown mode char to me for <channel>"}
     *
     * Sent when the client sets an AWAY message.
     */
    const ERR_UNKNOWNMODE           = 472;

    /**
     *  \brief
     *      Returned when trying to JOIN a channel which requires
     *      an invitation and you've not been invited.
     *
     *  \format{"<channel> :Cannot join channel (+i)"}
     */
    const ERR_INVITEONLYCHAN        = 473;

    /**
     *  \brief
     *      Returned when trying to JOIN a channel from which
     *      you've been banned.
     *
     *  \format{"<channel> :Cannot join channel (+b)"}
     */
    const ERR_BANNEDFROMCHAN        = 474;

    /**
     *  \brief
     *      Returned when trying to JOIN a channel for which
     *      a key was set and was either not given or incorrect.
     *
     *  \format{"<channel> :Cannot join channel (+k)"}
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
     *  \format{"<channel> :Channel doesn't support modes"}
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
     *  \format{"<channel> <char> :Channel list is full"}
     */
    const ERR_BANLISTFULL           = 478;

    /**
     *  \TODO
     */
    const ERR_BADCHANNAME           = 479;

    const ERR_LINKFAIL              = 479;  // UnrealIRCd

    const ERR_CANNOTKNOCK           = 480;  // UnrealIRCd

    const ERR_SERVERONLY            = 480;  // UltimateIRCd

    /**
     *  \brief
     *      Any command requiring operator privileges to operate
     *      will return this error to indicate the attempt was
     *      unsuccessful.
     *
     *  \format{":Permission Denied- You're not an IRC operator"}
     */
    const ERR_NOPRIVILEGES          = 481;

    /**
     *  \brief
     *      Any command requiring 'chanop' privileges (such as
     *      MODE messages) will return this error if the client
     *      making the attempt is not a channel operator on the
     *      specified channel.
     *
     *  \format{"<channel> :You're not channel operator"}
     */
    const ERR_CHANOPRIVSNEEDED      = 482;  // Bahamut
    const ERR_CHANOPPRIVSNEEDED     = 482;

    /**
     *  \brief
     *      Any attempts to use the KILL command on a server
     *      will be refused and this error returned directly
     *      to the client.
     *
     *  \format{":You can't kill a server!"}
     */
    const ERR_CANTKILLSERVER        = 483;

    /**
     *  \brief
     *      Sent by the server to a user upon connection to indicate
     *      the restricted nature of the connection (user mode "+r")
     *
     *  \format{":Your connection is restricted!"}
     */
    const ERR_RESTRICTED            = 484;

    const ERR_DESYNC                = 484;

    /**
     *  \TODO
     */
    const ERR_ISCHANSERVICE         = 484;

    const ERR_ATTACKDENY            = 484;

    /**
     *  \brief
     *      Any MODE requiring "channel creator" privileges will
     *      return this error if the client making the attempt is not
     *      a channel operator on the specified channel.
     *
     *  \format{":You're not the original channel operator"}
     */
    const ERR_UNIQOPPRIVSNEEDED     = 485;

    /**
     *  \TODO
     */
    const ERR_CHANBANREASON         = 485;

    const ERR_KILLDENY              = 485;

    const ERR_SSLCLIENTSONLY        = 486;  // UltimateIRCd

    /**
     *  \TODO
     */
    const ERR_NONONREG              = 487;  // UltimateIRCd

    const ERR_MSGSERVICES           = 487;

    const ERR_NOTFORUSERS           = 487;  // UnrealIRCd

    /**
     *  \TODO
     */
    const ERR_NOSSL                 = 488;
    const ERR_NOTSSLCLIENT          = 488;  // UltimateIRCd

    const ERR_HTMDISABLED           = 488;  // UnrealIRCd

    const ERR_SECUREONLYCHAN        = 489;  // UnrealIRCd

    const ERR_ALLMUSTSSL            = 490;  // InspIRCd

    const ERR_NOSWEAR               = 490;  // UnrealIRCd

    /**
     *  \brief
     *      If a client sends an OPER message and the server
     *      has not been configured to allow connections from
     *      the client's host as an operator, this error will
     *      be returned.
     *
     *  \format{":No O-lines for your host"}
     */
    const ERR_NOOPERHOST            = 491;

    const ERR_NOCTCPALLOWED         = 492;
    const ERR_NOCTCP                = 492;

    /**
     *  \TODO
     */
    const ERR_NOSHAREDCHAN          = 493;

    /**
     *  \TODO
     */
    const ERR_OWNMODE               = 494;

    const ERR_DELAYREJOIN           = 495;

    const ERR_CHANOWNPRIVNEEDED     = 499;  // UnrealIRCd

    const ERR_TOOMANYJOINS          = 500;  // UnrealIRCd

    /**
     *  \brief
     *      Returned by the server to indicate that a MODE
     *      message was sent with a nickname parameter and
     *      that the a mode flag sent was not recognized.
     *
     *  \format{":Unknown MODE flag"}
     */
    const ERR_UMODEUNKNOWNFLAG      = 501;

    const ERR_UNKNOWNSNOMASK        = 501;  // InspIRCd

    /**
     *  \brief
     *      Error sent to any user trying to view or change
     *      the user mode for a user other than themselves.
     *
     *  \format{":Cannot change mode for other users"}
     */
    const ERR_USERSDONTMATCH        = 502;

    /**
     *  \TODO
     */
    const ERR_GHOSTEDCLIENT         = 503;

    /**
     *  \TODO
     */
    const ERR_LAST_ERR_MSG          = 504;

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
    const ERR_NEEDPONG              = 513;  // UnrealIRCd

    /**
     *  \TODO
     */
    const ERR_TOOMANYDCC            = 514;

    const ERR_DISABLED              = 517;  // UnrealIRCd

    const ERR_NOINVITE              = 518;  // UnrealIRCd

    const ERR_ADMONLY               = 519;

    /**
     *  \TODO
     */
    const ERR_CANTJOINOPERSONLY     = 520;
    const ERR_OPERONLY              = 520;  // UnrealIRCd

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

    const ERR_OPERSPVERIFY          = 524;  // UnrealIRCd

    const ERR_CANTSENDTOUSER        = 531;

    const RPL_REAWAY                = 597;  // UnrealIRCd

    const RPL_GONEAWAY              = 598;  // UnrealIRCd

    const RPL_NOTAWAY               = 599;  // UnrealIRCd

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

    const RPL_CLEARWATCH            = 608;  // UnrealIRCd

    const RPL_NOWISAWAY             = 609;  // UnrealIRCd

    const RPL_WHOISSERVICES         = 613;  // UltimateIRCd

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

    const RPL_SETTINGS              = 630;  // UltimateIRCd

    const RPL_ENDOFSETTINGS         = 631;  // UltimateIRCd

    const RPL_IRCOPS                = 632;  // UltimateIRCd

    const RPL_ENDOFIRCOPS           = 633;  // UltimateIRCd

    const RPL_DUMPING               = 640;  // UnrealIRCd

    const RPL_OPERMOTDSTART         = 640;  // UltimateIRCd

    const RPL_DUMPRPL               = 641;  // UnrealIRCd

    const RPL_OPERMOTD              = 641;  // UltimateIRCd

    const RPL_EODUMP                = 642;  // UnrealIRCd

    const RPL_ENDOFOPERMOTD         = 642;  // UltimateIRCd

#    const RPL_MAPMORE               = 650;  // UltimateIRCd

    const RPL_SPAMCMDFWD            = 659;  // UnrealIRCd

    /**
     *  \brief
     *      Returned to a client after a STARTTLS command to indicate
     *      that the server is ready to proceed with data encrypted
     *      using the SSL/TLS protocol.
     *
     *  \format{":STARTTLS successful\, go ahead with TLS handshake"}
     *
     *  \note
     *      Upon receiving this message, the client should proceed
     *      with a TLS handshake. Once the handshake is completed,
     *      data may be exchanged securely between the server and
     *      the client.
     */
    const RPL_STARTTLSOK            = 670;
    const RPL_STARTTLS              = 670;

    const RPL_WHOISSECURE           = 671;  // UnrealIRCd

    const RPL_WHOHOST               = 671;  // UltimateIRCd

    /**
     *  \brief
     *      Returned to a client after STARTTLS command to indicate
     *      that the attempt to negotiate a secure channel for the
     *      communication to take place has failed.
     *
     *  \format{":STARTTLS failure"}
     *
     *  \note
     *      Upon receiving this message, the client may proceed with
     *      the communication (even though data will be exchanged in
     *      plain text), or it may choose to close the connection
     *      entirely.
     */
    const ERR_STARTTLSFAIL          = 691;
    const ERR_STARTTLS              = 691;

    const ERR_EXEMPTLISTFULL        = 700;  // UltimateIRCd

    const RPL_COMMANDS              = 702;  // InspIRCd

    const RPL_COMMANDSEND           = 703;  // InspIRCd

    const ERR_WORDFILTERED          = 936;  // InspIRCd

    const ERR_CANTUNLOADMODULE      = 972;  // InspIRCd

    const ERR_CANNOTDOCOMMAND       = 972;  // UnrealIRCd

    const RPL_UNLOADEDMODULE        = 973;  // InspIRCd

    const ERR_CANTLOADMODULE        = 974;  // InspIRCd

    const ERR_CANNOTCHANGECHANMODE  = 974;  // UnrealIRCd

    const RPL_LOADEDMODULE          = 975;  // InspIRCd

    const ERR_NUMERIC_ERR           = 999;
    const ERR_NUMERICERR            = 999;

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

