<?php

include_once('src/utils.php');
include_once('src/dependency.php');

class   DependencyTest
extends PHPUnit_Framework_TestCase
{
    static private  $opMapping  =   array(
                                        "<"     => "<",
                                        " lt "  => "<",
                                        "<="    => "<=",
                                        " le "  => "<=",
                                        ">"     => ">",
                                        " gt "  => ">",
                                        ">="    => ">=",
                                        " ge "  => ">=",
                                        "=="    => "=",
                                        "="     => "=",
                                        " eq "  => "=",
                                        "!="    => "!=",
                                        "<>"    => "!=",
                                        " ne "  => "!=",
                                    );

    public function testValidDependencySpecifications()
    {
        foreach (self::$opMapping as $mapped => $value) {
            $dep = new ErebotDependency('foo'.$mapped.'42');
            $this->assertEquals('foo '.$value.' 42', (string) $dep);
        }

        $dep = new ErebotDependency('foo');
        $this->assertEquals('foo', (string) $dep);

        // Same values with additional whitespaces.
        foreach (self::$opMapping as $mapped => $value) {
            $dep = new ErebotDependency('  foo  '.$mapped.'  42  ');
            $this->assertEquals('foo '.$value.' 42', (string) $dep);
        }

        $dep = new ErebotDependency('   foo   ');
        $this->assertEquals('foo', (string) $dep);
    }

    /**
     * @expectedException EErebotInvalidValue
     */
    public function testInvalidSpecification()
    {
        new ErebotDependency('foo ~= 42');
    }

    /**
     * @expectedException EErebotInvalidValue
     */
    public function testInvalidSpecification2()
    {
        new ErebotDependency('foo >');
    }
}

