<?php

class   UnoHand
{
    protected $player;
    protected $cards;
    protected $deck;

    public function __construct(&$player, &$deck, $cardsCount = 7)
    {
        $this->player   =&  $player;
        $this->cards    =   array();
        $this->deck     =&  $deck;

        for ($i = 0; $i < $cardsCount; $i++)
            $this->draw();
    }

    public function & getPlayer()
    {
        return $this->player;
    }

    public function getCards()
    {
        return $this->cards;
    }

    public function getCardsCount()
    {
        return count($this->cards);
    }

    public function draw()
    {
        $card = $this->deck->draw();
        $this->cards[] = $card;
        return $card;
    }

    public function discard($card)
    {
        $card   = $this->deck->extractCard($card);
        $key    = array_search($card['card'], $this->cards);
        if ($key === FALSE)
            throw new Exception();

        unset($this->cards[$key]);
        $this->deck->discard($card);
    }

    public function hasCard($card, $count)
    {
        if (is_array($card)) {
            if (!isset($card['card']))
                throw new Exception();
            $card = $card['card'];
        }

        $found  = array_keys($this->cards, $card);
        return (count($found) >= $count);
    }

    public function getScore()
    {
        $score = 0;
        foreach ($this->cards as $card) {
            if ($card == 'w' || $card == 'w+4')
                $score += 50;

            else if (!is_numeric($card[1]))
                $score += 20;

            else
                $score += (int) substr($card, 1);
        }
        return $score;
    }
}

?>
