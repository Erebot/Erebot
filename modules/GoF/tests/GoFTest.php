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

/*
ErebotUtils::incl('../../GoF.php');

class   GoFTest
extends PHPUnit_Framework_TestCase
{
    protected $connection = NULL;
    protected $gof;

    protected function setUp()
    {
        $proxy = createProxyClass('ErebotModule_GoF');

        if ($this->connection === NULL)
            $this->connection = new ErebotConnection();

        $this->gof  = new $proxy($this->connection, ErebotModuleBase::RELOAD_TESTING);
    }

    protected function tearDown()
    {
        unset($this->gof, $this->connection);
        $this->gof          =
        $this->connection   = NULL;
    }


    public function testCheckColorPrecedence()
    {
        $this->assertLessThan(0, $this->gof->publiccompareCards('g1', 'y1'));
    }

    public function testCheckColorPrecedence2()
    {
        $this->assertLessThan(0, $this->gof->publiccompareCards('y1', 'r1'));
    }

    public function testCompareSingleMulti1AndRed1()
    {
        $this->assertLessThan(0, $this->gof->publiccompareCards('r1', 'm1'));
    }

    public function testCompareSingleGreen1AndGreen2()
    {
        $this->assertLessThan(0, $this->gof->publiccompareCards('g1', 'g2'));
    }

    public function testCompareSingleRed10AndGreenPhoenix()
    {
        $this->assertLessThan(0, $this->gof->publiccompareCards('r10', 'gp'));
    }

    public function testCompareSingleGreenPhoenixAndYellowPhoenix()
    {
        $this->assertLessThan(0, $this->gof->publiccompareCards('gp', 'yp'));
    }

    public function testCompareSingleYellowPhoenixAndRedDragon()
    {
        $this->assertLessThan(0, $this->gof->publiccompareCards('yp', 'rd'));
    }

    public function testCompareASingleCardAgainstItself()
    {
        $this->assertEquals(0, $this->gof->publiccompareCards('g1', 'g1'));
    }

    public function testCheckUnqualifiable()
    {
        $move   = array('g1', 'g2');
        $qualif = $this->gof->publicqualify($move);
        $this->assertSame(NULL, $qualif);
    }

    public function testCheckUnqualifiable2()
    {
        $move   = array('g1', 'g1', 'g2');
        $qualif = $this->gof->publicqualify($move);
        $this->assertSame(NULL, $qualif);
    }

    public function testCheckUnqualifiable3()
    {
        $move   = array('g1', 'g2', 'g3');
        $qualif = $this->gof->publicqualify($move);
        $this->assertSame(NULL, $qualif);
    }

    public function testCheckUnqualifiable4()
    {
        $move   = array('g1', 'g2', 'g3', 'g4');
        $qualif = $this->gof->publicqualify($move);
        $this->assertSame(NULL, $qualif);
    }

    public function testCheckUnqualifiable5()
    {
        $move   = array('g1', 'g1', 'g2', 'g2');
        $qualif = $this->gof->publicqualify($move);
        $this->assertSame(NULL, $qualif);
    }

    public function testCheckUnqualifiable6()
    {
        $move   = array('g1', 'g2', 'g3', 'g4', 'y6');
        $qualif = $this->gof->publicqualify($move);
        $this->assertSame(NULL, $qualif);
    }

    public function testCheckUnqualifiable7()
    {
        $move   = array('g7', 'g8', 'g9', 'g10', 'gp');
        $qualif = $this->gof->publicqualify($move);
        $this->assertSame(NULL, $qualif);
    }

    public function testCheckUnqualifiable8()
    {
        $move   = array('y7', 'y8', 'y9', 'y10', 'yp');
        $qualif = $this->gof->publicqualify($move);
        $this->assertSame(NULL, $qualif);
    }

    public function testCheckUnqualifiable9()
    {
        $move   = array('r7', 'r8', 'r9', 'r10', 'rd');
        $qualif = $this->gof->publicqualify($move);
        $this->assertSame(NULL, $qualif);
    }

    public function testQualifyAsASingle()
    {
        $move   = array('g1');
        $qualif = $this->gof->publicqualify($move);

        $this->assertEquals(ErebotModule_GoF::MOVE_SINGLE, $qualif['type']);
        $this->assertEquals(1, $qualif['count']);
        $this->assertEquals('1', $qualif['base']);
    }

    public function testQualifyAsAPair()
    {
        $move   = array('g1', 'm1');
        $qualif = $this->gof->publicqualify($move);

        $this->assertEquals(ErebotModule_GoF::MOVE_PAIR, $qualif['type']);
        $this->assertEquals(2, $qualif['count']);
        $this->assertEquals('1', $qualif['base']);
    }

    public function testQualifyAsThreeOfAKind()
    {
        $move   = array('g1', 'y1', 'm1');
        $qualif = $this->gof->publicqualify($move);

        $this->assertEquals(ErebotModule_GoF::MOVE_TRIO, $qualif['type']);
        $this->assertEquals(3, $qualif['count']);
        $this->assertEquals('1', $qualif['base']);
    }

    public function testQualifyAsAGang()
    {
        $move   = array('g1', 'y1', 'r1', 'm1');
        $qualif = $this->gof->publicqualify($move);

        $this->assertEquals(ErebotModule_GoF::MOVE_GANG, $qualif['type']);
        $this->assertEquals(4, $qualif['count']);
        $this->assertEquals('1', $qualif['base']);
    }

    public function testQualifyAsAGang5()
    {
        $move   = array('g1', 'g1', 'y1', 'y1', 'm1');
        $qualif = $this->gof->publicqualify($move);

        $this->assertEquals(ErebotModule_GoF::MOVE_GANG, $qualif['type']);
        $this->assertEquals(5, $qualif['count']);
        $this->assertEquals('1', $qualif['base']);
    }

    public function testQualifyAsAGang6()
    {
        $move   = array('g1', 'g1', 'y1', 'y1', 'r1', 'r1');
        $qualif = $this->gof->publicqualify($move);

        $this->assertEquals(ErebotModule_GoF::MOVE_GANG, $qualif['type']);
        $this->assertEquals(6, $qualif['count']);
        $this->assertEquals('1', $qualif['base']);
    }

    public function testQualifyAsAGang7()
    {
        $move   = array('g1', 'g1', 'y1', 'y1', 'r1', 'r1', 'm1');
        $qualif = $this->gof->publicqualify($move);

        $this->assertEquals(ErebotModule_GoF::MOVE_GANG, $qualif['type']);
        $this->assertEquals(7, $qualif['count']);
        $this->assertEquals('1', $qualif['base']);
    }

    public function testQualifyAsAStraight()
    {
        $move   = array('g1', 'y2', 'r3', 'g4', 'y5');
        $qualif = $this->gof->publicqualify($move);

        $this->assertEquals(ErebotModule_GoF::MOVE_STRAIGHT, $qualif['type']);
    }

    public function testQualifyAsAFlush()
    {
        $move   = array('g1', 'g2', 'g6', 'g9', 'g10');
        $qualif = $this->gof->publicqualify($move);

        $this->assertEquals(ErebotModule_GoF::MOVE_FLUSH, $qualif['type']);
    }

    public function testQualifyAsAStraightFlush()
    {
        $move   = array('g1', 'g2', 'g3', 'g4', 'g5');
        $qualif = $this->gof->publicqualify($move);

        $this->assertEquals(ErebotModule_GoF::MOVE_STRAIGHT_FLUSH, $qualif['type']);
    }

    public function testComparePairs()
    {
        // A pair of 1s with another superior pair of 1s.
        $handA  = array('g1', 'r1');
        $handB  = array('g1', 'm1');
        $this->assertLessThan(0, $this->gof->publiccompareHands($handA, $handB));
    }

    public function testComparePairs2()
    {
        // A pair of 1s with a pair of 2s.
        $handA  = array('r1', 'm1');
        $handB  = array('g2', 'g2');
        $this->assertLessThan(0, $this->gof->publiccompareHands($handA, $handB));
    }

    public function testCompareAPairAgainstItself()
    {
        $handA  = array('g1', 'y1');
        $handB  = array('g1', 'y1');
        $this->assertEquals(0, $this->gof->publiccompareHands($handA, $handB));
    }

    public function testCompareAStraightAgainstAFlush()
    {
        $handA  = array('g1', 'y2', 'r3', 'g4', 'y5');
        $handB  = array('g1', 'g3', 'g5', 'g7', 'g9');
        $this->assertLessThan(0, $this->gof->publiccompareHands($handA, $handB));
    }

    public function testCompareAFlushAgainstAFull()
    {
        $handA  = array('g1', 'g3', 'g5', 'g7', 'g9');
        $handB  = array('g1', 'y1', 'r1', 'g2', 'g2');
        $this->assertLessThan(0, $this->gof->publiccompareHands($handA, $handB));
    }

    public function testCompareAFullAgainstAStraightFlush()
    {
        $handA  = array('g1', 'y1', 'r1', 'g2', 'g2');
        $handB  = array('y1', 'y2', 'y3', 'y4', 'y5');
        $this->assertLessThan(0, $this->gof->publiccompareHands($handA, $handB));
    }

    public function testCompareSingleOrCombinationWithGang()
    {
        $handA  = array('rd');
        $handB  = array('g1', 'g1', 'y1', 'y1');
        $this->assertLessThan(0, $this->gof->publiccompareHands($handA, $handB));
    }

    public function testCompareSingleOrCombinationWithGang2()
    {
        $handA  = array('gp', 'yp');
        $handB  = array('g1', 'g1', 'y1', 'y1');
        $this->assertLessThan(0, $this->gof->publiccompareHands($handA, $handB));
    }

    public function testCompareSingleOrCombinationWithGang3()
    {
        $handA  = array('r10', 'r10', 'y10');
        $handB  = array('g1', 'g1', 'y1', 'y1');
        $this->assertLessThan(0, $this->gof->publiccompareHands($handA, $handB));
    }

    public function testCompareSingleOrCombinationWithGang51()
    {
        $handA  = array('r1', 'g2', 'y3', 'g4', 'r5');
        $handB  = array('g1', 'g1', 'y1', 'y1');
        $this->assertLessThan(0, $this->gof->publiccompareHands($handA, $handB));
    }

    public function testCompareSingleOrCombinationWithGang52()
    {
        $handA  = array('g2', 'g3', 'g5', 'g7', 'g9');
        $handB  = array('g1', 'g1', 'y1', 'y1');
        $this->assertLessThan(0, $this->gof->publiccompareHands($handA, $handB));
    }

    public function testCompareSingleOrCombinationWithGang53()
    {
        $handA  = array('g2', 'g2', 'y2', 'g3', 'g3');
        $handB  = array('g1', 'g1', 'y1', 'y1');
        $this->assertLessThan(0, $this->gof->publiccompareHands($handA, $handB));
    }

    public function testCompareSingleOrCombinationWithGang54()
    {
        $handA  = array('g2', 'g3', 'g4', 'g5', 'g6');
        $handB  = array('g1', 'g1', 'y1', 'y1');
        $this->assertLessThan(0, $this->gof->publiccompareHands($handA, $handB));
    }

    public function testCompareGangs()
    {
        // Gang of Four vs better Gang of Four
        $handA  = array('y9', 'y9', 'r9', 'r9');
        $handB  = array('g10', 'g10', 'y10', 'y10');
        $this->assertLessThan(0, $this->gof->publiccompareHands($handA, $handB));
    }

    public function testCompareGangs45()
    {
        // Gang of Four vs Gang of Five
        $handA  = array('y10', 'y10', 'r10', 'r10');
        $handB  = array('g1', 'g1', 'y1', 'y1', 'r1');
        $this->assertLessThan(0, $this->gof->publiccompareHands($handA, $handB));
    }

    public function testCompareGangs56()
    {
        // Gang of Five vs Gang of Six
        $handA  = array('g10', 'y10', 'y10', 'r10', 'r10');
        $handB  = array('g1', 'g1', 'y1', 'y1', 'r1', 'r1');
        $this->assertLessThan(0, $this->gof->publiccompareHands($handA, $handB));
    }

    public function testCompareGangs67()
    {
        // Gang of Six vs Gang of Seven
        $handA  = array('g10', 'g10', 'y10', 'y10', 'r10', 'r10');
        $handB  = array('g1', 'g1', 'y1', 'y1', 'r1', 'r1', 'm1');
        $this->assertLessThan(0, $this->gof->publiccompareHands($handA, $handB));
    }

}

*/

?>
