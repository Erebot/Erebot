<?php

class ErebotDependency
{
    protected $name;
    protected $operator;
    protected $version;

    public function __construct($dependency)
    {
        $opTokens   = ' !<>=';
        $opMapping  =   array(
                            "<"     => "<",
                            "lt"    => "<",
                            "<="    => "<=",
                            "le"    => "<=",
                            ">"     => ">",
                            "gt"    => ">",
                            ">="    => ">=",
                            "ge"    => ">=",
                            "=="    => "=",
                            "="     => "=",
                            "eq"    => "=",
                            "!="    => "!=",
                            "<>"    => "!=",
                            "ne"    => "!=",
                        );

        $dependency     = trim($dependency);
        $depNameEnd     = strcspn($dependency, $opTokens);
        $depName        = substr($dependency, 0, $depNameEnd);

        $len = strlen($dependency);
        if ($depNameEnd == $len)
            $depOp = $depVer = NULL;

        else {
            $depVerStart    = $len - strcspn(strrev($dependency), $opTokens);
            if ($depVerStart <= $depNameEnd)
                throw new EErebotInvalidValue('Invalid dependency specification');

            $depVer         = strtolower(substr($dependency, $depVerStart));
            $depOp          = strtolower(trim(substr($dependency, $depNameEnd,
                                $depVerStart - $depNameEnd), ' '));

            if (!isset($opMapping[$depOp]))
                throw new EErebotInvalidValue(
                    'Invalid dependency operator ('.$depOp.')');

            if ($depVer == '')
                throw new EErebotInvalidValue('Invalid dependency specification');
        }

        $this->name     = $depName;
        $this->operator = ($depOp === NULL ? NULL : $opMapping[$depOp]);
        $this->version  = $depVer;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getOperator()
    {
        return $this->operator;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function __toString()
    {
        if ($this->version === NULL)
            return $this->name;
        return  $this->name." ".
                $this->operator." ".
                $this->version;
    }
}

?>
