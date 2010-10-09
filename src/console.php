<?php

class ErebotConsole
{
    final protected function __construct(&$socket)
    {

    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function __clone()
    {
        throw new Exception('Cloning is forbidden');
    }

    public function getInstance()
    {
        static $instance = NULL;
        if ($instance === NULL) {
            $c = __CLASS__;
            $instance = new $c;
        }
        return $instance;
    }

    public function hasData()
    {
    }

    public function readLine()
    {
    }

    public function writeLine($line)
    {
    }
}

?>