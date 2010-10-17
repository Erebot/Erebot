<?php

include_once('src/config/mainConfig.php');

class   ConfigTest
extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException   EErebotInvalidValue
     */
    public function testLoadConfigFromInvalidSource()
    {
        $config = new ErebotMainConfig('foo', 'bar');
    }

    public function testLoadValidConfigFromFile()
    {
        $file = dirname(dirname(__FILE__)).'/data/valid-config.xml';
        $config = new ErebotMainConfig($file, ErebotMainConfig::LOAD_FROM_FILE);
        unset($config);
    }

    public function testLoadValidConfigFromString()
    {
        $data = '<?xml version="1.0" ?'.'>';
        $data .=<<<CONFIG
<configuration
    xmlns="http://www.erebot.net/xmlns/erebot"
    version="%s"
    language="fr-FR"
    timezone="Europe/Paris">

    <networks>
        <network name="localhost">
            <servers>
                <server url="irc://localhost:6667/" />
            </servers>
        </network>
    </networks>
</configuration>
CONFIG;

        $data = sprintf($data, EREBOT_VERSION);
        $config = new ErebotMainConfig($data, ErebotMainConfig::LOAD_FROM_STRING);
        unset($config);
    }

    /**
     * @expectedException   EErebotInvalidValue
     */
    public function testLoadInvalidConfigFromString()
    {
        $data = '<?xml version="1.0" ?'.'>';
        $data .=<<<CONFIG
<configuration xmlns="http://www.erebot.net/xmlns/erebot"></configuration>
CONFIG;

        $config = new ErebotMainConfig($data, ErebotMainConfig::LOAD_FROM_STRING);
    }
}

