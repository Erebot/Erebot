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
 *      Numeric profile for UltimateIRCd-based IRC servers.
 */
class   Erebot_NumericProfile_Ultimate
extends Erebot_NumericProfile_Base
{
    /// \copydoc Erebot_Interface_Numerics::RPL_WELCOME.
    const RPL_WELCOME                   =   1;

    /// \copydoc Erebot_Interface_Numerics::RPL_YOURHOST.
    const RPL_YOURHOST                  =   2;

    /// \copydoc Erebot_Interface_Numerics::RPL_CREATED.
    const RPL_CREATED                   =   3;

    /// \copydoc Erebot_Interface_Numerics::RPL_MYINFO.
    const RPL_MYINFO                    =   4;

    /// \copydoc Erebot_Interface_Numerics::RPL_PROTOCTL.
    const RPL_PROTOCTL                  =   5;

    /// \copydoc Erebot_Interface_Numerics::RPL_MAP.
    const RPL_MAP                       =   6;

    /// \copydoc Erebot_Interface_Numerics::RPL_MAPEND.
    const RPL_MAPEND                    =   7;

    /// \copydoc Erebot_Interface_Numerics::RPL_TRACELINK.
    const RPL_TRACELINK                 = 200;

    /// \copydoc Erebot_Interface_Numerics::RPL_TRACECONNECTING.
    const RPL_TRACECONNECTING           = 201;

    /// \copydoc Erebot_Interface_Numerics::RPL_TRACEHANDSHAKE.
    const RPL_TRACEHANDSHAKE            = 202;

    /// \copydoc Erebot_Interface_Numerics::RPL_TRACEUNKNOWN.
    const RPL_TRACEUNKNOWN              = 203;

    /// \copydoc Erebot_Interface_Numerics::RPL_TRACEOPERATOR.
    const RPL_TRACEOPERATOR             = 204;

    /// \copydoc Erebot_Interface_Numerics::RPL_TRACEUSER.
    const RPL_TRACEUSER                 = 205;

    /// \copydoc Erebot_Interface_Numerics::RPL_TRACESERVER.
    const RPL_TRACESERVER               = 206;

    /// \copydoc Erebot_Interface_Numerics::RPL_TRACENEWTYPE.
    const RPL_TRACENEWTYPE              = 208;

    /// \copydoc Erebot_Interface_Numerics::RPL_TRACECLASS.
    const RPL_TRACECLASS                = 209;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSLINKINFO.
    const RPL_STATSLINKINFO             = 211;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSCOMMANDS.
    const RPL_STATSCOMMANDS             = 212;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSCLINE.
    const RPL_STATSCLINE                = 213;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSNLINE.
    const RPL_STATSNLINE                = 214;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSILINE.
    const RPL_STATSILINE                = 215;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSKLINE.
    const RPL_STATSKLINE                = 216;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSQLINE.
    const RPL_STATSQLINE                = 217;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSYLINE.
    const RPL_STATSYLINE                = 218;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFSTATS.
    const RPL_ENDOFSTATS                = 219;

    /// \copydoc Erebot_Interface_Numerics::RPL_NMODEIS.
    const RPL_NMODEIS                   = 220;

    /// \copydoc Erebot_Interface_Numerics::RPL_UMODEIS.
    const RPL_UMODEIS                   = 221;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSBLINE.
    const RPL_STATSBLINE                = 222;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSELINE.
    const RPL_STATSELINE                = 223;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSFLINE.
    const RPL_STATSFLINE                = 224;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSZLINE.
    const RPL_STATSZLINE                = 225;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSCOUNT.
    const RPL_STATSCOUNT                = 226;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSGLINE.
    const RPL_STATSGLINE                = 227;

    /// \copydoc Erebot_Interface_Numerics::RPL_SERVLIST.
    const RPL_SERVLIST                  = 234;

    /// \copydoc Erebot_Interface_Numerics::RPL_SERVLISTEND.
    const RPL_SERVLISTEND               = 235;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSLLINE.
    const RPL_STATSLLINE                = 241;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSUPTIME.
    const RPL_STATSUPTIME               = 242;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSOLINE.
    const RPL_STATSOLINE                = 243;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSHLINE.
    const RPL_STATSHLINE                = 244;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSSLINE.
    const RPL_STATSSLINE                = 245;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSULINE.
    const RPL_STATSULINE                = 246;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSDEBUG.
    const RPL_STATSDEBUG                = 249;

    /// \copydoc Erebot_Interface_Numerics::RPL_LUSERCLIENT.
    const RPL_LUSERCLIENT               = 251;

    /// \copydoc Erebot_Interface_Numerics::RPL_LUSEROP.
    const RPL_LUSEROP                   = 252;

    /// \copydoc Erebot_Interface_Numerics::RPL_LUSERUNKNOWN.
    const RPL_LUSERUNKNOWN              = 253;

    /// \copydoc Erebot_Interface_Numerics::RPL_LUSERCHANNELS.
    const RPL_LUSERCHANNELS             = 254;

    /// \copydoc Erebot_Interface_Numerics::RPL_LUSERME.
    const RPL_LUSERME                   = 255;

    /// \copydoc Erebot_Interface_Numerics::RPL_ADMINME.
    const RPL_ADMINME                   = 256;

    /// \copydoc Erebot_Interface_Numerics::RPL_ADMINLOC1.
    const RPL_ADMINLOC1                 = 257;

    /// \copydoc Erebot_Interface_Numerics::RPL_ADMINLOC2.
    const RPL_ADMINLOC2                 = 258;

    /// \copydoc Erebot_Interface_Numerics::RPL_ADMINEMAIL.
    const RPL_ADMINEMAIL                = 259;

    /// \copydoc Erebot_Interface_Numerics::RPL_TRACELOG.
    const RPL_TRACELOG                  = 261;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFTRACE.
    const RPL_ENDOFTRACE                = 262;

    /// \copydoc Erebot_Interface_Numerics::RPL_LOAD2HI.
    const RPL_LOAD2HI                   = 263;

    /// \copydoc Erebot_Interface_Numerics::RPL_SILELIST.
    const RPL_SILELIST                  = 271;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFSILELIST.
    const RPL_ENDOFSILELIST             = 272;

    /// \copydoc Erebot_Interface_Numerics::RPL_USINGSSL.
    const RPL_USINGSSL                  = 275;

    /// \copydoc Erebot_Interface_Numerics::RPL_NONE.
    const RPL_NONE                      = 300;

    /// \copydoc Erebot_Interface_Numerics::RPL_AWAY.
    const RPL_AWAY                      = 301;

    /// \copydoc Erebot_Interface_Numerics::RPL_USERHOST.
    const RPL_USERHOST                  = 302;

    /// \copydoc Erebot_Interface_Numerics::RPL_ISON.
    const RPL_ISON                      = 303;

    /// \copydoc Erebot_Interface_Numerics::RPL_TEXT.
    const RPL_TEXT                      = 304;

    /// \copydoc Erebot_Interface_Numerics::RPL_UNAWAY.
    const RPL_UNAWAY                    = 305;

    /// \copydoc Erebot_Interface_Numerics::RPL_NOWAWAY.
    const RPL_NOWAWAY                   = 306;

    /// \copydoc Erebot_Interface_Numerics::RPL_WHOISREGNICK.
    const RPL_WHOISREGNICK              = 307;

    /// \copydoc Erebot_Interface_Numerics::RPL_WHOISADMIN.
    const RPL_WHOISADMIN                = 308;

    /// \copydoc Erebot_Interface_Numerics::RPL_WHOISSADMIN.
    const RPL_WHOISSADMIN               = 309;

    /// \copydoc Erebot_Interface_Numerics::RPL_WHOISSVCMSG.
    const RPL_WHOISSVCMSG               = 310;

    /// \copydoc Erebot_Interface_Numerics::RPL_WHOISUSER.
    const RPL_WHOISUSER                 = 311;

    /// \copydoc Erebot_Interface_Numerics::RPL_WHOISSERVER.
    const RPL_WHOISSERVER               = 312;

    /// \copydoc Erebot_Interface_Numerics::RPL_WHOISOPERATOR.
    const RPL_WHOISOPERATOR             = 313;

    /// \copydoc Erebot_Interface_Numerics::RPL_WHOWASUSER.
    const RPL_WHOWASUSER                = 314;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFWHOWAS.
    const RPL_ENDOFWHOWAS               = 369;

    /// \copydoc Erebot_Interface_Numerics::RPL_WHOISCHANOP.
    const RPL_WHOISCHANOP               = 316;

    /// \copydoc Erebot_Interface_Numerics::RPL_WHOISIDLE.
    const RPL_WHOISIDLE                 = 317;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFWHOIS.
    const RPL_ENDOFWHOIS                = 318;

    /// \copydoc Erebot_Interface_Numerics::RPL_WHOISCHANNELS.
    const RPL_WHOISCHANNELS             = 319;

    /// \copydoc Erebot_Interface_Numerics::RPL_LISTSTART.
    const RPL_LISTSTART                 = 321;

    /// \copydoc Erebot_Interface_Numerics::RPL_LIST.
    const RPL_LIST                      = 322;

    /// \copydoc Erebot_Interface_Numerics::RPL_LISTEND.
    const RPL_LISTEND                   = 323;

    /// \copydoc Erebot_Interface_Numerics::RPL_CHANNELMODEIS.
    const RPL_CHANNELMODEIS             = 324;

    /// \copydoc Erebot_Interface_Numerics::RPL_CREATIONTIME.
    const RPL_CREATIONTIME              = 329;

    /// \copydoc Erebot_Interface_Numerics::RPL_NOTOPIC.
    const RPL_NOTOPIC                   = 331;

    /// \copydoc Erebot_Interface_Numerics::RPL_TOPIC.
    const RPL_TOPIC                     = 332;

    /// \copydoc Erebot_Interface_Numerics::RPL_TOPICWHOTIME.
    const RPL_TOPICWHOTIME              = 333;

    /// \copydoc Erebot_Interface_Numerics::RPL_COMMANDSYNTAX.
    const RPL_COMMANDSYNTAX             = 334;

    /// \copydoc Erebot_Interface_Numerics::RPL_INVITING.
    const RPL_INVITING                  = 341;

    /// \copydoc Erebot_Interface_Numerics::RPL_SUMMONING.
    const RPL_SUMMONING                 = 342;

    /// \copydoc Erebot_Interface_Numerics::RPL_EXEMPTLIST.
    const RPL_EXEMPTLIST                = 348;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFEXEMPTLIST.
    const RPL_ENDOFEXEMPTLIST           = 349;

    /// \copydoc Erebot_Interface_Numerics::RPL_VERSION.
    const RPL_VERSION                   = 351;

    /// \copydoc Erebot_Interface_Numerics::RPL_WHOREPLY.
    const RPL_WHOREPLY                  = 352;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFWHO.
    const RPL_ENDOFWHO                  = 315;

    /// \copydoc Erebot_Interface_Numerics::RPL_NAMREPLY.
    const RPL_NAMREPLY                  = 353;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFNAMES.
    const RPL_ENDOFNAMES                = 366;

    /// \copydoc Erebot_Interface_Numerics::RPL_KILLDONE.
    const RPL_KILLDONE                  = 361;

    /// \copydoc Erebot_Interface_Numerics::RPL_CLOSING.
    const RPL_CLOSING                   = 362;

    /// \copydoc Erebot_Interface_Numerics::RPL_CLOSEEND.
    const RPL_CLOSEEND                  = 363;

    /// \copydoc Erebot_Interface_Numerics::RPL_LINKS.
    const RPL_LINKS                     = 364;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFLINKS.
    const RPL_ENDOFLINKS                = 365;

    /// \copydoc Erebot_Interface_Numerics::RPL_BANLIST.
    const RPL_BANLIST                   = 367;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFBANLIST.
    const RPL_ENDOFBANLIST              = 368;

    /// \copydoc Erebot_Interface_Numerics::RPL_INFO.
    const RPL_INFO                      = 371;

    /// \copydoc Erebot_Interface_Numerics::RPL_MOTD.
    const RPL_MOTD                      = 372;

    /// \copydoc Erebot_Interface_Numerics::RPL_INFOSTART.
    const RPL_INFOSTART                 = 373;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFINFO.
    const RPL_ENDOFINFO                 = 374;

    /// \copydoc Erebot_Interface_Numerics::RPL_MOTDSTART.
    const RPL_MOTDSTART                 = 375;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFMOTD.
    const RPL_ENDOFMOTD                 = 376;

    /// \copydoc Erebot_Interface_Numerics::RPL_YOUREOPER.
    const RPL_YOUREOPER                 = 381;

    /// \copydoc Erebot_Interface_Numerics::RPL_REHASHING.
    const RPL_REHASHING                 = 382;

    /// \copydoc Erebot_Interface_Numerics::RPL_MYPORTIS.
    const RPL_MYPORTIS                  = 384;

    /// \copydoc Erebot_Interface_Numerics::RPL_NOTOPERANYMORE.
    const RPL_NOTOPERANYMORE            = 385;

    /// \copydoc Erebot_Interface_Numerics::RPL_TIME.
    const RPL_TIME                      = 391;

    /// \copydoc Erebot_Interface_Numerics::RPL_USERSSTART.
    const RPL_USERSSTART                = 392;

    /// \copydoc Erebot_Interface_Numerics::RPL_USERS.
    const RPL_USERS                     = 393;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFUSERS.
    const RPL_ENDOFUSERS                = 394;

    /// \copydoc Erebot_Interface_Numerics::RPL_NOUSERS.
    const RPL_NOUSERS                   = 395;

    /// \copydoc Erebot_Interface_Numerics::ERR_NOSUCHNICK.
    const ERR_NOSUCHNICK                = 401;

    /// \copydoc Erebot_Interface_Numerics::ERR_NOSUCHSERVER.
    const ERR_NOSUCHSERVER              = 402;

    /// \copydoc Erebot_Interface_Numerics::ERR_NOSUCHCHANNEL.
    const ERR_NOSUCHCHANNEL             = 403;

    /// \copydoc Erebot_Interface_Numerics::ERR_CANNOTSENDTOCHAN.
    const ERR_CANNOTSENDTOCHAN          = 404;

    /// \copydoc Erebot_Interface_Numerics::ERR_TOOMANYCHANNELS.
    const ERR_TOOMANYCHANNELS           = 405;

    /// \copydoc Erebot_Interface_Numerics::ERR_WASNOSUCHNICK.
    const ERR_WASNOSUCHNICK             = 406;

    /// \copydoc Erebot_Interface_Numerics::ERR_TOOMANYTARGETS.
    const ERR_TOOMANYTARGETS            = 407;

    /// \copydoc Erebot_Interface_Numerics::ERR_NOCOLORSONCHAN.
    const ERR_NOCOLORSONCHAN            = 408;

    /// \copydoc Erebot_Interface_Numerics::ERR_NOORIGIN.
    const ERR_NOORIGIN                  = 409;

    /// \copydoc Erebot_Interface_Numerics::ERR_NORECIPIENT.
    const ERR_NORECIPIENT               = 411;

    /// \copydoc Erebot_Interface_Numerics::ERR_NOTEXTTOSEND.
    const ERR_NOTEXTTOSEND              = 412;

    /// \copydoc Erebot_Interface_Numerics::ERR_NOTOPLEVEL.
    const ERR_NOTOPLEVEL                = 413;

    /// \copydoc Erebot_Interface_Numerics::ERR_WILDTOPLEVEL.
    const ERR_WILDTOPLEVEL              = 414;

    /// \copydoc Erebot_Interface_Numerics::ERR_UNKNOWNCOMMAND.
    const ERR_UNKNOWNCOMMAND            = 421;

    /// \copydoc Erebot_Interface_Numerics::ERR_NOMOTD.
    const ERR_NOMOTD                    = 422;

    /// \copydoc Erebot_Interface_Numerics::ERR_NOADMININFO.
    const ERR_NOADMININFO               = 423;

    /// \copydoc Erebot_Interface_Numerics::ERR_FILEERROR.
    const ERR_FILEERROR                 = 424;

    /// \copydoc Erebot_Interface_Numerics::ERR_TOOMANYAWAY.
    const ERR_TOOMANYAWAY               = 429;

    /// \copydoc Erebot_Interface_Numerics::ERR_NONICKNAMEGIVEN.
    const ERR_NONICKNAMEGIVEN           = 431;

    /// \copydoc Erebot_Interface_Numerics::ERR_ERRONEUSNICKNAME.
    const ERR_ERRONEUSNICKNAME          = 432;

    /// \copydoc Erebot_Interface_Numerics::ERR_NICKNAMEINUSE.
    const ERR_NICKNAMEINUSE             = 433;

    /// \copydoc Erebot_Interface_Numerics::ERR_BANONCHAN.
    const ERR_BANONCHAN                 = 435;

    /// \copydoc Erebot_Interface_Numerics::ERR_NICKCOLLISION.
    const ERR_NICKCOLLISION             = 436;

    /// \copydoc Erebot_Interface_Numerics::ERR_BANNICKCHANGE.
    const ERR_BANNICKCHANGE             = 437;

    /// \copydoc Erebot_Interface_Numerics::ERR_TARGETTOFAST.
    const ERR_TARGETTOFAST              = 439;

    /// \copydoc Erebot_Interface_Numerics::ERR_SERVICESDOWN.
    const ERR_SERVICESDOWN              = 440;

    /// \copydoc Erebot_Interface_Numerics::ERR_USERNOTINCHANNEL.
    const ERR_USERNOTINCHANNEL          = 441;

    /// \copydoc Erebot_Interface_Numerics::ERR_NOTONCHANNEL.
    const ERR_NOTONCHANNEL              = 442;

    /// \copydoc Erebot_Interface_Numerics::ERR_USERONCHANNEL.
    const ERR_USERONCHANNEL             = 443;

    /// \copydoc Erebot_Interface_Numerics::ERR_NOLOGIN.
    const ERR_NOLOGIN                   = 444;

    /// \copydoc Erebot_Interface_Numerics::ERR_SUMMONDISABLED.
    const ERR_SUMMONDISABLED            = 445;

    /// \copydoc Erebot_Interface_Numerics::ERR_USERSDISABLED.
    const ERR_USERSDISABLED             = 446;

    /// \copydoc Erebot_Interface_Numerics::ERR_NOTREGISTERED.
    const ERR_NOTREGISTERED             = 451;

    /// \copydoc Erebot_Interface_Numerics::ERR_NEEDMOREPARAMS.
    const ERR_NEEDMOREPARAMS            = 461;

    /// \copydoc Erebot_Interface_Numerics::ERR_ALREADYREGISTRED.
    const ERR_ALREADYREGISTRED          = 462;

    /// \copydoc Erebot_Interface_Numerics::ERR_NOPERMFORHOST.
    const ERR_NOPERMFORHOST             = 463;

    /// \copydoc Erebot_Interface_Numerics::ERR_PASSWDMISMATCH.
    const ERR_PASSWDMISMATCH            = 464;

    /// \copydoc Erebot_Interface_Numerics::ERR_YOUREBANNEDCREEP.
    const ERR_YOUREBANNEDCREEP          = 465;

    /// \copydoc Erebot_Interface_Numerics::ERR_YOUWILLBEBANNED.
    const ERR_YOUWILLBEBANNED           = 466;

    /// \copydoc Erebot_Interface_Numerics::ERR_KEYSET.
    const ERR_KEYSET                    = 467;

    /// \copydoc Erebot_Interface_Numerics::ERR_ONLYSERVERSCANCHANGE.
    const ERR_ONLYSERVERSCANCHANGE      = 468;

    /// \copydoc Erebot_Interface_Numerics::ERR_CHANNELISFULL.
    const ERR_CHANNELISFULL             = 471;

    /// \copydoc Erebot_Interface_Numerics::ERR_UNKNOWNMODE.
    const ERR_UNKNOWNMODE               = 472;

    /// \copydoc Erebot_Interface_Numerics::ERR_INVITEONLYCHAN.
    const ERR_INVITEONLYCHAN            = 473;

    /// \copydoc Erebot_Interface_Numerics::ERR_BANNEDFROMCHAN.
    const ERR_BANNEDFROMCHAN            = 474;

    /// \copydoc Erebot_Interface_Numerics::ERR_BADCHANNELKEY.
    const ERR_BADCHANNELKEY             = 475;

    /// \copydoc Erebot_Interface_Numerics::ERR_BADCHANMASK.
    const ERR_BADCHANMASK               = 476;

    /// \copydoc Erebot_Interface_Numerics::ERR_NEEDREGGEDNICK.
    const ERR_NEEDREGGEDNICK            = 477;

    /// \copydoc Erebot_Interface_Numerics::ERR_BANLISTFULL.
    const ERR_BANLISTFULL               = 478;

    /// \copydoc Erebot_Interface_Numerics::ERR_BADCHANNAME.
    const ERR_BADCHANNAME               = 479;

    /// \copydoc Erebot_Interface_Numerics::ERR_SERVERONLY.
    const ERR_SERVERONLY                = 480;

    /// \copydoc Erebot_Interface_Numerics::ERR_NOPRIVILEGES.
    const ERR_NOPRIVILEGES              = 481;

    /// \copydoc Erebot_Interface_Numerics::ERR_CHANOPRIVSNEEDED.
    const ERR_CHANOPRIVSNEEDED          = 482;

    /// \copydoc Erebot_Interface_Numerics::ERR_CANTKILLSERVER.
    const ERR_CANTKILLSERVER            = 483;

    /// \copydoc Erebot_Interface_Numerics::ERR_DESYNC.
    const ERR_DESYNC                    = 484;

    /// \copydoc Erebot_Interface_Numerics::ERR_CHANBANREASON.
    const ERR_CHANBANREASON             = 485;

    /// \copydoc Erebot_Interface_Numerics::ERR_SSLCLIENTSONLY.
    const ERR_SSLCLIENTSONLY            = 486;

    /// \copydoc Erebot_Interface_Numerics::ERR_NONONREG.
    const ERR_NONONREG                  = 487;

    /// \copydoc Erebot_Interface_Numerics::ERR_NOTSSLCLIENT.
    const ERR_NOTSSLCLIENT              = 488;

    /// \copydoc Erebot_Interface_Numerics::ERR_NOOPERHOST.
    const ERR_NOOPERHOST                = 491;

    /// \copydoc Erebot_Interface_Numerics::ERR_UMODEUNKNOWNFLAG.
    const ERR_UMODEUNKNOWNFLAG          = 501;

    /// \copydoc Erebot_Interface_Numerics::ERR_USERSDONTMATCH.
    const ERR_USERSDONTMATCH            = 502;

    /// \copydoc Erebot_Interface_Numerics::ERR_GHOSTEDCLIENT.
    const ERR_GHOSTEDCLIENT             = 503;

    /// \copydoc Erebot_Interface_Numerics::ERR_LAST_ERR_MSG.
    const ERR_LAST_ERR_MSG              = 504;

    /// \copydoc Erebot_Interface_Numerics::ERR_SILELISTFULL.
    const ERR_SILELISTFULL              = 511;

    /// \copydoc Erebot_Interface_Numerics::ERR_TOOMANYWATCH.
    const ERR_TOOMANYWATCH              = 512;

    /// \copydoc Erebot_Interface_Numerics::ERR_TOOMANYDCC.
    const ERR_TOOMANYDCC                = 514;

    /// \copydoc Erebot_Interface_Numerics::ERR_LISTSYNTAX.
    const ERR_LISTSYNTAX                = 521;

    /// \copydoc Erebot_Interface_Numerics::ERR_WHOSYNTAX.
    const ERR_WHOSYNTAX                 = 522;

    /// \copydoc Erebot_Interface_Numerics::ERR_WHOLIMEXCEED.
    const ERR_WHOLIMEXCEED              = 523;

    /// \copydoc Erebot_Interface_Numerics::RPL_LOGON.
    const RPL_LOGON                     = 600;

    /// \copydoc Erebot_Interface_Numerics::RPL_LOGOFF.
    const RPL_LOGOFF                    = 601;

    /// \copydoc Erebot_Interface_Numerics::RPL_WATCHOFF.
    const RPL_WATCHOFF                  = 602;

    /// \copydoc Erebot_Interface_Numerics::RPL_WATCHSTAT.
    const RPL_WATCHSTAT                 = 603;

    /// \copydoc Erebot_Interface_Numerics::RPL_NOWON.
    const RPL_NOWON                     = 604;

    /// \copydoc Erebot_Interface_Numerics::RPL_NOWOFF.
    const RPL_NOWOFF                    = 605;

    /// \copydoc Erebot_Interface_Numerics::RPL_WATCHLIST.
    const RPL_WATCHLIST                 = 606;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFWATCHLIST.
    const RPL_ENDOFWATCHLIST            = 607;

    /// \copydoc Erebot_Interface_Numerics::RPL_WHOISSERVICES.
    const RPL_WHOISSERVICES             = 613;

    /// \copydoc Erebot_Interface_Numerics::RPL_WHOISHELPOP.
    const RPL_WHOISHELPOP               = 614;

    /// \copydoc Erebot_Interface_Numerics::RPL_WHOISMODES.
    const RPL_WHOISMODES                = 615;

    /// \copydoc Erebot_Interface_Numerics::RPL_WHOISHOST.
    const RPL_WHOISHOST                 = 616;

    /// \copydoc Erebot_Interface_Numerics::RPL_DCCSTATUS.
    const RPL_DCCSTATUS                 = 617;

    /// \copydoc Erebot_Interface_Numerics::RPL_DCCLIST.
    const RPL_DCCLIST                   = 618;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFDCCLIST.
    const RPL_ENDOFDCCLIST              = 619;

    /// \copydoc Erebot_Interface_Numerics::RPL_DCCINFO.
    const RPL_DCCINFO                   = 620;

    /// \copydoc Erebot_Interface_Numerics::RPL_SETTINGS.
    const RPL_SETTINGS                  = 630;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFSETTINGS.
    const RPL_ENDOFSETTINGS             = 631;

    /// \copydoc Erebot_Interface_Numerics::RPL_IRCOPS.
    const RPL_IRCOPS                    = 632;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFIRCOPS.
    const RPL_ENDOFIRCOPS               = 633;

    /// \copydoc Erebot_Interface_Numerics::RPL_OPERMOTDSTART.
    const RPL_OPERMOTDSTART             = 640;

    /// \copydoc Erebot_Interface_Numerics::RPL_OPERMOTD.
    const RPL_OPERMOTD                  = 641;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFOPERMOTD.
    const RPL_ENDOFOPERMOTD             = 642;

    /// \copydoc Erebot_Interface_Numerics::RPL_RULESSTART.
    const RPL_RULESSTART                = 644;

    /// \copydoc Erebot_Interface_Numerics::RPL_RULES.
    const RPL_RULES                     = 645;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFRULES.
    const RPL_ENDOFRULES                = 646;

    /// \copydoc Erebot_Interface_Numerics::RPL_MAPMORE.
    const RPL_MAPMORE                   = 650;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSVLINE.
    const RPL_STATSVLINE                = 660;

    /// \copydoc Erebot_Interface_Numerics::RPL_WHOHOST.
    const RPL_WHOHOST                   = 671;

    /// \copydoc Erebot_Interface_Numerics::ERR_EXEMPTLISTFULL.
    const ERR_EXEMPTLISTFULL            = 700;

    /// \copydoc Erebot_Interface_Numerics::ERR_NORULES.
    const ERR_NORULES                   = 710;

    /// \copydoc Erebot_Interface_Numerics::ERR_NOOPERMOTD.
    const ERR_NOOPERMOTD                = 711;

    /// \copydoc Erebot_Interface_Numerics::ERR_NUMERIC_ERR.
    const ERR_NUMERIC_ERR               = 999;
}
