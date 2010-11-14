<?php

include_once('Erebot/XglobStream.php');

class   XglobStreamTest
extends PHPUnit_Framework_TestCase
{
    public function testXglobStream()
    {
        $expected = array(
            '<xglob:wrapping xmlns:xglob="http://www.erebot.net/xmlns/xglob"><?xml version="1.0" ?>',
            '<!-- kate: tab-width: 4 -->',
            '<configuration',
            '    xmlns="http://www.erebot.net/xmlns/erebot"',
            '    version="0.3.2-dev1"',
            '    language="fr-FR"',
            '    timezone="Europe/Paris">',
            '    <networks>',
            '        <network name="localhost">',
            '            <servers>',
            '                <server url="irc://localhost:6667/" />',
            '            </servers>',
            '        </network>',
            '    </networks>',
            '</configuration>',
            '</xglob:wrapping>',
        );

        $lines = file(
            'xglob://'.dirname(__FILE__).'/../data/valid-*.xml',
            FILE_IGNORE_NEW_LINES |
            FILE_SKIP_EMPTY_LINES |
            FILE_USE_INCLUDE_PATH
        );
        $this->assertEquals($expected, $lines);
    }
}
