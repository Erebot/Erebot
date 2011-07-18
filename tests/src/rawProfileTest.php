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

abstract class  RawProfileTestHelper
extends         ErebotModuleTestCase
{
    protected $_loader = NULL;

    public function setUp()
    {
        $this->_loader = new Erebot_RawProfileLoader(array());
        parent::setUp();
        $this->_connection
            ->expects($this->any())
            ->method('getRawProfileLoader')
            ->will($this->returnValue($this->_loader));
    }
}

class   RawReferenceTest
extends RawProfileTestHelper
{
    /**
     * @covers Erebot_RawReference
     */
    public function testSimpleReference()
    {
        // The loader is initially empty.
        $ref = new Erebot_RawReference($this->_connection, 'RPL_WELCOME');
        $this->assertNull($ref->getValue());

        // Swap a profile in that adds the missing raw and test again.
        $this->_loader[] = 'Erebot_Interface_RawProfile_RFC2812';
        $this->assertSame(0x001, $ref->getValue());
    }
}

class   RawProfileLoaderTest
extends RawProfileTestHelper
{
    /**
     * @covers Erebot_RawProfileLoader::offsetSet
     * @covers Erebot_RawProfileLoader::getRawByName
     */
    public function testProfileOverride()
    {
        // We need to fill two slots as the loader uses a foreach loop
        // which doesn't sort the array first...
        $this->_loader[0] = 'Erebot_Interface_RawProfile_ConflictingRFC2812';
        $this->_loader[1] = 'Erebot_Interface_RawProfile_ConflictingRFC2812';
        $this->assertSame(0x005, $this->_loader->getRawByName('RPL_BOUNCE'));

        // Replace the profile in the first slot with one that overrides
        // the raws and has higher priority.
        $this->_loader[0] = 'Erebot_Interface_RawProfile_Bounce';
        $this->assertSame(0x00A, $this->_loader->getRawByName('RPL_BOUNCE'));
    }
}

