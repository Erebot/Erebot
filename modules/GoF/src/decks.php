<?php

abstract class  GoFDeck
{
    final public function isValidColor($color)
    {
        return  (strlen($color) == 1 &&
                strpos('gyr', $color) !== FALSE);
    }

    abstract public function draw();
    abstract public function shuffle();
    abstract public function getLastDiscardedCombo();
    abstract public function discard($card);
}

class   UnoDeckReal
extends UnoDeck
{
    protected $cards;
    protected $discarded;

    public function __construct()
    {
        // Shuffle takes care of recreating the deck.
        $this->shuffle();
    }

    public function draw()
    {
        if (!count($this->cards))
            throw new EGoFInternalError();
        return array_shift($this->cards);
    }

    public function discard($combo)
    {
        $this->discarded = $combo;
    }

    public function shuffle()
    {
        $this->discarded    = NULL;
        $this->cards        = array();
        $colors             = str_split('gyr');

        // Add colored cards.
        foreach ($colors as $color) {
            for ($i = 0; $i < 2; $i++) {
                for ($j = 1; $j <= 10; $j++)
                    $this->cards[] = $color.$j;
            }
        }

        // Add special cards.
        $this->cards[] = 'm1';  // Multi-colored 1
        $this->cards[] = 'gp';  // Green phoenix
        $this->cards[] = 'yp';  // Yellow phoenix
        $this->cards[] = 'rd';  // Red dragon

        shuffle($this->cards);
    }

    public function getLastDiscardedCombo()
    {
        return $this->discarded;
    }
}

?>
