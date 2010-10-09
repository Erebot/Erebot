<?php

include_once('../../tests/connectionStub.php');

/**
 * @runTestsInSeparateProcesses
 */
class   NickTrackerGeneralTest
extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->connection = new ErebotStubbedConnection(array(
            'ServerCapabilities',
            'NickTracker',
        ));

        $this->module =& $this->connection->getModule('NickTracker',
                            ErebotStubbedConnection::MODULE_BY_NAME);

        $this->token = $this->module->startTracking('foo');
    }

    public function tearDown()
    {
        $this->module->stopTracking($this->token);
        unset($this->token);
        unset($this->module);
        unset($this->connection);
    }

    public function testGetNick()
    {
        $nick = $this->module->getNick($this->token);
        $this->assertSame('foo', $nick);
    }

    public function testNickChange()
    {
        $event = new ErebotEvent($this->connection,
            'foo', NULL, ErebotEvent::ON_NICK, 'bar');
        $this->connection->dispatchEvent($event);

        $nick = $this->module->getNick($this->token);
        $this->assertSame('bar', $nick);
    }
}

/**
 * @runTestsInSeparateProcesses
 */
class   NickTrackerQuitTest
extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->connection = new ErebotStubbedConnection(array(
            'ServerCapabilities',
            'NickTracker',
        ));

        $this->module =& $this->connection->getModule('NickTracker',
                            ErebotStubbedConnection::MODULE_BY_NAME);

        $this->token = $this->module->startTracking('foo');
    }

    public function tearDown()
    {
        unset($this->token);
        unset($this->module);
        unset($this->connection);
    }

    /**
     * @expectedException   EErebotNotFound
     */
    public function testQuit()
    {
        $event = new ErebotEvent($this->connection,
            'foo', NULL, ErebotEvent::ON_QUIT, '');
        $this->connection->dispatchEvent($event);

        $nick = $this->module->getNick($this->token);
    }
}

?>
