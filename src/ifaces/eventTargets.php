<?php

interface iErebotEventTargets
{
    public function __construct($order);
    public function setOrder($order);
    public function getOrder();
    public function match(iErebotEvent &$event);
    public function addRule($type, $nick = self::MATCH_ALL, $chan = self::MATCH_ALL);
    public function removeRule($type, $nick = self::MATCH_ALL, $chan = self::MATCH_ALL);    
}

?>
