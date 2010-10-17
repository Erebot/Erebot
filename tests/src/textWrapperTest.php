<?php

include_once('src/textWrapper.php');

class   TextWrapperTest
extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->text = ' foo   bar baz   ';
        $this->wrapped = new ErebotTextWrapper($this->text);
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

