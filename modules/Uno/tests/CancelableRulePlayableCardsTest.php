<?php

include_once(dirname(__FILE__).'/StrictRulePlayableCardsTest.php');

class UnoCancelableRulePlayableCardsTest
extends UnoStrictRulePlayableCardsTest
{
    const RULES = Uno::RULES_CANCELABLE_PENALTIES;

    public function testCancelling()
    {
        // r+2, rs -> OK
        $this->uno->play('r+2');
        $this->uno->play('rs');
    }

    public function testCancelling3()
    {
        // r+2, rs -> OK
        $this->uno->play('w+4r');
        $this->uno->play('rs');
    }
}

?>
