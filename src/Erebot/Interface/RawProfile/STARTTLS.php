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
 * \see
 *      InspIRCd's wiki on the STARTTLS feature
 *      (http://wiki.inspircd.org/STARTTLS_Documentation)
 */
interface   Erebot_Interface_RawProfile_STARTTLS
extends     Erebot_Interface_RawProfile
{
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
}
