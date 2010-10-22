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
 *      Interface for a timer implementation.
 *
 * This interface provides the necessary methods
 * to implement timers and make them available
 * to other parts of the bot.
 */
interface iErebotTimer
{
    /**
     * Creates a new timer, set off to call the given callback
     * (optionally, repeatedly) when the associated delay passed.
     *
     * \param callback $callback
     *      The callback to call when the timer expires.
     *      See http://php.net/manual/en/language.pseudo-types.php
     *      for acceptable callback values.
     *
     * \param int|float $delay
     *      The number of seconds to wait for before calling the
     *      callback. This may be a float/double or an int, but
     *      the implementation may choose to round it up to the
     *      nearest integer if sub-second precision is impossible
     *      to get (eg. on Windows).
     *
     * \param bool|int $repeat
     *      Either a boolean indicating whether the callback should
     *      be called repeatedly every $delay seconds or just once,
     *      or an integer specifying the exact number of times the
     *      callback will be called.
     */
    public function __construct($callback, $delay, $repeat);

    /**
     * Returns a reference to the callback associated with this timer.
     *
     * \retval callback
     *      The callback for this timer.
     */
    public function & getCallback();

    /**
     * Returns the delay after which the callback will be called.
     * This is the original value given to the timer during construction,
     * and it is not updated live as time passes by.
     *
     * \retval int
     *      The original delay for this timer.
     * \retval float
     *      The original delay for this timer.
     */
    public function getDelay();

    /**
     * Returns the repetition flag of this timer.
     * It can also be used to change that flag.
     *
     * \param bool|int $repeat
     *      (optional) If given, it can be:
     *      \arg    An integer indicating the number of times the timer
     *              will be triggered (with any negative value being
     *              treated as positive infinity).
     *      \arg    A boolean which indicates that the timer should call
     *              the callback periodically (TRUE, same as -1) or just
     *              once (FALSE, same as 1).
     *
     * \retval int
     *      Returns the repetition state of the timer.
     *      If the value was changed by means of the $repeat parameter,
     *      the value before any change was made is returned.
     *
     * \deprecated
     *      This method is only a wrapper around iErebotTimer::setRepetition
     *      and iErebotTimer::getRepetition meant to keep the code backward
     *      compatible. It will be removed as of Erebot 0.4.0.
     *      Use iErebotTimer::setRepetition & iErebotTimer::getRepetition
     *      in new code instead of this method.
     */
    public function isRepeated($repeat = NULL);

    /**
     * Returns the number of timer this timer will be restarted.
     *
     * \retval int
     *      Returns the repetition state of the timer.
     */
    public function getRepetition();

    /**
     * Changes the number of times this timer can go off.
     *
     * \param bool|int $repeat
     *      Can be either:
     *      \arg    An integer indicating the number of times the timer
     *              will be triggered (with any negative value being
     *              treated as positive infinity).
     *      \arg    A boolean which indicates that the timer should call
     *              the callback periodically (TRUE, same as -1) or just
     *              once (FALSE, same as 1).
     */
    public function setRepetition($repeat);

    /**
     * Returns the underlying stream used by the implementation
     * to create timers.
     *
     * \internal
     *
     * \retval stream
     *      The underlying PHP stream.
     */
    public function & getStream();

    /**
     * (Re)starts the timer.
     *
     * \post
     *      The timer is started. The repetition counter is
     *      decremented as necessary.
     *
     * \warning
     *      It is the responsability of whoever uses the timer
     *      to restart it when needed. In that respect, the default
     *      implementation of Erebot does it automatically (when
     *      the timer is added with Erebot::addTimer() and each time
     *      the callback is called Erebot::start()), so you usually
     *      don't need to call this method yourself.
     */
    public function reset();

    /**
     * Calls the callback.
     *
     * \warning
     *      This method is automatically called from Erebot::start()
     *      whenever a timer expires. Calling it manually may lead
     *      to unexpected results.
     */
    public function activate();
}

