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
    /// Specific commands/options supported by the server.
    const RPL_ISUPPORT              =   5;

    /// Active PROTOcol ConTroL flags (obsolete).
    const RPL_PROTOCTL              =   5;

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

    const RPL_STATSHELP             = 210;  // UnrealIRCd

    /**
     *  \TODO
     */
    const RPL_STATSOLDNLINE         = 214;

    /**
     *  \TODO
     */
    const RPL_STATSPLINE            = 217;

    const RPL_NMODEIS               = 220;

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

    const RPL_STATSBANVER           = 228;  // UnrealIRCd

    const RPL_STATSSPAMF            = 229;  // UnrealIRCd

    const RPL_STATSEXCEPTTKL        = 230;  // UnrealIRCd

    const RPL_RULES                 = 232;  // UnrealIRCd

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

    /**
     *  \TODO
     */
    const RPL_TEXT                  = 304;
    const RPL_SYNTAX                = 304;  // InspIRCd

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

    const RPL_WHOISSPECIAL          = 320;  // UnrealIRCd

    /**
     *  \TODO
     */
    const RPL_CREATIONTIME          = 329;
    const RPL_CHANNELCREATED        = 329;

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

#    const RPL_USERIP                = 340;

    /**
     *  \TODO
     */
    const RPL_RWHOREPLY             = 354;

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
     *  \TODO
     */
    const RPL_NOTOPERANYMORE        = 385;

    const RPL_QLIST                 = 386;  // UnrealIRCd

    const RPL_ENDOFQLIST            = 387;  // UnrealIRCd

    const RPL_ALIST                 = 388;  // UnrealIRCd

    const RPL_ENDOFALIST            = 389;  // UnrealIRCd

    const RPL_YOURDISPLAYEDHOST     = 396;  // charybdis

    const ERR_NOCOLORSONCHAN        = 408;  // UltimateIRCd

    /**
     *  \TODO
     */
    const ERR_NOCTRLSONCHAN         = 408;

    const ERR_INVALIDCAPSUBCOMMAND  = 410;

    /**
     *  \TODO
     */
    const ERR_QUERYTOOLONG          = 416;

    const ERR_NOOPERMOTD            = 425;  // UnrealIRCd

    /**
     *  \TODO
     */
    const ERR_TOOMANYAWAY           = 429;

    const ERR_NORULES               = 434;  // InspIRCd

    /**
     *  \TODO
     */
    const ERR_BANONCHAN             = 435;

    const ERR_SERVICECONFUSED       = 435;  // UnrealIRCd

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

    const ERR_CANTCHANGENICK        = 447;
    const ERR_NONICKCHANGE          = 447;  // UnrealIRCd

    /**
     *  \TODO
     */
    const ERR_HOSTILENAME           = 455;

    const ERR_NOHIDING              = 459;  // UnrealIRCd

    const ERR_NOTFORHALFOPS         = 460;  // UnrealIRCd

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
     *  \TODO
     */
    const ERR_NEEDREGGEDNICK        = 477;

    /**
     *  \TODO
     */
    const ERR_BADCHANNAME           = 479;

    const ERR_LINKFAIL              = 479;  // UnrealIRCd

    const ERR_CANNOTKNOCK           = 480;  // UnrealIRCd

    const ERR_SERVERONLY            = 480;  // UltimateIRCd

    const ERR_DESYNC                = 484;

    /**
     *  \TODO
     */
    const ERR_ISCHANSERVICE         = 484;

    const ERR_ATTACKDENY            = 484;

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

    const ERR_UNKNOWNSNOMASK        = 501;  // InspIRCd

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

