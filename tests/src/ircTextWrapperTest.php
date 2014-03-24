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

class   IrcTextWrapperTest
extends Erebot_TestEnv_TestCase
{
    /**
     * @cover \Erebot\IrcTextWrapper::__construct
     * @cover \Erebot\IrcTextWrapper::count
     * @cover \Erebot\IrcTextWrapper::__toString
     */
    public function testEmptyString()
    {
        $wrapper = new \Erebot\IrcTextWrapper('');
        $this->assertEquals(1, count($wrapper));
        $this->assertEquals('', $wrapper[0]);
        $this->assertEquals('', (string) $wrapper);
    }

    /**
     * @cover \Erebot\IrcTextWrapper::__construct
     * @cover \Erebot\IrcTextWrapper::count
     * @cover \Erebot\IrcTextWrapper::__toString
     */
    public function testSingleColon()
    {
        $wrapper = new \Erebot\IrcTextWrapper(':');
        $this->assertEquals(1, count($wrapper));
        $this->assertEquals('', $wrapper[0]);
        $this->assertEquals('', (string) $wrapper);
    }

    /**
     * @cover \Erebot\IrcTextWrapper::__construct
     * @cover \Erebot\IrcTextWrapper::count
     * @cover \Erebot\IrcTextWrapper::__toString
     */
    public function testSpaceHandling()
    {
        $wrapper = new \Erebot\IrcTextWrapper('a b c');
        $this->assertEquals(3, count($wrapper));
        $this->assertEquals('a', $wrapper[0]);
        $this->assertEquals('b', $wrapper[1]);
        $this->assertEquals('c', $wrapper[2]);
        $this->assertEquals('a b c', (string) $wrapper);
    }

    /**
     * @cover \Erebot\IrcTextWrapper::__construct
     * @cover \Erebot\IrcTextWrapper::count
     * @cover \Erebot\IrcTextWrapper::__toString
     */
    public function testColonHandling()
    {
        $wrapper = new \Erebot\IrcTextWrapper('a b:c');
        $this->assertEquals(2, count($wrapper));
        $this->assertEquals('a', $wrapper[0]);
        $this->assertEquals('b:c', $wrapper[1]);
        $this->assertEquals('a b:c', (string) $wrapper);
    }

    /**
     * @cover \Erebot\IrcTextWrapper::__construct
     * @cover \Erebot\IrcTextWrapper::count
     * @cover \Erebot\IrcTextWrapper::__toString
     */
    public function testSpaceAndColonHandling()
    {
        $wrapper = new \Erebot\IrcTextWrapper('a :b c');
        $this->assertEquals(2, count($wrapper));
        $this->assertEquals('a', $wrapper[0]);
        $this->assertEquals('b c', $wrapper[1]);
        $this->assertEquals('a :b c', (string) $wrapper);
    }

    /**
     * @cover \Erebot\IrcTextWrapper::__construct
     * @cover \Erebot\IrcTextWrapper::count
     * @cover \Erebot\IrcTextWrapper::__toString
     */
    public function testTokenLeadingColonHandling()
    {
        $wrapper = new \Erebot\IrcTextWrapper('a ::b');
        $this->assertEquals(2, count($wrapper));
        $this->assertEquals('a', $wrapper[0]);
        $this->assertEquals(':b', $wrapper[1]);
        $this->assertEquals('a ::b', (string) $wrapper);
    }

    /**
     * @cover \Erebot\IrcTextWrapper::offsetGet
     */
    public function testArrayGet()
    {
        $wrapper = new \Erebot\IrcTextWrapper('foo bar baz');
        $this->assertEquals('foo', $wrapper[0]);
        $this->assertEquals('bar', $wrapper[1]);
        $this->assertEquals('baz', $wrapper[2]);
        $this->assertSame(NULL, $wrapper['test']);
        $this->assertEquals('foo', $wrapper[-3]);
        $this->assertEquals('bar', $wrapper[-2]);
        $this->assertEquals('baz', $wrapper[-1]);
    }

    /**
     * @cover \Erebot\IrcTextWrapper::offsetSet
     * @expectedException RuntimeException
     * @expectedExceptionMessage The wrapped text is read-only
     */
    public function testArraySet()
    {
        $wrapper = new \Erebot\IrcTextWrapper('foo');
        $wrapper[0] = 'bar';
    }

    /**
     * @cover \Erebot\IrcTextWrapper::offsetUnset
     */
    public function testArrayUnsetAndReindex()
    {
        $wrapper = new \Erebot\IrcTextWrapper('foo bar baz');
        $this->assertEquals(3, count($wrapper));
        $this->assertEquals('foo', $wrapper[0]);
        unset($wrapper[0]);
        $this->assertEquals(2, count($wrapper));
        $this->assertEquals('bar', $wrapper[0]);
        unset($wrapper[0]);
        $this->assertEquals(1, count($wrapper));
        $this->assertEquals('baz', $wrapper[0]);
        unset($wrapper[0]);
        $this->assertEquals(0, count($wrapper));
    }

    /**
     * @cover \Erebot\IrcTextWrapper::offsetExists
     */
    public function testArrayExistence()
    {
        $wrapper = new \Erebot\IrcTextWrapper('foo');
        $this->assertTrue(isset($wrapper[0]));
        $this->assertFalse(isset($wrapper[1]));
    }

    /**
     * @cover \Erebot\IrcTextWrapper::current
     * @cover \Erebot\IrcTextWrapper::key
     * @cover \Erebot\IrcTextWrapper::next
     * @cover \Erebot\IrcTextWrapper::rewind
     * @cover \Erebot\IrcTextWrapper::valid
     */
    public function testIteration()
    {
        $wrapper    = new \Erebot\IrcTextWrapper('a b :c d');
        $expected   = array('a', 'b', 'c d');
        foreach ($wrapper as $index => $real)
            $this->assertEquals($expected[$index], $real);
    }

    /**
     * @cover \Erebot\IrcTextWrapper::__construct
     */
    public function testConstructFromList()
    {
        $wrapper = new \Erebot\IrcTextWrapper(array('a', 'b', 'c d'));
        $this->assertEquals(3, count($wrapper));
        $this->assertEquals('a', $wrapper[0]);
        $this->assertEquals('b', $wrapper[1]);
        $this->assertEquals('c d', $wrapper[2]);
    }

    /**
     * @cover \Erebot\IrcTextWrapper::__construct
     * @expectedException \Erebot\InvalidValueException
     * @expectedExceptionMessage Multiple tokens containing spaces
     */
    public function testListWithMultipleSpaces()
    {
        $wrapper = new \Erebot\IrcTextWrapper(array('a b', 'c d'));
    }

    /**
     * @cover \Erebot\IrcTextWrapper::__construct
     * @expectedException \Erebot\InvalidValueException
     * @expectedExceptionMessage At least one token must be passed
     */
    public function testEmptyList()
    {
        $wrapper = new \Erebot\IrcTextWrapper(array());
    }

    /**
     * @cover \Erebot\IrcTextWrapper::__construct
     * @expectedException \Erebot\InvalidValueException
     * @expectedExceptionMessage A string or an array was expected
     */
    public function testInvalidInput()
    {
        $wrapper = new \Erebot\IrcTextWrapper(42);
    }
}

