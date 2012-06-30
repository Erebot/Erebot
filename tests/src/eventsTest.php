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
        $this->_mainConfig = $this->getMock('Erebot_Interface_Config_Main', array(), array(), '', FALSE, FALSE, FALSE);
        $this->_networkConfig = $this->getMock('Erebot_Interface_Config_Network', array(), array($this->_mainConfig, $sxml), '', FALSE, FALSE, FALSE);
        $this->_serverConfig = $this->getMock('Erebot_Interface_Config_Server', array(), array($this->_networkConfig, $sxml), '', FALSE, FALSE, FALSE);
        $this->_bot = $this->getMock('Erebot_Testenv_Stub_Core', array(), array($this->_mainConfig), '', FALSE, FALSE, FALSE);
        $this->_connection = $this->getMock('Erebot_Interface_Connection', array(), array($this->_bot, $this->_serverConfig), '', FALSE, FALSE, FALSE);
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
     * @covers Erebot_Event_Ping
     */
    public function testPing()
    {
        $event = new Erebot_Event_Ping($this->_connection, "foo");
        $this->assertEquals("foo", (string) $event->getText());
    }

#    /**
#     * @covers Erebot_Event_Connect
#     */
#    public function testConnectAndRaw255()
#    {
#        $this->_connection->handleMessage(
#            ':localhost 255 :I have 42 clients and 23 servers'
#        );
#        $dispatched = $this->_connection->resetDispatched();
#        $this->assertSame(2, count($dispatched));
#        $this->assertTrue($dispatched[0] instanceof Erebot_Event_Connect);
#        $this->assertTrue($dispatched[1] instanceof Erebot_Event_Raw);
#        $this->assertEquals(
#            Erebot_Interface_RawProfile_RFC1459::RPL_LUSERME,
#            $dispatched[1]->getRaw()
#        );
#    }

#    /**
#     * @covers Erebot_Event_Notify
#     * @covers Erebot_Event_UnNotify
#     */
#    public function testWatchUnwatch()
#    {
#        $this->_connection->handleMessage(
#            ':localhost 604 Erebot foo bar baz 42 :is now online'
#        );
#        $dispatched = $this->_connection->resetDispatched();
#        $this->assertSame(2, count($dispatched));
#        $this->assertTrue($dispatched[0] instanceof Erebot_Event_Notify);
#        $this->assertEquals(
#            "foo!bar@baz",
#            $dispatched[0]->getSource()->getMask(
#                Erebot_Interface_Identity::CANON_IPV4
#            )
#        );
#        $ts = $dispatched[0]->getTimestamp();
#        $this->assertEquals(42, $ts->format('U'));
#        $this->assertEquals(
#            "is now online",
#            (string) $dispatched[0]->getText()
#        );
#        $this->assertTrue($dispatched[1] instanceof Erebot_Event_Raw);
#        $this->assertEquals(
#            Erebot_Interface_RawProfile_WATCH::RPL_NOWON,
#            $dispatched[1]->getRaw()
#        );

#        $this->_connection->handleMessage(
#            ':localhost 605 Erebot foo bar baz 42 :is now offline'
#        );
#        $dispatched = $this->_connection->resetDispatched();
#        $this->assertSame(2, count($dispatched));
#        $this->assertTrue($dispatched[0] instanceof Erebot_Event_UnNotify);
#        $this->assertEquals(
#            "foo!bar@baz",
#            $dispatched[0]->getSource()->getMask(
#                Erebot_Interface_Identity::CANON_IPV4
#            )
#        );
#        $ts = $dispatched[0]->getTimestamp();
#        $this->assertEquals(42, $ts->format('U'));
#        $this->assertEquals(
#            "is now offline",
#            (string) $dispatched[0]->getText()
#        );
#        $this->assertTrue($dispatched[1] instanceof Erebot_Event_Raw);
#        $this->assertEquals(
#            Erebot_Interface_RawProfile_WATCH::RPL_NOWOFF,
#            $dispatched[1]->getRaw()
#        );
#    }
}

