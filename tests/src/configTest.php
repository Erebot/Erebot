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

class   ConfigTest
extends Erebot_TestEnv_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->_translator = new \Erebot\Intl('Erebot\\Core');
    }

    /**
     * @expectedException   \Erebot\InvalidValueException
     * @covers              \Erebot\Config\Main
     */
    public function testLoadConfigFromInvalidSource()
    {
        $config = new \Erebot\Config\Main('foo', 'bar', $this->_translator);
    }

    /**
     * @covers \Erebot\Config\Main
     */
    public function testLoadValidConfigFromFile()
    {
        $file = dirname(__DIR__).
                DIRECTORY_SEPARATOR.'data'.
                DIRECTORY_SEPARATOR.'valid-config.xml';
        $config = new \Erebot\Config\Main(
            $file,
            \Erebot\Config\Main::LOAD_FROM_FILE,
            $this->_translator
        );
        unset($config);
    }

    /**
     * @covers \Erebot\Config\Main
     */
    public function testLoadValidConfigFromURIObject()
    {
        $file = \Erebot\URI::fromAbsPath(
            dirname(__DIR__).
            DIRECTORY_SEPARATOR.'data'.
            DIRECTORY_SEPARATOR.'valid-config.xml'
        );
        $config = new \Erebot\Config\Main(
            $file,
            \Erebot\Config\Main::LOAD_FROM_FILE,
            $this->_translator
        );
        unset($config);
    }

    /**
     * @covers \Erebot\Config\Main
     */
    public function testLoadValidConfigFromString()
    {
        $data = '<?xml version="1.0"?'.'>';
        $data .=<<<CONFIG
<configuration
    xmlns="http://www.erebot.net/xmlns/erebot"
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

        $config = new \Erebot\Config\Main(
            $data,
            \Erebot\Config\Main::LOAD_FROM_STRING,
            $this->_translator
        );
        unset($config);
    }

    /**
     * @expectedException   \Erebot\InvalidValueException
     * @covers              \Erebot\Config\Main
     */
    public function testLoadInvalidConfigFromString()
    {
        $path   = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;

        if (DIRECTORY_SEPARATOR == "/")
            $xmlPath = $path;
        else {
            // Under Windows, libxml2 adds a "file:///" prefix
            // and puts the volume's letter in lowercase.
            $pos = strpos($path, ':');
            $xmlPath = "file:///". strtolower(substr($path, 0, $pos)) .
                str_replace(DIRECTORY_SEPARATOR, '/', substr($path, $pos));
        }

        $this->setExpectedLogs(<<<LOGS
ERROR:Array
(
    [0] => LibXMLError Object
        (
            [level] => 2
            [code] => 24
            [column] => 0
            [message] => Element configuration failed to validate attributes
            [file] => $xmlPath
            [line] => 1
        )
)
LOGS
        );

        $data = '<?xml version="1.0" ?'.'>';
        $data .=<<<CONFIG
<configuration xmlns="http://www.erebot.net/xmlns/erebot"></configuration>
CONFIG;

        $config = new \Erebot\Config\Main(
            $data,
            \Erebot\Config\Main::LOAD_FROM_STRING,
            $this->_translator
        );
    }
}

