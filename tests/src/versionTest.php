<?php

include_once('src/ifaces/core.php');
include_once('src/version.php');

class   VersionTest
extends PHPUnit_Framework_TestCase
{
    public function testVersionCoherence()
    {
        $this->assertEquals(iErebot::VERSION, EREBOT_VERSION);

        if (!strncasecmp(PHP_OS, 'WIN', 3))
            $executable = 'php.exe';
        else
            $executable = '/usr/bin/env php';

        $output = exec(
            $executable.' -f '.
            dirname(dirname(dirname(__FILE__))).
            '/src/version.php'
        );
        $this->assertEquals(iErebot::VERSION, $output);
    }
}

?>
