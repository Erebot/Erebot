<?php

require_once(
    dirname(dirname(__FILE__)) .
    DIRECTORY_SEPARATOR . 'testenv' .
    DIRECTORY_SEPARATOR . 'bootstrap.php'
);

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
        $this->_cb = array($this, 'dummyCallback');
    }

    public function testMatchByDirectClass()
    {
        $handler    = new Erebot_EventHandler(
            $this->_cb,
            new Erebot_Event_Match_InstanceOf('Erebot_Event_Logon')
        );
        $event      = new Erebot_Event_Logon($this->_connection);
        $this->assertTrue($handler->handleEvent($this->_mainConfig, $event));
    }

    public function testMatchByInheritedClass()
    {
        $handler    = new Erebot_EventHandler(
            $this->_cb,
            new Erebot_Event_Match_InstanceOf('Erebot_Event_WithTextAbstract')
        );
        $event      = new Erebot_Event_Ping($this->_connection, 'foo');
        $this->assertTrue($handler->handleEvent($this->_mainConfig, $event));
    }

    public function testMatchByTopClass()
    {
        $handler    = new Erebot_EventHandler(
            $this->_cb,
            new Erebot_Event_Match_InstanceOf('Erebot_Event_Abstract')
        );
        $event      = new Erebot_Event_Ping($this->_connection, 'foo');
        $this->assertTrue($handler->handleEvent($this->_mainConfig, $event));
    }

    public function testMatchByDirectInterface()
    {
        $handler    = new Erebot_EventHandler(
            $this->_cb,
            new Erebot_Event_Match_InstanceOf(
                'Erebot_Interface_Event_TextMessage'
            )
        );
        $event      = new Erebot_Event_ChanText(
            $this->_connection,
            '#foo', 'bar', 'baz'
        );
        $this->assertTrue($handler->handleEvent($this->_mainConfig, $event));
    }

    public function testMatchByInheritedInterface()
    {
        $handler    = new Erebot_EventHandler(
            $this->_cb,
            new Erebot_Event_Match_InstanceOf(
                'Erebot_Interface_Event_MessageCapable'
            )
        );
        $event      = new Erebot_Event_ChanText(
            $this->_connection,
            '#foo', 'bar', 'baz'
        );
        $this->assertTrue($handler->handleEvent($this->_mainConfig, $event));
    }

    public function testMatchByTopInterface()
    {
        $handler    = new Erebot_EventHandler(
            $this->_cb,
            new Erebot_Event_Match_InstanceOf('Erebot_Interface_Event_Generic')
        );
        $event      = new Erebot_Event_ChanText(
            $this->_connection,
            '#foo', 'bar', 'baz'
        );
        $this->assertTrue($handler->handleEvent($this->_mainConfig, $event));
    }
}

