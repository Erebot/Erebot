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

interface   Erebot_Interface_RawProfile_DCCINFO
extends     Erebot_Interface_RawProfile
{
    /**
     *  \TODO
     */
    const ERR_TOOMANYDCC            = 514;

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
}

