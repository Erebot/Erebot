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

class   EUno
extends Exception   {}

class   EUnoInvalidMove
extends EUno        {}

class   EUnoMissingCards
extends EUno        {}

class   EUnoMoveNotAllowed
extends EUno        {
    protected $allowed;

    public function __construct($message = NULL, $code = 0, Exception $previous = NULL, $allowed = NULL)
    {
        parent::__construct($message, $code, $previous);
        $this->allowed = $allowed;
    }

    public function getAllowedCards()
    {
        return $this->allowed;
    }
}

class   EUnoEmptyDeck
extends EUno        {}

class   EUnoMustDrawBeforePass
extends EUno        {}

class   EUnoAlreadyDrew
extends EUno        {}

class   EUnoInternalError
extends EUno        {}

class   EUnoWaitingForColor
extends EUno        {}

class   EUnoCannotBeChallenged
extends EUno        {}

?>
