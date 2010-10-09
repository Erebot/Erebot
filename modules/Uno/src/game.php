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

include_once('modules/Uno/src/exceptions.php');
include_once('modules/Uno/src/decks.php');
include_once('modules/Uno/src/hand.php');

class   Uno
{
    const RULES_LOOSE_DRAW              = 0x01;
    const RULES_CHAINABLE_PENALTIES     = 0x02;
    const RULES_REVERSIBLE_PENALTIES    = 0x04;
    const RULES_SKIPPABLE_PENALTIES     = 0x08;
    const RULES_CANCELABLE_PENALTIES    = 0x18;
    const RULES_UNLIMITED_DECK          = 0x20;
    const RULES_MULTIPLE_CARDS          = 0x40;

    protected $penalty;
    protected $lastPenaltyCard;
    protected $rules;
    protected $deck;
    protected $order;
    protected $players;
    protected $startTime;
    protected $creator;
    protected $challengeable;
    protected $legalMove;

    public function __construct($creator, $rules = 0)
    {
        if (is_numeric($rules))
            $rules = intval($rules, 0);
        else if (is_string($rules))
            $rules = self::labelsToRules($rules);
        else
            $rules = 0;

        $this->creator          =&  $creator;
        $this->penalty          =   0;
        $this->drawnCard        =   NULL;
        $this->lastPenaltyCard  =   NULL;
        $this->rules            =   $rules;
        $deckClass              =   ($rules & self::RULES_UNLIMITED_DECK ?
                                        'UnoDeckUnlimited' : 'UnoDeckReal');
        $this->deck             =   new $deckClass();
        $this->players          =   array();
        $this->startTime        =   NULL;
        $this->challengeable    =   FALSE;
        $this->legalMove        =   FALSE;
    }

    public function __destruct()
    {
        
    }

    public function & join($token)
    {
        // Determine how many cards should
        // be dealt to that new player.
        $nbPlayers = count($this->players);
        if ($nbPlayers) {
            $cardsCount = 0;
            foreach ($this->players as &$player) {
                $cardsCount += $player->getCardsCount();
            }
            unset($player);
            $cardsCount = ceil($cardsCount / $nbPlayers);
        }
        else
            $cardsCount = 7;

        $this->players[]    = new UnoHand($token, $this->deck, $cardsCount);
        $player             = end($this->players);
        if (count($this->players) == 2) {
            $this->startTime = time();
            shuffle($this->players);
        }
        return $player;
    }

    public static function labelsToRules($labels)
    {
        if (!is_string($labels))
            throw new EErebotInvalidValue('Invalid ruleset');

        $rulesMapping   =   array(
                                'loose_draw'    => self::RULES_LOOSE_DRAW,
                                'chainable'     => self::RULES_CHAINABLE_PENALTIES,
                                'reversible'    => self::RULES_REVERSIBLE_PENALTIES,
                                'skippable'     => self::RULES_SKIPPABLE_PENALTIES,
                                'cancelable'    => self::RULES_CANCELABLE_PENALTIES,    // Both spellings are correct,
                                'cancellable'   => self::RULES_CANCELABLE_PENALTIES,    // but we prefer 'cancelable'.
                                'unlimited'     => self::RULES_UNLIMITED_DECK,
                                'multiple'      => self::RULES_MULTIPLE_CARDS,
                            );

        $rules  = 0;
        $labels = strtolower($labels);
        $labels = explode(',', str_replace(' ', ',', $labels));

        foreach ($labels as $label) {
            $label = trim($label);
            if (isset($rulesMapping[$label]))
                $rules |= $rulesMapping[$label];
        }
        return $rules;
    }

