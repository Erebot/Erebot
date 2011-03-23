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
 *      Represents the identity of an IRC user.
 */
interface Erebot_Interface_Identity
{
    /**
     * Returns the nickname of the user
     * represented by this identity.
     *
     * \retval mixed
     *      This user's nickname or NULL if unavailable.
     */
    public function getNick();

    /**
     * Returns the identity string of the user
     * represented by this identity.
     *
     * \retval mixed
     *      This user's identity string or NULL if unavailable.
     *
     * \note
     *      The name of this method is somewhat misleading,
     *      as it returns the "identity" as defined by the
     *      user in his/her client.
     *      This is not the same as the "identity" represented
     *      here (which contains additional information).
     *      To try to disambiguate, the term "identity string"
     *      has been used when referring to the user-defined
     *      identity. 
     */
    public function getIdent();

    /**
     * Returns the host of the user
     * represented by this identity.
     *
     * \retval string
     *      This user's hostname or NULL if unavailable.
     */
    public function getHost();

    /**
     * Returns a mask which can later be used
     * to match against this user.
     *
     * \retval string
     *      A mask matching against this user.
     *
     * \note
     *      Fields for which no value is available should be
     *      replaced with '*'. This can result in very generic
     *      masks (eg. "foo!*@*") if not enough information
     *      is known.
     */
    public function getMask();

    /**
     * This method works like Erebot_Interface_Identity::getNick(),
     * except that if no information is available on the user's
     * nickname, it should return a very distinctive value.
     * Eg. an appropriate return value in such a case could be '?'.
     *
     * \retval string
     *      This user's nickname or a distinctive value
     *      if it is not available.
     */
    public function __toString();
}
