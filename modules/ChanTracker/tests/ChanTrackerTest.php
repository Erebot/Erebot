<?php

include_once(dirname(dirname(__FILE__)).'/ChanTracker.php');

/*
class   ChanTrackerTest
extends PHPUnit_Framework_TestCase
{
    protected $connection = NULL;
    protected $tracker;

    protected function setUp()
    {
        $proxy = createProxyClass('ErebotModule_ChanTracker');

        if ($this->connection === NULL)
            $this->connection = new ErebotConnection();

        $this->tracker = new $proxy(
            $this->connection,
            ErebotModuleBase::RELOAD_TESTING    |
            ErebotModuleBase::RELOAD_MEMBERS);
    }

    protected function tearDown()
    {
        unset($this->tracker, $this->connection);
        $this->tracker      =
        $this->connection   = NULL;
    }


    public function testDetectPresenceOnChannel()
    {
        $this->assertFalse($this->tracker->publicisInChannel('#test', 'test'),
            "No activity yet. Test user should not be in channel");
    }

    public function testDetectPresenceOnChannel2()
    {
        // Test JOIN/PART
        $event = new ErebotEvent($this->connection, 'test', '#test', ErebotEvent::ON_JOIN, NULL);
        $this->tracker->publichandleJoin($event);
        $this->assertTrue($this->tracker->publicisInChannel('#test', 'test'),
            "Test user should have joined the channel");

        $event = new ErebotEvent($this->connection, 'test', '#test', ErebotEvent::ON_PART, 'Bye');
        $this->tracker->publichandlePart($event);
        $this->assertFalse($this->tracker->publicisInChannel('#test', 'test'),
            "Test user should have left the channel");
    }

    public function testDetectPresenceOnChannel3()
    {
        // Test JOIN/NICK
        $event = new ErebotEvent($this->connection, 'test', '#test', ErebotEvent::ON_JOIN, NULL);
        $this->tracker->publichandleJoin($event);
        $this->assertFalse($this->tracker->publicisInChannel('#test', 'test'),
            "Test user should have joined the channel");

        $event = new ErebotEvent($this->connection, 'test', 'test2', ErebotEvent::ON_NICK, NULL);
        $this->tracker->publichandleNick($event);
        $this->assertTrue($this->tracker->publicisInChannel('#test', 'test2'),
            "Test user changed his nickname! The new nickname SHOULD be detected");
        $this->assertFalse($this->tracker->publicisInChannel('#test', 'test'),
            "Test user changed his nickname! The old nickname SHOULD NOT be detected");
    }

    public function testDetectPresenceOnChannel4()
    {
        // Test JOIN/KICK
        $event = new ErebotEvent($this->connection, 'test', '#test', ErebotEvent::ON_JOIN, NULL);
        $this->tracker->publichandleJoin($event);
        $this->assertFalse($this->tracker->publicisInChannel('#test', 'test'),
            "Test user should have joined the channel");

        $event = new ErebotEvent($this->connection, 'test', '#test', ErebotEvent::ON_KICK, NULL);
        $this->tracker->publichandleKick($event);
        $this->assertFalse($this->tracker->publicisInChannel('#test', 'test2'),
            "Test user has been kicked from channel. It SHOULD NOT be detected");
    }

    public function testDetectPresenceOnChannel5()
    {
        // Test JOIN/QUIT
        $event = new ErebotEvent($this->connection, 'test', '#test', ErebotEvent::ON_JOIN, NULL);
        $this->tracker->publichandleJoin($event);
        $this->assertFalse($this->tracker->publicisInChannel('#test', 'test'),
            "Test user should have joined the channel again");

        $event = new ErebotEvent($this->connection, 'test', NULL, ErebotEvent::ON_QUIT, 'Bye');
        $this->tracker->publichandleQuit($event);
        $this->assertFalse($this->tracker->publicisInChannel('#test', 'test'),
            "Test user should have left the server");
    }

    public function testRetrieveCommonChannels()
    {
        $comchans   = $this->tracker->publicgetCommonChannels('test');
        $this->assertEquals($comchans, array());
    }

    public function testRetrieveCommonChannels2()
    {
        // JOIN/JOIN/PART
        $event = new ErebotEvent($this->connection, 'test', '#test', ErebotEvent::ON_JOIN, NULL);
        $this->tracker->publichandleJoin($event);
        $comchans   = $this->tracker->publicgetCommonChannels('test');
        $this->assertContains('#test', $comchans);

        $event = new ErebotEvent($this->connection, 'test', '#test2', ErebotEvent::ON_JOIN, NULL);
        $this->tracker->publichandleJoin($event);
        $comchans   = $this->tracker->publicgetCommonChannels('test');
        $this->assertContains('#test2', $comchans);

        $event = new ErebotEvent($this->connection, 'test', '#test2', ErebotEvent::ON_JOIN, NULL);
        $this->tracker->publichandlePart($event);
        $comchans   = $this->tracker->publicgetCommonChannels('test');
        $this->assertFalse($this->logicalNot(in_array('#test2', $comchans)));
    }

    public function testRetrieveUsersBasedOnStatus()
    {
        // VOICEs
        for ($i = 0; $i < 3; $i++) {
            $event = new ErebotEvent($this->connection, 'test'.$i, '#test', ErebotEvent::ON_JOIN, NULL);
            $this->tracker->publichandleJoin($event);
        }

        $event = new ErebotEvent($this->connection, 'test2', '#test', ErebotEvent::ON_VOICE, 'test2');
        $this->tracker->publichandleMode($event);

        $users = $this->tracker->publicgetUsersByStatus(ErebotModule_ChanTracker::STATUS_VOICE);
        $this->assertEquals(count($users), 1);
        $this->assertEquals(reset($users), 'test2');

        $event = new ErebotEvent($this->connection, 'test2', '#test', ErebotEvent::ON_DEVOICE, 'test2');
        $this->tracker->publichandleMode($event);

        $users = $this->tracker->publicgetUsersByStatus(ErebotModule_ChanTracker::STATUS_OP);
        $this->assertEquals(count($users), 0);
    }

    public function testRetrieveUsersBasedOnStatus2()
    {
        // OPerators
        for ($i = 0; $i < 3; $i++) {
            $event = new ErebotEvent($this->connection, 'test'.$i, '#test', ErebotEvent::ON_JOIN, NULL);
            $this->tracker->publichandleJoin($event);
        }

        $event = new ErebotEvent($this->connection, 'test2', '#test', ErebotEvent::ON_OP, 'test2');
        $this->tracker->publichandleMode($event);

        $users = $this->tracker->publicgetUsersByStatus(ErebotModule_ChanTracker::STATUS_OP);
        $this->assertEquals(count($users), 1);
        $this->assertEquals(reset($users), 'test2');

        $event = new ErebotEvent($this->connection, 'test2', '#test', ErebotEvent::ON_DEOP, 'test2');
        $this->tracker->publichandleMode($event);

        $users = $this->tracker->publicgetUsersByStatus(ErebotModule_ChanTracker::STATUS_OP);
        $this->assertEquals(count($users), 0);
    }
}
*/

?>