    public static function rulesToLabels($rules)
    {
        $labels         =   array();
        $rulesMapping   =   array(
                                'loose_draw'    => self::RULES_LOOSE_DRAW,
                                'chainable'     => self::RULES_CHAINABLE_PENALTIES,
                                'reversible'    => self::RULES_REVERSIBLE_PENALTIES,
                                'cancelable'    => self::RULES_CANCELABLE_PENALTIES,
                                'unlimited'     => self::RULES_UNLIMITED_DECK,
                                'multiple'      => self::RULES_MULTIPLE_CARDS,
                            );

        foreach ($rulesMapping as $label => $mask) {
            if (($rules & $mask) == $mask)
                $labels[] = $label;
        }

        // 'skippable' is a subcase of 'cancelable'
        // and is therefore treated separately.
        if (($rules & self::RULES_SKIPPABLE_PENALTIES) == self::RULES_SKIPPABLE_PENALTIES &&
            ($rules & self::RULES_CANCELABLE_PENALTIES) != self::RULES_CANCELABLE_PENALTIES)
            $labels[] = 'skippable';

        sort($labels);
        return $labels;
    }

    static public function extractCard($card, $with_color)
    {
        $card       = strtolower($card);

        $wild_pattern   = '/^(w\\+4|w)(';
        if ($with_color !== FALSE)
            $wild_pattern .= '[rbgy]';
        if ($with_color === NULL)
            $wild_pattern .= '?';
        $wild_pattern  .= ')$/';

        if (preg_match($wild_pattern, $card, $matches)) {
            return array(
                'card'  => $matches[1],
                'color' => $matches[2],
                'count' => 1,
            );
        }

        if (preg_match('/^([rbgy])([0-9]|\\+2)(?:\\1\\2)*$/', $card, $matches)) {
            $count  = strlen($card) / (strlen($matches[1]) + strlen($matches[2]));
            return array(
                'card'  => $matches[1].$matches[2],
                'color' => $matches[1],
                'count' => $count,
            );
        }

        if (preg_match('/^([rbgy])([rs])(?:\\1\\2)*$/', $card, $matches)) {
            $count  = strlen($card) / (strlen($matches[1]) + strlen($matches[2]));
            return array(
                'card'  => $matches[1].$matches[2],
                'color' => $matches[1],
                'count' => $count,
            );
        }

        return NULL;
    }

