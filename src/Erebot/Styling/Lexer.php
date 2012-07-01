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

/**
 * \brief
 *      A lexer (tokenizer) for variables used
 *      in styling templates.
 */
class Erebot_Styling_Lexer
{
    /// Formula to be tokenized.
    protected $_formula;

    /// Length of the formula.
    protected $_length;

    /// Current position in the formula.
    protected $_position;

    /// Parser for the formula.
    protected $_parser;


    /// Allow stuff such as "1234".
    const PATT_INTEGER  = '/^[0-9]+/';

    /// Allow stuff such as "1.23", "1." or ".23".
    const PATT_REAL     = '/^[0-9]*\.[0-9]+|^[0-9]+\.[0-9]*/';

    /// Pattern for variable names.
    const PATT_VAR_NAME = '/^[a-zA-Z0-9_\\.]+/';


    /**
     * Constructs a new lexer for some formula.
     *
     * \param string $formula
     *      Some formula to tokenize.
     */
    public function __construct($formula, array $vars)
    {
        $this->_formula     = $formula;
        $this->_length      = strlen($formula);
        $this->_position    = 0;
        $this->_parser      = new Erebot_Styling_Parser($vars);
        $this->_tokenize();
    }

    /**
     * Returns the result of the formula.
     *
     * \retval mixed
     *      Result of the formula.
     */
    public function getResult()
    {
        return $this->_parser->getResult();
    }

    /// This method does the actual work.
    protected function _tokenize()
    {
        $operators = array(
            '(' =>  Erebot_Styling_Parser::TK_PAR_OPEN,
            ')' =>  Erebot_Styling_Parser::TK_PAR_CLOSE,
            '+' =>  Erebot_Styling_Parser::TK_OP_ADD,
            '-' =>  Erebot_Styling_Parser::TK_OP_SUB,
            '#' =>  Erebot_Styling_Parser::TK_OP_COUNT,
        );

        while ($this->_position < $this->_length) {
            $c          = $this->_formula[$this->_position];
            $subject    = substr($this->_formula, $this->_position);

            // Operators ("(", ")", "+", "-" & "#").
            if (isset($operators[$c])) {
                $this->_parser->doParse($operators[$c], $c);
                $this->_position++;
                continue;
            }

            // Real numbers (eg. "3.14").
            if (preg_match(self::PATT_REAL, $subject, $matches)) {
                $this->_position += strlen($matches[0]);
                $this->_parser->doParse(
                    Erebot_Styling_Parser::TK_NUMBER,
                    (double) $matches[0]
                );
                continue;
            }

            // Integers (eg. "42").
            if (preg_match(self::PATT_INTEGER, $subject, $matches)) {
                $this->_position += strlen($matches[0]);
                $this->_parser->doParse(
                    Erebot_Styling_Parser::TK_NUMBER,
                    (int) $matches[0]
                );
                continue;
            }

            // Whitespace.
            if (strpos(" \t", $c) !== FALSE) {
                $this->_position++;
                continue;
            }

            // Variable names.
            if (preg_match(self::PATT_VAR_NAME, $subject, $matches)) {
                $this->_position += strlen($matches[0]);
                $this->_parser->doParse(
                    Erebot_Styling_Parser::TK_VARIABLE,
                    $matches[0]
                );
                continue;
            }

            // Raise an exception.
            $this->_parser->doParse(
                Erebot_Styling_Parser::YY_ERROR_ACTION,
                $c
            );
        }

        // End of tokenization.
        $this->_parser->doParse(0, 0);
    }
}

