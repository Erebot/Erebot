<?php

ErebotUtils::incl('lexer.php');

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
