<?php

include_once(dirname(__FILE__).'/utils.php');

class   UnoStrictRulePlayableCardsTest
extends PHPUnit_Framework_TestCase
{
    const RULES = 0;    // No variants applied.

    protected $uno = NULL;

    public function setUp()
    {
        $reflect    = new ReflectionObject($this);
        $this->uno  = new UnoStub('Clicky', $reflect->getConstant('RULES'));
        $this->uno->join('foo');
        $this->uno->join('bar');
        $this->uno->join('foobar');
    }

    public function tearDown()
    {
        unset($this->uno);
        $this->uno = NULL;
    }

    public function testPlaySameColor()
    {
        // r0, r1 -> OK
        $this->uno->play('r0');
        $this->uno->play('r1');
    }

    public function testPlaySameColor2()
    {
        // r0, rs -> OK
        $this->uno->play('r0');
        $this->uno->play('rs');
    }

    public function testPlaySameColor3()
    {
        // r0, rr -> OK
        $this->uno->play('r0');
        $this->uno->play('rr');
    }

    public function testPlaySameColor4()
    {
        // r0, r+2 -> OK
        $this->uno->play('r0');
        $this->uno->play('r+2');
    }

    public function testPlaySameFigure()
    {
        // r0, g0 -> OK
        $this->uno->play('r0');
        $this->uno->play('g0');
    }

    public function testPlaySameFigure2()
    {
        // rs, gs -> OK
        $this->uno->play('rs');
        $this->uno->play('gs');
    }

    public function testPlaySameFigure3()
    {
        // rr, gr -> OK
        $this->uno->play('rr');
        $this->uno->play('gr');
    }

    /**
     * @expectedException   EUnoMoveNotAllowed
     */
    public function testSanity()
    {
        // r0, g1 -> NOK
        $this->uno->play('r0');
        $this->uno->play('g1');
    }

    /**
     * @expectedException   EUnoMoveNotAllowed
     */
    public function testSanity2()
    {
        // r0, gs -> NOK
        $this->uno->play('r0');
        $this->uno->play('gs');
    }

    /**
     * @expectedException   EUnoMoveNotAllowed
     */
    public function testSanity3()
    {
        // r0, gr -> NOK
        $this->uno->play('r0');
        $this->uno->play('gr');
    }

    /**
     * @expectedException   EUnoMoveNotAllowed
     */
    public function testSanity4()
    {
        // r0, g+2 -> NOK
        $this->uno->play('r0');
        $this->uno->play('g+2');
    }

    public function testPlayWild()
    {
        // r0, wg -> OK
        $this->uno->play('r0');
        $this->uno->play('wg');
    }

    /**
     * @expectedException   EUnoMoveNotAllowed
     */
    public function testPlayWild2()
    {
        // r+2, wg -> NOK
        $this->uno->play('r+2');
        $this->uno->play('wg');
    }

    public function testPlayWild3()
    {
        // r0, w+4g -> OK
        $this->uno->play('r0');
        $this->uno->play('w+4g');
    }

    public function testPlayWild4()
    {
        // wr, wg -> OK
        $this->uno->play('wr');
        $this->uno->play('wg');
    }

    public function testPlayWild5()
    {
        // wr, w+4g -> OK
        $this->uno->play('wr');
        $this->uno->play('w+4g');
    }

    public function testPlayAfterWild()
    {
        // wr, r0 -> OK
        $this->uno->play('wr');
        $this->uno->play('r0');
    }

    public function testPlayAfterWild2()
    {
        // wr, rs -> OK
        $this->uno->play('wr');
        $this->uno->play('rs');
    }

    public function testPlayAfterWild3()
    {
        // wr, rr -> OK
        $this->uno->play('wr');
        $this->uno->play('rr');
    }

    public function testPlayAfterWild4()
    {
        // wr, r+2 -> OK
        $this->uno->play('wr');
        $this->uno->play('r+2');
    }

    /**
     * @expectedException   EUnoMoveNotAllowed
     */
    public function testPlayAfterWild7()
    {
        // wr, gr -> NOK
        // The card "gr" is used to test whether "wr"
        // could be (mis)interpreted as "wild reverse".
        $this->uno->play('wr');
        $this->uno->play('gr');
    }

    /**
     * @expectedException   EUnoMoveNotAllowed
     */
    public function testChainPenalty()
    {
        // r+2, g+2 -> NOK
        $this->uno->play('r+2');
        $this->uno->play('g+2');
    }

    /**
     * @expectedException   EUnoMoveNotAllowed
     */
    public function testChainPenalty2()
    {
        // r+2, w+4g -> NOK
        $this->uno->play('r+2');
        $this->uno->play('w+4g');
    }

    /**
     * @expectedException   EUnoMoveNotAllowed
     */
    public function testChainPenalty3()
    {
        // +4, +2 -> NOK
        $this->uno->play('w+4r');
        $this->uno->play('r+2');
    }

    /**
     * @expectedException   EUnoMoveNotAllowed
     */
    public function testChainPenalty4()
    {
        // +4, +4 -> NOK
        $this->uno->play('w+4r');
        $this->uno->play('w+4g');
    }

    /**
     * @expectedException   EUnoMoveNotAllowed
     */
    public function testReversing()
    {
        // r+2, rr -> NOK
        $this->uno->play('r+2');
        $this->uno->play('rr');
    }

    /**
     * @expectedException   EUnoMoveNotAllowed
     */
    public function testReversing2()
    {
        // r+2, gr -> NOK
        $this->uno->play('r+2');
        $this->uno->play('gr');
    }

    /**
     * @expectedException   EUnoMoveNotAllowed
     */
    public function testReversing3()
    {
        // w+4r, rr -> NOK
        $this->uno->play('w+4r');
        $this->uno->play('rr');
    }

    /**
     * @expectedException   EUnoMoveNotAllowed
     */
    public function testReversing4()
    {
        // w+4r, gr -> NOK
        $this->uno->play('w+4r');
        $this->uno->play('gr');
    }

    /**
     * @expectedException   EUnoMoveNotAllowed
     */
    public function testCancelling()
    {
        // r+2, rs -> NOK
        $this->uno->play('r+2');
        $this->uno->play('rs');
    }

    /**
     * @expectedException   EUnoMoveNotAllowed
     */
    public function testCancelling2()
    {
        // r+2, rs -> NOK
        $this->uno->play('r+2');
        $this->uno->play('gs');
    }

    /**
     * @expectedException   EUnoMoveNotAllowed
     */
    public function testCancelling3()
    {
        // r+2, rs -> NOK
        $this->uno->play('w+4r');
        $this->uno->play('rs');
    }

    /**
     * @expectedException   EUnoMoveNotAllowed
     */
    public function testCancelling4()
    {
        // r+2, rs -> NOK
        $this->uno->play('w+4r');
        $this->uno->play('gs');
    }
}

?>
