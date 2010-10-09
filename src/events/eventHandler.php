<?php

ErebotUtils::incl('../textFilter.php');
ErebotUtils::incl('../ifaces/eventHandler.php');

/**
 * \brief
 *      An event handler which will call a callback function/method
 *      whenever a set of conditions are met.
 *
 *  Such conditions may be related to the event being of a certain type,
 *  being addressed to a certain target and/or having a certain content.
 */
class       ErebotEventHandler
implements  iErebotEventHandler
{
    protected $callback;
    protected $constraints;
    protected $targets;
    protected $filters;

    // Documented in the interface.
    public function __construct(
        $callback,
        $constraints,
        iErebotEventTargets $targets    = NULL,
        iErebotTextFilter   $filters    = NULL
        )
    {
        $reflector  = new ReflectionParameter($callback, 0);
        $cls        = $reflector->getClass();
        if ($cls === NULL || !$cls->implementsInterface('iErebotEvent'))
            throw new EErebotInvalidValue('Invalid signature');

        if (!is_array($constraints))
            $constraints = array($constraints);

        foreach ($constraints as $constraint) {
            if (!is_string($constraint))
                throw new EErebotInvalidValue('Invalid event type');

            if (!class_exists($constraint) && !interface_exists($constraint))
                throw new EErebotInvalidValue('Invalid event type');

            // We want to determine if the given type (either a class
            // or an interface) implements the iErebotEvent interface.
            $reflect = new ReflectionClass($constraint);
            if (!$reflect->implementsInterface('iErebotEvent'))
                throw new EErebotInvalidValue('Invalid event type');
        }

        $this->callback         =&  $callback;
        $this->constraints      =   $constraints;
        $this->targets          =&  $targets;
        $this->filters          =&  $filters;
    }

    public function __destruct()
    {
    }

    // Documented in the interface.
    public function & getCallback()
    {
        return $this->callback;
    }

    // Documented in the interface.
    public function getConstraints()
    {
        return $this->constraints;
    }

    // Documented in the interface.
    public function & getTargets()
    {
        return $this->targets;
    }

    // Documented in the interface.
    public function & getFilters()
    {
        return $this->filters;
    }

    // Documented in the interface.
    public function handleEvent(iErebotEvent &$event)
    {
        foreach ($this->constraints as $constraint) {
            if (!($event instanceof $constraint))
                return NULL;
        }

        if ($this->targets !== NULL && !$this->targets->match($event))
            return NULL;

        if ($this->filters !== NULL && !$this->filters->match($event))
            return NULL;

        return call_user_func($this->callback, $event);
    }
}

?>
