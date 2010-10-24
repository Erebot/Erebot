<?php

include_once('tests/testenv/bootstrap.php');
include_once('src/events/eventHandler.php');

class   EventHandlerMatchTest
extends ErebotModuleTestCase
{
    protected $_connection = NULL;
    protected $_cb = NULL;

    public function dummyCallback(iErebotEvent $event)
    {
        return TRUE;
    }

    public function setUp()
    {
        $sxml = new SimpleXMLElement('<foo/>');
        $networkConfig = $this->getMock('iErebotNetworkConfig', array(), array($mainConfig, $sxml), '', FALSE, FALSE, FALSE);
        $serverConfig = $this->getMock('iErebotServerConfig', array(), array($networkConfig, $sxml), '', FALSE, FALSE, FALSE);
        $bot = $this->getMock('ErebotTestCore', array(), array($mainConfig), '', FALSE, FALSE, FALSE);
        $this->_connection = $this->getMock('iErebotConnection', array(), array($bot, $serverConfig), '', FALSE, FALSE, FALSE);
        $this->_cb           = array($this, 'dummyCallback');
    }

    public function testMatchByDirectClass()
    {
        $handler    = new ErebotEventHandler($this->_cb, 'ErebotEventLogon');
        $event      = new ErebotEventLogon($this->_connection);
        $this->assertTrue($handler->handleEvent($event));
    }

    public function testMatchByInheritedClass()
    {
        $handler    = new ErebotEventHandler($this->_cb, 'ErebotEventWithText');
        $event      = new ErebotEventPing($this->_connection, 'foo');
        $this->assertTrue($handler->handleEvent($event));
    }

    public function testMatchByTopClass()
    {
        $handler    = new ErebotEventHandler($this->_cb, 'ErebotEvent');
        $event      = new ErebotEventPing($this->_connection, 'foo');
        $this->assertTrue($handler->handleEvent($event));
    }

    public function testMatchByDirectInterface()
    {
        $handler    = new ErebotEventHandler($this->_cb, 'iErebotEventMessageText');
        $event      = new ErebotEventTextChan($this->_connection, '#foo', 'bar', 'baz');
        $this->assertTrue($handler->handleEvent($event));
    }

    public function testMatchByInheritedInterface()
    {
        $handler    = new ErebotEventHandler($this->_cb, 'iErebotEventMessageCapable');
        $event      = new ErebotEventTextChan($this->_connection, '#foo', 'bar', 'baz');
        $this->assertTrue($handler->handleEvent($event));
    }

    public function testMatchByTopInterface()
    {
        $handler    = new ErebotEventHandler($this->_cb, 'iErebotEvent');
        $event      = new ErebotEventTextChan($this->_connection, '#foo', 'bar', 'baz');
        $this->assertTrue($handler->handleEvent($event));
    }
}

