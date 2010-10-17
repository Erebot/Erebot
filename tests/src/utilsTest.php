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

    public function testStripCodes()
    {
        /*
         * a is in bold,
         * c is underlined,
         * e has reversed colors
         * after f, styles are reset
         * h & i are in white on default background
         * j is in white on black
         * colors are reset before k
         */
        $message = "\002a\002b\037c\037d\026e\026f\017g\00300h\0030,1i\00300,01j\003k";
        $this->assertEquals(
            "ab\037c\037d\026e\026f\017g\00300h\0030,1i\00300,01j\003k",
            ErebotUtils::stripCodes($message, ErebotUtils::STRIP_BOLD));
        $this->assertEquals(
            "\002a\002b\037c\037d\026e\026f\017ghijk",
            ErebotUtils::stripCodes($message, ErebotUtils::STRIP_COLORS));
        $this->assertEquals(
            "\002a\002b\037c\037d\026e\026fg\00300h\0030,1i\00300,01j\003k",
            ErebotUtils::stripCodes($message, ErebotUtils::STRIP_RESET));
        $this->assertEquals(
            "\002a\002b\037c\037def\017g\00300h\0030,1i\00300,01j\003k",
            ErebotUtils::stripCodes($message, ErebotUtils::STRIP_REVERSE));
        $this->assertEquals(
            "\002a\002bcd\026e\026f\017g\00300h\0030,1i\00300,01j\003k",
            ErebotUtils::stripCodes($message, ErebotUtils::STRIP_UNDERLINE));
        $this->assertEquals(
            "abcdefghijk",
            ErebotUtils::stripCodes($message, ErebotUtils::STRIP_ALL));
        $this->assertEquals(
            "abcdefghijk",
            ErebotUtils::stripCodes($message));
    }

    public function testExtractNick()
    {
        $extracted = ErebotUtils::extractNick('foo!bar@foobar.baz');
        $this->assertEquals('foo', $extracted);
        $this->assertEquals($extracted, ErebotUtils::extractNick($extracted));
    }

    public function testIsUTF8()
    {
        // U+00E9 (LATIN SMALL LETTER E WITH ACUTE) - UTF-8 encoded.
        $this->assertTrue(ErebotUtils::isUTF8("\xC3\xA9"));
        // Same character, but encoded using iso-8859-1.
        $this->assertFalse(ErebotUtils::isUTF8("\xE9"));
        
        // Known invalid sequences, taken from Wikipedia:
        // http://en.wikipedia.org/wiki/UTF-8#Invalid_byte_sequences

        // Overlong sequences for U+0020 (SPACE).
        $this->assertFalse(ErebotUtils::isUTF8("\xC0\x20"));
        $this->assertFalse(ErebotUtils::isUTF8("\xC1\x20"));

        // Invalid continuation code points.
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xA0"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xA1"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xA2"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xA3"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xA4"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xA5"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xA6"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xA7"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xA8"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xA9"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xAA"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xAB"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xAC"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xAD"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xAE"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xAF"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xB0"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xB1"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xB2"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xB3"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xB4"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xB5"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xB6"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xB7"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xB8"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xB9"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xBA"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xBB"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xBC"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xBD"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xBE"));
        $this->assertFalse(ErebotUtils::isUTF8("\xED\xBF"));

        $this->assertFalse(ErebotUtils::isUTF8("\xF4\x90"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\x91"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\x92"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\x93"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\x94"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\x95"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\x96"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\x97"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\x98"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\x99"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\x9A"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\x9B"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\x9C"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\x9D"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\x9E"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\x9F"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xA0"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xA1"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xA2"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xA3"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xA4"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xA5"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xA6"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xA7"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xA8"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xA9"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xAA"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xAB"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xAC"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xAD"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xAE"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xAF"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xB0"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xB1"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xB2"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xB3"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xB4"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xB5"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xB6"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xB7"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xB8"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xB9"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xBA"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xBB"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xBC"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xBD"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xBE"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF4\xBF"));

        // Invalid starting bytes.
        $this->assertFalse(ErebotUtils::isUTF8("\xF5"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF6"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF7"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF8"));
        $this->assertFalse(ErebotUtils::isUTF8("\xF9"));
        $this->assertFalse(ErebotUtils::isUTF8("\xFA"));
        $this->assertFalse(ErebotUtils::isUTF8("\xFB"));
        $this->assertFalse(ErebotUtils::isUTF8("\xFC"));
        $this->assertFalse(ErebotUtils::isUTF8("\xFD"));
        $this->assertFalse(ErebotUtils::isUTF8("\xFE"));
        $this->assertFalse(ErebotUtils::isUTF8("\xFF"));
    }

    public function testToUTF8()
    {
        // U+00E9 (LATIN SMALL LETTER E WITH ACUTE).
        // When it's already encoded in UTF-8.
        $this->assertEquals("\xC3\xA9", ErebotUtils::toUTF8("\xC3\xA9"));
        // Encoded with the default charset (ISO-8859-1).
        $this->assertEquals("\xC3\xA9", ErebotUtils::toUTF8("\xE9"));
        $this->assertEquals("\xC3\xA9", ErebotUtils::toUTF8("\xE9", "iso-8859-1"));
    }
}

