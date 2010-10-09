<?php

ErebotUtils::incl('../textFilter.php');

class ErebotEventHandler
{
    protected $callback;
    protected $type;
    protected $targets;
    protected $module;
    protected $filters;

    public function __construct(
        $callback,
        $type,
        ErebotEventTargets  $targets    = NULL,
        ErebotTextFilter    $filters    = NULL
        )
    {
        if (!is_callable($callback))
            throw new EErebotInvalidValue('Invalid callback');

        $allowedIds = ErebotEvent::getEvents();
        if (isset($allowedIds[$type]))
            $type = $allowedIds[$type];
        else if (!in_array($type, $allowedIds))
            throw new EErebotInvalidValue('Unknown event type');

        $caller = ErebotUtils::getCallerObject();
        if ($caller === NULL)
            throw new EErebotIllegalAction('Called from invalid context');

        $this->callback         =&  $callback;
        $this->type             =   $type;
        $this->targets          =&  $targets;
        $this->module           =   get_class($caller);
        $this->filters          =&  $filters;
    }

    public function __destruct()
    {
    }

    public function & getCallback()
    {
        return $this->callback;
    }

    public function getType()
    {
        return $this->type;
    }

    public function & getTargets()
    {
        return $this->targets;
    }

    public function & getFilters()
    {
        return $this->filters;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function match(ErebotEvent &$event)
    {
        if ($event->getType() != $this->type)
            return FALSE;

        if ($this->targets !== NULL && !$this->targets->match($event))
            return FALSE;

        if ($this->filters !== NULL && !$this->filters->match($event))
            return FALSE;

        return TRUE;
    }
}

?>
