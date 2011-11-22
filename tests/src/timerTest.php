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

abstract class  AbstractTimerTest
extends         PHPUnit_Framework_TestCase
{
    protected $_flag;

    public function helper(Erebot_Interface_Timer $timer, $foo, $bar)
    {
        $this->assertNotEquals('bar', $foo);
        $this->assertEquals('bar', $bar);
        $this->_flag = TRUE;
    }
}

class   TimerTest
extends AbstractTimerTest
{
    private $_delay = 2.5;
    private $_min   = 2.5;
    private $_max   = 4.0;
    private $_timer;

    /**
     * Nominal case for timers.
     *
     * We create a timer set to go off twice with a delay of 2.5 seconds.
     * We check that each parameter is correctly set before each run.
     * We test whether or not the timer went off roughly at the right time
     * (between 2.5 and 3.5 seconds, to allow some CPU overhead) and we do
     * some extra checks.
     *
     * @covers Erebot_Timer::reset
     * @covers Erebot_Timer::getStream
     * @covers Erebot_Timer::activate
     * @covers Erebot_Timer::__construct
     * @covers Erebot_Timer::__destruct
     */
    public function testNominalCase()
    {
        $this->_timer = new Erebot_Timer(
            new Erebot_Callable(array($this, 'helper')),
            $this->_delay,
            2,
            array('foo', 'bar')
        );

        // Do a first pass : after that,
        // we expect exactly 1 repetition left.
        $this->_check();
        $this->assertEquals(1, $this->_timer->getRepetition());

        // Do a second pass : after that,
        // we expect exactly 0 repetitions left.
        $this->_check();
        $this->assertEquals(0, $this->_timer->getRepetition());

        // Trying to reset the timer MUST fail
        // because there are no more repetitions left.
        $this->assertFalse($this->_timer->reset());
    }

    protected function _check()
    {
        $this->_flag = FALSE;

        // Resetting the timer decrements
        // the number of repetitions.
        $this->assertTrue($this->_timer->reset());

        // Do the actual select() and do a rough check
        // on the duration it took to complete it.
        $start = microtime(TRUE);
        list($nb, $read) = self::_select();
        $duration = microtime(TRUE) - $start;
        $this->assertGreaterThanOrEqual($this->_min, $duration);
        $this->assertLessThanOrEqual($this->_max, $duration);

        // Make sure select() returned exactly
        // one stream : our timer.
        $this->assertEquals(1, $nb);
        $this->assertSame($this->_timer->getStream(), $read);

        // The flag MUST NOT be set before the timer
        // has been activated, but MUST be set afterward.
        $this->assertFalse($this->_flag);
        $this->_timer->activate();
        $this->assertTrue($this->_flag);
    }

    protected function _select()
    {
        $start  = microtime(TRUE);
        $stream = $this->_timer->getStream();
        do {
            $read = array($stream);
            $null = array();
            $wait = $this->_max - (microtime(TRUE) - $start);
            if ($wait <= 0)
                return array(0, NULL);

            /** The silencer is required to avoid PHPUnit choking
             *  when the syscall is interrupted by a signal and
             *  this function displays a warning as a result. */
            $nb   = @stream_select(
                $read,
                $null,
                $null,
                intval($wait),
                ((int) ($wait * 100000)) % 100000
            );
        } while ($nb === FALSE);
        if (!$nb)
            return array(0, NULL);
        return array($nb, $read[0]);
    }
}

class   TimerGettersTest
extends AbstractTimerTest
{
    /**
     * @covers Erebot_Timer::getArgs
     * @covers Erebot_Timer::getCallback
     * @covers Erebot_Timer::getDelay
     * @covers Erebot_Timer::getRepetition
     * @covers Erebot_Timer::__construct
     * @covers Erebot_Timer::__destruct
     */
    public function testGetters()
    {
        $callback   = new Erebot_Callable(array($this, 'helper'));
        $args       = array('foo', 'bar');
        $timer      = new Erebot_Timer($callback, 4.2, 42, $args);
        $this->assertEquals($args, $timer->getArgs());
        $this->assertEquals($callback, $timer->getCallback());
        $this->assertEquals(4.2, $timer->getDelay());
        $this->assertEquals(42, $timer->getRepetition());
    }

    /**
     * @covers Erebot_Timer::setRepetition
     * @covers Erebot_Timer::getRepetition
     */
    public function testRepetition()
    {
        $callback   = new Erebot_Callable(array($this, 'helper'));
        $args       = array('foo', 'bar');
        $timer      = new Erebot_Timer($callback, 42, FALSE, $args);

        $this->assertEquals(1, $timer->getRepetition());
        $timer->setRepetition(TRUE);
        $this->assertEquals(-1, $timer->getRepetition());
        $timer->setRepetition(2);
        $this->assertEquals(2, $timer->getRepetition());
    }

}

