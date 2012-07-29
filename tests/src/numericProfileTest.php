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

abstract class  NumericProfileTestHelper
extends         Erebot_Testenv_Module_TestCase
{
    protected $_profile = NULL;

    public function setUp()
    {
        parent::setUp();
        $this->_connection
            ->expects($this->any())
            ->method('getNumericProfile')
            ->will($this->returnCallback(array($this, 'getNumericProfile')));
    }

    public function getNumericProfile()
    {
        return $this->_profile;
    }
}

class   NumericReferenceTest
extends NumericProfileTestHelper
{
    /**
     * @covers Erebot_NumericReference
     */
    public function testSimpleReference()
    {
        // The loader is initially empty.
        $ref = new Erebot_NumericReference($this->_connection, 'RPL_WELCOME');
        $this->assertNull($ref->getValue());

        // Swap a profile in that adds the missing numeric and test again.
        $this->_profile = new Erebot_NumericProfile_RFC2812();
        $this->assertSame(0x001, $ref->getValue());
    }

    public function testExtension()
    {
        $this->_profile = new Erebot_NumericProfile_RFC2812();
        // That numeric is an extension used by bahamut
        // and so it isn't defined in RFC 2812.
        $ref = new Erebot_NumericReference($this->_connection, 'ERR_GHOSTEDCLIENT');
        $this->assertNull($ref->getValue());
    }

    public function testAlias()
    {
        $this->_profile = new Erebot_NumericProfile_RFC2812();
        // RFC 2812 misspells this numeric message.
        $ref = new Erebot_NumericReference(
            $this->_connection,
            'ERR_ALREADYREGISTRED'
        );
        $this->assertSame(462, $ref->getValue());
        // Now, try again with the correct spelling
        // (which is an alias used by some IRCds).
        $ref = new Erebot_NumericReference(
            $this->_connection,
            'ERR_ALREADYREGISTERED'
        );
        $this->assertSame(462, $ref->getValue());
    }
}

