<?php
/*
    This file is part of Erebot.

    Erebot is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Erebot is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Erebot.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once(
    dirname(dirname(__FILE__)) .
    DIRECTORY_SEPARATOR . 'testenv' .
    DIRECTORY_SEPARATOR . 'bootstrap.php'
);

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
            $dep = new Erebot_Dependency('foo'.$mapped.'42');
            $this->assertEquals('foo '.$value.' 42', (string) $dep);
        }

        $dep = new Erebot_Dependency('foo');
        $this->assertEquals('foo', (string) $dep);

        // Same values with additional whitespaces.
        foreach (self::$opMapping as $mapped => $value) {
            $dep = new Erebot_Dependency('  foo  '.$mapped.'  42  ');
            $this->assertEquals('foo '.$value.' 42', (string) $dep);
        }

        $dep = new Erebot_Dependency('   foo   ');
        $this->assertEquals('foo', (string) $dep);
    }

    public function testGetters()
    {
        $dep = new Erebot_Dependency('Pi >= 3.14');
        $this->assertEquals('Pi', $dep->getName());
        $this->assertEquals('>=', $dep->getOperator());
        $this->assertEquals('3.14', $dep->getVersion());
        $this->assertEquals('Pi >= 3.14', (string) $dep);
    }

    /**
     * @expectedException Erebot_InvalidValueException
     */
    public function testInvalidSpecification()
    {
        new Erebot_Dependency('foo ~= 42');
    }

    /**
     * @expectedException Erebot_InvalidValueException
     */
    public function testInvalidSpecification2()
    {
        new Erebot_Dependency('foo >');
    }

    /**
     * @expectedException Erebot_InvalidValueException
     */
    public function testInvalidSpecification3()
    {
        new Erebot_Dependency('> foo');
    }

    /**
     * @expectedException Erebot_InvalidValueException
     */
    public function testInvalidSpecification4()
    {
        new Erebot_Dependency('');
    }

    /**
     * @expectedException Erebot_InvalidValueException
     */
    public function testInvalidSpecification5()
    {
        new Erebot_Dependency(42);
    }

    /**
     * @expectedException Erebot_InvalidValueException
     */
    public function testInvalidSpecification6()
    {
        new Erebot_Dependency('<>');
    }

}

