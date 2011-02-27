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

class   UtilsTest
extends PHPUnit_Framework_TestCase
{
    protected function getCallerObjectHelper()
    {
        return Erebot_Utils::getCallerObject();
    }

    public function testGetCallerObject()
    {
        $this->assertSame($this, $this->getCallerObjectHelper());
        $this->assertNotSame($this, Erebot_Utils::getCallerObject());
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
            Erebot_Utils::stripCodes($message, Erebot_Utils::STRIP_BOLD));
        $this->assertEquals(
            "\002a\002b\037c\037d\026e\026f\017ghijk",
            Erebot_Utils::stripCodes($message, Erebot_Utils::STRIP_COLORS));
        $this->assertEquals(
            "\002a\002b\037c\037d\026e\026fg\00300h\0030,1i\00300,01j\003k",
            Erebot_Utils::stripCodes($message, Erebot_Utils::STRIP_RESET));
        $this->assertEquals(
            "\002a\002b\037c\037def\017g\00300h\0030,1i\00300,01j\003k",
            Erebot_Utils::stripCodes($message, Erebot_Utils::STRIP_REVERSE));
        $this->assertEquals(
            "\002a\002bcd\026e\026f\017g\00300h\0030,1i\00300,01j\003k",
            Erebot_Utils::stripCodes($message, Erebot_Utils::STRIP_UNDERLINE));
        $this->assertEquals(
            "abcdefghijk",
            Erebot_Utils::stripCodes($message, Erebot_Utils::STRIP_ALL));
        $this->assertEquals(
            "abcdefghijk",
            Erebot_Utils::stripCodes($message));
    }

    public function testExtractNick()
    {
        $extracted = Erebot_Utils::extractNick('foo!bar@foobar.baz');
        $this->assertEquals('foo', $extracted);
        $this->assertEquals($extracted, Erebot_Utils::extractNick($extracted));
    }

    public function invalidUTF8Sequences()
    {
        // Known invalid sequences, taken from Wikipedia:
        // http://en.wikipedia.org/wiki/UTF-8#Invalid_byte_sequences
        $sequences = array(
            // Overlong sequences for U+0020 (SPACE).
            "\xC0\x20",
            "\xC1\x20",

            // Invalid continuation code points.
            "\xED\xA0",
            "\xED\xA1",
            "\xED\xA2",
            "\xED\xA3",
            "\xED\xA4",
            "\xED\xA5",
            "\xED\xA6",
            "\xED\xA7",
            "\xED\xA8",
            "\xED\xA9",
            "\xED\xAA",
            "\xED\xAB",
            "\xED\xAC",
            "\xED\xAD",
            "\xED\xAE",
            "\xED\xAF",
            "\xED\xB0",
            "\xED\xB1",
            "\xED\xB2",
            "\xED\xB3",
            "\xED\xB4",
            "\xED\xB5",
            "\xED\xB6",
            "\xED\xB7",
            "\xED\xB8",
            "\xED\xB9",
            "\xED\xBA",
            "\xED\xBB",
            "\xED\xBC",
            "\xED\xBD",
            "\xED\xBE",
            "\xED\xBF",

            "\xF4\x90",
            "\xF4\x91",
            "\xF4\x92",
            "\xF4\x93",
            "\xF4\x94",
            "\xF4\x95",
            "\xF4\x96",
            "\xF4\x97",
            "\xF4\x98",
            "\xF4\x99",
            "\xF4\x9A",
            "\xF4\x9B",
            "\xF4\x9C",
            "\xF4\x9D",
            "\xF4\x9E",
            "\xF4\x9F",
            "\xF4\xA0",
            "\xF4\xA1",
            "\xF4\xA2",
            "\xF4\xA3",
            "\xF4\xA4",
            "\xF4\xA5",
            "\xF4\xA6",
            "\xF4\xA7",
            "\xF4\xA8",
            "\xF4\xA9",
            "\xF4\xAA",
            "\xF4\xAB",
            "\xF4\xAC",
            "\xF4\xAD",
            "\xF4\xAE",
            "\xF4\xAF",
            "\xF4\xB0",
            "\xF4\xB1",
            "\xF4\xB2",
            "\xF4\xB3",
            "\xF4\xB4",
            "\xF4\xB5",
            "\xF4\xB6",
            "\xF4\xB7",
            "\xF4\xB8",
            "\xF4\xB9",
            "\xF4\xBA",
            "\xF4\xBB",
            "\xF4\xBC",
            "\xF4\xBD",
            "\xF4\xBE",
            "\xF4\xBF",

            // Invalid starting bytes.
            "\xF5",
            "\xF6",
            "\xF7",
            "\xF8",
            "\xF9",
            "\xFA",
            "\xFB",
            "\xFC",
            "\xFD",
            "\xFE",
            "\xFF",
        );
        $result = array();
        foreach ($sequences as $sequence)
            $result[] = array($sequence);
        return $result;
    }

    /**
     * @dataProvider invalidUTF8Sequences
     */
    public function testIsUTF8($sequence)
    {
        $this->assertFalse(Erebot_Utils::isUTF8($sequence));
    }

    public function testIsUTF8SimpleSequences()
    {
        // U+00E9 (LATIN SMALL LETTER E WITH ACUTE) - UTF-8 encoded.
        $this->assertTrue(Erebot_Utils::isUTF8("\xC3\xA9"));
        // Same character, but encoded using iso-8859-1.
        $this->assertFalse(Erebot_Utils::isUTF8("\xE9"));
    }

    public function testToUTF8()
    {
        // U+00E9 (LATIN SMALL LETTER E WITH ACUTE).
        // When it's already encoded in UTF-8.
        $this->assertEquals("\xC3\xA9", Erebot_Utils::toUTF8("\xC3\xA9"));
        // Encoded with the default charset (ISO-8859-1).
        $this->assertEquals("\xC3\xA9", Erebot_Utils::toUTF8("\xE9"));
        $this->assertEquals("\xC3\xA9", Erebot_Utils::toUTF8("\xE9", "iso-8859-1"));
    }
}

