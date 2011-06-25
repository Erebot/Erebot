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

interface   Erebot_Interface_RawProfile_RFC1459
extends     Erebot_Interface_RawProfile
{
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
    const RPL_STATSILINE            = 215;

    /**
     *  \TODO
     */
    const RPL_STATSKLINE            = 216;

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
     *      only Erebot_Interface_Event_Raw_RFC1459::RPL_LISTEND
     *      will be sent.
     */
    const RPL_LISTEND               = 323;

    /**
     *  \TODO
     */
    const RPL_CHANNELMODEIS         = 324;

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
     * Alias for Erebot_Interface_Event_Raw::RPL_NAMEREPLY,
     * which is often mispelled in documentation.
     */
    const RPL_NAMREPLY              = 353;
    const RPL_NAMEREPLY             = 353;

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
     *      PING or PONG message missing the originator parameter.
     *
     *  \format{":No origin specified"}
     */
    const ERR_NOORIGIN              = 409;

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
    const ERR_ALREADYREGISTERED     = 462;

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
     *      Sent when attempting to set a key for a channel
     *      which already has one.
     *
     *  \format{"<channel> :Channel key already set"}
     */
    const ERR_KEYSET                = 467;

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
    const ERR_CHANOPRIVSNEEDED      = 482;
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
     *      If a client sends an OPER message and the server
     *      has not been configured to allow connections from
     *      the client's host as an operator, this error will
     *      be returned.
     *
     *  \format{":No O-lines for your host"}
     */
    const ERR_NOOPERHOST            = 491;

    /**
     *  \brief
     *      Returned by the server to indicate that a MODE
     *      message was sent with a nickname parameter and
     *      that the a mode flag sent was not recognized.
     *
     *  \format{":Unknown MODE flag"}
     */
    const ERR_UMODEUNKNOWNFLAG      = 501;

    /**
     *  \brief
     *      Error sent to any user trying to view or change
     *      the user mode for a user other than themselves.
     *
     *  \format{":Cannot change mode for other users"}
     */
    const ERR_USERSDONTMATCH        = 502;

}
