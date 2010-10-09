<?php

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
