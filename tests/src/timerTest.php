<?php

class   TimerTest
extends PHPUnit_Framework_TestCase
{
    private $_flag;

    public function helper(Erebot_Interface_Timer &$timer)
    {
        $this->_flag = TRUE;
    }

    /**
     * Nominal case for timers.
     *
     * We create a timer set to go off twice with a delay of 2.5 seconds.
     * We check that each parameter is correctly set before each run.
     * We test whether or not the timer went off roughly at the right time
     * (between 2.5 et 3 seconds, to allow some CPU overhead).
     */
    public function testNominalCase()
    {
        $delay = 2.5;
        $min = 2.5;
        $max = 3;

        $this->_flag = FALSE;
        $callback = array($this, 'helper');
        $timer = new Erebot_Timer($callback, $delay, FALSE);
        $this->assertEquals($callback, $timer->getCallback());
        $this->assertSame($delay, $timer->getDelay());

        $this->assertEquals(1, $timer->getRepetition());
        $timer->setRepetition(TRUE);
        $this->assertEquals(-1, $timer->getRepetition());
        $timer->setRepetition(2);
        $this->assertEquals(2, $timer->getRepetition());

        $start = microtime(TRUE);
        $timer->reset();
        $read = array($timer->getStream());
        $null = array();

        $nb = stream_select(
            $read,
            $null,
            $null,
            intval($max),
            ($max - intval($max)) * 100000
        );
        $this->assertEquals(1, $nb);
        $this->assertSame($timer->getStream(), $read[0]);

        $timer->activate();
        $this->assertTrue($this->_flag);
        $this->assertGreaterThanOrEqual($min, microtime(TRUE) - $start);
        $this->assertEquals(1, $timer->getRepetition());

        $this->_flag = FALSE;
        $start = microtime(TRUE);
        $timer->reset();
        $read = array($timer->getStream());
        $null = array();

        $nb = stream_select(
            $read,
            $null,
            $null,
            intval($max),
            ($max - intval($max)) * 100000
        );
        $this->assertEquals(1, $nb);
        $this->assertSame($timer->getStream(), $read[0]);

        $timer->activate();
        $this->assertTrue($this->_flag);
        $this->assertGreaterThanOrEqual($min, microtime(TRUE) - $start);
    }
}

