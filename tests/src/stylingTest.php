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

class   StylingTest
extends ErebotModuleTestCase
{
    protected $_translator = NULL;

    public function setUp()
    {
        parent::setUp();
        $this->_translator
            ->expects($this->any())
            ->method('getLocale')
            ->will($this->returnValue('en-US'));
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * @covers Erebot_Styling
     */
    public function testArrayWithOnlyOneElement()
    {
        $source = '<for from="names" item="name"><var name="name"/></for>';
        $tpl    = new Erebot_Styling($this->_translator);
        $vars   = array('names' => array('Clicky'));
        $result = addcslashes($tpl->render($source, $vars), "\000..\037");
        $expected = "Clicky";
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers Erebot_Styling
     */
    public function testBeatlesTest()
    {
        $source =   'The Beatles: <for from="Beatles" item="Beatle">'.
                    '<u><var name="Beatle"/></u></for>.';

        $tpl    = new Erebot_Styling($this->_translator);
        $vars   = array('Beatles' => array('George', 'John', 'Paul', 'Ringo'));
        $result = $tpl->render($source, $vars);
        $result = addcslashes($tpl->render($source, $vars), "\000..\037");
        $expected   =   "The Beatles: \\037George\\037, \\037John\\037, ".
                        "\\037Paul\\037 & \\037Ringo\\037.";
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers Erebot_Styling
     */
    public function testScoreTest()
    {
        $source =   '<b>Scores</b>: <for item="score" key="nick" '.
                    'from="scores" separator=", " last_separator=" &amp; ">'.
                    '<b><u><color fg="green"><var name="nick"/></color></u>: '.
                    '<var name="score"/></b></for>';

        $tpl    =   new Erebot_Styling($this->_translator);
        $scores =   array(
                        'Clicky' => 42,
                        'Looksup' => 23,
                        'MiSsInGnO' => 16
                    );
        $result = $tpl->render($source, array('scores' => $scores));
        $result = addcslashes($result, "\000..\037");
        $expected   =   "\\002Scores\\002: \\002\\037\\00303Clicky\\037: ".
                        "42\\002, \\002\\037\\00303Looksup\\037: 23\\002 & ".
                        "\\002\\037\\00303MiSsInGnO\\037: 16\\002";
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests whether a \<color\> tag without any "fg"
     * or "bg" attribute is correctly marked as invalid.
     *
     * @expectedException   Erebot_InvalidValueException
     * @covers              Erebot_Styling
     */
    public function testColorMissingAttributes()
    {
        $tpl = new Erebot_Styling($this->_translator);
        $tpl->render('<color>foo</color>');
    }

    /**
     * @covers Erebot_Styling
     * @covers Erebot_I18n
     */
    public function testPlural()
    {
        /* We use special characters in the sentence {, }, ' and #
         * to test how the styling API deals with ICU's meta-characters. */
        $source =   "<plural var='foo'><case form='one'>there's <var ".
                    "name='foo'/> file</case><case form='other'>there ".
                    "are #{''<var name='foo'/>''}# files</case></plural>";
        $tpl    = new Erebot_Styling($this->_translator);
        $result = $tpl->render($source, array('foo' => 0));
        $this->assertEquals("there are #{''0''}# files", $result);
        $result = $tpl->render($source, array('foo' => 1));
        $this->assertEquals("there's 1 file", $result);
        $result = $tpl->render($source, array('foo' => 42));
        $this->assertEquals("there are #{''42''}# files", $result);
    }

    public function testInteger()
    {
        $source = "<var name='foo'/>";
        $tpl    = new Erebot_Styling($this->_translator);
        $result = $tpl->render($source, array('foo' => 12345));
        $this->assertEquals('12345', $result);

        $result = $tpl->render(
            $source,
            array('foo' => new Erebot_Styling_Integer(12345))
        );
        $this->assertEquals('12345', $result);
    }

    public function testFloat()
    {
        $source = "<var name='foo'/>";
        $tpl    = new Erebot_Styling($this->_translator);
        $result = $tpl->render($source, array('foo' => 12345.67891));
        $this->assertEquals('12,345.67891', $result);

        $result = $tpl->render(
            $source,
            array('foo' => new Erebot_Styling_Float(12345.67891))
        );
        $this->assertEquals('12,345.67891', $result);
    }

    public function testCurrency()
    {
        $source = "<var name='foo'/>";
        $tpl    = new Erebot_Styling($this->_translator);
        $result = $tpl->render(
            $source,
            array('foo' => new Erebot_Styling_Currency(12345.67891, 'EUR'))
        );
        // Monetary values are rounded.
        $this->assertEquals('€12,345.68', $result);
    }

    public function testDateTime()
    {
        $source = "<var name='foo'/>";
        $tpl    = new Erebot_Styling($this->_translator);
        // 28 Nov 1985, 14:10:00 +0100.
        $date = 502031400;
        $formatter  = new Erebot_Styling_DateTime(
            $date,
            IntlDateFormatter::FULL,
            IntlDateFormatter::LONG
        );
        $result     = $tpl->render($source, array('foo' => $formatter));
        $expected   = 'Thursday, November 28, 1985 1:10:00 PM GMT+00:00';
        $this->assertEquals($expected, $result);
    }

    public function testDuration()
    {
        $source = "<var name='foo'/>";
        $tpl    = new Erebot_Styling($this->_translator);
        $values = array(
            0       => "0 seconds",
            1       => "1 second",
            2       => "2 seconds",
            694861  => "1 week, 1 day, 1 hour, 1 minute, 1 second",
            1389722 => "2 weeks, 2 days, 2 hours, 2 minutes, 2 seconds",
        );

        foreach ($values as $duration => $spellout) {
            $result = $tpl->render(
                $source,
                array('foo' => new Erebot_Styling_Duration($duration))
            );
            $this->assertEquals($spellout, $result);
        }
    }
}

