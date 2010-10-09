<?php

include_once(dirname(__FILE__).'/StrictRulePlayableCardsTest.php');

class UnoReversableRulePlayableCardsTest
extends UnoStrictRulePlayableCardsTest
{
    const RULES = Uno::RULES_REVERSIBLE_PENALTIES;

    public function testReversing()
    {
        // r+2, rr -> OK
        $this->uno->play('r+2');
        $this->uno->play('rr');
        $this->uno->play('gr');
        $this->uno->play('br');
        $this->uno->play('yr');
    }

    public function testReversing3()
    {
        // w+4r, rr -> NOK
        $this->uno->play('w+4r');
        $this->uno->play('rr');
        $this->uno->play('gr');
        $this->uno->play('br');
        $this->uno->play('yr');
    }
}

?>
