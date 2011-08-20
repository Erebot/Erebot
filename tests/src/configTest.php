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

if (!defined('TESTENV_DIR'))
    define(
        'TESTENV_DIR',
        dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'testenv'
    );
require_once(TESTENV_DIR . DIRECTORY_SEPARATOR . 'bootstrap.php');

class   ConfigTest
extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_translator = new Erebot_I18n('Erebot');
    }

    /**
     * @expectedException   Erebot_InvalidValueException
     * @covers              Erebot_Config_Main
     */
    public function testLoadConfigFromInvalidSource()
    {
        $config = new Erebot_Config_Main('foo', 'bar', $this->_translator);
    }

    /**
     * @covers Erebot_Config_Main
     */
    public function testLoadValidConfigFromFile()
    {
        $file = dirname(dirname(__FILE__)).'/data/valid-config.xml';
        $config = new Erebot_Config_Main(
            $file,
            Erebot_Config_Main::LOAD_FROM_FILE,
            $this->_translator
        );
        unset($config);
    }

    /**
     * @covers Erebot_Config_Main
     */
    public function testLoadValidConfigFromString()
    {
        $data = '<?xml version="1.0"?'.'>';
        $data .=<<<CONFIG
<configuration
    xmlns="http://www.erebot.net/xmlns/erebot"
    version="%s"
    timezone="Europe/Paris"
>
    <networks>
        <network name="localhost">
            <servers>
                <server url="irc://localhost:6667/"/>
            </servers>
        </network>
    </networks>
</configuration>
CONFIG;

        $data = sprintf($data, EREBOT_VERSION);
        $config = new Erebot_Config_Main(
            $data,
            Erebot_Config_Main::LOAD_FROM_STRING,
            $this->_translator
        );
        unset($config);
    }

    /**
     * @expectedException   Erebot_InvalidValueException
     * @covers              Erebot_Config_Main
     */
    public function testLoadInvalidConfigFromString()
    {
        $data = '<?xml version="1.0" ?'.'>';
        $data .=<<<CONFIG
<configuration xmlns="http://www.erebot.net/xmlns/erebot"></configuration>
CONFIG;

        $config = new Erebot_Config_Main(
            $data,
            Erebot_Config_Main::LOAD_FROM_STRING,
            $this->_translator
        );
    }
}

