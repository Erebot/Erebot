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
 */
interface Erebot_Interface_Event_Raw
{
    /// @TODO document each constant...

    /* Define constant names for raw messages. */
    const RPL_WELCOME               =   1;
    const RPL_YOURHOST              =   2;
    const RPL_CREATED               =   3;
    const RPL_MYINFO                =   4;
    const RPL_ISUPPORT              =   5;
    const RPL_PROTOCTL              =   5;
    const RPL_BOUNCE                =   5;
    const RPL_MAP                   =   6;
    const RPL_MAPMORE               =   6;
    const RPL_MAPEND                =   7;
    const RPL_SNOMASK               =   8;
    const RPL_STATMEM               =  10;
    const RPL_STATMEMTOT            =  10;
    const RPL_YOURCOOKIE            =  14;
    const RPL_YOURID                =  42;
    const RPL_SAVENICK              =  43;
    const RPL_ATTEMPTINGJUNC        =  50;
    const RPL_ATTEMPTINGREROUTE     =  51;

    const RPL_TRACELINK             = 200;
    const RPL_TRACECONNECTING       = 201;
    const RPL_TRACEHANDSHAKE        = 202;
    const RPL_TRACEUNKNOWN          = 203;
    const RPL_TRACEOPERATOR         = 204;
    const RPL_TRACEUSER             = 205;
    const RPL_TRACESERVER           = 206;
    const RPL_TRACESERVICE          = 207;
    const RPL_TRACENEWTYPE          = 208;
    const RPL_TRACECLASS            = 209;
    const RPL_TRACECONNECT          = 210;
    const RPL_STATSLINKINFO         = 211;
    const RPL_STATSCOMMANDS         = 212;
    const RPL_STATSCLINE            = 213;
    const RPL_STATSNLINE            = 214;
    const RPL_STATSOLDNLINE         = 214;
    const RPL_STATSILINE            = 215;
    const RPL_STATSKLINE            = 216;
    const RPL_STATSPLINE            = 217;
    const RPL_STATSQLINE            = 217;
    const RPL_STATSYLINE            = 218;
    const RPL_ENDOFSTATS            = 219;
    const RPL_STATSBLINE            = 220;
    const RPL_UMODEIS               = 221;
    const RPL_SQLINE_NICK           = 222;
    const RPL_STATS_E               = 223;
    const RPL_STATS_D               = 224;
    const RPL_STATSCLONE            = 225;
    const RPL_STATSCOUNT            = 226;
    const RPL_STATSGLINE            = 227;
    const RPL_SERVICEINFO           = 231;
    const RPL_ENDOFSERVICES         = 232;
    const RPL_SERVICE               = 233;
    const RPL_SERVLIST              = 234;
    const RPL_SERVLISTEND           = 235;
    const RPL_STATSLLINE            = 241;
    const RPL_STATSUPTIME           = 242;
    const RPL_STATSOLINE            = 243;
    const RPL_STATSHLINE            = 244;
    const RPL_STATSSLINE            = 245;
    const RPL_STATSXLINE            = 246;
    const RPL_STATSULINE            = 248;
    const RPL_STATSDEBUG            = 249;
    const RPL_STATSCONN             = 250;
    const RPL_LUSERCLIENT           = 251;
    const RPL_LUSEROP               = 252;
    const RPL_LUSERUNKNOWN          = 253;
    const RPL_LUSERCHANNELS         = 254;
    const RPL_LUSERME               = 255;
    const RPL_ADMINME               = 256;
    const RPL_ADMINLOC1             = 257;
    const RPL_ADMINLOC2             = 258;
    const RPL_ADMINEMAIL            = 259;
    const RPL_TRACELOG              = 261;
    const RPL_TRACEPING             = 262;
    const RPL_LOAD2HI               = 263;
    const RPL_TRYAGAIN              = 263;
    const RPL_LOCALUSERS            = 265;
    const RPL_LUSERSC               = 265;
    const RPL_GLOBALUSERS           = 266;
    const RPL_LUSERSG               = 266;
    const RPL_SILELIST              = 271;
    const RPL_ENDOFSILELIST         = 272;
    const RPL_STATSDLINE            = 275;
    const RPL_GLIST                 = 280;
    const RPL_ENDOFGLIST            = 281;
    const RPL_HELPHDR               = 290;
    const RPL_HELPOP                = 291;
    const RPL_HELPTLR               = 292;
    const RPL_HELPHLP               = 293;
    const RPL_HELPFWD               = 294;
    const RPL_HELPIGN               = 295;
    const RPL_NEWNICK               = 298;

