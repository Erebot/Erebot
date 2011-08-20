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

if (!defined('TESTENV_DIR'))
    define(
        'TESTENV_DIR',
        dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'testenv'
    );
require_once(TESTENV_DIR . DIRECTORY_SEPARATOR . 'bootstrap.php');

class   IdentityTest
extends PHPUnit_Framework_TestCase
{
    /**
     * @covers Erebot_Identity
     */
    public function testNominalCase()
    {
        $identity = new Erebot_Identity('foo!ident@host');
        $this->assertSame('foo',    (string) $identity);
        $this->assertSame('foo',    $identity->getNick());
        $this->assertSame('ident',  $identity->getIdent());
        $this->assertSame('host',   $identity->getHost());
    }

    /**
     * @covers Erebot_Identity
     */
    public function testNominalCase2()
    {
        $identity = new Erebot_Identity('foo');
        $this->assertSame('foo',    (string) $identity);
        $this->assertSame('foo',    $identity->getNick());
        $this->assertSame(NULL,     $identity->getIdent());
        $this->assertSame(NULL,     $identity->getHost());
    }
}

class   InvalidIdentitiesTest
extends PHPUnit_Framework_TestCase
{
    public function invalidMasksProvider()
    {
        $masks = array(
            'foo!@',
            'foo@!',
            'foo!',
            'foo@',
            'foo!ident@',
            'foo!@host',
            'foo@host!ident',
            'foo!ident',
            'foo@host',
        );
        $masks = array_map(create_function('$a', 'return array($a);'), $masks);
        return $masks;
    }

    /**
     * @dataProvider        invalidMasksProvider
     * @expectedException   Erebot_InvalidValueException
     * @covers              Erebot_Identity
     */
    public function testInvalidMasks($mask)
    {
        new Erebot_Identity($mask);
    }
}
