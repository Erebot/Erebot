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
    public function testEmptyString()
    {
        $wrapper = new Erebot_IrcTextWrapper('');
        $this->assertEquals(1, count($wrapper));
        $this->assertEquals('', $wrapper[0]);
        $this->assertEquals('', (string) $wrapper);
    }

    public function testSingleColon()
    {
        $wrapper = new Erebot_IrcTextWrapper(':');
        $this->assertEquals(1, count($wrapper));
        $this->assertEquals('', $wrapper[0]);
        $this->assertEquals('', (string) $wrapper);
    }

    public function testSpaceHandling()
    {
        $wrapper = new Erebot_IrcTextWrapper('a b c');
        $this->assertEquals(3, count($wrapper));
        $this->assertEquals('a', $wrapper[0]);
        $this->assertEquals('b', $wrapper[1]);
        $this->assertEquals('c', $wrapper[2]);
        $this->assertEquals('a b c', (string) $wrapper);
    }

    public function testColonHandling()
    {
        $wrapper = new Erebot_IrcTextWrapper('a b:c');
        $this->assertEquals(2, count($wrapper));
        $this->assertEquals('a', $wrapper[0]);
        $this->assertEquals('b:c', $wrapper[1]);
        $this->assertEquals('a b:c', (string) $wrapper);
    }

    public function testSpaceAndColonHandling()
    {
        $wrapper = new Erebot_IrcTextWrapper('a :b c');
        $this->assertEquals(2, count($wrapper));
        $this->assertEquals('a', $wrapper[0]);
        $this->assertEquals('b c', $wrapper[1]);
        $this->assertEquals('a :b c', (string) $wrapper);
    }

    public function testTokenLeadingColonHandling()
    {
        $wrapper = new Erebot_IrcTextWrapper('a ::b');
        $this->assertEquals(2, count($wrapper));
        $this->assertEquals('a', $wrapper[0]);
        $this->assertEquals(':b', $wrapper[1]);
        $this->assertEquals('a ::b', (string) $wrapper);
    }

    public function testArrayGet()
    {
        $wrapper = new Erebot_IrcTextWrapper('foo');
        $this->assertEquals('foo', $wrapper[0]);
        $this->assertSame(NULL, $wrapper['test']);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The wrapped text is read-only
     */
    public function testArraySet()
    {
        $wrapper = new Erebot_IrcTextWrapper('foo');
        $wrapper[0] = 'bar';
    }

    public function testArrayUnsetAndReindex()
    {
        $wrapper = new Erebot_IrcTextWrapper('foo bar baz');
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

    public function testArrayExistence()
    {
        $wrapper = new Erebot_IrcTextWrapper('foo');
        $this->assertTrue(isset($wrapper[0]));
        $this->assertFalse(isset($wrapper[1]));
    }

    public function testIteration()
    {
        $wrapper    = new Erebot_IrcTextWrapper('a b :c d');
        $expected   = array('a', 'b', 'c d');
        foreach ($wrapper as $index => $real)
            $this->assertEquals($expected[$index], $real);
    }
}

