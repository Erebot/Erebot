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

class   IrcParserTest
extends Erebot_TestEnv_TestCase
{
    public function setUp()
    {
        $sxml = new SimpleXMLElement('<foo/>');
        $this->_mainConfig = $this->getMock('Erebot_Interface_Config_Main', array(), array(), '', FALSE, FALSE);
        $this->_networkConfig = $this->getMock('Erebot_Interface_Config_Network', array(), array($this->_mainConfig, $sxml), '', FALSE, FALSE);
        $this->_serverConfig = $this->getMock('Erebot_Interface_Config_Server', array(), array($this->_networkConfig, $sxml), '', FALSE, FALSE);
        $this->_bot = $this->getMock('Erebot_Testenv_Stub_Core', array(), array($this->_mainConfig), '', FALSE, FALSE);
        $this->_connection = $this->getMock('Erebot_Interface_IrcConnection', array(), array($this->_bot, $this->_serverConfig), '', FALSE, FALSE);
        $this->_event = $this->getMock('Erebot_Interface_Event_Base_Generic', array(), array(), '', FALSE, FALSE);
        $this->_parser = $this->getMock('Erebot_IrcParser', array('makeEvent'), array($this->_connection));
    }

    /**
     * @cover Erebot_IrcParser::_handleINVITE
     */
    public function testINVITE()
    {
        $this->_parser
            ->expects($this->once())
            ->method('makeEvent')
            ->with(
                '!Invite',
                '#Dust',
                'Angel!wings@irc.org',
                'Wiz'
            )
            ->will($this->returnValue($this->_event));

        $this->_parser->parseLine(':Angel!wings@irc.org INVITE Wiz #Dust');
    }

    /**
     * @cover Erebot_IrcParser::_handleJOIN
     */
    public function testJOIN()
    {
        $this->_parser
            ->expects($this->once())
            ->method('makeEvent')
            ->with(
                '!Join',
                '#Twilight_zone',
                'WiZ!jto@tolsun.oulu.fi'
            )
            ->will($this->returnValue($this->_event));

        $this->_parser->parseLine(
            ':WiZ!jto@tolsun.oulu.fi JOIN #Twilight_zone'
        );
    }

    /**
     * @cover Erebot_IrcParser::_handleKICK
     */
    public function testKICK()
    {
        $this->_parser
            ->expects($this->once())
            ->method('makeEvent')
            ->with(
                '!Kick',
                '#Finnish',
                'WiZ!jto@tolsun.oulu.fi',
                'John',
                'No reason'
            )
            ->will($this->returnValue($this->_event));

        $this->_parser->parseLine(
            ':WiZ!jto@tolsun.oulu.fi KICK #Finnish John :No reason'
        );
    }

    /**
     * @cover Erebot_IrcParser::_handleMODE
     */
    public function testUserMODE()
    {
        $this->_parser
            ->expects($this->once())
            ->method('makeEvent')
            ->with(
                '!UserMode',
                'WiZ!jto@tolsun.oulu.fi',
                'WiZ',
                '+i'
            )
            ->will($this->returnValue($this->_event));

        $this->_connection
            ->expects($this->once())
            ->method('isChannel')
            ->with('WiZ')
            ->will($this->returnValue(FALSE));

        $this->_parser->parseLine(
            ':WiZ!jto@tolsun.oulu.fi MODE WiZ +i'
        );
    }

    /**
     * @cover Erebot_IrcParser::_handleMODE
     */
    public function testCapturedChannelMODE()
    {
        $this->_parser
            ->expects($this->once())
            ->method('makeEvent')
            ->with(
                '!RawMode',
                '#Finnish',
                'WiZ!jto@tolsun.oulu.fi',
                new Erebot_IrcTextWrapper('+imI *!*@*.fi')
            )
            ->will($this->returnValue($this->_event));

        $this->_connection
            ->expects($this->once())
            ->method('isChannel')
            ->with('#Finnish')
            ->will($this->returnValue(TRUE));

        // Emulate some event handler capturing the event.
        $this->_event
            ->expects($this->once())
            ->method('preventDefault')
            ->with(TRUE)
            ->will($this->returnValue(TRUE));

        $this->_parser->parseLine(
            ':WiZ!jto@tolsun.oulu.fi MODE #Finnish +imI *!*@*.fi'
        );
    }

    /**
     * @cover Erebot_IrcParser::_handleNICK
     */
    public function testNICK()
    {
        $this->_parser
            ->expects($this->exactly(2))
            ->method('makeEvent')
            ->with(
                '!Nick',
                'WiZ!jto@tolsun.oulu.fi',
                'Kilroy'
            )
            ->will($this->returnValue($this->_event));

        $this->_parser->parseLine(':WiZ!jto@tolsun.oulu.fi NICK Kilroy');
        // Some versions of Bahamut send NICK messages
        // with ':' before the nick (which should work).
        $this->_parser->parseLine(':WiZ!jto@tolsun.oulu.fi NICK :Kilroy');
    }

    public function noticeProvider()
    {
        $expectations = array(
            // First up is a NOTICE to some user.
            ':Angel!wings@irc.org NOTICE Wiz :Are you receiving this message ?'
            => array(
                '!PrivateNotice',
                'Angel!wings@irc.org',
                'Are you receiving this message ?'
            ),
            // Then, a NOTICE to some channel.
            ':Angel!wings@irc.org NOTICE #Finnish :Hello world :)'
            => array(
                '!ChanNotice',
                '#Finnish',
                'Angel!wings@irc.org',
                'Hello world :)'
            ),
            // Then a CTCP reply to some user.
            ":Angel!wings@irc.org NOTICE Wiz :\001PING 1337\001"
            => array(
                '!PrivateCtcpReply',
                'Angel!wings@irc.org',
                'PING',
                '1337'
            ),
            // And finally, a CTCP reply to some channel.
            ":Angel!wings@irc.org NOTICE #Finnish :\001HELLO World :)\001"
            => array(
                '!ChanCtcpReply',
                '#Finnish',
                'Angel!wings@irc.org',
                'HELLO',
                'World :)'
            ),
        );

        $res = array();
        foreach (array_keys($expectations) as $index => $line) {
            $args = $expectations[$line];
            $res[] = array($line, $args, !($index % 2) ? 'Wiz' : '#Finnish');
        }
        return $res;
    }

    /**
     * @cover Erebot_IrcParser::_handleNOTICE
     * @dataProvider noticeProvider
     */
    public function testNOTICE($line, $args, $target)
    {
        $this->_connection
            ->expects($this->once())
            ->method('isChannel')
            ->with($target)
            ->will($this->returnValue($target == '#Finnish' ? TRUE : FALSE));

        $matcher = $this->_parser
            ->expects($this->once())
            ->method('makeEvent')
            ->will($this->returnValue($this->_event));
        call_user_func_array(array($matcher, 'with'), $args);

        $this->_parser->parseLine($line);
    }

    /**
     * @cover Erebot_IrcParser::_handlePART
     */
    public function testPART()
    {
        $this->_parser
            ->expects($this->once())
            ->method('makeEvent')
            ->with(
                '!Part',
                '#playzone',
                'WiZ!jto@tolsun.oulu.fi',
                'I lost'
            )
            ->will($this->returnValue($this->_event));

        $this->_parser->parseLine(
            ':WiZ!jto@tolsun.oulu.fi PART #playzone :I lost'
        );
    }

    /**
     * @cover Erebot_IrcParser::_handlePING
     */
    public function testPING()
    {
        $this->_parser
            ->expects($this->once())
            ->method('makeEvent')
            ->with(
                '!Ping',
                'irc.funet.fi'
            )
            ->will($this->returnValue($this->_event));

        $this->_parser->parseLine('PING :irc.funet.fi');
    }

    /**
     * @cover Erebot_IrcParser::_handlePONG
     */
    public function testPONG()
    {
        $this->_parser
            ->expects($this->once())
            ->method('makeEvent')
            ->with(
                '!Pong',
                'not.configured',
                '1340835762.1589'
            )
            ->will($this->returnValue($this->_event));

        $this->_parser->parseLine(
            ':not.configured PONG not.configured :1340835762.1589'
        );
    }

    public function privmsgProvider()
    {
        $expectations = array(
            // First up is a PRIVMSG to some user.
            ':Angel!wings@irc.org PRIVMSG Wiz :Are you receiving this message ?'
            => array(
                '!PrivateText',
                'Angel!wings@irc.org',
                'Are you receiving this message ?'
            ),
            // A PRIVMSG to some channel.
            ':Angel!wings@irc.org PRIVMSG #Finnish :Hello world :)'
            => array(
                '!ChanText',
                '#Finnish',
                'Angel!wings@irc.org',
                'Hello world :)'
            ),
            // A CTCP query to some user.
            ":Angel!wings@irc.org PRIVMSG Wiz :\001PING 1337\001"
            => array(
                '!PrivateCtcp',
                'Angel!wings@irc.org',
                'PING',
                '1337'
            ),
            // A CTCP query to some channel.
            ":Angel!wings@irc.org PRIVMSG #Finnish :\001HELLO World :)\001"
            => array(
                '!ChanCtcp',
                '#Finnish',
                'Angel!wings@irc.org',
                'HELLO',
                'World :)'
            ),
            // An ACTION in a query with some user.
            ":Angel!wings@irc.org PRIVMSG Wiz :\001ACTION dances\001"
            => array(
                '!PrivateAction',
                'Angel!wings@irc.org',
                'dances'
            ),
            // And finally, an ACTION on some channel.
            ":Angel!wings@irc.org PRIVMSG #Finnish :\001ACTION dances\001"
            => array(
                '!ChanAction',
                '#Finnish',
                'Angel!wings@irc.org',
                'dances'
            ),
        );

        $res = array();
        foreach (array_keys($expectations) as $index => $line) {
            $args = $expectations[$line];
            $res[] = array($line, $args, !($index % 2) ? 'Wiz' : '#Finnish');
        }
        return $res;
    }

    /**
     * @cover Erebot_IrcParser::_handlePRIVMSG
     * @dataProvider privmsgProvider
     */
    public function testPRIVMSG($line, $args, $target)
    {
        $this->_connection
            ->expects($this->once())
            ->method('isChannel')
            ->with($target)
            ->will($this->returnValue($target == '#Finnish' ? TRUE : FALSE));

        $matcher = $this->_parser
            ->expects($this->once())
            ->method('makeEvent')
            ->will($this->returnValue($this->_event));
        call_user_func_array(array($matcher, 'with'), $args);

        $this->_parser->parseLine($line);
    }

    /**
     * @cover Erebot_IrcParser::_handleQUIT
     */
    public function testQUIT()
    {
        $this->_parser
            ->expects($this->once())
            ->method('makeEvent')
            ->with(
                '!Quit',
                'syrk!kalt@millennium.stealth.net',
                'Gone to have lunch'
            )
            ->will($this->returnValue($this->_event));

        $this->_parser->parseLine(
            ':syrk!kalt@millennium.stealth.net QUIT :Gone to have lunch'
        );
    }

    /**
     * @cover Erebot_IrcParser::_handleTOPIC
     */
    public function testTOPIC()
    {
        $this->_parser
            ->expects($this->once())
            ->method('makeEvent')
            ->with(
                '!Topic',
                '#test',
                'WiZ!jto@tolsun.oulu.fi',
                'New topic'
            )
            ->will($this->returnValue($this->_event));

        $this->_parser->parseLine(
            ':WiZ!jto@tolsun.oulu.fi TOPIC #test :New topic'
        );
    }

    /**
     * @cover Erebot_IrcParser::_handle255
     */
    public function testNumeric255()
    {
        $this->_parser
            ->expects($this->exactly(3))
            ->method('makeEvent')
            ->will($this->returnValue($this->_event));
        $this->_parser
            ->expects($this->at(0))
            ->method('makeEvent')
            ->with('!Connect');
        $this->_parser
            ->expects($this->at(1))
            ->method('makeEvent')
            ->with(
                '!Numeric',
                255,
                'unconfigured.name',
                'WiZ',
                new Erebot_IrcTextWrapper(':I have 1195 clients and 2 servers')
            );
        $this->_parser
            ->expects($this->at(2))
            ->method('makeEvent')
            ->with(
                '!Numeric',
                255,
                'unconfigured.name',
                'WiZ',
                new Erebot_IrcTextWrapper(':I have 1195 clients and 3 servers')
            );

        $this->_connection
            ->expects($this->exactly(2))
            ->method('isConnected')
            ->will($this->onConsecutiveCalls(FALSE, TRUE));

        // The first time, both a Connect event and
        // a numeric event are emitted (in this order).
        $this->_parser->parseLine(
            ':unconfigured.name 255 WiZ :I have 1195 clients and 2 servers'
        );
        // The second time, only a numeric event is emitted.
        $this->_parser->parseLine(
            ':unconfigured.name 255 WiZ :I have 1195 clients and 3 servers'
        );
    }

    public function watchListProvider()
    {
        $expectations = array(
            ':unconfigured.name 600 Wiz mspai goddess example.com '.
            '911248011 :logged online'
            => array(
                array(
                    '!Notify',
                    'mspai',
                    'goddess',
                    'example.com',
                    new DateTime('@911248011'),
                    'logged online'
                ),
                array(
                    '!Numeric',
                    600,
                    'unconfigured.name',
                    'Wiz',
                    new Erebot_IrcTextWrapper(
                        'mspai goddess example.com 911248011 :logged online'
                    )
                ),
            ),

            ':unconfigured.name 601 Wiz mspai goddess example.com '.
            '911248011 :logged offline'
            => array(
                array(
                    '!UnNotify',
                    'mspai',
                    'goddess',
                    'example.com',
                    new DateTime('@911248011'),
                    'logged offline'
                ),
                array(
                    '!Numeric',
                    601,
                    'unconfigured.name',
                    'Wiz',
                    new Erebot_IrcTextWrapper(
                        'mspai goddess example.com 911248011 :logged offline'
                    )
                ),
            ),

            ':unconfigured.name 604 Wiz SB tikiman '.
            'example.com 911076465 :is online'
            => array(
                array(
                    '!Notify',
                    'SB',
                    'tikiman',
                    'example.com',
                    new DateTime('@911076465'),
                    'is online'
                ),
                array(
                    '!Numeric',
                    604,
                    'unconfigured.name',
                    'Wiz',
                    new Erebot_IrcTextWrapper(
                        'SB tikiman example.com 911076465 :is online'
                    )
                ),
            ),

            ':unconfigured.name 605 Wiz hotblack * * 0 :is offline'
            => array(
                array(
                    '!UnNotify',
                    'hotblack',
                    NULL,
                    NULL,
                    new DateTime('@0'),
                    'is offline'
                ),
                array(
                    '!Numeric',
                    605,
                    'unconfigured.name',
                    'Wiz',
                    new Erebot_IrcTextWrapper('hotblack * * 0 :is offline')
                ),
            ),
        );
        $res = array();
        foreach ($expectations as $line => $args) {
            $res[] = array($line, $args);
        }
        return $res;
    }

    /**
     * @cover Erebot_IrcParser::_handle601
     * @cover Erebot_IrcParser::_handle602
     * @cover Erebot_IrcParser::_handle604
     * @cover Erebot_IrcParser::_handle605
     * @cover Erebot_IrcParser::_watchList
     * @dataProvider watchListProvider
     */
    public function testWatchList($line, $args)
    {
        $this->_parser
            ->expects($this->exactly(2))
            ->method('makeEvent')
            ->will($this->returnValue($this->_event));
        foreach (array_values($args) as $index => $arg) {
            $matcher = $this->_parser
                ->expects($this->at($index))
                ->method('makeEvent');
            call_user_func_array(array($matcher, 'with'), $arg);
        }
        $this->_parser->parseLine($line);
    }
}

