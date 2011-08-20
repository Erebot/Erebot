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

if (!defined('TESTENV_DIR'))
    define(
        'TESTENV_DIR',
        dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'testenv'
    );
require_once(TESTENV_DIR . DIRECTORY_SEPARATOR . 'bootstrap.php');

class   TextWrapperTest
extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->_text = ' foo   bar baz   ';
        $this->_wrapped = new Erebot_TextWrapper($this->_text);
    }

    /**
     * @covers Erebot_TextWrapper::getTokens
     */
    public function testGetTokens()
    {
        $this->assertEquals('bar baz', $this->_wrapped->getTokens(1));
        $this->assertEquals('bar', $this->_wrapped->getTokens(1,1));
        $this->assertEquals('foo bar', $this->_wrapped->getTokens(0,2));
        $this->assertEquals('baz', $this->_wrapped->getTokens(-1));
        $this->assertEquals('bar baz', $this->_wrapped->getTokens(-2));
        $this->assertEquals('foo', $this->_wrapped->getTokens(-3, 1));
        $this->assertSame(NULL, $this->_wrapped->getTokens(3));
    }

    /**
     * @covers Erebot_TextWrapper::countTokens
     */
    public function testCountTokens()
    {
        $this->assertEquals(3, $this->_wrapped->countTokens());
        $this->assertEquals(3, $this->_wrapped->countTokens(' '));
        $this->assertEquals(3, $this->_wrapped->countTokens('a'));
        $this->assertEquals(2, $this->_wrapped->countTokens('f'));
    }

    /**
     * @covers Erebot_TextWrapper::__toString
     */
    public function testToString()
    {
        $this->assertEquals($this->_text, (string) $this->_wrapped);
    }

    /**
     * @covers Erebot_TextWrapper::current
     * @covers Erebot_TextWrapper::next
     * @covers Erebot_TextWrapper::rewind
     * @covers Erebot_TextWrapper::valid
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
     * @covers Erebot_TextWrapper::count
     * @covers Erebot_TextWrapper::offsetExists
     * @covers Erebot_TextWrapper::offsetGet
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
     * @expectedException   RuntimeException
     * @covers              Erebot_TextWrapper::offsetSet
     */
    public function testReadOnlyTryingToSet($offset)
    {
        $this->_wrapped[$offset] = 42;
    }

    /**
     * @dataProvider        offsetsProvider
     * @expectedException   RuntimeException
     * @covers              Erebot_TextWrapper::offsetUnset
     */
    public function testReadOnlyTryingToUnset($offset)
    {
        unset($this->_wrapped[$offset]);
    }
}

