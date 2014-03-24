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

class   XglobStreamTest
extends Erebot_TestEnv_TestCase
{
    /**
     * @covers \Erebot\XGlobStream<extended>
     */
    public function testXglobStream()
    {
        \Erebot\Patches::patch();
        $expected = array(
            '<xglob:wrapping xmlns:xglob="http://www.erebot.net/xmlns/xglob"><?xml version="1.0"?>',
            '<!-- kate: tab-width: 4 -->',
            '<configuration xmlns="http://www.erebot.net/xmlns/erebot" '.
                'timezone="Europe/Paris">',
            '    <networks>',
            '        <network name="localhost">',
            '            <servers>',
            '                <server url="irc://localhost:6667/"/>',
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
