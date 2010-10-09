<?php

include_once('src/utils.php');

class   UtilsTest
extends PHPUnit_Framework_TestCase
{
    protected function getCallerObjectHelper()
    {
        return ErebotUtils::getCallerObject();
    }

    public function testGetCallerObject()
    {
        $this->assertSame($this, $this->getCallerObjectHelper());
        $this->assertNotSame($this, ErebotUtils::getCallerObject());
    }

    public function numtokProvider()
    {
        return array(
            array(3, 'foo bar baz',   ' '),
            array(2, 'foo       baz', ' '),
            array(3, 'foo, bar, baz', ','),
        );
    }

    /**
     * @dataProvider    numtokProvider
     */
    public function testNumtok($expected, $text, $separator)
    {
        $this->assertEquals($expected, ErebotUtils::numtok($text, $separator));
    }

    public function testGettok()
    {
        $this->assertEquals('foo', ErebotUtils::gettok('foo bar baz', 0, 1));
        $this->assertEquals('baz', ErebotUtils::gettok('foo bar baz', -1, 1));
        $this->assertEquals('bar baz', ErebotUtils::gettok('foo bar baz', 1));
        $this->assertEquals('bar baz', ErebotUtils::gettok('foo bar baz', -2));
        $this->assertSame(NULL, ErebotUtils::gettok('foo bar baz', 3));
    }
}

?>
