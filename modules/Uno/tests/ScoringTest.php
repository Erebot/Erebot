<?php

#include_once(dirname(dirname(__FILE__)).'/src/game.php');

#class   UnoScoringTest
#extends PHPUnit_Framework_TestCase
#{
#    public function scoringProvider()
#    {
#        return array(
#            array('r0',     0),
#            array('g1',     1),
#            array('b2',     2),
#            array('r3',     3),
#            array('g4',     4),
#            array('b5',     5),
#            array('y6',     6),
#            array('r7',     7),
#            array('g8',     8),
#            array('y9',     9),
#            array('rr',    20),
#            array('rs',    20),
#            array('r+2',   20),
#            array('w',     50),
#            array('w+4',   50),
#            array(array('r0', 'r2', 'b7', 'w', 'yr', 'g+2'),    99),
#        );
#    }

#    /**
#     * @dataProvider    scoringProvider
#     */
#    public function testScoringFunction($card, $score)
#    {
#        $result = Uno::getScore($card);
#        $this->assertEquals($score, $result);
#    }
#}

?>
