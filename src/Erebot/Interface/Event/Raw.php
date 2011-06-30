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
interface   Erebot_Interface_Event_Raw
extends     Erebot_Interface_Event_Base_Generic,
            Erebot_Interface_Event_Base_Source,
            Erebot_Interface_Event_Base_Target,
            Erebot_Interface_Event_Base_Text
{
    /**
     *  \TODO
     */
    const RPL_SNOMASK               =   8;
    const RPL_SNOMASKIS             =   8;

    /**
     *  \TODO
     */
    const RPL_SAVENICK              =  43;

    /**
     *  \TODO
     */
    const RPL_STATSPLINE            = 217;

    /**
     *  \TODO
     */
    const RPL_STATS_E               = 223;

    const RPL_STATSELINE            = 223;  // Bahamut

    /**
     *  \TODO
     */
    const RPL_STATS_D               = 224;
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
    const RPL_USINGSSL              = 275;

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
    const RPL_COMMANDSYNTAX         = 334;
    const RPL_LISTSYNTAX            = 334;

    /**
     *  \TODO
     */
    const RPL_WHOISTEXT             = 337;

    /**
     *  \TODO
     */
    const RPL_WHOISACTUALLY         = 338;

    /**
     *  \TODO
     */
    const RPL_WHOISHOST             = 378;

    const RPL_WHOISMODES            = 379;  // UnrealIRCd

    /**
     *  \TODO
     */
    const ERR_NOCTRLSONCHAN         = 408;

    /**
     *  \TODO
     */
    const ERR_BANONCHAN             = 435;

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

    const ERR_CANTCHANGENICK        = 447;
    const ERR_NONICKCHANGE          = 447;  // UnrealIRCd

    /**
     *  \TODO
     */
    const ERR_INVALIDUSERNAME       = 468;

    /**
     *  \TODO
     */
    const ERR_BADCHANNAME           = 479;

    /**
     *  \TODO
     */
    const ERR_ISCHANSERVICE         = 484;

    /**
     *  \TODO
     */
    const ERR_CHANBANREASON         = 485;

    /**
     *  \TODO
     */
    const ERR_NOSSL                 = 488;
    const ERR_NOTSSLCLIENT          = 488;  // UltimateIRCd

    const ERR_NOCTCPALLOWED         = 492;
    const ERR_NOCTCP                = 492;

    /**
     *  \TODO
     */
    const ERR_OWNMODE               = 494;

    /**
     *  \TODO
     */
    const ERR_GHOSTEDCLIENT         = 503;

    /**
     *  \TODO
     */
    const ERR_BADPING               = 513;
    const ERR_NEEDPONG              = 513;  // UnrealIRCd

    /**
     *  \TODO
     */
    const ERR_CANTJOINOPERSONLY     = 520;
    const ERR_OPERONLY              = 520;  // UnrealIRCd

    /**
     *  \TODO
     */
    const ERR_WHOSYNTAX             = 522;

    /**
     *  \TODO
     */
    const ERR_WHOLIMEXCEED          = 523;

    const RPL_WHOHOST               = 671;  // UltimateIRCd

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

