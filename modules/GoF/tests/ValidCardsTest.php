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

/*
include_once(dirname(dirname(__FILE__)).'/src/game.php');

class   GoFValidCardsTest
extends PHPUnit_Framework_TestCase
{
    public function testRejectInvalidCards()
    {
        $cards = array(
            0,
            'r',
            'g0',
            'y0',
            'r0',
            '0',
            'rg',
            'x0',
            'xd',
            'xy',
            'gd',
            'yd',
            'rp',
            'm0',
            'm2',
            'm3',
            'm4',
            'm5',
            'm6',
            'm7',
            'm8',
            'm9',
            'mp',
            'md',
        );
        foreach ($cards as $card) {
            $result = GoF::extractCard($card);
            $this->assertSame(NULL, $result, $card);
        }
    }

    public function testAcceptValidCards()
    {
        $cards = array(
            'r1',   // Red serie
            'r10',
            'rd',
            'g1',   // Green serie
            'g10',
            'gp',
            'y1',   // Yellow serie
            'y10',
            'yp',
            'm1',    // Multicolor serie
        );
        foreach ($cards as $card) {
            $result = GoF::extractCard($card);
            $this->assertNotSame(NULL, $result, $card);
        }
    }
}
*/

?>
