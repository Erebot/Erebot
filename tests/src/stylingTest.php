<?php

include_once('src/utils.php');
include_once('src/styling.php');

class   StylingTest
extends PHPUnit_Framework_TestCase
{
    public function testArrayWithOnlyOneElement()
    {
        $source =   '<for from="names" item="name"><var name="name"/></for>';
        $template   = new ErebotStyling($source);
        $template->assign('names', array('Clicky'));
        $result     = addcslashes($template->render(), "\000..\037");
        $expected   = "Clicky";
        $this->assertEquals($expected, $result);        
    }

    public function testBeatlesTest()
    {
        $source =   'The Beatles: <for from="Beatles" item="Beatle">'.
                    '<u><var name="Beatle"/></u></for>.';

        $template   = new ErebotStyling($source);
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

        $template   =   new ErebotStyling($source);
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
}

?>
