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

class   TextWrapperTest
extends Erebot_TestEnv_TestCase
{
    public function setUp()
    {
        $this->_text = ' foo   bar baz   ';
        $this->_wrapped = new \Erebot\TextWrapper($this->_text);
    }

    /**
     * @covers \Erebot\TextWrapper::getTokens
     */
    public function testGetTokens()
    {
        $this->assertEquals('bar baz', $this->_wrapped->getTokens(1));
        $this->assertEquals('bar', $this->_wrapped->getTokens(1,1));
        $this->assertEquals('foo bar', $this->_wrapped->getTokens(0,2));
        $this->assertEquals('baz', $this->_wrapped->getTokens(-1));
        $this->assertEquals('bar baz', $this->_wrapped->getTokens(-2));
        $this->assertEquals('foo', $this->_wrapped->getTokens(-3, 1));
        $this->assertEquals("", $this->_wrapped->getTokens(3));
    }

    /**
     * @covers \Erebot\TextWrapper::countTokens
     */
    public function testCountTokens()
    {
        $this->assertEquals(3, $this->_wrapped->countTokens());
        $this->assertEquals(3, $this->_wrapped->countTokens(' '));
        $this->assertEquals(3, $this->_wrapped->countTokens('a'));
        $this->assertEquals(2, $this->_wrapped->countTokens('f'));
    }

    /**
     * @covers \Erebot\TextWrapper::__toString
     */
    public function testToString()
    {
        $this->assertEquals($this->_text, (string) $this->_wrapped);
    }

    /**
     * @covers \Erebot\TextWrapper::current
     * @covers \Erebot\TextWrapper::next
     * @covers \Erebot\TextWrapper::rewind
     * @covers \Erebot\TextWrapper::valid
     */
    public function testIterator()
    {
        $tokens = array('foo', 'bar', 'baz');
        $i = 0;
        foreach ($this->_wrapped as $token) {
            if (!isset($tokens[$i]))
                $this->fail('Too many tokens');
            $this->assertEquals($tokens[$i++], $token);
        }
        $this->assertNotEquals(0, $i);
    }

    /**
     * @covers \Erebot\TextWrapper::count
     * @covers \Erebot\TextWrapper::offsetExists
     * @covers \Erebot\TextWrapper::offsetGet
     */
    public function testCountableAndArrayAccess()
    {
        $tokens = array('foo', 'bar', 'baz');
        $len = count($this->_wrapped);
        $this->assertEquals(3, $len);

        for ($i = 0; $i < $len; $i++) {
            $this->assertTrue(isset($this->_wrapped[$i]));
            $this->assertEquals($tokens[$i], $this->_wrapped[$i]);
        }
    }

    public function offsetsProvider()
    {
        return array_chunk(range(0, 2), 1);
    }

    /**
     * @dataProvider        offsetsProvider
     * @expectedException   \RuntimeException
     * @covers              \Erebot\TextWrapper::offsetSet
     */
    public function testReadOnlyTryingToSet($offset)
    {
        $this->_wrapped[$offset] = 42;
    }

    /**
     * @dataProvider        offsetsProvider
     * @expectedException   \RuntimeException
     * @covers              \Erebot\TextWrapper::offsetUnset
     */
    public function testReadOnlyTryingToUnset($offset)
    {
        unset($this->_wrapped[$offset]);
    }
}

