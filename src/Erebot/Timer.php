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
 *      An implementation of timers.
 */
class       Erebot_Timer
implements  Erebot_Interface_Timer
{
    /// A file descriptor which is used to implement timers.
    protected $_handle;

    /// Internal resource used to implement timers.
    protected $_resource;

    /// Function or method to call when the timer expires.
    protected $_callback;

    /// Delay after which the timer will expire.
    protected $_delay;

    /// Number of times the timer will be reset.
    protected $_repeat;

    /// Additional arguments to call the callback function with.
    protected $_args;

    static protected $_binary   = NULL;
    static protected $_isWin    = FALSE;

    /**
     * Creates a new timer, set off to call the given callback
     * (optionally, repeatedly) when the associated delay passed.
     *
     * \param callback $callback
     *      The callback to call when the timer expires.
     *      See http://php.net/manual/en/language.pseudo-types.php
     *      for acceptable callback values.
     *
     * \param number $delay
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
     *
     * \param array $args
     *      (optional) Additional arguments to pass to the callback
     *      when it is called.
     */
    public function __construct(
        Erebot_Interface_Callable   $callback,
                                    $delay,
                                    $repeat,
                                    $args = array()
    )
    {
        if (self::$_binary === NULL) {
            $binary = '@php_bin@';
            if ($binary == '@'.'php_bin'.'@') {
                if (!strncasecmp(PHP_OS, 'WIN', 3)) {
                    $binary = 'php.exe';
                    self::$_isWin = TRUE;
                }
                else
                    $binary = '/usr/bin/env php';
            }
            self::$_binary = $binary;
        }

        $this->_delay       = $delay;
        $this->_handle      = NULL;
        $this->_resource    = NULL;
        $this->setCallback($callback);
        $this->setRepetition($repeat);
        $this->setArgs($args);
    }

    public function __destruct()
    {
        $this->_cleanup();
    }

    protected function _cleanup()
    {
        if ($this->_resource)
            proc_close($this->_resource);
        if (is_resource($this->_handle))
            fclose($this->_handle);
        $this->_handle      = NULL;
        $this->_resource    = NULL;
    }

    public function setCallback(Erebot_Interface_Callable $callback)
    {
        $this->_callback = $callback;
    }

    /// \copydoc Erebot_Interface_Timer::getCallback()
    public function getCallback()
    {
        return $this->_callback;
    }

    public function setArgs($args)
    {
        if (!is_array($args))
            throw new Erebot_InvalidValueException('Expected an array');
        $this->_args = $args;
    }

    /// \copydoc Erebot_Interface_Timer::getArgs()
    public function getArgs()
    {
        return $this->_args;
    }

    /// \copydoc Erebot_Interface_Timer::getDelay()
    public function getDelay()
    {
        return $this->_delay;
    }

    /// \copydoc Erebot_Interface_Timer::getRepetition()
    public function getRepetition()
    {
        return $this->_repeat;
    }

    /// \copydoc Erebot_Interface_Timer::setRepetition()
    public function setRepetition($repeat)
    {
        // If repeat = FALSE, then repeat = 1 (once)
        // If repeat = TRUE, then repeat = -1 (forever)
        if (is_bool($repeat))
            $repeat = (-intval($repeat)) * 2 + 1;
        // If repeat = NULL, return current value with no modification.
        // If repeat > 0, the timer will be triggered 'repeat' times.
        if (!is_int($repeat) && $repeat !== NULL)
            throw new Erebot_InvalidValueException('Invalid repetition');

        $this->_repeat = $repeat;
    }

    /// \copydoc Erebot_Interface_Timer::getStream()
    public function getStream()
    {
        return $this->_handle;
    }

    /// \copydoc Erebot_Interface_Timer::reset()
    public function reset()
    {
        if ($this->_repeat > 0)
            $this->_repeat--;
        else if (!$this->_repeat)
            return FALSE;

        $this->_cleanup();

        if (self::$_isWin) {
            // We create a temporary file to which the subprocess will write to.
            // This makes it possible to wait for the delay to pass by using
            // select() on this file descriptor.
            // Simpler approaches don't work on Windows because the underlying
            // php_select() implementation doesn't seem to support pipes.
            $this->_handle = tmpfile();
            $descriptors = $this->_handle;
        }
        else {
            // On other OSes, we just use a pipe to communicate.
            $descriptors = array('pipe', 'w');
        }

        // Build the command that will be executed by the subprocess.
        $command = self::$_binary . ' -r "usleep('.
            ((int) ($this->_delay * 1000000)).
            '); ' .
            'var_dump(42); ' .  // Required to make the subprocess send
                                // a completion notification back to us.
            // We add the name of the callback used to ease debugging.
            '// '.addslashes($this->_callback).'"';

        $this->_resource = proc_open(
            $command,
            array(1 => $descriptors),
            $pipes
        );

        if (self::$_isWin) {
            // Required to remove the "read-ready" flag from the fd.
            // The call will always return FALSE since no data has
            // been written to the temporary file yet.
            fgets($this->_handle);
        }
        else
            $this->_handle = $pipes[1];

        return TRUE;
    }

    /// \copydoc Erebot_Interface_Timer::activate()
    public function activate()
    {
        $this->_cleanup();
        $args = array_merge(array(&$this), $this->_args);
        return (bool) $this->_callback->invokeArgs($args);
    }
}

