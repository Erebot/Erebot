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

interface   Erebot_Interface_RawProfile_CommonExtensions
extends     Erebot_Interface_RawProfile
{
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
    const RPL_GLOBALUSERS           = 266;

    const RPL_CREATIONTIME          = 329;
    const RPL_CHANNELCREATED        = 329;

    const RPL_TOPICWHOTIME          = 333;
    const RPL_TOPICTIME             = 333;

    /**
     *  \TODO
     */
    const ERR_BANNICKCHANGE         = 437;

    /**
     *  \TODO
     */
    const ERR_SERVICESDOWN          = 440;

    /**
     *  \TODO
     */
    const ERR_ONLYSERVERSCANCHANGE  = 468;

    /**
     *  \TODO
     */
    const ERR_NEEDREGGEDNICK        = 477;

    /**
     *  \TODO
     */
    const ERR_NONONREG              = 486;

    /**
     *  \TODO
     */
    const ERR_LISTSYNTAX            = 521;
}

