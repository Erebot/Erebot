<?php

ErebotUtils::incl('../ifaces/rawHandler.php');

class       ErebotRawHandler
implements  iErebotRawHandler
{
    protected $raw;
    protected $callback;

    public function __construct($callback, $raw)
    {
        $reflector  = new ReflectionParameter($callback, 0);
        $cls        = $reflector->getClass();
        if ($cls === NULL || !$cls->implementsInterface('iErebotRaw'))
            throw new EErebotInvalidValue('Invalid signature');

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

    public function handleRaw(iErebotRaw &$raw)
    {
        if ($raw->getRaw() != $this->raw)
            return NULL;

        return call_user_func($this->callback, $raw);
    }
}

?>
