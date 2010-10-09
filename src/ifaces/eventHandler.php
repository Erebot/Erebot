<?php

interface iErebotEventHandler
{
    public function __construct($callback, $constraints,
        iErebotEventTargets $targets    = NULL,
        iErebotTextFilter   $filters    = NULL);
    public function & getCallback();
    public function getConstraints();
    public function & getTargets();
    public function & getFilters();
    public function handleEvent(iErebotEvent &$event);
}

?>
