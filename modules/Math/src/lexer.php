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

require_once(dirname(__FILE__).'/exceptions.php');
require_once(dirname(__FILE__).'/parser.php');

class MathLexer
{
    protected $formula;
    protected $length;
    protected $position;
    protected $skip;
    protected $parser;

    // Allow stuff such as "1234".
    const PATT_INTEGER  = '/^[0-9]+/';

    // Allow stuff such as "1.23", "1." or ".23".
    const PATT_REAL     = '/^[0-9]*\.[0-9]+|^[0-9]+\.[0-9]*/';

    public function __construct($formula)
    {
        $this->formula  = strtolower($formula);
        $this->length   = strlen($formula);
        $this->position = 0;

        $this->parser   = new MathParser();
        $this->tokenize();
    }

    public function getResult() { return $this->parser->getResult(); }

    protected function tokenize()
    {
        $operators = array(
            '(' =>  MathParser::TK_PAR_OPEN,
            ')' =>  MathParser::TK_PAR_CLOSE,
            '+' =>  MathParser::TK_OP_ADD,
            '-' =>  MathParser::TK_OP_SUB,
            '*' =>  MathParser::TK_OP_MUL,
            '/' =>  MathParser::TK_OP_DIV,
            '%' =>  MathParser::TK_OP_MOD,
            '^' =>  MathParser::TK_OP_POW,
        );

        while ($this->position < $this->length) {
            $c          = $this->formula[$this->position];
            $subject    = substr($this->formula, $this->position);

            if (isset($operators[$c])) {
                $this->parser->doParse($operators[$c], $c);
                $this->position++;
            }

            else if (preg_match(self::PATT_REAL, $subject, $matches)) {
                $this->position += strlen($matches[0]);
                $this->parser->doParse(MathParser::TK_NUMBER, (double) $matches[0]);
            }

            else if (preg_match(self::PATT_INTEGER, $subject, $matches)) {
                $this->position += strlen($matches[0]);
                $this->parser->doParse(MathParser::TK_NUMBER, (int) $matches[0]);
            }

            else if (strpos(" \t", $c) !== FALSE)
                $this->position++;
            else
                $this->parser->doParse(MathParser::YY_ERROR_ACTION, $c);
        }

        // End of tokenization.
        $this->parser->doParse(0, 0);
    }
}

?>
