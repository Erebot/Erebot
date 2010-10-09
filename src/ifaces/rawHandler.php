<?php

interface iErebotRawHandler
{
    public function getRaw();
    public function getCallback();
    public function handleRaw(iErebotRaw &$raw);
}

?>
