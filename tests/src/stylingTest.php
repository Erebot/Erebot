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

class   StylingTest
extends PHPUnit_Framework_TestCase
{
    protected $_translator = NULL;

    public function setUp()
    {
        parent::setUp();
        $this->_translator = $this->getMock(
            'Erebot_Interface_I18n',
            array(), array('', ''), '',
            FALSE, FALSE, FALSE
        );

        $this->_translator
            ->expects($this->any())
            ->method('getLocale')
            ->will($this->returnValue('en-US'));
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testArrayWithOnlyOneElement()
    {
        $source     = '<for from="names" item="name"><var name="name"/></for>';
        $template   = new Erebot_Styling($source, $this->_translator);
        $template->assign('names', array('Clicky'));
        $result     = addcslashes($template->render(), "\000..\037");
        $expected   = "Clicky";
        $this->assertEquals($expected, $result);
    }

    public function testBeatlesTest()
    {
        $source =   'The Beatles: <for from="Beatles" item="Beatle">'.
                    '<u><var name="Beatle"/></u></for>.';

        $template   = new Erebot_Styling($source, $this->_translator);
        $template->assign('Beatles', array('George', 'John', 'Paul', 'Ringo'));
        $result     = addcslashes($template->render(), "\000..\037");
        $expected   =   "The Beatles: \\037George\\037, \\037John\\037, ".
                        "\\037Paul\\037 & \\037Ringo\\037.";

        $this->assertEquals($expected, $result);
    }

    public function testScoreTest()
    {
        $source =   '<b>Scores</b>: <for item="score" key="nick" '.
                    'from="scores" separator=", " last_separator=" &amp; ">'.
                    '<b><u><color fg="green"><var name="nick"/></color></u>: '.
                    '<var name="score"/></b></for>';

        $template   =   new Erebot_Styling($source, $this->_translator);
        $scores     =   array(
                            'Clicky' => 42,
                            'Looksup' => 23,
                            'MiSsInGnO' => 16
                        );
        $template->assign('scores', $scores);
        $result     = addcslashes($template->render(), "\000..\037");
        $expected   =   "\\002Scores\\002: \\002\\037\\00303Clicky\\037: ".
                        "42\\002, \\002\\037\\00303Looksup\\037: 23\\002 & ".
                        "\\002\\037\\00303MiSsInGnO\\037: 16\\002";

        $this->assertEquals($expected, $result);
    }

    /**
     * Tests whether a \<color\> tag without any "fg"
     * or "bg" attribute is correctly marked as invalid.
     *
     * @expectedException Erebot_InvalidValueException
     */
    public function testColorMissingAttributes()
    {
        new Erebot_Styling('<color>foo</color>', $this->_translator);
    }

    public function testPlural()
    {
        /* We use special characters in the sentence {, }, ' and #
         * to test how the styling API deals with ICU's meta-characters. */
        $source =   "<plural var='foo'><case form='one'>there's <var ".
                    "name='foo'/> file</case><case form='other'>there ".
                    "are #{''<var name='foo'/>''}# files</case></plural>";
        $template   =   new Erebot_Styling($source, $this->_translator);
        $template->assign('foo', 0);
        $this->assertEquals("there are #{''0''}# files", $template->render());
        $template->assign('foo', 1);
        $this->assertEquals("there's 1 file", $template->render());
        $template->assign('foo', 42);
        $this->assertEquals("there are #{''42''}# files", $template->render());
    }
}

