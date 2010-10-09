<?php

interface iErebotRaw
{
    public function __construct(iErebotConnection &$connection, $raw, $source, $target, $text);
    public function & getConnection();
    public function getRaw();
    public function getSource();
    public function getTarget();
    public function getText();
}

?>
