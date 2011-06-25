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

interface   Erebot_Interface_RawProfile_NonGenericRFC2812
extends     Erebot_Interface_RawProfile
{
    /**
     *  \TODO
     */
    const RPL_SERVICEINFO           = 231;

    /**
     *  \TODO
     */
    const RPL_ENDOFSERVICES         = 232;

    /**
     *  \TODO
     */
    const RPL_SERVICE               = 233;

    /// Dummy reply number. Not used.
    const RPL_NONE                  = 300;

    /**
     *  \TODO
     */
    const RPL_WHOISCHANOP           = 316;

    /**
     *  \TODO
     */
    const RPL_KILLDONE              = 361;

    /**
     *  \TODO
     */
    const RPL_CLOSING               = 362;

    /**
     *  \TODO
     */
    const RPL_CLOSEEND              = 363;


    const RPL_INFOSTART             = 373;

    /**
     *  \TODO
     */
    const RPL_MYPORTIS              = 384;

    /**
     *  \TODO
     *  "C <address> * <server> <port> <class>"
     *
     *  \note
     *      The "*" is treated as a litteral, not some
     *      wildcard character.
     */
    const RPL_STATSCLINE            = 213;

    /**
     *  \TODO
     */
    const RPL_STATSNLINE            = 214;

    /**
     *  \TODO
     */
    const RPL_STATSILINE            = 215;

    /**
     *  \TODO
     */
    const RPL_STATSKLINE            = 216;

    /**
     *  \TODO
     */
    const RPL_STATSQLINE            = 217;

    /**
     *  \TODO
     */
    const RPL_STATSYLINE            = 218;

    const RPL_STATSVLINE            = 240;
    const RPL_STATSLLINE            = 241;
    const RPL_STATSHLINE            = 244;

    /**
     *  \TODO
     */
    const RPL_STATSSLINE            = 245;

    const RPL_STATSPING             = 246;
    const RPL_STATSBLINE            = 247;
    const RPL_STATSDLINE            = 250;

    const ERR_NOSERVICEHOST         = 492;
}
