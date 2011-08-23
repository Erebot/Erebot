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
extends ErebotModuleTestCase
{
    protected $_connection = NULL;
    protected $_cb = NULL;

    public function dummyCallback(
        Erebot_Interface_EventHandler       $handler,
        Erebot_Interface_Event_Base_Generic $event
    )
    {
        return TRUE;
    }

    public function setUp()
    {
        $sxml = new SimpleXMLElement('<foo/>');
        $this->_mainConfig = $this->getMock(
            'Erebot_Interface_Config_Main',
            array(), array(), '',
            FALSE, FALSE, FALSE
        );
        $networkConfig = $this->getMock(
            'Erebot_Interface_Config_Network',
            array(),
            array($this->_mainConfig, $sxml),
            '',
            FALSE,
            FALSE,
            FALSE
        );
        $serverConfig = $this->getMock(
            'Erebot_Interface_Config_Server',
            array(),
            array($networkConfig, $sxml),
            '',
            FALSE,
            FALSE,
            FALSE
        );
        $bot = $this->getMock(
            'ErebotTestCore',
            array(),
            array($this->_mainConfig),
            '',
            FALSE,
            FALSE,
            FALSE
        );
        $this->_connection = $this->getMock(
            'Erebot_Interface_Connection',
            array(),
            array($bot, $serverConfig),
            '',
            FALSE,
            FALSE,
            FALSE
        );
        $this->_cb = new Erebot_Callable(array($this, 'dummyCallback'));
    }

    public function classProvider()
    {
        return array(
            // Direct class
            array('Erebot_Event_Ping'),
            // Inherited class
            array('Erebot_Event_WithTextAbstract'),
            // Top class
            array('Erebot_Event_Abstract'),
        );
    }

    public function interfaceProvider()
    {
        return array(
            // Direct interface
            array('Erebot_Interface_Event_Base_TextMessage'),
            // Inherited interface
            array('Erebot_Interface_Event_Base_MessageCapable'),
            // Top interface
            array('Erebot_Interface_Event_Base_Generic'),
        );
    }

    /**
     * @dataProvider classProvider
     * @covers Erebot_Event_Match_InstanceOf
     * @covers Erebot_EventHandler
     */
    public function testMatchInstanceOfClass($cls)
    {
        $matcher    = new Erebot_Event_Match_InstanceOf($cls);
        $this->assertEquals(array($cls), $matcher->getType());
        $handler    = new Erebot_EventHandler($this->_cb, $matcher);
        $event      = new Erebot_Event_Ping($this->_connection, 'foo');
        $this->assertTrue($handler->handleEvent($event));
    }

    /**
     * @dataProvider interfaceProvider
     * @covers Erebot_Event_Match_InstanceOf
     * @covers Erebot_EventHandler
     */
    public function testMatchInstanceOfInterface($iface)
    {
        $matcher    = new Erebot_Event_Match_InstanceOf($iface);
        $this->assertEquals(array($iface), $matcher->getType());
        $handler    = new Erebot_EventHandler($this->_cb, $matcher);
        $event      = new Erebot_Event_ChanText(
            $this->_connection,
            '#foo', 'bar', 'baz'
        );
        $this->assertTrue($handler->handleEvent($event));
    }
}

