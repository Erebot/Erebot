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
 *      Interface for raw message handlers.
 *
 * This interface provides the necessary methods to handle
 * a raw numeric message from an IRC server.
 */
interface Erebot_Interface_RawHandler
{
    /**
     * Constructs a raw event handler.
     *
     * \param callback $callback
     *      A callback function/method which will be called
     *      whenever the bot receives a message of the given
     *      $raw numeric.
     *
     * \param int $raw
     *      The particular raw numeric code this raw handler will
     *      react to.
     */
    public function __construct($callback, $raw);

    /**
     * Returns the raw numeric code associated with this handler.
     *
     * \retval int
     *      The raw numeric code for this handler.
     */
    public function getRaw();

    /**
     * Returns the callback function/method associated with
     * this handler.
     *
     * \retval callback
     *      The callback for this handler.
     */
    public function getCallback();

    /**
     * Given a raw message, this methods tries to handle it.
     *
     * \param Erebot_Interface_Event_Raw $raw
     *      The raw message to try to handle.
     *
     * \note
     *      It is the implementation's duty to make any appropriate
     *      checks on the message and take any action depending on
     *      the result of those checks.
     */
    public function handleRaw(Erebot_Interface_Event_Raw &$raw);
}
