<?php

class ErebotTextWrapper
{
    protected $text;

    public function __construct($text)
    {
        $this->text = $text;
    }

    public function getTokens($start, $length = 0, $separator = ' ')
    {
        return ErebotUtils::gettok($this->text, $start, $length, $separator);
    }

    public function countTokens($separator = ' ')
    {
        return ErebotUtils::numtok($this->text, $separator);
    }

    public function __toString()
    {
        return $this->text;
    }
}

?>