    public function play($card)
    {
        if ($this->deck->isWaitingForColor())
            throw new EUnoWaitingForColor();

        $card       = strtolower($card);
        $savedCard  = $card;
        $card       = self::extractCard($card, NULL);
        $player     = $this->getCurrentPlayer();

        if ($card === NULL) {
            throw new EUnoInvalidMove('Not a valid card');
        }

        $figure = substr($card['card'], 1);

        // Trying to play multiple reverse/skip
        // at once in a non 1-vs-1 game.
        if (strlen($card['card']) == 2 && count($this->players) != 2 &&
            strpos($figure, 'rs') !== FALSE && $card['count'] > 1)
            throw new EUnoMoveNotAllowed('You cannot play multiple reverses/skips in a non 1vs1 game', 1);

        // Trying to play multiple cards at once.
        if (!($this->rules & self::RULES_MULTIPLE_CARDS) && $card['count'] > 1)
            throw new EUnoMoveNotAllowed('You cannot play multiple cards', 2);

        if (!($this->rules & self::RULES_LOOSE_DRAW) &&
            $this->drawnCard !== NULL && $card['card'] != $this->drawnCard)
            throw new EUnoMoveNotAllowed('You may only play the card you just drew', 3);

        $discard = $this->deck->getLastDiscardedCard();
        if ($discard !== NULL &&
            !$player->hasCard($card['card'], $card['count']))
            throw new EUnoMissingCards();

        do {
            // No card has been played yet,
            // so anything is acceptable.
            if ($discard === NULL)
                break;

            if ($this->penalty) {
                $colors     = str_split('bryg');
                $allowed    = array();
                $disc_fig   = substr($discard['card'], 1);
                $pen_fig    = substr($this->lastPenaltyCard['card'], 1);

                if ($disc_fig == 'r') {
                    if ($this->rules & self::RULES_REVERSIBLE_PENALTIES)
                        foreach ($colors as $color)
                            $allowed[] = $color.'r';

                    // Also takes care of self::RULES_CANCELABLE_PENALTIES.
                    if ($this->rules & self::RULES_SKIPPABLE_PENALTIES)
                        $allowed[] = $this->lastPenaltyCard['color'].'s';
                }

                else if ($disc_fig == 's') {
                    // Also takes care of self::RULES_CANCELABLE_PENALTIES.
                    if ($this->rules & self::RULES_SKIPPABLE_PENALTIES)
                        foreach ($colors as $color)
                            $allowed[] = $color.'s';

                    if ($this->rules & self::RULES_REVERSIBLE_PENALTIES)
                        $allowed[] = $this->lastPenaltyCard['color'].'r';
                }

                else if (!strcmp($pen_fig, '+2')) {
                    if ($this->rules & self::RULES_CHAINABLE_PENALTIES) {
                        $allowed[] = 'w+4';
                        foreach ($colors as $color)
                            $allowed[] = $color.'+2';
                    }

                    if ($this->rules & self::RULES_REVERSIBLE_PENALTIES)
                        $allowed[] = $this->lastPenaltyCard['color'].'r';

                    // Also takes care of self::RULES_SKIPPABLE_PENALTIES.
                    if ($this->rules & self::RULES_CANCELABLE_PENALTIES)
                        $allowed[] = $this->lastPenaltyCard['color'].'s';
                }

                else if (!strcmp($pen_fig, '+4')) {
                    if ($this->rules & self::RULES_CHAINABLE_PENALTIES)
                        $allowed[] = 'w+4';

                    if ($this->rules & self::RULES_REVERSIBLE_PENALTIES)
                        $allowed[] = $this->lastPenaltyCard['color'].'r';

                    // Also takes care of self::RULES_SKIPPABLE_PENALTIES.
                    if ($this->rules & self::RULES_CANCELABLE_PENALTIES)
                        $allowed[] = $this->lastPenaltyCard['color'].'s';
                }

                if (!in_array($card['card'], $allowed))
                    throw new EUnoMoveNotAllowed('You may not play that move now', 4, NULL, $allowed);
            }

            if ($card['card'][0] == 'w')
                break;  // Wilds.

            if ($card['color'] == $discard['color'])
                break;  // Same color.

            if ($figure == substr($discard['card'], 1))
                break;  // Same figure.

            throw new EUnoMoveNotAllowed();
        } while (0);

        // Remember last played penalty card.
        $this->challengeable = FALSE;
        if ($card['card'] == 'w+4') {
            $this->lastPenaltyCard = $card;
            $this->penalty += 4;
            if ($this->penalty == 4)
                $this->challengeable = TRUE;
        }
        else if (!strcmp($figure, '+2')) {
            $this->lastPenaltyCard = $card;
            $this->penalty += 2 * $card['count'];
        }

        // If at least one card was played before.
        if ($discard !== NULL) {
            $this->legalMove = self::isLegalMove($discard['color'],
                                                $player->getCards());

            // Remove those cards from the player's hand.
            for ($i = 0; $i < $card['count']; $i++)
                $player->discard($savedCard);
        }
        // No card has been played yet, anything is acceptable.
        else
            $this->deck->discard($savedCard);

        $change_player = TRUE;
        $skipped_player = NULL;
        if ($figure == 'r') {
            if (count($this->players) > 2 || ($this->penalty &&
                    ($this->rules & self::RULES_REVERSIBLE_PENALTIES)))
                $this->players = array_reverse($this->players);
            else
                $skipped_player = $this->getLastPlayer();
            $change_player = FALSE;
        }

        else if ($figure == 's') {
            if ($this->penalty) {
                if (($this->rules & self::RULES_CANCELABLE_PENALTIES) ==
                        self::RULES_CANCELABLE_PENALTIES) {
                    // The penalty gets canceled.
                    $this->penalty = 0;
                }
            }
            // Regular skip.
            else {
                $this->endTurn(TRUE);
                $skipped_player = $this->getCurrentPlayer();
            }
        }

        else if ($card['card'][0] == 'w' && empty($card['color']))
            throw new EUnoWaitingForColor();

        $this->endTurn($change_player);
        return $skipped_player;
    }

