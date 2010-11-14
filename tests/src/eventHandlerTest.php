<?php

include_once('tests/testenv/bootstrap.php');

class   EventHandlerMatchTest
extends ErebotModuleTestCase
{
    protected $_connection = NULL;
    protected $_cb = NULL;

    public function dummyCallback(Erebot_Interface_Event_Generic $event)
    {
        return TRUE;
    }

    public function setUp()
    {
        $sxml = new SimpleXMLElement('<foo/>');
        $networkConfig = $this->getMock('Erebot_Interface_Config_Network', array(), array($mainConfig, $sxml), '', FALSE, FALSE, FALSE);
        $serverConfig = $this->getMock('Erebot_Interface_Config_Server', array(), array($networkConfig, $sxml), '', FALSE, FALSE, FALSE);
        $bot = $this->getMock('ErebotTestCore', array(), array($mainConfig), '', FALSE, FALSE, FALSE);
        $this->_connection = $this->getMock('Erebot_Interface_Connection', array(), array($bot, $serverConfig), '', FALSE, FALSE, FALSE);
        $this->_cb           = array($this, 'dummyCallback');
    }

    public function testMatchByDirectClass()
    {
        $handler    = new Erebot_EventHandler($this->_cb, 'Erebot_Event_Logon');
        $event      = new Erebot_Event_Logon($this->_connection);
        $this->assertTrue($handler->handleEvent($event));
    }

    public function testMatchByInheritedClass()
    {
        $handler    = new Erebot_EventHandler($this->_cb, 'Erebot_Event_WithTextAbstract');
        $event      = new Erebot_Event_Ping($this->_connection, 'foo');
        $this->assertTrue($handler->handleEvent($event));
    }

    public function testMatchByTopClass()
    {
        $handler    = new Erebot_EventHandler($this->_cb, 'Erebot_Event_Abstract');
        $event      = new Erebot_Event_Ping($this->_connection, 'foo');
        $this->assertTrue($handler->handleEvent($event));
    }

    public function testMatchByDirectInterface()
    {
        $handler    = new Erebot_EventHandler($this->_cb, 'Erebot_Interface_Event_TextMessage');
        $event      = new Erebot_Event_ChanText($this->_connection, '#foo', 'bar', 'baz');
        $this->assertTrue($handler->handleEvent($event));
    }

    public function testMatchByInheritedInterface()
    {
        $handler    = new Erebot_EventHandler($this->_cb, 'Erebot_Interface_Event_MessageCapable');
        $event      = new Erebot_Event_ChanText($this->_connection, '#foo', 'bar', 'baz');
        $this->assertTrue($handler->handleEvent($event));
    }

    public function testMatchByTopInterface()
    {
        $handler    = new Erebot_EventHandler($this->_cb, 'Erebot_Interface_Event_Generic');
        $event      = new Erebot_Event_ChanText($this->_connection, '#foo', 'bar', 'baz');
        $this->assertTrue($handler->handleEvent($event));
    }
}

