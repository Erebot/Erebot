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

interface   Erebot_Interface_RawProfile_UnrealIRCd
extends     Erebot_Interface_RawProfile_RFC2812,
            Erebot_Interface_RawProfile_NumericError,
            Erebot_Interface_RawProfile_005,
            Erebot_Interface_RawProfile_Bounce,
            Erebot_Interface_RawProfile_CommonExtensions,
            Erebot_Interface_RawProfile_RULES,
            Erebot_Interface_RawProfile_SILENCE,
            Erebot_Interface_RawProfile_ISON,
            Erebot_Interface_RawProfile_DCCINFO,
            Erebot_Interface_RawProfile_MAP
{
    const RPL_REMOTEISUPPORT        = 105;
    const RPL_STATSHELP             = 210;
    const RPL_STATSOLDNLINE         = 214;
    const RPL_SQLINE                = 222;
    const RPL_STATSTLINE            = 224;
#    const RPL_STATSNLINE            = 226;
    const RPL_STATSBANVER           = 228;
    const RPL_STATSSPAMF            = 229;
    const RPL_STATSEXCEPTTKL        = 230;
    const RPL_STATSXLINE            = 246;
    const RPL_HELPHDR               = 290;
    const RPL_HELPOP                = 291;
    const RPL_HELPTLR               = 292;
    const RPL_HELPHLP               = 293;
    const RPL_HELPFWD               = 294;
    const RPL_HELPIGN               = 295;

    /**
     *  \TODO
     */
    const RPL_TEXT                  = 304;

    const RPL_WHOISHELPOP           = 310;
    const RPL_WHOISSPECIAL          = 320;
    const RPL_WHOISBOT              = 335;
    const RPL_USERIP                = 340;
    const RPL_QLIST                 = 386;
    const RPL_ENDOFQLIST            = 387;
    const RPL_ALIST                 = 388;
    const RPL_ENDOFALIST            = 389;
    const ERR_NOOPERMOTD            = 425;
    const ERR_SERVICECONFUSED       = 435;
    const ERR_HOSTILENAME           = 455;
    const ERR_NOHIDING              = 459;
    const ERR_NOTFORHALFOPS         = 460;
    const ERR_LINKSET               = 469;
    const ERR_LINKCHANNEL           = 470;
    const ERR_LINKFAIL              = 479;
    const ERR_CANNOTKNOCK           = 480;
    const ERR_ATTACKDENY            = 484;
    const ERR_KILLDENY              = 485;
    const ERR_NOTFORUSERS           = 487;
    const ERR_HTMDISABLED           = 488;
    const ERR_SECUREONLYCHAN        = 489;

    const ERR_NOSWEAR               = 490;
    const ERR_WORDFILTERED          = 490;

    const ERR_CHANOWNPRIVNEEDED     = 499;
    const ERR_TOOMANYJOINS          = 500;
    const ERR_DISABLED              = 517;
    const ERR_NOINVITE              = 518;
    const ERR_ADMONLY               = 519;
    const ERR_OPERSPVERIFY          = 524;
    const RPL_REAWAY                = 597;
    const RPL_GONEAWAY              = 598;
    const RPL_NOTAWAY               = 599;
    const RPL_CLEARWATCH            = 608;
    const RPL_NOWISAWAY             = 609;
    const RPL_DUMPING               = 640;
    const RPL_DUMPRPL               = 641;
    const RPL_EODUMP                = 642;
    const RPL_SPAMCMDFWD            = 659;
    const RPL_WHOISSECURE           = 671;
    const ERR_CANNOTDOCOMMAND       = 972;
    const ERR_CANNOTCHANGECHANMODE  = 974;
}