    const RPL_AWAY                  = 301;
    const RPL_USERHOST              = 302;
    const RPL_ISON                  = 303;
    const RPL_UNAWAY                = 305;
    const RPL_NOWAWAY               = 306;
    const RPL_USERIP                = 307;
    const RPL_WHOISREGNICK          = 307;
    const RPL_WHOISADMIN            = 308;
    const RPL_WHOISSADMIN           = 309;
    const RPL_WHOISSVCMSG           = 310;
    const RPL_WHOISHELPOP           = 310;
    const RPL_WHOISUSER             = 311;
    const RPL_WHOISSERVER           = 312;
    const RPL_WHOISOPERATOR         = 313;
    const RPL_WHOWASUSER            = 314;
    const RPL_ENDOFWHO              = 315;
    const RPL_WHOISCHANOP           = 316;
    const RPL_WHOISIDLE             = 317;
    const RPL_ENDOFWHOIS            = 318;
    const RPL_WHOISCHANNELS         = 319;
    const RPL_LISTSTART             = 321;
    const RPL_LIST                  = 322;
    const RPL_LISTEND               = 323;
    const RPL_CHANNELMODEIS         = 324;
    const RPL_UNIQOPIS              = 325;
    const RPL_CREATIONTIME          = 329;
    const RPL_NOTOPIC               = 331;
    const RPL_TOPIC                 = 332;
    const RPL_TOPICWHOTIME          = 333;
    const RPL_COMMANDSYNTAX         = 334;
    const RPL_WHOISTEXT             = 337;
    const RPL_WHOISACTUALLY         = 338;
    const RPL_INVITING              = 341;
    const RPL_SUMMONING             = 342;
    const RPL_INVITELIST            = 346;
    const RPL_ENDOFINVITELIST       = 347;
    const RPL_EXCEPTLIST            = 348;
    const RPL_EXEMPTLIST            = 348;
    const RPL_ENDOFEXCEPTLIST       = 349;
    const RPL_ENDOFEXEMPTLIST       = 349;
    const RPL_VERSION               = 351;
    const RPL_WHOREPLY              = 352;
    const RPL_NAMREPLY              = 353;
    const RPL_RWHOREPLY             = 354;
    const RPL_CLOSING               = 362;
    const RPL_CLOSEEND              = 363;
    const RPL_LINKS                 = 364;
    const RPL_ENDOFLINKS            = 365;
    const RPL_ENDOFNAMES            = 366;
    const RPL_BANLIST               = 367;
    const RPL_ENDOFBANLIST          = 368;
    const RPL_ENDOFWHOWAS           = 369;
    const RPL_INFO                  = 371;
    const RPL_MOTD                  = 372;
    const RPL_ENDOFINFO             = 374;
    const RPL_MOTDSTART             = 375;
    const RPL_ENDOFMOTD             = 376;
    const RPL_ISASERVICE            = 377;
    const RPL_WHOISHOST             = 378;
    const RPL_YOUREOPER             = 381;
    const RPL_REHASHING             = 382;
    const RPL_MYPORTIS              = 384;
    const RPL_TIME                  = 391;

    const ERR_NOSUCHNICK            = 401;
    const ERR_NOSUCHSERVER          = 402;
    const ERR_NOSUCHCHANNEL         = 403;
    const ERR_CANNOTSENDTOCHAN      = 404;
    const ERR_TOOMANYCHANNELS       = 405;
    const ERR_WASNOSUCHNICK         = 406;
    const ERR_TOOMANYTARGETS        = 407;
    const ERR_NOSUCHSERVICE         = 408;
    const ERR_NOCTRLSONCHAN         = 408;
    const ERR_NOORIGIN              = 409;
    const ERR_NORECIPIENT           = 411;
    const ERR_NOTEXTTOSEND          = 412;
    const ERR_NOTOPLEVEL            = 413;
    const ERR_WILDTOPLEVEL          = 414;
    const ERR_BADMASK               = 415;
    const ERR_QUERYTOOLONG          = 416;
    const ERR_UNKNOWNCOMMAND        = 421;
    const ERR_NOMOTD                = 422;
    const ERR_NOADMININFO           = 423;
    const ERR_FILEERROR             = 424;
    const ERR_TOOMANYAWAY           = 429;
    const ERR_NONICKNAMEGIVEN       = 431;
    const ERR_ERRONEUSNICKNAME      = 432;
    const ERR_NICKNAMEINUSE         = 433;
    const ERR_BANONCHAN             = 435;
    const ERR_NICKCOLLISION         = 436;
    const ERR_BANNICKCHANGE         = 437;
    const ERR_NICKTOOFAST           = 438;
    const ERR_TARGETTOOFAST         = 439;
    const ERR_SERVICESDOWN          = 440;
    const ERR_USERNOTINCHANNEL      = 441;
    const ERR_NOTONCHANNEL          = 442;
    const ERR_USERONCHANNEL         = 443;
    const ERR_NOLOGIN               = 444;
    const ERR_SUMMONDISABLED        = 445;
    const ERR_USERSDISABLED         = 446;
    const ERR_NOTREGISTERED         = 451;
    const ERR_HOSTILENAME           = 455;
    const ERR_NEEDMOREPARAMS        = 461;
    const ERR_ALREADYREGISTRED      = 462;
    const ERR_NOPERMFORHOST         = 463;
    const ERR_PASSWDMISMATCH        = 464;
    const ERR_YOUREBANNEDCREEP      = 465;
    const ERR_KEYSET                = 467;
    const ERR_INVALIDUSERNAME       = 468;
    const ERR_ONLYSERVERSCANCHANGE  = 468;
    const ERR_CHANNELISFULL         = 471;
    const ERR_UNKNOWNMODE           = 472;
    const ERR_INVITEONLYCHAN        = 473;
    const ERR_BANNEDFROMCHAN        = 474;
    const ERR_BADCHANNELKEY         = 475;
    const ERR_BADCHANMASK           = 476;
    const ERR_NEEDREGGEDNICK        = 477;
    const ERR_NOCHANMODES           = 477;
    const ERR_BANLISTFULL           = 478;
    const ERR_BADCHANNAME           = 479;
    const ERR_NOPRIVILEGES          = 481;
    const ERR_CHANOPPRIVSNEEDED     = 482;
    const ERR_CANTKILLSERVER        = 483;
    const ERR_ISCHANSERVICE         = 484;
    const ERR_CHANBANREASON         = 485;
    const ERR_NONONREG              = 486;
    const ERR_MSGSERVICES           = 487;
    const ERR_NOOPERHOST            = 491;
    const ERR_OWNMODE               = 494;

