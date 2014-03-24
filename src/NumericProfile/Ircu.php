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
 *      Numeric profile for ircu-based IRC servers.
 */
class Ircu extends \Erebot\NumericProfile\Base
{
    /// \copydoc Erebot::Interfaces::Numerics::RPL_WELCOME.
    const RPL_WELCOME                   =   1;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_YOURHOST.
    const RPL_YOURHOST                  =   2;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_CREATED.
    const RPL_CREATED                   =   3;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_MYINFO.
    const RPL_MYINFO                    =   4;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ISUPPORT.
    const RPL_ISUPPORT                  =   5;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_SNOMASK.
    const RPL_SNOMASK                   =   8;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_MAP.
    const RPL_MAP                       =  15;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_MAPMORE.
    const RPL_MAPMORE                   =  16;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_MAPEND.
    const RPL_MAPEND                    =  17;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_APASSWARN_SET.
    const RPL_APASSWARN_SET             =  30;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_APASSWARN_SECRET.
    const RPL_APASSWARN_SECRET          =  31;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_APASSWARN_CLEAR.
    const RPL_APASSWARN_CLEAR           =  32;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TRACELINK.
    const RPL_TRACELINK                 = 200;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TRACECONNECTING.
    const RPL_TRACECONNECTING           = 201;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TRACEHANDSHAKE.
    const RPL_TRACEHANDSHAKE            = 202;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TRACEUNKNOWN.
    const RPL_TRACEUNKNOWN              = 203;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TRACEOPERATOR.
    const RPL_TRACEOPERATOR             = 204;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TRACEUSER.
    const RPL_TRACEUSER                 = 205;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TRACESERVER.
    const RPL_TRACESERVER               = 206;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TRACENEWTYPE.
    const RPL_TRACENEWTYPE              = 208;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TRACECLASS.
    const RPL_TRACECLASS                = 209;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSLINKINFO.
    const RPL_STATSLINKINFO             = 211;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSCOMMANDS.
    const RPL_STATSCOMMANDS             = 212;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSCLINE.
    const RPL_STATSCLINE                = 213;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSNLINE.
    const RPL_STATSNLINE                = 214;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSILINE.
    const RPL_STATSILINE                = 215;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSKLINE.
    const RPL_STATSKLINE                = 216;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSPLINE.
    const RPL_STATSPLINE                = 217;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSYLINE.
    const RPL_STATSYLINE                = 218;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFSTATS.
    const RPL_ENDOFSTATS                = 219;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_UMODEIS.
    const RPL_UMODEIS                   = 221;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSJLINE.
    const RPL_STATSJLINE                = 222;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSALINE.
    const RPL_STATSALINE                = 226;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSQLINE.
    const RPL_STATSQLINE                = 228;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSVERBOSE.
    const RPL_STATSVERBOSE              = 236;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSENGINE.
    const RPL_STATSENGINE               = 237;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSFLINE.
    const RPL_STATSFLINE                = 238;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSLLINE.
    const RPL_STATSLLINE                = 241;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSUPTIME.
    const RPL_STATSUPTIME               = 242;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSOLINE.
    const RPL_STATSOLINE                = 243;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSHLINE.
    const RPL_STATSHLINE                = 244;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSTLINE.
    const RPL_STATSTLINE                = 246;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSGLINE.
    const RPL_STATSGLINE                = 247;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSULINE.
    const RPL_STATSULINE                = 248;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSDEBUG.
    const RPL_STATSDEBUG                = 249;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSCONN.
    const RPL_STATSCONN                 = 250;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LUSERCLIENT.
    const RPL_LUSERCLIENT               = 251;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LUSEROP.
    const RPL_LUSEROP                   = 252;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LUSERUNKNOWN.
    const RPL_LUSERUNKNOWN              = 253;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LUSERCHANNELS.
    const RPL_LUSERCHANNELS             = 254;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LUSERME.
    const RPL_LUSERME                   = 255;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ADMINME.
    const RPL_ADMINME                   = 256;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ADMINLOC1.
    const RPL_ADMINLOC1                 = 257;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ADMINLOC2.
    const RPL_ADMINLOC2                 = 258;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ADMINEMAIL.
    const RPL_ADMINEMAIL                = 259;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TRACEEND.
    const RPL_TRACEEND                  = 262;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_PRIVS.
    const RPL_PRIVS                     = 270;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_SILELIST.
    const RPL_SILELIST                  = 271;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFSILELIST.
    const RPL_ENDOFSILELIST             = 272;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSDLINE.
    const RPL_STATSDLINE                = 275;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_STATSRLINE.
    const RPL_STATSRLINE                = 276;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_GLIST.
    const RPL_GLIST                     = 280;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFGLIST.
    const RPL_ENDOFGLIST                = 281;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_JUPELIST.
    const RPL_JUPELIST                  = 282;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFJUPELIST.
    const RPL_ENDOFJUPELIST             = 283;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_FEATURE.
    const RPL_FEATURE                   = 284;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_AWAY.
    const RPL_AWAY                      = 301;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_USERHOST.
    const RPL_USERHOST                  = 302;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ISON.
    const RPL_ISON                      = 303;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_UNAWAY.
    const RPL_UNAWAY                    = 305;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_NOWAWAY.
    const RPL_NOWAWAY                   = 306;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOISUSER.
    const RPL_WHOISUSER                 = 311;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOISSERVER.
    const RPL_WHOISSERVER               = 312;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOISOPERATOR.
    const RPL_WHOISOPERATOR             = 313;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOWASUSER.
    const RPL_WHOWASUSER                = 314;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFWHO.
    const RPL_ENDOFWHO                  = 315;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOISIDLE.
    const RPL_WHOISIDLE                 = 317;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFWHOIS.
    const RPL_ENDOFWHOIS                = 318;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOISCHANNELS.
    const RPL_WHOISCHANNELS             = 319;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LISTSTART.
    const RPL_LISTSTART                 = 321;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LIST.
    const RPL_LIST                      = 322;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LISTEND.
    const RPL_LISTEND                   = 323;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_CHANNELMODEIS.
    const RPL_CHANNELMODEIS             = 324;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_CREATIONTIME.
    const RPL_CREATIONTIME              = 329;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOISACCOUNT.
    const RPL_WHOISACCOUNT              = 330;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_NOTOPIC.
    const RPL_NOTOPIC                   = 331;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TOPIC.
    const RPL_TOPIC                     = 332;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TOPICWHOTIME.
    const RPL_TOPICWHOTIME              = 333;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LISTUSAGE.
    const RPL_LISTUSAGE                 = 334;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOISACTUALLY.
    const RPL_WHOISACTUALLY             = 338;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_USERIP.
    const RPL_USERIP                    = 340;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_INVITING.
    const RPL_INVITING                  = 341;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ISSUEDINVITE.
    const RPL_ISSUEDINVITE              = 345;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_INVITELIST.
    const RPL_INVITELIST                = 346;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFINVITELIST.
    const RPL_ENDOFINVITELIST           = 347;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_VERSION.
    const RPL_VERSION                   = 351;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOREPLY.
    const RPL_WHOREPLY                  = 352;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_NAMREPLY.
    const RPL_NAMREPLY                  = 353;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_WHOSPCRPL.
    const RPL_WHOSPCRPL                 = 354;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_DELNAMREPLY.
    const RPL_DELNAMREPLY               = 355;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_CLOSING.
    const RPL_CLOSING                   = 362;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_CLOSEEND.
    const RPL_CLOSEEND                  = 363;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LINKS.
    const RPL_LINKS                     = 364;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFLINKS.
    const RPL_ENDOFLINKS                = 365;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFNAMES.
    const RPL_ENDOFNAMES                = 366;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_BANLIST.
    const RPL_BANLIST                   = 367;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFBANLIST.
    const RPL_ENDOFBANLIST              = 368;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFWHOWAS.
    const RPL_ENDOFWHOWAS               = 369;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_INFO.
    const RPL_INFO                      = 371;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_MOTD.
    const RPL_MOTD                      = 372;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFINFO.
    const RPL_ENDOFINFO                 = 374;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_MOTDSTART.
    const RPL_MOTDSTART                 = 375;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFMOTD.
    const RPL_ENDOFMOTD                 = 376;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_YOUREOPER.
    const RPL_YOUREOPER                 = 381;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_REHASHING.
    const RPL_REHASHING                 = 382;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TIME.
    const RPL_TIME                      = 391;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_HOSTHIDDEN.
    const RPL_HOSTHIDDEN                = 396;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOSUCHNICK.
    const ERR_NOSUCHNICK                = 401;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOSUCHSERVER.
    const ERR_NOSUCHSERVER              = 402;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOSUCHCHANNEL.
    const ERR_NOSUCHCHANNEL             = 403;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_CANNOTSENDTOCHAN.
    const ERR_CANNOTSENDTOCHAN          = 404;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_TOOMANYCHANNELS.
    const ERR_TOOMANYCHANNELS           = 405;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_WASNOSUCHNICK.
    const ERR_WASNOSUCHNICK             = 406;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_TOOMANYTARGETS.
    const ERR_TOOMANYTARGETS            = 407;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOORIGIN.
    const ERR_NOORIGIN                  = 409;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_UNKNOWNCAPCMD.
    const ERR_UNKNOWNCAPCMD             = 410;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NORECIPIENT.
    const ERR_NORECIPIENT               = 411;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOTEXTTOSEND.
    const ERR_NOTEXTTOSEND              = 412;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOTOPLEVEL.
    const ERR_NOTOPLEVEL                = 413;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_WILDTOPLEVEL.
    const ERR_WILDTOPLEVEL              = 414;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_QUERYTOOLONG.
    const ERR_QUERYTOOLONG              = 416;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_INPUTTOOLONG.
    const ERR_INPUTTOOLONG              = 417;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_UNKNOWNCOMMAND.
    const ERR_UNKNOWNCOMMAND            = 421;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOMOTD.
    const ERR_NOMOTD                    = 422;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOADMININFO.
    const ERR_NOADMININFO               = 423;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NONICKNAMEGIVEN.
    const ERR_NONICKNAMEGIVEN           = 431;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_ERRONEUSNICKNAME.
    const ERR_ERRONEUSNICKNAME          = 432;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NICKNAMEINUSE.
    const ERR_NICKNAMEINUSE             = 433;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NICKCOLLISION.
    const ERR_NICKCOLLISION             = 436;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_BANNICKCHANGE.
    const ERR_BANNICKCHANGE             = 437;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NICKTOOFAST.
    const ERR_NICKTOOFAST               = 438;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_TARGETTOOFAST.
    const ERR_TARGETTOOFAST             = 439;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_SERVICESDOWN.
    const ERR_SERVICESDOWN              = 440;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_USERNOTINCHANNEL.
    const ERR_USERNOTINCHANNEL          = 441;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOTONCHANNEL.
    const ERR_NOTONCHANNEL              = 442;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_USERONCHANNEL.
    const ERR_USERONCHANNEL             = 443;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOTREGISTERED.
    const ERR_NOTREGISTERED             = 451;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NEEDMOREPARAMS.
    const ERR_NEEDMOREPARAMS            = 461;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_ALREADYREGISTRED.
    const ERR_ALREADYREGISTRED          = 462;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOPERMFORHOST.
    const ERR_NOPERMFORHOST             = 463;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_PASSWDMISMATCH.
    const ERR_PASSWDMISMATCH            = 464;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_YOUREBANNEDCREEP.
    const ERR_YOUREBANNEDCREEP          = 465;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_YOUWILLBEBANNED.
    const ERR_YOUWILLBEBANNED           = 466;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_KEYSET.
    const ERR_KEYSET                    = 467;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_INVALIDUSERNAME.
    const ERR_INVALIDUSERNAME           = 468;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_CHANNELISFULL.
    const ERR_CHANNELISFULL             = 471;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_UNKNOWNMODE.
    const ERR_UNKNOWNMODE               = 472;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_INVITEONLYCHAN.
    const ERR_INVITEONLYCHAN            = 473;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_BANNEDFROMCHAN.
    const ERR_BANNEDFROMCHAN            = 474;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_BADCHANNELKEY.
    const ERR_BADCHANNELKEY             = 475;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_BADCHANMASK.
    const ERR_BADCHANMASK               = 476;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NEEDREGGEDNICK.
    const ERR_NEEDREGGEDNICK            = 477;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_BANLISTFULL.
    const ERR_BANLISTFULL               = 478;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_BADCHANNAME.
    const ERR_BADCHANNAME               = 479;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOPRIVILEGES.
    const ERR_NOPRIVILEGES              = 481;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_CHANOPRIVSNEEDED.
    const ERR_CHANOPRIVSNEEDED          = 482;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_CANTKILLSERVER.
    const ERR_CANTKILLSERVER            = 483;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_ISCHANSERVICE.
    const ERR_ISCHANSERVICE             = 484;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_VOICENEEDED.
    const ERR_VOICENEEDED               = 489;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOOPERHOST.
    const ERR_NOOPERHOST                = 491;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOFEATURE.
    const ERR_NOFEATURE                 = 493;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_BADFEATVALUE.
    const ERR_BADFEATVALUE              = 494;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_BADLOGTYPE.
    const ERR_BADLOGTYPE                = 495;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_BADLOGSYS.
    const ERR_BADLOGSYS                 = 496;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_BADLOGVALUE.
    const ERR_BADLOGVALUE               = 497;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_ISOPERLCHAN.
    const ERR_ISOPERLCHAN               = 498;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_UMODEUNKNOWNFLAG.
    const ERR_UMODEUNKNOWNFLAG          = 501;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_USERSDONTMATCH.
    const ERR_USERSDONTMATCH            = 502;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_SILELISTFULL.
    const ERR_SILELISTFULL              = 511;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOSUCHGLINE.
    const ERR_NOSUCHGLINE               = 512;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_BADPING.
    const ERR_BADPING                   = 513;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOSUCHJUPE.
    const ERR_NOSUCHJUPE                = 514;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_BADEXPIRE.
    const ERR_BADEXPIRE                 = 515;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_DONTCHEAT.
    const ERR_DONTCHEAT                 = 516;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_DISABLED.
    const ERR_DISABLED                  = 517;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_LONGMASK.
    const ERR_LONGMASK                  = 518;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_TOOMANYUSERS.
    const ERR_TOOMANYUSERS              = 519;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_MASKTOOWIDE.
    const ERR_MASKTOOWIDE               = 520;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_QUARANTINED.
    const ERR_QUARANTINED               = 524;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_INVALIDKEY.
    const ERR_INVALIDKEY                = 525;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOTLOWEROPLEVEL.
    const ERR_NOTLOWEROPLEVEL           = 560;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOTMANAGER.
    const ERR_NOTMANAGER                = 561;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_CHANSECURED.
    const ERR_CHANSECURED               = 562;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_UPASSSET.
    const ERR_UPASSSET                  = 563;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_UPASSNOTSET.
    const ERR_UPASSNOTSET               = 564;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOMANAGER.
    const ERR_NOMANAGER                 = 566;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_UPASS_SAME_APASS.
    const ERR_UPASS_SAME_APASS          = 567;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_LASTERROR.
    const ERR_LASTERROR                 = 568;
}
