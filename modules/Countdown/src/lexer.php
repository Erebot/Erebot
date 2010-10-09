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

include_once('modules/Countdown/src/parser.php');

class CountdownLexer
{
    protected $formula;
    protected $length;
    protected $position;
    protected $skip;
    protected $parser;
    protected $numbers;

    // Allow stuff such as "1234".
    const PATT_INTEGER  = '/^[0-9]+/';

    public function __construct($formula)
    {
        $this->formula  = $formula;
        $this->length   = strlen($formula);
        $this->position = 0;
        $this->numbers  = array();

        $this->parser   = new CountdownParser();
        $this->tokenize();
    }

    public function getResult()     { return $this->parser->getResult(); }
    public function getNumbers()    { return $this->numbers; }

    protected function tokenize()
    {
        $operators = array(
            '(' =>  CountdownParser::TK_PAR_OPEN,
            ')' =>  CountdownParser::TK_PAR_CLOSE,
            '+' =>  CountdownParser::TK_OP_ADD,
            '-' =>  CountdownParser::TK_OP_SUB,
            '*' =>  CountdownParser::TK_OP_MUL,
            '/' =>  CountdownParser::TK_OP_DIV,

        );

        while ($this->position < $this->length) {
            $c          = $this->formula[$this->position];
            $subject    = substr($this->formula, $this->position);

            if (isset($operators[$c])) {
                $this->parser->doParse($operators[$c], $c);
                $this->position++;
                continue;
            }

            if (preg_match(self::PATT_INTEGER, $subject, $matches)) {
                $this->position += strlen($matches[0]);
                $integer            = (int) $matches[0];
                $this->numbers[]    = $integer;
                $this->parser->doParse(CountdownParser::TK_INTEGER, $integer);
                continue;
            }

            // This will like result in an exception
            // being thrown, which is actually good!
            $this->parser->doParse(0, 0);
        }

        // End of tokenization.
        $this->parser->doParse(0, 0);
    }
}

?>
