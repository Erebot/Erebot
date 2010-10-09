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

include_once('modules/Countdown/src/lexer.php');

class CountdownFormula
{
    protected $lexer;
    protected $owner;
    protected $formula;

    public function __construct($owner, $formula)
    {
        if (!is_string($formula) || $formula == '')
            throw new ECountdownFormulaMustBeAString();

        $this->owner        =   $owner;
        $this->formula      =   $formula;
        $formula            =   str_replace(' ', '', $formula);
        $this->lexer        =   new CountdownLexer($formula);
    }

    public function __destruct()
    {
        unset($this->lexer);
    }

    public function getResult()
    {
        return $this->lexer->getResult();
    }

    public function getNumbers()
    {
        return $this->lexer->getNumbers();
    }

    public function getFormula()
    {
        return $this->formula;
    }

    public function getOwner()
    {
        return $this->owner;
    }
}

?>
