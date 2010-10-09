<?php

include_once(dirname(dirname(__FILE__)).'/src/game.php');
include_once(dirname(dirname(__FILE__)).'/src/hand.php');

class   UnoStub
extends Uno
{
    public function __construct($creator, $rules = 0)
    {
        parent::__construct($creator, $rules);
        unset($this->deck);
        $this->deck = new UnoDeckStub();
    }

    public function & join($player)
    {
        $this->players[]    = new UnoHandStub($player, $this->deck);
        $token              = end($this->players);
        if (count($this->players) == 2)
            $this->startTime = time();
        return $token;
    }
}

class   UnoStub2
extends Uno
{}

class   UnoDeckStub
extends UnoDeckReal
{
    protected function chooseFirstCard()
    {
        $this->firstCard = NULL;
    }
}

class   UnoHandStub
extends UnoHand
{
    public function hasCard($card, $count)
    {
        return TRUE;
    }

    public function discard($card)
    {
        if (is_array($card)) {
            if (!isset($card['card']))
                throw new Exception();
            $card = $card['card'];
        }

        $this->deck->discard($card);
    }
}

?>
