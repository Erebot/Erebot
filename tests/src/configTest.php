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
        $data =<<<CONFIG
<?xml version="1.0" ?>
<configuration
    xmlns="http://www.erebot.net/xmlns/erebot"
    version="0.3.0"
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

        $config = new ErebotMainConfig($data, ErebotMainConfig::LOAD_FROM_STRING);
        unset($config);
    }

    /**
     * @expectedException   EErebotInvalidValue
     */
    public function testLoadInvalidConfigFromString()
    {
        $data =<<<CONFIG
<?xml version="1.0" ?>
<configuration xmlns="http://www.erebot.net/xmlns/erebot"></configuration>
CONFIG;

        $config = new ErebotMainConfig($data, ErebotMainConfig::LOAD_FROM_STRING);
    }
}

?>
