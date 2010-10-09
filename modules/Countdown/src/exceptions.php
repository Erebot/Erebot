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