    public function chooseColor($color)
    {
        $this->deck->chooseColor($color);
        $this->endTurn(TRUE);
    }

    public function draw()
    {
        if ($this->deck->isWaitingForColor())
            throw new EUnoWaitingForColor();

        if ($this->drawnCard !== NULL)
            throw new EUnoAlreadyDrew();

        // Draw = pass when a penalty is at stake.
        if ($this->penalty) {
            $this->drawnCard = TRUE;
            return $this->pass();
        }

        // Otherwise, it's a normal card draw.
        else {
            $player = $this->getCurrentPlayer();
            $this->drawnCard = $player->draw();
            return array($this->drawnCard);
        }
    }

    public function pass()
    {
        if ($this->deck->isWaitingForColor())
            throw new EUnoWaitingForColor();

        if ($this->drawnCard === NULL && !$this->penalty)
            throw new EUnoMustDrawBeforePass();

        // Draw the penalty.
        $player = $this->getCurrentPlayer();
        $drawnCards = array();
        for (; $this->penalty > 0; $this->penalty--)
            $drawnCards[] = $player->draw();
        unset($player);

        $this->endTurn(TRUE);
        return $drawnCards;
    }

    protected function endTurn($change_player)
    {
        if ($change_player) {
            $last = array_shift($this->players);
            $this->players[] =& $last;
        }

        $this->drawnCard = NULL;
    }

    public function challenge()
    {
        if (!$this->challengeable)
            throw new EUnoCannotBeChallenged();

        $target = $this->getLastPlayer();
        if (!$target)
            throw new EUnoCannotBeChallenged();

        $hand   = $target->getCards();
        $legal  = $this->legalMove;
        if ($legal) {
            $target = $this->getCurrentPlayer();
            $this->penalty += 2;
            $this->endTurn(TRUE);
        }

        $drawnCards = array();
        for (; $this->penalty > 0; $this->penalty--)
            $drawnCards[] = $target->draw();

        $this->challengeable = FALSE;

        return array(
            'legal' => $legal,
            'cards' => $drawnCards,
            'hand'  => $hand,
        );
    }

    static protected function isLegalMove($color, $cards)
    {
        foreach ($cards as &$card) {
            $infos = self::extractCard($card, FALSE);
            if ($infos['color'] == $color && is_numeric($infos['card'][1]))
                return FALSE;
        }
        unset($card);
        return TRUE;
    }

    public function getCurrentPlayer()
    {
        return reset($this->players);
    }

    public function getLastPlayer()
    {
        if (!$this->getLastPlayedCard())
            return NULL;

        return end($this->players);
    }

    public function getRules($as_text)
    {
        if ($as_text)
            return $this->rulesToLabels($this->rules);
        return $this->rules;
    }

    public function & getCreator()
    {
        return $this->creator;
    }

    public function getElapsedTime()
    {
        if ($this->startTime === NULL)
            return NULL;

        return time() - $this->startTime;
    }

    public function getPenalty()
    {
        return $this->penalty;
    }

    public function getLastPlayedCard()
    {
        return $this->deck->getLastDiscardedCard();
    }

    public function & getPlayers()
    {
        return $this->players;
    }

    public function getFirstCard()
    {
        return $this->deck->getFirstCard();
    }

    public function getRemainingCardsCount()
    {
        $count = $this->deck->getRemainingCardsCount();
        if (!is_int($count) || $count < 0)
            return NULL;
        return $count;
    }
}

# vim: et ts=4 sts=4 sw=4
?>
