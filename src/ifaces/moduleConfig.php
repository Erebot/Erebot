<?php

interface iErebotModuleConfig
{
    public function __construct(SimpleXMLElement &$xml);
    public function isActive($active = NULL);
    public function getName();
    public function getParam($param);
}

?>
