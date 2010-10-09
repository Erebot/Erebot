<?php

interface iErebotTimer
{
    public function __construct($callback, $delay, $repeat);
    public function & getCallback();
    public function getDelay();
    public function isRepeated($repeat = NULL);
    public function & getStream();
    public function reset();
    public function activate();
}

?>
