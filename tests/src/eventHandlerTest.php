<?php

include_once('tests/connectionStub.php');
include_once('src/events/eventHandler.php');

class   EventHandlerMatchTest
extends PHPUnit_Framework_TestCase
{
    public function dummyCallback(iErebotEvent $event)
    {
        return TRUE;
    }

    public function setUp()
    {
        $this->cb           = array($this, 'dummyCallback');
        $bot                = new ErebotStubbedCore();
        $serverConfig       = ErebotStubbedServerConfig::create(array());
        $this->connection   = new ErebotStubbedConnection($bot, $serverConfig);
    }

    public function testMatchByDirectClass()
    {
        $handler    = new ErebotEventHandler($this->cb, 'ErebotEventLogon');
        $event      = new ErebotEventLogon($this->connection);
        $this->assertTrue($handler->handleEvent($event));
    }

    public function testMatchByInheritedClass()
    {
        $handler    = new ErebotEventHandler($this->cb, 'ErebotEventWithText');
        $event      = new ErebotEventPing($this->connection, 'foo');
        $this->assertTrue($handler->handleEvent($event));
    }

    public function testMatchByTopClass()
    {
        $handler    = new ErebotEventHandler($this->cb, 'ErebotEvent');
        $event      = new ErebotEventPing($this->connection, 'foo');
        $this->assertTrue($handler->handleEvent($event));
    }

    public function testMatchByDirectInterface()
    {
        $handler    = new ErebotEventHandler($this->cb, 'iErebotEventMessageText');
        $event      = new ErebotEventTextChan($this->connection, '#foo', 'bar', 'baz');
        $this->assertTrue($handler->handleEvent($event));
    }

    public function testMatchByInheritedInterface()
    {
        $handler    = new ErebotEventHandler($this->cb, 'iErebotEventMessageCapable');
        $event      = new ErebotEventTextChan($this->connection, '#foo', 'bar', 'baz');
        $this->assertTrue($handler->handleEvent($event));
    }

    public function testMatchByTopInterface()
    {
        $handler    = new ErebotEventHandler($this->cb, 'iErebotEvent');
        $event      = new ErebotEventTextChan($this->connection, '#foo', 'bar', 'baz');
        $this->assertTrue($handler->handleEvent($event));
    }
}

