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

// Generic GoF error for easy
// try...catch'ing.
class   EGoF
extends Exception   {}

// If there are already 4 players
// and someone tries to join.
class   EGoFEnoughPlayersAlready
extends EGoF        {}

// If the given combo could not be
// recognized as a valid one.
// eg. "g0" ou "b1" are invalid moves.
class   EGoFInvalidCombo
extends EGoF        {}

// If the player doesn't have all the
// required cards to perform that combo.
class   EGoFMissingCards
extends EGoF        {}

// For other (unsorted) errors.
class   EGoFInternalError
extends EGoF        {}

// If the starting player has M1
// but tried not to play it.
// (neither alone or in a combo)
class   EGoFMustStartWithM1
extends EGoF        {}

// If a previous round took place and the winner
// tries to start the next one without first
// choosing a card to give to the previous round's loser.
class   EGoFWaitingForCard
extends EGoF        {}

// If the given combo is inferior to the currently
// leading one. The $allowed parameter may contain
// an array of valid combos which could be played.
class   EGoFInferiorCombo
extends EGoF        {
    protected $allowed;

    public function __construct($message = NULL, $code = 0, Exception $previous = NULL, $allowed = NULL)
    {
        parent::__construct($message, $code, $previous);
        $this->allowed = $allowed;
    }

    public function getAllowedCombo()
    {
        return $this->allowed;
    }
}



?>
