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

class   EventsTest
extends Erebot_TestEnv_TestCase
{
    public function setUp()
    {
        $sxml = new SimpleXMLElement('<foo/>');
        $this->_mainConfig = $this->getMock('\\Erebot\\Interfaces\\Config\\Main', array(), array(), '', FALSE, FALSE, FALSE);
        $this->_networkConfig = $this->getMock('\\Erebot\\Interfaces\\Config\\Network', array(), array($this->_mainConfig, $sxml), '', FALSE, FALSE, FALSE);
        $this->_serverConfig = $this->getMock('\\Erebot\\Interfaces\\Config\\Server', array(), array($this->_networkConfig, $sxml), '', FALSE, FALSE, FALSE);
        $this->_bot = $this->getMock('\\Erebot\\Interfaces\\Core', array(), array($this->_mainConfig), '', FALSE, FALSE, FALSE);
        $this->_connection = $this->getMock('\\Erebot\\Interfaces\\Connection', array(), array($this->_bot, $this->_serverConfig), '', FALSE, FALSE, FALSE);
    }

    public function tearDown()
    {
        unset(
            $this->_mainConfig,
            $this->_networkConfig,
            $this->_serverConfig,
            $this->_bot,
            $this->_connection
        );
    }

    /**
     * @covers \Erebot\Event\Ping
     */
    public function testPing()
    {
        $event = new \Erebot\Event\Ping($this->_connection, "foo");
        $this->assertEquals("foo", (string) $event->getText());
    }
}

