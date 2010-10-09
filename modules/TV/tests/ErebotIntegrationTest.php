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

include_once(dirname(dirname(dirname(dirname(__FILE__)))).'/src/utils.php');
ErebotUtils::incl('../../../tests/connectionStub.php');
ErebotUtils::incl('../../../tests/configStub.php');

class TestTvRetriever
{
    protected $ID_mappings = array('foo' => 42, 'bar' => 69);
    static protected $instance;

    public static function getInstance()
    {
        if (self::$instance === NULL) {
            $c = __CLASS__;
            self::$instance = new $c();
        }

        return self::$instance;
    }

    public function getSupportedChannels()
    {
        return array_keys($this->ID_mappings);
    }

    public function getIdFromChannel($channel)
    {
        $channel = strtolower(trim($channel));
        if (!isset($this->ID_mappings[$channel]))
            return NULL;
        return $this->ID_mappings[$channel];
    }

    public function getChannelsData($timestamp, $ids)
    {
        if (!is_array($ids))
            $ids = array($ids);

        return array(
            'foo' => array(
                'Date_Debut' => "2010-09-02 17:23:00",
                'Date_Fin' => "2010-09-02 17:42:00",
                'Titre' => 'foo',
            ),
            'bar' => array(
                'Date_Debut' => "2010-09-03 17:23:00",
                'Date_Fin' => "2010-09-03 17:42:00",
                'Titre' => 'bar',
            ),
        );
    }
}

class   ErebotIntegrationTest
extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $xml        = new SimpleXMLElement('<stub/>');
        $mainCfg    = new ErebotStubbedMainConfig(NULL, NULL);
        $this->bot  = new ErebotStubbedCore($mainCfg);
    }

    public function testMissingDefaultGroup()
    {
        $config = ErebotStubbedServerConfig::create(array(
                    'TV' => '
<module xmlns="http://www.erebot.net/xmlns/erebot" name="TV">
    <param name="trigger" value="tv-schedule"/>
    <param name="retriever_class" value="TestTvRetriever"/>
</module>
',
                    'TriggerRegistry' => NULL,
                    'Helper' => NULL));
        $connection = new ErebotStubbedConnection($this->bot, $config);
        $module = $connection->getModule('TV', ErebotConnection::MODULE_BY_NAME);

        $event = new ErebotEventTextPrivate($connection, 'test', '!tv-schedule');
        $module->handleTv($event);
        $output = $connection->getSendQueue();
        $this->assertEquals(1, count($output));
        $this->assertEquals(
            "PRIVMSG test :No channel given and no default.",
            $output[0]
        );
    }

    public function testUsingDefaultGroupWithChannelOverride()
    {
        $config = ErebotStubbedServerConfig::create(array(
                    'TV' => '
<module xmlns="http://www.erebot.net/xmlns/erebot" name="TV">
    <param name="default_group" value="foo"/>
    <param name="group_foo" value="foo,bar"/>
    <param name="retriever_class" value="TestTvRetriever"/>
</module>
',
                    'TriggerRegistry' => NULL,
                    'Helper' => NULL));
        $connection = new ErebotStubbedConnection($this->bot, $config);
        $module = $connection->getModule('TV', ErebotConnection::MODULE_BY_NAME);

        $event = new ErebotEventTextPrivate($connection, 'test', '!tv 23h42 foo');
        $module->handleTv($event);
        $output = $connection->getSendQueue();
        $translator = new ErebotStubbedI18n();

        $fmt = new ErebotStyling('/PRIVMSG test :TV programs for <u><var name="date"/>'.
                        '</u>: <for from="programs" key="channel" item="'.
                        'timetable" separator=" - "><b><var name="channel"'.
                        '/></b>: <var name="timetable"/></for>/', $translator);
        $fmt->assign('date', '.*?');
        $fmt->assign('programs', array(
            'foo' => 'foo \\(17:23 - 17:42\\)',
            'bar' => 'bar \\(17:23 - 17:42\\)',
        ));
        $this->assertEquals(1, count($output));
        $this->assertRegExp($fmt->render(), $output[0]);
    }
}

?>
