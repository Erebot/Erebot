<?php

class   ECountdownInvalidValue
extends Exception
{
    public function __construct($location, $expected, $given)
    {
        $this->location     = $location;
        $this->expectedData = $expected;
        $this->givenData    = $given;

        parent::__construct(
            sprintf("Invalid value, expected %s, got %s for %s",
                $this->expectedData, $this->givenData, $this->location)
        );
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function getExpectedData()
    {
        return $this->expectedData;
    }

    public function getGivenData()
    {
        return $this->givenData;
    }
}

class   ECountdownNoSuchNumberOrAlreadyUsed
extends Exception {}

class   ECountdownFormulaMustBeAString
extends Exception {}

class   ECountdownDivisionByZero
extends Exception {}

class   ECountdownNonIntegralDivision
extends Exception {}

class   ECountdownSyntaxError
extends Exception {}

?>
