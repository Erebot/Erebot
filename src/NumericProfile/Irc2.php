<?php
/*
    This file is part of Erebot, a modular IRC bot written in PHP.

    Copyright © 2010 François Poirotte

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

namespace Erebot\NumericProfile;

/**
 * \brief
 *      Numeric profile for irc2-based IRC servers.
 */
class Irc2 extends \Erebot\NumericProfile\Base
{
    /// \copydoc Erebot::Interfaces::Numerics::RPL_WELCOME
    const RPL_WELCOME                   =   1;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_YOURHOST
    const RPL_YOURHOST                  =   2;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_CREATED
    const RPL_CREATED                   =   3;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_MYINFO
    const RPL_MYINFO                    =   4;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ISUPPORT
    const RPL_ISUPPORT                  =   5;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_BOUNCE
    const RPL_BOUNCE                    =  10;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_MAP
    const RPL_MAP                       =  15;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_MAPEND
    const RPL_MAPEND                    =  17;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_MAPSTART
    const RPL_MAPSTART                  =  18;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_HELLO
    const RPL_HELLO                     =  20;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_YOURID
    const RPL_YOURID                    =  42;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_SAVENICK
    const RPL_SAVENICK                  =  43;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOSUCHNICK
    const ERR_NOSUCHNICK                = 401;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOSUCHSERVER
    const ERR_NOSUCHSERVER              = 402;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOSUCHCHANNEL
    const ERR_NOSUCHCHANNEL             = 403;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_CANNOTSENDTOCHAN
    const ERR_CANNOTSENDTOCHAN          = 404;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_TOOMANYCHANNELS
    const ERR_TOOMANYCHANNELS           = 405;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_WASNOSUCHNICK
    const ERR_WASNOSUCHNICK             = 406;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_TOOMANYTARGETS
    const ERR_TOOMANYTARGETS            = 407;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOSUCHSERVICE
    const ERR_NOSUCHSERVICE             = 408;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOORIGIN
    const ERR_NOORIGIN                  = 409;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NORECIPIENT
    const ERR_NORECIPIENT               = 411;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOTEXTTOSEND
    const ERR_NOTEXTTOSEND              = 412;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOTOPLEVEL
    const ERR_NOTOPLEVEL                = 413;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_WILDTOPLEVEL
    const ERR_WILDTOPLEVEL              = 414;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_BADMASK
    const ERR_BADMASK                   = 415;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_TOOMANYMATCHES
    const ERR_TOOMANYMATCHES            = 416;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_UNKNOWNCOMMAND
    const ERR_UNKNOWNCOMMAND            = 421;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOMOTD
    const ERR_NOMOTD                    = 422;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOADMININFO
    const ERR_NOADMININFO               = 423;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_FILEERROR
    const ERR_FILEERROR                 = 424;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NONICKNAMEGIVEN
    const ERR_NONICKNAMEGIVEN           = 431;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_ERRONEOUSNICKNAME
    const ERR_ERRONEOUSNICKNAME         = 432;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NICKNAMEINUSE
    const ERR_NICKNAMEINUSE             = 433;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_SERVICENAMEINUSE
    const ERR_SERVICENAMEINUSE          = 434;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_SERVICECONFUSED
    const ERR_SERVICECONFUSED           = 435;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NICKCOLLISION
    const ERR_NICKCOLLISION             = 436;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_UNAVAILRESOURCE
    const ERR_UNAVAILRESOURCE           = 437;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_USERNOTINCHANNEL
    const ERR_USERNOTINCHANNEL          = 441;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOTONCHANNEL
    const ERR_NOTONCHANNEL              = 442;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_USERONCHANNEL
    const ERR_USERONCHANNEL             = 443;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOLOGIN
    const ERR_NOLOGIN                   = 444;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_SUMMONDISABLED
    const ERR_SUMMONDISABLED            = 445;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_USERSDISABLED
    const ERR_USERSDISABLED             = 446;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOTREGISTERED
    const ERR_NOTREGISTERED             = 451;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NEEDMOREPARAMS
    const ERR_NEEDMOREPARAMS            = 461;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_ALREADYREGISTRED
    const ERR_ALREADYREGISTRED          = 462;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOPERMFORHOST
    const ERR_NOPERMFORHOST             = 463;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_PASSWDMISMATCH
    const ERR_PASSWDMISMATCH            = 464;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_YOUREBANNEDCREEP
    const ERR_YOUREBANNEDCREEP          = 465;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_YOUWILLBEBANNED
    const ERR_YOUWILLBEBANNED           = 466;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_KEYSET
    const ERR_KEYSET                    = 467;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_CHANNELISFULL
    const ERR_CHANNELISFULL             = 471;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_UNKNOWNMODE
    const ERR_UNKNOWNMODE               = 472;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_INVITEONLYCHAN
    const ERR_INVITEONLYCHAN            = 473;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_BANNEDFROMCHAN
    const ERR_BANNEDFROMCHAN            = 474;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_BADCHANNELKEY
    const ERR_BADCHANNELKEY             = 475;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_BADCHANMASK
    const ERR_BADCHANMASK               = 476;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOCHANMODES
    const ERR_NOCHANMODES               = 477;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_BANLISTFULL
    const ERR_BANLISTFULL               = 478;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOPRIVILEGES
    const ERR_NOPRIVILEGES              = 481;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_CHANOPRIVSNEEDED
    const ERR_CHANOPRIVSNEEDED          = 482;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_CANTKILLSERVER
    const ERR_CANTKILLSERVER            = 483;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_RESTRICTED
    const ERR_RESTRICTED                = 484;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_UNIQOPRIVSNEEDED
    const ERR_UNIQOPRIVSNEEDED          = 485;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOOPERHOST
    const ERR_NOOPERHOST                = 491;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOSERVICEHOST
    const ERR_NOSERVICEHOST             = 492;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_STATSKLINE
    const ERR_STATSKLINE                = 499;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_UMODEUNKNOWNFLAG
    const ERR_UMODEUNKNOWNFLAG          = 501;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_USERSDONTMATCH
    const ERR_USERSDONTMATCH            = 502;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_NONE
    const RPL_NONE                      = 300;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_AWAY
    const RPL_AWAY                      = 301;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_USERHOST
    const RPL_USERHOST                  = 302;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ISON
    const RPL_ISON                      = 303;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TEXT
    const RPL_TEXT                      = 304;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_UNAWAY
    const RPL_UNAWAY                    = 305;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_NOWAWAY
    const RPL_NOWAWAY                   = 306;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOISUSER
    const RPL_WHOISUSER                 = 311;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOISSERVER
    const RPL_WHOISSERVER               = 312;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOISOPERATOR
    const RPL_WHOISOPERATOR             = 313;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOWASUSER
    const RPL_WHOWASUSER                = 314;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFWHOWAS
    const RPL_ENDOFWHOWAS               = 369;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOISCHANOP
    const RPL_WHOISCHANOP               = 316;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOISIDLE
    const RPL_WHOISIDLE                 = 317;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFWHOIS
    const RPL_ENDOFWHOIS                = 318;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOISCHANNELS
    const RPL_WHOISCHANNELS             = 319;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LISTSTART
    const RPL_LISTSTART                 = 321;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LIST
    const RPL_LIST                      = 322;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LISTEND
    const RPL_LISTEND                   = 323;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_CHANNELMODEIS
    const RPL_CHANNELMODEIS             = 324;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_UNIQOPIS
    const RPL_UNIQOPIS                  = 325;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_NOTOPIC
    const RPL_NOTOPIC                   = 331;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TOPIC
    const RPL_TOPIC                     = 332;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TOPIC_WHO_TIME
    const RPL_TOPIC_WHO_TIME            = 333;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_INVITING
    const RPL_INVITING                  = 341;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_SUMMONING
    const RPL_SUMMONING                 = 342;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_REOPLIST
    const RPL_REOPLIST                  = 344;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFREOPLIST
    const RPL_ENDOFREOPLIST             = 345;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_INVITELIST
    const RPL_INVITELIST                = 346;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFINVITELIST
    const RPL_ENDOFINVITELIST           = 347;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_EXCEPTLIST
    const RPL_EXCEPTLIST                = 348;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFEXCEPTLIST
    const RPL_ENDOFEXCEPTLIST           = 349;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_VERSION
    const RPL_VERSION                   = 351;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOREPLY
    const RPL_WHOREPLY                  = 352;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFWHO
    const RPL_ENDOFWHO                  = 315;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_NAMREPLY
    const RPL_NAMREPLY                  = 353;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFNAMES
    const RPL_ENDOFNAMES                = 366;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_KILLDONE
    const RPL_KILLDONE                  = 361;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_CLOSING
    const RPL_CLOSING                   = 362;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_CLOSEEND
    const RPL_CLOSEEND                  = 363;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LINKS
    const RPL_LINKS                     = 364;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFLINKS
    const RPL_ENDOFLINKS                = 365;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_BANLIST
    const RPL_BANLIST                   = 367;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFBANLIST
    const RPL_ENDOFBANLIST              = 368;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_INFO
    const RPL_INFO                      = 371;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_MOTD
    const RPL_MOTD                      = 372;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_INFOSTART
    const RPL_INFOSTART                 = 373;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFINFO
    const RPL_ENDOFINFO                 = 374;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_MOTDSTART
    const RPL_MOTDSTART                 = 375;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFMOTD
    const RPL_ENDOFMOTD                 = 376;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_YOUREOPER
    const RPL_YOUREOPER                 = 381;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_REHASHING
    const RPL_REHASHING                 = 382;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_YOURESERVICE
    const RPL_YOURESERVICE              = 383;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_MYPORTIS
    const RPL_MYPORTIS                  = 384;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_NOTOPERANYMORE
    const RPL_NOTOPERANYMORE            = 385;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TIME
    const RPL_TIME                      = 391;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_USERSSTART
    const RPL_USERSSTART                = 392;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_USERS
    const RPL_USERS                     = 393;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFUSERS
    const RPL_ENDOFUSERS                = 394;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_NOUSERS
    const RPL_NOUSERS                   = 395;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TRACELINK
    const RPL_TRACELINK                 = 200;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TRACECONNECTING
    const RPL_TRACECONNECTING           = 201;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TRACEHANDSHAKE
    const RPL_TRACEHANDSHAKE            = 202;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TRACEUNKNOWN
    const RPL_TRACEUNKNOWN              = 203;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TRACEOPERATOR
    const RPL_TRACEOPERATOR             = 204;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TRACEUSER
    const RPL_TRACEUSER                 = 205;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TRACESERVER
    const RPL_TRACESERVER               = 206;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TRACESERVICE
    const RPL_TRACESERVICE              = 207;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TRACENEWTYPE
    const RPL_TRACENEWTYPE              = 208;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TRACECLASS
    const RPL_TRACECLASS                = 209;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSLINKINFO
    const RPL_STATSLINKINFO             = 211;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSCOMMANDS
    const RPL_STATSCOMMANDS             = 212;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSCLINE
    const RPL_STATSCLINE                = 213;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSNLINE
    const RPL_STATSNLINE                = 214;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSILINE
    const RPL_STATSILINE                = 215;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSKLINE
    const RPL_STATSKLINE                = 216;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSQLINE
    const RPL_STATSQLINE                = 217;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSYLINE
    const RPL_STATSYLINE                = 218;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFSTATS
    const RPL_ENDOFSTATS                = 219;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_UMODEIS
    const RPL_UMODEIS                   = 221;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_SERVICEINFO
    const RPL_SERVICEINFO               = 231;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFSERVICES
    const RPL_ENDOFSERVICES             = 232;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_SERVICE
    const RPL_SERVICE                   = 233;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_SERVLIST
    const RPL_SERVLIST                  = 234;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_SERVLISTEND
    const RPL_SERVLISTEND               = 235;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSIAUTH
    const RPL_STATSIAUTH                = 239;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSVLINE
    const RPL_STATSVLINE                = 240;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSLLINE
    const RPL_STATSLLINE                = 241;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSUPTIME
    const RPL_STATSUPTIME               = 242;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSOLINE
    const RPL_STATSOLINE                = 243;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSHLINE
    const RPL_STATSHLINE                = 244;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSSLINE
    const RPL_STATSSLINE                = 245;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSPING
    const RPL_STATSPING                 = 246;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSBLINE
    const RPL_STATSBLINE                = 247;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSDEFINE
    const RPL_STATSDEFINE               = 248;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSDEBUG
    const RPL_STATSDEBUG                = 249;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSDLINE
    const RPL_STATSDLINE                = 250;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LUSERCLIENT
    const RPL_LUSERCLIENT               = 251;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LUSEROP
    const RPL_LUSEROP                   = 252;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LUSERUNKNOWN
    const RPL_LUSERUNKNOWN              = 253;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LUSERCHANNELS
    const RPL_LUSERCHANNELS             = 254;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LUSERME
    const RPL_LUSERME                   = 255;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ADMINME
    const RPL_ADMINME                   = 256;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ADMINLOC1
    const RPL_ADMINLOC1                 = 257;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ADMINLOC2
    const RPL_ADMINLOC2                 = 258;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ADMINEMAIL
    const RPL_ADMINEMAIL                = 259;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TRACEEND
    const RPL_TRACEEND                  = 262;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TRYAGAIN
    const RPL_TRYAGAIN                  = 263;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LOCALUSERS
    const RPL_LOCALUSERS                = 265;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_GLOBALUSERS
    const RPL_GLOBALUSERS               = 266;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ETRACEFULL
    const RPL_ETRACEFULL                = 708;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ETRACEEND
    const RPL_ETRACEEND                 = 759;
}
