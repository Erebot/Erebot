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
        $this->_mainConfig = $this->getMockBuilder('\\Erebot\\Interfaces\\Config\\Main')->getMock();
        $this->_networkConfig = $this->getMockBuilder('\\Erebot\\Interfaces\\Config\\Network')
            ->setConstructorArgs(array($this->_mainConfig, $sxml))
            ->getMock();
        $this->_serverConfig = $this->getMockBuilder('\\Erebot\\Interfaces\\Config\\Server')
            ->setConstructorArgs(array($this->_networkConfig, $sxml))
            ->getMock();
        $this->_bot = $this->getMockBuilder('\\Erebot\\Interfaces\\Core')
            ->setConstructorArgs(array($this->_mainConfig))
            ->getMock();
        $this->_connection = $this->getMockBuilder('\\Erebot\\Interfaces\\IrcConnection')
            ->setConstructorArgs(array($this->_bot, $this->_serverConfig))
            ->getMock();
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

