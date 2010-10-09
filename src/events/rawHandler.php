<?php

ErebotUtils::incl('../ifaces/rawHandler.php');

/**
 * \brief
 *      A class to handle raw numeric events.
 *
 * This class will call a given callback method/function
 * whenever the bot receives a raw numeric event for the
 * raw code this instance is meant to handle.
 */
class       ErebotRawHandler
implements  iErebotRawHandler
{
    protected $raw;
    protected $callback;

    // Documented in the interface.
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

    // Documented in the interface.
    public function getRaw()
    {
        return $this->raw;
    }

    // Documented in the interface.
    public function getCallback()
    {
        return $this->callback;
    }

    // Documented in the interface.
    public function handleRaw(iErebotRaw &$raw)
    {
        if ($raw->getRaw() != $this->raw)
            return NULL;

        return call_user_func($this->callback, $raw);
    }
}

?>
