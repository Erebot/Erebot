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

interface   Erebot_Interface_RawProfile_WATCH
extends     Erebot_Interface_RawProfile
{
    /**
     *  \TODO
     */
    const ERR_TOOMANYWATCH          = 512;

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
}

