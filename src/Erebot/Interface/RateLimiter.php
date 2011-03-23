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
 *      Interface used to implement a rate-limit on the number
 *      of messages the bot may send to any IRC server.
 */
interface   Erebot_Interface_RateLimiter
{
    /**
     * Decides whether a message can be sent or not,
     * using whatever strategy deemed useful.
     * Classes implementing this interface are responsible
     * for keeping track of the measurements required for
     * their proper functioning.
     *
     * \retval bool
     *      Returns TRUE if the message can be sent,
     *      FALSE otherwise.
     *
     * \note
     *      If an exception is thrown by this method,
     *      an implicit return value of TRUE may be
     *      assumed by its caller to avoid deadlocks.
     */
    public function canSend();
}
