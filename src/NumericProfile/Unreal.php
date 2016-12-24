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
 *      Numeric profile for UnrealIRCd-based IRC servers.
 */
class Unreal extends \Erebot\NumericProfile\Base
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

    /// \copydoc Erebot::Interfaces::Numerics::RPL_REDIR
    const RPL_REDIR                     =  10;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_REMOTEISUPPORT
    const RPL_REMOTEISUPPORT            = 105;

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

    /// \copydoc Erebot::Interfaces::Numerics::ERR_UNKNOWNCOMMAND
    const ERR_UNKNOWNCOMMAND            = 421;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOMOTD
    const ERR_NOMOTD                    = 422;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOADMININFO
    const ERR_NOADMININFO               = 423;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_FILEERROR
    const ERR_FILEERROR                 = 424;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOOPERMOTD
    const ERR_NOOPERMOTD                = 425;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_TOOMANYAWAY
    const ERR_TOOMANYAWAY               = 429;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NONICKNAMEGIVEN
    const ERR_NONICKNAMEGIVEN           = 431;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_ERRONEUSNICKNAME
    const ERR_ERRONEUSNICKNAME          = 432;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NICKNAMEINUSE
    const ERR_NICKNAMEINUSE             = 433;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NORULES
    const ERR_NORULES                   = 434;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_SERVICECONFUSED
    const ERR_SERVICECONFUSED           = 435;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NICKCOLLISION
    const ERR_NICKCOLLISION             = 436;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_BANNICKCHANGE
    const ERR_BANNICKCHANGE             = 437;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NCHANGETOOFAST
    const ERR_NCHANGETOOFAST            = 438;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_TARGETTOOFAST
    const ERR_TARGETTOOFAST             = 439;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_SERVICESDOWN
    const ERR_SERVICESDOWN              = 440;

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

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NONICKCHANGE
    const ERR_NONICKCHANGE              = 447;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOTREGISTERED
    const ERR_NOTREGISTERED             = 451;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_HOSTILENAME
    const ERR_HOSTILENAME               = 455;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOHIDING
    const ERR_NOHIDING                  = 459;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOTFORHALFOPS
    const ERR_NOTFORHALFOPS             = 460;

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

    /// \copydoc Erebot::Interfaces::Numerics::ERR_ONLYSERVERSCANCHANGE
    const ERR_ONLYSERVERSCANCHANGE      = 468;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_LINKSET
    const ERR_LINKSET                   = 469;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_LINKCHANNEL
    const ERR_LINKCHANNEL               = 470;

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

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NEEDREGGEDNICK
    const ERR_NEEDREGGEDNICK            = 477;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_BANLISTFULL
    const ERR_BANLISTFULL               = 478;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_LINKFAIL
    const ERR_LINKFAIL                  = 479;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_CANNOTKNOCK
    const ERR_CANNOTKNOCK               = 480;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOPRIVILEGES
    const ERR_NOPRIVILEGES              = 481;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_CHANOPRIVSNEEDED
    const ERR_CHANOPRIVSNEEDED          = 482;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_CANTKILLSERVER
    const ERR_CANTKILLSERVER            = 483;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_ATTACKDENY
    const ERR_ATTACKDENY                = 484;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_KILLDENY
    const ERR_KILLDENY                  = 485;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NONONREG
    const ERR_NONONREG                  = 486;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOTFORUSERS
    const ERR_NOTFORUSERS               = 487;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_HTMDISABLED
    const ERR_HTMDISABLED               = 488;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_SECUREONLYCHAN
    const ERR_SECUREONLYCHAN            = 489;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOSWEAR
    const ERR_NOSWEAR                   = 490;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOOPERHOST
    const ERR_NOOPERHOST                = 491;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOCTCP
    const ERR_NOCTCP                    = 492;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_CHANOWNPRIVNEEDED
    const ERR_CHANOWNPRIVNEEDED         = 499;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_TOOMANYJOINS
    const ERR_TOOMANYJOINS              = 500;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_UMODEUNKNOWNFLAG
    const ERR_UMODEUNKNOWNFLAG          = 501;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_USERSDONTMATCH
    const ERR_USERSDONTMATCH            = 502;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_SILELISTFULL
    const ERR_SILELISTFULL              = 511;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_TOOMANYWATCH
    const ERR_TOOMANYWATCH              = 512;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NEEDPONG
    const ERR_NEEDPONG                  = 513;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_TOOMANYDCC
    const ERR_TOOMANYDCC                = 514;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_DISABLED
    const ERR_DISABLED                  = 517;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOINVITE
    const ERR_NOINVITE                  = 518;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_ADMONLY
    const ERR_ADMONLY                   = 519;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_OPERONLY
    const ERR_OPERONLY                  = 520;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_LISTSYNTAX
    const ERR_LISTSYNTAX                = 521;

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

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOISREGNICK
    const RPL_WHOISREGNICK              = 307;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_RULESSTART
    const RPL_RULESSTART                = 308;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFRULES
    const RPL_ENDOFRULES                = 309;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOISHELPOP
    const RPL_WHOISHELPOP               = 310;

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

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOISSPECIAL
    const RPL_WHOISSPECIAL              = 320;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LISTSTART
    const RPL_LISTSTART                 = 321;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LIST
    const RPL_LIST                      = 322;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LISTEND
    const RPL_LISTEND                   = 323;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_CHANNELMODEIS
    const RPL_CHANNELMODEIS             = 324;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_CREATIONTIME
    const RPL_CREATIONTIME              = 329;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_NOTOPIC
    const RPL_NOTOPIC                   = 331;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TOPIC
    const RPL_TOPIC                     = 332;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TOPICWHOTIME
    const RPL_TOPICWHOTIME              = 333;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_INVITELIST
    const RPL_INVITELIST                = 336;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFINVITELIST
    const RPL_ENDOFINVITELIST           = 337;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LISTSYNTAX
    const RPL_LISTSYNTAX                = 334;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOISBOT
    const RPL_WHOISBOT                  = 335;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_USERIP
    const RPL_USERIP                    = 340;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_INVITING
    const RPL_INVITING                  = 341;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_SUMMONING
    const RPL_SUMMONING                 = 342;

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

    /// \copydoc Erebot::Interfaces::Numerics::RPL_INVEXLIST
    const RPL_INVEXLIST                 = 346;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFINVEXLIST
    const RPL_ENDOFINVEXLIST            = 347;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_EXLIST
    const RPL_EXLIST                    = 348;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFEXLIST
    const RPL_ENDOFEXLIST               = 349;

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

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOISHOST
    const RPL_WHOISHOST                 = 378;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOISMODES
    const RPL_WHOISMODES                = 379;

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

    /// \copydoc Erebot::Interfaces::Numerics::RPL_QLIST
    const RPL_QLIST                     = 386;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFQLIST
    const RPL_ENDOFQLIST                = 387;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ALIST
    const RPL_ALIST                     = 388;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFALIST
    const RPL_ENDOFALIST                = 389;

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

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSHELP
    const RPL_STATSHELP                 = 210;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSLINKINFO
    const RPL_STATSLINKINFO             = 211;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSCOMMANDS
    const RPL_STATSCOMMANDS             = 212;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSCLINE
    const RPL_STATSCLINE                = 213;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSOLDNLINE
    const RPL_STATSOLDNLINE             = 214;

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

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSBLINE
    const RPL_STATSBLINE                = 220;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_UMODEIS
    const RPL_UMODEIS                   = 221;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_SQLINE_NICK
    const RPL_SQLINE_NICK               = 222;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSGLINE
    const RPL_STATSGLINE                = 223;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSTLINE
    const RPL_STATSTLINE                = 224;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSELINE
    const RPL_STATSELINE                = 225;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSNLINE
    const RPL_STATSNLINE                = 226;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSVLINE
    const RPL_STATSVLINE                = 227;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSBANVER
    const RPL_STATSBANVER               = 228;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSSPAMF
    const RPL_STATSSPAMF                = 229;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSEXCEPTTKL
    const RPL_STATSEXCEPTTKL            = 230;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_SERVICEINFO
    const RPL_SERVICEINFO               = 231;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_RULES
    const RPL_RULES                     = 232;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_SERVICE
    const RPL_SERVICE                   = 233;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_SERVLIST
    const RPL_SERVLIST                  = 234;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_SERVLISTEND
    const RPL_SERVLISTEND               = 235;

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

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSXLINE
    const RPL_STATSXLINE                = 247;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSULINE
    const RPL_STATSULINE                = 248;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSDEBUG
    const RPL_STATSDEBUG                = 249;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSCONN
    const RPL_STATSCONN                 = 250;

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

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TRACELOG
    const RPL_TRACELOG                  = 261;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LOCALUSERS
    const RPL_LOCALUSERS                = 265;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_GLOBALUSERS
    const RPL_GLOBALUSERS               = 266;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_SILELIST
    const RPL_SILELIST                  = 271;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFSILELIST
    const RPL_ENDOFSILELIST             = 272;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSDLINE
    const RPL_STATSDLINE                = 275;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_HELPHDR
    const RPL_HELPHDR                   = 290;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_HELPOP
    const RPL_HELPOP                    = 291;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_HELPTLR
    const RPL_HELPTLR                   = 292;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_HELPHLP
    const RPL_HELPHLP                   = 293;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_HELPFWD
    const RPL_HELPFWD                   = 294;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_HELPIGN
    const RPL_HELPIGN                   = 295;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_MAP
    const RPL_MAP                       =   6;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_MAPMORE
    const RPL_MAPMORE                   = 610;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_MAPEND
    const RPL_MAPEND                    =   7;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_WHOSYNTAX
    const ERR_WHOSYNTAX                 = 522;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_WHOLIMEXCEED
    const ERR_WHOLIMEXCEED              = 523;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_OPERSPVERIFY
    const ERR_OPERSPVERIFY              = 524;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_SNOMASK
    const RPL_SNOMASK                   =   8;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_REAWAY
    const RPL_REAWAY                    = 597;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_GONEAWAY
    const RPL_GONEAWAY                  = 598;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_NOTAWAY
    const RPL_NOTAWAY                   = 599;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LOGON
    const RPL_LOGON                     = 600;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LOGOFF
    const RPL_LOGOFF                    = 601;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WATCHOFF
    const RPL_WATCHOFF                  = 602;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WATCHSTAT
    const RPL_WATCHSTAT                 = 603;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_NOWON
    const RPL_NOWON                     = 604;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_NOWOFF
    const RPL_NOWOFF                    = 605;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WATCHLIST
    const RPL_WATCHLIST                 = 606;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFWATCHLIST
    const RPL_ENDOFWATCHLIST            = 607;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_CLEARWATCH
    const RPL_CLEARWATCH                = 608;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_NOWISAWAY
    const RPL_NOWISAWAY                 = 609;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_DCCSTATUS
    const RPL_DCCSTATUS                 = 617;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_DCCLIST
    const RPL_DCCLIST                   = 618;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFDCCLIST
    const RPL_ENDOFDCCLIST              = 619;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_DCCINFO
    const RPL_DCCINFO                   = 620;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_DUMPING
    const RPL_DUMPING                   = 640;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_DUMPRPL
    const RPL_DUMPRPL                   = 641;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_EODUMP
    const RPL_EODUMP                    = 642;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_SPAMCMDFWD
    const RPL_SPAMCMDFWD                = 659;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STARTTLS
    const RPL_STARTTLS                  = 670;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOISSECURE
    const RPL_WHOISSECURE               = 671;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_CANNOTDOCOMMAND
    const ERR_CANNOTDOCOMMAND           = 972;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_CANNOTCHANGECHANMODE
    const ERR_CANNOTCHANGECHANMODE      = 974;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_STARTTLS
    const ERR_STARTTLS                  = 691;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NUMERICERR
    const ERR_NUMERICERR                = 999;
}
