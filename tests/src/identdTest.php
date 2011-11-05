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

class   Erebot_Identd_Worker_TestStub
extends Erebot_Identd_Worker
{
    private $_expose;

    public function __construct(Erebot_Interface_Core $bot, $socket = NULL)
    {
        parent::__construct($bot, $socket);
        $this->_expose = TRUE;
    }

    public function getConfig($chan)
    {
        return $this;
    }

    public function setExposure($expose)
    {
        $this->_expose = !!$expose;
    }

    public function parseString()
    {
        if (!$this->_expose)
            throw new Erebot_Exception('Hidden');
        return "Foobar";
    }
}

abstract class AbstractIdentdTest
extends PHPUnit_Framework_TestCase
{
    protected $_mainConfig;
    protected $_bot;
    protected $_worker;
    protected $_connection;
    protected $_sockets;

    public function setUp()
    {
        parent::setUp();
        $this->_mainConfig = $this->getMock('Erebot_Interface_Config_Main', array(), array(), '', FALSE, FALSE);
        $this->_bot = $this->getMock('ErebotTestCore', array(), array($this->_mainConfig), '', FALSE, FALSE);

        // Create two connected stream-oriented TCP sockets.
        // We can't use stream_socket_pair() as it doesn't exist on Windows
        // until PHP 5.3.0 and, at least on my computer, it doesn't work with
        // the PF_INET protocol family.
        $server = stream_socket_server("tcp://127.0.0.1:0", $errno, $errstr);
        $client = stream_socket_client(
            "tcp://127.0.0.1".
                strrchr(stream_socket_get_name($server, FALSE), ":"),
            $errno,
            $errstr,
            10,
            STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT
        );
        $accepted = stream_socket_accept($server);
        fclose($server);
        $this->assertTrue(stream_set_blocking($client, 1));

        // Finish the rest of the preparations
        // (by creating connection objets).
        $this->_sockets = array($accepted, $client);
        $this->_worker = new Erebot_Identd_Worker($this->_bot, $this->_sockets[0]);
        $this->_connection = new Erebot_Identd_Worker_TestStub($this->_bot, $this->_sockets[1]);
        $this->_bot
            ->expects($this->any())
            ->method('getConnections')
            ->will($this->returnValue(array($this->_connection)));
    }

    public function tearDown()
    {
        fclose($this->_sockets[0]);
        fclose($this->_sockets[1]);
        parent::tearDown();
    }

    public function send($msg)
    {
        // Uncomment if you want to debug the tests.
#        echo "Sent: '".trim($msg)."'\r\n";
        fputs($this->_sockets[1], $msg);
        $this->_worker->processIncomingData();
        $this->_worker->processQueuedData();

        $w = $e = NULL;
        $r = array($this->_sockets[1]);
        // The call gets silenced to work around
        // <https://bugs.php.net/bug.php?id=54563>,
        // <https://bugs.php.net/bug.php?id=49948>.
        $nb = @stream_select($r, $w, $e, 1);
        if ($nb <= 0)
            return NULL;
        return rtrim(fgets($this->_sockets[1], 1024));
    }
}

class   IdentdInvalidPortsTest
extends AbstractIdentdTest
{
    public function invalidPorts()
    {
        return array(
            array('0xF04D', 42),
            array(42, '0xF04D'),
            array(0, 12345),
            array(12345, 0),
            array(-1, 12345),
            array(12345, -1),
            array(65536, 12345),
            array(12345, 65536),
        );
    }

    /**
     * @dataProvider invalidPorts
     */
    public function testInvalidPort($cport, $sport)
    {
        $res = $this->send("$cport, $sport\r\n");
        $this->assertEquals("$cport , $sport : ERROR : INVALID-PORT", $res);
    }
}

class   IdentdCommonCasesTest
extends AbstractIdentdTest
{
    public function testNominal()
    {
        $sport  = substr(strrchr(stream_socket_get_name($this->_sockets[1], TRUE), ':'), 1);
        $cport  = substr(strrchr(stream_socket_get_name($this->_sockets[1], FALSE), ':'), 1);
        $res    = $this->send("$cport, $sport\r\n");
        $this->assertEquals("$cport , $sport : USERID : UNIX : Foobar", $res);
    }

    public function testHiddenIdentity()
    {
        $sport  = substr(strrchr(stream_socket_get_name($this->_sockets[1], TRUE), ':'), 1);
        $cport  = substr(strrchr(stream_socket_get_name($this->_sockets[1], FALSE), ':'), 1);
        $this->_connection->setExposure(FALSE);
        $res    = $this->send("$cport, $sport\r\n");
        $this->assertEquals("$cport , $sport : ERROR : HIDDEN-USER", $res);
    }

    public function testUserNotFound()
    {
        $sport  = substr(strrchr(stream_socket_get_name($this->_sockets[1], TRUE), ':'), 1);
        $cport  = substr(strrchr(stream_socket_get_name($this->_sockets[1], FALSE), ':'), 1);
        $cport++;   // Change the client port so that no connection
                    // matches the given parameters anymore.
        $res    = $this->send("$cport, $sport\r\n");
        $this->assertEquals("$cport , $sport : ERROR : NO-USER", $res);
    }

    public function testInvalidQuery()
    {
        $res    = $this->send("foobar\r\n");
        $this->assertEquals(NULL, $res);
    }
}

