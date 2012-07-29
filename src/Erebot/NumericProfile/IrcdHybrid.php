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
 *      Numeric profile for ircd-hybrid-based IRC servers.
 */
class   Erebot_NumericProfile_IrcdHybrid
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

    /// \copydoc Erebot_Interface_Numerics::RPL_ISUPPORT.
    const RPL_ISUPPORT                  =   5;

    /// \copydoc Erebot_Interface_Numerics::RPL_REDIR.
    const RPL_REDIR                     =  10;

    /// \copydoc Erebot_Interface_Numerics::RPL_MAP.
    const RPL_MAP                       =  15;

    /// \copydoc Erebot_Interface_Numerics::RPL_MAPMORE.
    const RPL_MAPMORE                   =  16;

    /// \copydoc Erebot_Interface_Numerics::RPL_MAPEND.
    const RPL_MAPEND                    =  17;

    /// \copydoc Erebot_Interface_Numerics::RPL_YOURID.
    const RPL_YOURID                    =  42;

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

    /// \copydoc Erebot_Interface_Numerics::RPL_TRACECAPTURED.
    const RPL_TRACECAPTURED             = 207;

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

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSPLINE.
    const RPL_STATSPLINE                = 220;

    /// \copydoc Erebot_Interface_Numerics::RPL_UMODEIS.
    const RPL_UMODEIS                   = 221;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSFLINE.
    const RPL_STATSFLINE                = 224;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSDLINE.
    const RPL_STATSDLINE                = 225;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSALINE.
    const RPL_STATSALINE                = 226;

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

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSXLINE.
    const RPL_STATSXLINE                = 247;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSULINE.
    const RPL_STATSULINE                = 248;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSDEBUG.
    const RPL_STATSDEBUG                = 249;

    /// \copydoc Erebot_Interface_Numerics::RPL_STATSCONN.
    const RPL_STATSCONN                 = 250;

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

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFTRACE.
    const RPL_ENDOFTRACE                = 262;

    /// \copydoc Erebot_Interface_Numerics::RPL_LOAD2HI.
    const RPL_LOAD2HI                   = 263;

    /// \copydoc Erebot_Interface_Numerics::RPL_LOCALUSERS.
    const RPL_LOCALUSERS                = 265;

    /// \copydoc Erebot_Interface_Numerics::RPL_GLOBALUSERS.
    const RPL_GLOBALUSERS               = 266;

    /// \copydoc Erebot_Interface_Numerics::RPL_ACCEPTLIST.
    const RPL_ACCEPTLIST                = 281;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFACCEPT.
    const RPL_ENDOFACCEPT               = 282;

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

    /// \copydoc Erebot_Interface_Numerics::RPL_USERIP.
    const RPL_USERIP                    = 307;

    /// \copydoc Erebot_Interface_Numerics::RPL_WHOISADMIN.
    const RPL_WHOISADMIN                = 308;

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

    /// \copydoc Erebot_Interface_Numerics::RPL_WHOISACTUALLY.
    const RPL_WHOISACTUALLY             = 338;

    /// \copydoc Erebot_Interface_Numerics::RPL_INVITING.
    const RPL_INVITING                  = 341;

    /// \copydoc Erebot_Interface_Numerics::RPL_INVITELIST.
    const RPL_INVITELIST                = 346;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFINVITELIST.
    const RPL_ENDOFINVITELIST           = 347;

    /// \copydoc Erebot_Interface_Numerics::RPL_EXCEPTLIST.
    const RPL_EXCEPTLIST                = 348;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFEXCEPTLIST.
    const RPL_ENDOFEXCEPTLIST           = 349;

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

    /// \copydoc Erebot_Interface_Numerics::RPL_RSACHALLENGE.
    const RPL_RSACHALLENGE              = 386;

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

    /// \copydoc Erebot_Interface_Numerics::RPL_HOSTHIDDEN.
    const RPL_HOSTHIDDEN                = 396;

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

    /// \copydoc Erebot_Interface_Numerics::ERR_NOORIGIN.
    const ERR_NOORIGIN                  = 409;

    /// \copydoc Erebot_Interface_Numerics::ERR_INVALIDCAPCMD.
    const ERR_INVALIDCAPCMD             = 410;

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

    /// \copydoc Erebot_Interface_Numerics::ERR_NONICKNAMEGIVEN.
    const ERR_NONICKNAMEGIVEN           = 431;

    /// \copydoc Erebot_Interface_Numerics::ERR_ERRONEUSNICKNAME.
    const ERR_ERRONEUSNICKNAME          = 432;

    /// \copydoc Erebot_Interface_Numerics::ERR_NICKNAMEINUSE.
    const ERR_NICKNAMEINUSE             = 433;

    /// \copydoc Erebot_Interface_Numerics::ERR_NICKCOLLISION.
    const ERR_NICKCOLLISION             = 436;

    /// \copydoc Erebot_Interface_Numerics::ERR_UNAVAILRESOURCE.
    const ERR_UNAVAILRESOURCE           = 437;

    /// \copydoc Erebot_Interface_Numerics::ERR_NICKTOOFAST.
    const ERR_NICKTOOFAST               = 438;

    /// \copydoc Erebot_Interface_Numerics::ERR_SERVICESDOWN.
    const ERR_SERVICESDOWN              = 440;

    /// \copydoc Erebot_Interface_Numerics::ERR_USERNOTINCHANNEL.
    const ERR_USERNOTINCHANNEL          = 441;

    /// \copydoc Erebot_Interface_Numerics::ERR_NOTONCHANNEL.
    const ERR_NOTONCHANNEL              = 442;

    /// \copydoc Erebot_Interface_Numerics::ERR_USERONCHANNEL.
    const ERR_USERONCHANNEL             = 443;

    /// \copydoc Erebot_Interface_Numerics::ERR_NOTREGISTERED.
    const ERR_NOTREGISTERED             = 451;

    /// \copydoc Erebot_Interface_Numerics::ERR_ACCEPTFULL.
    const ERR_ACCEPTFULL                = 456;

    /// \copydoc Erebot_Interface_Numerics::ERR_ACCEPTEXIST.
    const ERR_ACCEPTEXIST               = 457;

    /// \copydoc Erebot_Interface_Numerics::ERR_ACCEPTNOT.
    const ERR_ACCEPTNOT                 = 458;

    /// \copydoc Erebot_Interface_Numerics::ERR_NEEDMOREPARAMS.
    const ERR_NEEDMOREPARAMS            = 461;

    /// \copydoc Erebot_Interface_Numerics::ERR_ALREADYREGISTRED.
    const ERR_ALREADYREGISTRED          = 462;

    /// \copydoc Erebot_Interface_Numerics::ERR_PASSWDMISMATCH.
    const ERR_PASSWDMISMATCH            = 464;

    /// \copydoc Erebot_Interface_Numerics::ERR_YOUREBANNEDCREEP.
    const ERR_YOUREBANNEDCREEP          = 465;

    /// \copydoc Erebot_Interface_Numerics::ERR_OPERONLYCHAN.
    const ERR_OPERONLYCHAN              = 470;

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

    /// \copydoc Erebot_Interface_Numerics::ERR_BANLISTFULL.
    const ERR_BANLISTFULL               = 478;

    /// \copydoc Erebot_Interface_Numerics::ERR_BADCHANNAME.
    const ERR_BADCHANNAME               = 479;

    /// \copydoc Erebot_Interface_Numerics::ERR_SSLONLYCHAN.
    const ERR_SSLONLYCHAN               = 480;

    /// \copydoc Erebot_Interface_Numerics::ERR_NOPRIVILEGES.
    const ERR_NOPRIVILEGES              = 481;

    /// \copydoc Erebot_Interface_Numerics::ERR_CHANOPRIVSNEEDED.
    const ERR_CHANOPRIVSNEEDED          = 482;

    /// \copydoc Erebot_Interface_Numerics::ERR_CANTKILLSERVER.
    const ERR_CANTKILLSERVER            = 483;

    /// \copydoc Erebot_Interface_Numerics::ERR_RESTRICTED.
    const ERR_RESTRICTED                = 484;

    /// \copydoc Erebot_Interface_Numerics::ERR_NOOPERHOST.
    const ERR_NOOPERHOST                = 491;

    /// \copydoc Erebot_Interface_Numerics::ERR_UMODEUNKNOWNFLAG.
    const ERR_UMODEUNKNOWNFLAG          = 501;

    /// \copydoc Erebot_Interface_Numerics::ERR_USERSDONTMATCH.
    const ERR_USERSDONTMATCH            = 502;

    /// \copydoc Erebot_Interface_Numerics::ERR_GHOSTEDCLIENT.
    const ERR_GHOSTEDCLIENT             = 503;

    /// \copydoc Erebot_Interface_Numerics::ERR_USERNOTONSERV.
    const ERR_USERNOTONSERV             = 504;

    /// \copydoc Erebot_Interface_Numerics::ERR_TOOMANYWATCH.
    const ERR_TOOMANYWATCH              = 512;

    /// \copydoc Erebot_Interface_Numerics::ERR_WRONGPONG.
    const ERR_WRONGPONG                 = 513;

    /// \copydoc Erebot_Interface_Numerics::ERR_LONGMASK.
    const ERR_LONGMASK                  = 518;

    /// \copydoc Erebot_Interface_Numerics::ERR_LISTSYNTAX.
    const ERR_LISTSYNTAX                = 521;

    /// \copydoc Erebot_Interface_Numerics::ERR_HELPNOTFOUND.
    const ERR_HELPNOTFOUND              = 524;

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

    /// \copydoc Erebot_Interface_Numerics::RPL_WHOISSECURE.
    const RPL_WHOISSECURE               = 671;

    /// \copydoc Erebot_Interface_Numerics::RPL_MODLIST.
    const RPL_MODLIST                   = 702;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFMODLIST.
    const RPL_ENDOFMODLIST              = 703;

    /// \copydoc Erebot_Interface_Numerics::RPL_HELPSTART.
    const RPL_HELPSTART                 = 704;

    /// \copydoc Erebot_Interface_Numerics::RPL_HELPTXT.
    const RPL_HELPTXT                   = 705;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFHELP.
    const RPL_ENDOFHELP                 = 706;

    /// \copydoc Erebot_Interface_Numerics::RPL_ETRACE_FULL.
    const RPL_ETRACE_FULL               = 708;

    /// \copydoc Erebot_Interface_Numerics::RPL_ETRACE.
    const RPL_ETRACE                    = 709;

    /// \copydoc Erebot_Interface_Numerics::RPL_KNOCK.
    const RPL_KNOCK                     = 710;

    /// \copydoc Erebot_Interface_Numerics::RPL_KNOCKDLVR.
    const RPL_KNOCKDLVR                 = 711;

    /// \copydoc Erebot_Interface_Numerics::ERR_TOOMANYKNOCK.
    const ERR_TOOMANYKNOCK              = 712;

    /// \copydoc Erebot_Interface_Numerics::ERR_CHANOPEN.
    const ERR_CHANOPEN                  = 713;

    /// \copydoc Erebot_Interface_Numerics::ERR_KNOCKONCHAN.
    const ERR_KNOCKONCHAN               = 714;

    /// \copydoc Erebot_Interface_Numerics::ERR_KNOCKDISABLED.
    const ERR_KNOCKDISABLED             = 715;

    /// \copydoc Erebot_Interface_Numerics::RPL_TARGUMODEG.
    const RPL_TARGUMODEG                = 716;

    /// \copydoc Erebot_Interface_Numerics::RPL_TARGNOTIFY.
    const RPL_TARGNOTIFY                = 717;

    /// \copydoc Erebot_Interface_Numerics::RPL_UMODEGMSG.
    const RPL_UMODEGMSG                 = 718;

    /// \copydoc Erebot_Interface_Numerics::RPL_OMOTDSTART.
    const RPL_OMOTDSTART                = 720;

    /// \copydoc Erebot_Interface_Numerics::RPL_OMOTD.
    const RPL_OMOTD                     = 721;

    /// \copydoc Erebot_Interface_Numerics::RPL_ENDOFOMOTD.
    const RPL_ENDOFOMOTD                = 722;

    /// \copydoc Erebot_Interface_Numerics::ERR_NOPRIVS.
    const ERR_NOPRIVS                   = 723;

    /// \copydoc Erebot_Interface_Numerics::RPL_TESTMASK.
    const RPL_TESTMASK                  = 724;

    /// \copydoc Erebot_Interface_Numerics::RPL_TESTLINE.
    const RPL_TESTLINE                  = 725;

    /// \copydoc Erebot_Interface_Numerics::RPL_NOTESTLINE.
    const RPL_NOTESTLINE                = 726;

    /// \copydoc Erebot_Interface_Numerics::RPL_ISCAPTURED.
    const RPL_ISCAPTURED                = 727;

    /// \copydoc Erebot_Interface_Numerics::RPL_ISUNCAPTURED.
    const RPL_ISUNCAPTURED              = 728;

    /// \copydoc Erebot_Interface_Numerics::ERR_LAST_ERR_MSG.
    const ERR_LAST_ERR_MSG              = 999;
}
