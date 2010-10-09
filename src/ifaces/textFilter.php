<?php

interface iErebotTextFilter
{
    public function __construct($type = NULL, $pattern = NULL, $require_prefix = FALSE);
    public function addPattern($type, $pattern, $require_prefix = FALSE);
    public function removePattern($type, $pattern, $require_prefix = FALSE);
    public function getPatterns($type = NULL);
    public function match(iErebotEvent &$event);
    public static function getRecognizedPrefixes();
}

?>