    const ERR_UMODEUNKNOWNFLAG      = 501;
    const ERR_USERSDONTMATCH        = 502;
    const ERR_SILELISTFULL          = 511;
    const ERR_NOSUCHGLINE           = 512;
    const ERR_TOOMANYWATCH          = 512;
    const ERR_BADPING               = 513;
    const ERR_TOOMANYDCC            = 514;
    const ERR_LISTSYNTAX            = 521;
    const ERR_WHOSYNTAX             = 522;
    const ERR_WHOLIMEXCEED          = 523;

    const RPL_LOGON                 = 600;
    const RPL_LOGOFF                = 601;
    const RPL_WATCHOFF              = 602;
    const RPL_WATCHSTAT             = 603;
    const RPL_NOWON                 = 604;
    const RPL_NOWOFF                = 605;
    const RPL_WATCHLIST             = 606;
    const RPL_ENDOFWATCHLIST        = 607;
    const RPL_DCCSTATUS             = 617;
    const RPL_DCCLIST               = 618;
    const RPL_ENDOFDCCLIST          = 619;
    const RPL_DCCINFO               = 620;
    
    // Those are unofficial raws for InspIRCd's STARTTLS extension.
    const RPL_STARTTLSOK            = 670;
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
        Erebot_Interface_Connection    &$connection,
                                        $raw,
                                        $source,
                                        $target,
                                        $text
    );

    /**
     * Returns the connection this raw message came from.
     * This is the same object as that passed during construction.
     *
     * \retval Erebot_Interface_Connection
     *      The connection this raw message came from.
     */
    public function & getConnection();

    /**
     * Returns the raw numeric code associated with
     * the current message.
     *
     * \retval int
     *      The raw numeric code of this message.
     *
     * \see
     *      You may compare the value returned by this method
     *      with one of the constants defined in raws.php.
     *
     * \note
     *      Multiple constants may point to the same code
     *      as the same code may have different interpretations
     *      depending on the server (IRCd) where it is used.
     */
    public function getRaw();

    /**
     * Returns the source of the current message.
     * This will generally be the name of an IRC
     * server.
     *
     * \retval string
     *      The source of this message.
     */
    public function getSource();

    /**
     * Returns the target of the current message.
     * This will generally be the bot's nickname.
     *
     * \retval string
     *      The target of this message.
     */
    public function getTarget();

    /**
     * Returns the raw content of the current
     * message. No attempt is made at parsing
     * the content.
     *
     * \retval string
     *      The content of this message.
     */
    public function getText();
}

/// @TODO For backward-compatibility (will be removed in 0.5.0).
$reflector = new ReflectionClass('Erebot_Interface_Event_Raw');
foreach ($reflector->getConstants() as $name => $value) {
    if (strncasecmp($name, 'ERR_', 4) &&
        strncasecmp($name, 'RPL_', 4))
        continue;
    if (defined($name))
        continue;
    define($name, $value);
}
unset($reflector, $name, $value);

