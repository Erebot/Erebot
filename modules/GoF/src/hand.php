<?php

class   GoFHand
{
    protected $player;
    protected $cards;
    protected $deck;

    public function __construct(&$player, &$deck)
    {
        $this->player   =&  $player;
        $this->cards    =   array();
        $this->deck     =&  $deck;

        // The deck contains 64 cards,
        // each player is dealt 16 cards.
        for ($i = 0; $i < 16; $i++)
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

    public function discard($combo)
    {
        $combo  = $this->deck->extractCombination($combo);
        $key    = array_search($card['card'], $this->cards);
        if ($key === FALSE)
            throw new Exception();

        unset($this->cards[$key]);
        $this->deck->discard($combo);
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
        $count      = $this->getCardsCount();
        $factors    = array(
                        16  => 5,
                        14  => 4,
                        11  => 3,
                        8   => 2,
                        1   => 1,
                    );
        foreach ($factors as $threshold => $factor)
            if ($count >= $threshold)
                return $count * $factor;
        return 0;
    }
}

?>
