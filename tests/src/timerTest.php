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

class   TimerTest
extends PHPUnit_Framework_TestCase
{
    private $_flag;

    public function helper(Erebot_Interface_Timer $timer, $foo, $bar)
    {
        $this->assertNotEquals('bar', $foo);
        $this->assertEquals('bar', $bar);
        $this->_flag = TRUE;
    }

    /**
     * Nominal case for timers.
     *
     * We create a timer set to go off twice with a delay of 2.5 seconds.
     * We check that each parameter is correctly set before each run.
     * We test whether or not the timer went off roughly at the right time
     * (between 2.5 and 3.5 seconds, to allow some CPU overhead).
     *
     * @covers Erebot_Timer::setRepetition
     * @covers Erebot_Timer::reset
     * @covers Erebot_Timer::getStream
     * @covers Erebot_Timer::activate
     * @covers Erebot_Timer::__construct
     * @covers Erebot_Timer::__destruct
     */
    public function testNominalCase()
    {
        $delay  = 2.5;
        $min    = 2.5;
        $max    = 3.5;

        $this->_flag = FALSE;
        $callback   = new Erebot_Callable(array($this, 'helper'));
        $timer      = new Erebot_Timer($callback, $delay, FALSE, array('foo', 'bar'));
        $this->assertEquals((string) $callback, (string) $timer->getCallback());
        $this->assertSame($delay, $timer->getDelay());

        $this->assertEquals(1, $timer->getRepetition());
        $timer->setRepetition(TRUE);
        $this->assertEquals(-1, $timer->getRepetition());
        $timer->setRepetition(2);
        $this->assertEquals(2, $timer->getRepetition());

        $start = microtime(TRUE);
        $this->assertTrue($timer->reset());
        list($nb, $read) = self::_select($timer, $max);
        $this->assertEquals(1, $nb);
        $this->assertSame($timer->getStream(), $read);

        $timer->activate();
        $this->assertTrue($this->_flag);
        $this->assertGreaterThanOrEqual($min, microtime(TRUE) - $start);
        $this->assertEquals(1, $timer->getRepetition());

        $this->_flag = FALSE;
        $start = microtime(TRUE);
        $this->assertTrue($timer->reset());
        list($nb, $read) = self::_select($timer, $max);
        $this->assertEquals(1, $nb);
        $this->assertSame($timer->getStream(), $read);

        $timer->activate();
        $this->assertTrue($this->_flag);
        $this->assertGreaterThanOrEqual($min, microtime(TRUE) - $start);

        $this->assertFalse($timer->reset());
    }

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

    static protected function _select($timer, $max)
    {
        $start  = microtime(TRUE);
        $stream = $timer->getStream();
        do {
            $read = array($stream);
            $null = array();
            $wait = $max - (microtime(TRUE) - $start);
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
        return array($nb, $read[0]);
    }
}

