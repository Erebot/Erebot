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

include_once('../../tests/connectionStub.php');

class   NickTrackerGeneralTest
extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $bot                =   new ErebotStubbedCore();
        $config             =   ErebotStubbedServerConfig::create(array(
                                    'ServerCapabilities' => NULL,
                                    'NickTracker' => NULL,
                                ));
        $this->connection   =   new ErebotStubbedConnection($bot, $config);

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
        $event = new ErebotEventNick($this->connection, 'foo', 'bar');
        $this->connection->dispatchEvent($event);

        $nick = $this->module->getNick($this->token);
        $this->assertSame('bar', $nick);
    }
}

class   NickTrackerQuitTest
extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $bot                =   new ErebotStubbedCore();
        $config             =   ErebotStubbedServerConfig::create(array(
                                    'ServerCapabilities' => NULL,
                                    'NickTracker' => NULL,
                                ));
        $this->connection   =   new ErebotStubbedConnection($bot, $config);

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
        $event = new ErebotEventQuit($this->connection, 'foo', '');
        $this->connection->dispatchEvent($event);

        $nick = $this->module->getNick($this->token);
    }
}

?>
