<?php

class ErebotRawHandler
{
    protected $raw;
    protected $callback;

    public function __construct($callback, $raw)
    {
        $this->raw      =   $raw;
        $this->callback =&  $callback;
    }

    public function __destruct()
    {
    }

    public function getRaw()
    {
        return $this->raw;
    }

    public function getCallback()
    {
        return $this->callback;
    }

    public function match(ErebotRaw &$raw)
    {
        return ($raw->getRaw() == $this->raw);
    }
}

?>