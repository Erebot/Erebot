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

require_once(
    dirname(dirname(__FILE__)) .
    DIRECTORY_SEPARATOR . 'testenv' .
    DIRECTORY_SEPARATOR . 'bootstrap.php'
);

class   TextWrapperTest
extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->text = ' foo   bar baz   ';
        $this->wrapped = new Erebot_TextWrapper($this->text);
    }

    public function testGetTokens()
    {
        $this->assertEquals('bar baz', $this->wrapped->getTokens(1));
        $this->assertEquals('bar', $this->wrapped->getTokens(1,1));
        $this->assertEquals('foo bar', $this->wrapped->getTokens(0,2));
        $this->assertEquals('baz', $this->wrapped->getTokens(-1));
        $this->assertEquals('bar baz', $this->wrapped->getTokens(-2));
        $this->assertEquals('foo', $this->wrapped->getTokens(-3, 1));
    }


    public function testCountTokens()
    {
        $this->assertEquals(3, $this->wrapped->countTokens());
        $this->assertEquals(3, $this->wrapped->countTokens(' '));
        $this->assertEquals(3, $this->wrapped->countTokens('a'));
        $this->assertEquals(2, $this->wrapped->countTokens('f'));
    }

    public function testToString()
    {
        $this->assertEquals($this->text, (string) $this->wrapped);
    }
}

