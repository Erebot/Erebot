<?php

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
     * \param $callback
     *      The callback to call when the timer expires.
     *      See http://php.net/manual/en/language.pseudo-types.php
     *      for acceptable callback values.
     *
     * \param $delay
     *      The number of seconds to wait for before calling the
     *      callback. This may be a float/double or an int, but
     *      the implementation may choose to round it up to the
     *      nearest integer if sub-second precision is impossible
     *      to get (eg. on Windows).
     *
     * \param $repeat
     *      A boolean which indicates whether the callback should
     *      be called repeatedly every $delay seconds or just once.
     */
    public function __construct($callback, $delay, $repeat);

    /**
     * Returns a reference to the callback associated with this timer.
     */
    public function & getCallback();

    /**
     * Returns the delay after which the callback will be called.
     * This is the original value given to the timer during construction,
     * and it is not updated live as time passes by.
     */
    public function getDelay();

    /**
     * Returns the repetition flag of this timer.
     * It can also be used to change that flag.
     *
     * \param $repeat
     *      (optional) If given, it can be:
     *      *   An integer indicating the number of times the timer
     *          will be triggered (with any negative value being
     *          treated as positive infinity).
     *      *   A boolean which indicates that the timer should call
     *          the callback periodically (TRUE, same as -1) or just
     *          once (FALSE, same as 1).
     *
     * \return
     *      Returns the repetition state of the timer.
     *      If the value was changed by means of the $repeat parameter,
     *      the value before any change was made is returned.
     */
    public function isRepeated($repeat = NULL);

    /**
     * Returns the underlying stream used by the implementation
     * to create timers.
     *
     * \internal
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

?>
