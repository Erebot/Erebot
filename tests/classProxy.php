<?php

function createProxyClass($class)
{
    if (is_object($class))
        $class = get_class($class);

    $proxyName = "Proxy$class";
    if (class_exists($proxyName))
        return $proxyName;

    $code =<<<CLASS
class   $proxyName
extends $class
{
    public function __call(\$name,\$args)
    {
        if (substr(\$name, 0, 6) == 'public')
            return call_user_func_array(array(\$this, 'parent::'.substr(\$name, 6)), \$args);
    }

    public static function __callStatic(\$name,\$args)
    {
        if (substr(\$name, 0, 6) == 'public')
        return call_user_func_array(array('$class', 'parent::'.substr(\$name, 6)), \$args);
    }
}
CLASS;

    eval($code);
    return $proxyName;
}

?>