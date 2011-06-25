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

interface   Erebot_Interface_RawProfile_ReservedRFC1459
extends     Erebot_Interface_RawProfile
{
    const RPL_TRACECLASS            = 209;
    const RPL_STATSQLINE            = 217;

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

    /**
     *  \brief
     *      When listing services in reply to a SERVLIST message,
     *      a separate RPL_SERVLIST is sent for each service.
     *
     *  \format{"<name> <server> <mask> <type> <hopcount> <info>"}
     */
    const RPL_SERVLIST              = 234;

    /**
     *  \brief
     *      Marks the end of the list of services,
     *      sent in response to a SERVLIST message.
     *
     *  \format{"<mask> <type> :End of service listing"}
     */
    const RPL_SERVLISTEND           = 235;

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
     *  \brief
     *      Sent by a server to a user to inform that access to the
     *      server will soon be denied.
     */
    const ERR_YOUWILLBEBANNED       = 466;

    const ERR_BADCHANMASK           = 476;
    const ERR_NOSERVICEHOST         = 492;
}
