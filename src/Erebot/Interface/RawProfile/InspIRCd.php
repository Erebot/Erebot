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

interface   Erebot_Interface_RawProfile_InspIRCd
extends     Erebot_Interface_RawProfile_RFC2812,
            Erebot_Interface_RawProfile_005,
            Erebot_Interface_RawProfile_UniqueUserID,
            Erebot_Interface_RawProfile_RULES,
            Erebot_Interface_RawProfile_MAP,
            Erebot_Interface_RawProfile_STARTTLS
{
    const RPL_MAPUSERS              = 270;
    const RPL_SYNTAX                = 304;
    const RPL_YOURDISPLAYEDHOST     = 396;
    const ERR_INVALIDCAPSUBCOMMAND  = 410;
    const ERR_ALLMUSTSSL            = 490;

    const ERR_DELAYREJOIN           = 495;
    const ERR_KICKNOREJOIN          = 495;

    const ERR_UNKNOWNSNOMASK        = 501;

    const ERR_CANTSENDTOUSER        = 531;

    const RPL_COMMANDS              = 702;
    const RPL_COMMANDSEND           = 703;

    const ERR_WORDFILTERED          = 936;
    const ERR_NOSWEAR               = 936;

    const ERR_CANTUNLOADMODULE      = 972;
    const RPL_UNLOADEDMODULE        = 973;
    const ERR_CANTLOADMODULE        = 974;
    const RPL_LOADEDMODULE          = 975;
}

