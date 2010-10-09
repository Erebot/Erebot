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

abstract class  UnoDeck
{
    protected $firstCard = NULL;
    protected $waitingForColor = FALSE;

    public function extractCard($card)
    {
        if (is_array($card)) {
            if (!isset($card['card']))
                throw new EUnoInvalidMove();
            return $card;
        }
        $card = Uno::extractCard($card, NULL);
        if ($card === NULL)
            throw new EUnoInvalidMove();
        return $card;
    }

    final public function getFirstCard()
    {
        return $this->firstCard;
    }

    final public function isValidColor($color)
    {
        return  (strlen($color) == 1 &&
                strpos('rgby', $color) !== FALSE);
    }

    final public function isWaitingForColor()
    {
        return $this->waitingForColor;
    }

    abstract protected function chooseFirstCard();

    abstract public function draw();
    abstract public function shuffle();
    abstract public function getLastDiscardedCard();
    abstract public function getRemainingCardsCount();

    public function chooseColor($color)
    {
        $color = strtolower($color);
        if (!$this->isValidColor($color))
            throw new EUnoInternalError();

        $last = $this->getLastDiscardedCard();
        if ($last === NULL)
            throw new EUnoInternalError();

        if ($last['card'][0] != 'w' || !empty($last['color']))
            throw new EUnoInternalError();
        $this->waitingForColor = FALSE;
    }

    public function discard($card)
    {
        if ($this->waitingForColor)
            throw new EUnoWaitingForColor();

        $card = $this->extractCard($card);
        if ($card['color'] === NULL)
            $this->waitingForColor = TRUE;
    }
}

class   UnoDeckReal
extends UnoDeck
{
    protected $cards;
    protected $discarded;
    protected $firstCard;

    public function __construct()
    {
        $colors             = str_split('rbgy');
        $this->discarded    = array();
        $this->cards        = array();

        // Add colored cards.
        foreach ($colors as $color) {
            $this->discarded[] = array('card' => $color.'0');
            for ($i = 0; $i < 2; $i++) {
                $this->discarded[] = array('card' => $color.'r');
                $this->discarded[] = array('card' => $color.'s');
                $this->discarded[] = array('card' => $color.'+2');
                for ($j = 1; $j <= 9; $j++)
                    $this->discarded[] = array('card' => $color.$j);
            }
        }

        // Add wilds.
        for ($i = 0; $i < 4; $i++) {
            $this->discarded[] = array('card' => 'w');
            $this->discarded[] = array('card' => 'w+4');
        }

        // Shuffle cards.
        $this->shuffle();

        $this->chooseFirstCard();
    }

    protected function chooseFirstCard()
    {
        // Find the first (playable) card.
        for ($this->firstCard = reset($this->cards);
            $this->firstCard[0] == 'w';
            $this->firstCard = next($this->cards))
            ;

        unset($this->cards[key($this->cards)]);
    }

    public function draw()
    {
        if (!count($this->cards))
            throw new EUnoEmptyDeck();
        return array_shift($this->cards);
    }

    public function discard($card)
    {
        parent::discard($card);
        array_unshift($this->discarded, $this->extractCard($card));
    }

    static private function __getCard($a)
    {
        return $a['card'];
    }

    public function shuffle()
    {
        if (count($this->cards))
            throw new EUnoInternalError();

        $this->cards        = array_map(array($this, '__getCard'), $this->discarded);
        shuffle($this->cards);
        $this->discarded    = array();
    }

    public function getLastDiscardedCard()
    {
        if (!count($this->discarded))
            return NULL;
        return $this->discarded[0];
    }

    public function getRemainingCardsCount()
    {
        return count($this->cards);
    }

    public function chooseColor($color)
    {
        parent::chooseColor($color);
        $last = $this->getLastDiscardedCard();
        $last['color']      = $color;
        $this->discarded[0] = $last;
    }
}

class   UnoDeckUnlimited
extends UnoDeck
{
    protected $discarded;
    protected $firstCard;

    public function __construct()
    {
        $this->discarded = NULL;
        $this->chooseFirstCard();
    }

    protected function chooseFirstCard()
    {
        // Find the first (playable) card.
        for ($this->firstCard = $this->draw();
            $this->firstCard[0] == 'w';
            )
            ;
    }

    public function draw()
    {
        $card   = mt_rand(0, 108);

        if ($card >= 104)
            return 'w+4';
        if ($card >= 100)
            return 'w';

        $colors = array('r', 'g', 'b', 'y');
        $perCol = 18    // cards from 1 to 9 (2 each)
                + 1     // 0-card
                + 2     // draw two
                + 2     // reverse
                + 2;    // skip
        $color  = $colors[(int) $card / $perCol];
        $card  %= $perCol;

        if ($card < 18)
            return $color.(($card % 9) + 1);
        if ($card < 19)
            return $color.'0';
        if ($card < 21)
            return $color.'+2';
        if ($card < 23)
            return $color.'r';
        if ($card < 25)
            return $color.'s';

        throw new EUnoInternalError();
    }

    public function discard($card)
    {
        parent::discard($card);
        $this->discarded = $this->extractCard($card);
    }

    public function shuffle()
    {
        // Nothing to do.
    }

    public function getLastDiscardedCard()
    {
        return $this->discarded;
    }

    public function getRemainingCardsCount()
    {
        return NULL;
    }

    public function chooseColor($color)
    {
        parent::chooseColor($color);
        $last = $this->getLastDiscardedCard();
        $last['color']      = $color;
        $this->discarded    = $last;
    }
}

?>
