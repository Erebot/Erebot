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
 */
interface Erebot_Interface_IrcComparator
{
    /**
     * Compares two string in a case-sensitive fashion (like strcmp).
     *
     * \param $a
     *      First string to compare.
     *
     * \param $b
     *      Second string to compare.
     *
     * \retval int
     *      An integer less than, equal to or greater than zero if
     *      $a is found, respectively, to be less than, to match,
     *      or be greater than $b.
     */
    public function irccmp($a, $b);

    /**
     * Compares two string in a case-sensitive fashion
     * up to a given length (like strncmp).
     *
     * \param $a
     *      First string to compare.
     *
     * \param $b
     *      Second string to compare.
     *
     * \param $len
     *      Limit the comparison to only the first $len bytes
     *      of each string.
     *
     * \retval int
     *      An integer less than, equal to or greater than zero if
     *      the first $len bytes of $a are found, respectively,
     *      to be less than, to match, or be greater than the first
     *      $len bytes of $b.
     */
    public function ircncmp($a, $b, $len);

    /**
     * Compares two string in a case-insensitive fashion
     * (like strcasecmp).
     *
     * \param $a
     *      First string to compare.
     *
     * \param $b
     *      Second string to compare.
     *
     * \param $mappingName
     *      (optional) Name of the mapping which will be used to
     *      normalize the two strings. Possible values include
     *      "strict-rfc1459", "rfc1459" and "ascii".
     *      The default is to determine the right mapping to use
     *      automatically.
     *
     * \retval int
     *      An integer less than, equal to or greater than zero if
     *      $a is found, respectively, to be less than, to match,
     *      or be greater than $b.
     *
     * \throw Erebot_InvalidValueException
     *      An invalid $mappingName was given.
     *
     * \throw Erebot_NotFoundException
     *      No mapping could be found matching $mappingName.
     */
    public function irccasecmp($a, $b, $mappingName = NULL);

    /**
     * Compares two string in a case-insensitive fashion
     * up to a given length (like strncasecmp).
     *
     * \param $a
     *      First string to compare.
     *
     * \param $b
     *      Second string to compare.
     *
     * \param $len
     *      Limit the comparison to only the first $len bytes
     *      of each string.
     *
     * \param $mappingName
     *      (optional) Name of the mapping which will be used to
     *      normalize the two strings. Possible values include
     *      "strict-rfc1459", "rfc1459" and "ascii".
     *      The default is to determine the right mapping to use
     *      automatically.
     *
     * \retval int
     *      An integer less than, equal to or greater than zero if
     *      the first $len bytes of $a are found, respectively,
     *      to be less than, to match, or be greater than the first
     *      $len bytes of $b.
     *
     * \throw Erebot_InvalidValueException
     *      An invalid $mappingName was given.
     *
     * \throw Erebot_NotFoundException
     *      No mapping could be found matching $mappingName.
     */
    public function ircncasecmp($a, $b, $len, $mappingName = NULL);

    /**
     * Determines if the given string is a valid channel name or not.
     * A channel name usually starts with the hash symbol (#).
     * Valid characters for the rest of the name vary between IRC networks.
     *
     * \param $chan
     *      Tentative channel name.
     *
     * \retval bool
     *      TRUE if $chan is a valid channel name, FALSE otherwise.
     *
     * \throw Erebot_InvalidValueException
     *      $chan is not a string or is empty.
     */
    public function isChannel($chan);

    public function normalizeNick($nick, $mappingName = NULL);
}
