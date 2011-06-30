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

interface   Erebot_Interface_RawProfile_Bahamut
extends     Erebot_Interface_RawProfile_RFC2812,
            Erebot_Interface_RawProfile_NumericError,
            Erebot_Interface_RawProfile_SILENCE,
            Erebot_Interface_RawProfile_ISON,
            Erebot_Interface_RawProfile_DCCINFO
{
    /**
     *  \TODO
     */
    const RPL_RWHOREPLY             = 354;
    const ERR_DESYNC                = 484;
    const ERR_MSGSERVICES           = 487;

    /**
     *  \TODO
     */
    const ERR_NOSHAREDCHAN          = 493;
}

