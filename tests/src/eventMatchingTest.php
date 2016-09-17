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

class   EventHandlerMatchTest
extends Erebot_Testenv_Module_TestCase
{
    protected $_connection = NULL;
    protected $_cb = NULL;

    public function dummyCallback(
        \Erebot\Interfaces\EventHandler $handler,
        \Erebot\Interfaces\Event\Base\Generic $event
    ) {
        return TRUE;
    }

    public function setUp()
    {
        $sxml = new SimpleXMLElement('<foo/>');
        $this->_mainConfig = $this->getMockBuilder('\\Erebot\\Interfaces\\Config\\Main')->getMock();
        $networkConfig = $this->getMockBuilder('\\Erebot\\Interfaces\\Config\\Network')
            ->setConstructorArgs(array($this->_mainConfig, $sxml))
            ->getMock();
        $serverConfig = $this->getMockBuilder('\\Erebot\\Interfaces\\Config\\Server')
            ->setConstructorArgs(array($networkConfig, $sxml))
            ->getMock();
        $bot = $this->getMockBuilder('\\Erebot\\Interfaces\\Core')
            ->setConstructorArgs(array($this->_mainConfig))
            ->getMock();
        $this->_connection = $this->getMockBuilder('\\Erebot\\Interfaces\\Connection')
            ->setConstructorArgs(array($bot, $serverConfig))
            ->getMock();
        $this->_cb = \Erebot\CallableWrapper::wrap(array($this, 'dummyCallback'));
    }

    public function classProvider()
    {
        return array(
            // Direct class
            array('\\Erebot\\Event\\Ping'),
            // Inherited class
            array('\\Erebot\\Event\\WithTextAbstract'),
            // Top class
            array('\\Erebot\\Event\\AbstractEvent'),
        );
    }

    public function interfaceProvider()
    {
        return array(
            // Direct interface
            array('\\Erebot\\Interfaces\\Event\\Base\\TextMessage'),
            // Inherited interface
            array('\\Erebot\\Interfaces\\Event\\Base\\MessageCapable'),
            // Top interface
            array('\\Erebot\\Interfaces\\Event\\Base\\Generic'),
        );
    }

    /**
     * @dataProvider classProvider
     * @covers \Erebot\Event\Match\Type
     * @covers \Erebot\EventHandler
     */
    public function testMatchTypeClass($cls)
    {
        $matcher    = new \Erebot\Event\Match\Type($cls);
        $this->assertEquals(array($cls), $matcher->getType());
        $handler    = new \Erebot\EventHandler($this->_cb, $matcher);
        $event      = new \Erebot\Event\Ping($this->_connection, 'foo');
        $this->assertTrue($handler->handleEvent($event));
    }

    /**
     * @dataProvider interfaceProvider
     * @covers \Erebot\Event\Match\Type
     * @covers \Erebot\EventHandler
     */
    public function testMatchTypeInterface($iface)
    {
        $matcher    = new \Erebot\Event\Match\Type($iface);
        $this->assertEquals(array($iface), $matcher->getType());
        $handler    = new \Erebot\EventHandler($this->_cb, $matcher);
        $event      = new \Erebot\Event\ChanText(
            $this->_connection,
            '#foo', 'bar', 'baz'
        );
        $this->assertTrue($handler->handleEvent($event));
    }
}

