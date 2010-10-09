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
include_once(dirname(__FILE__).'/utils.php');

class   GoFGameFlowTest
extends PHPUnit_Framework_TestCase
{
    public function testPassingSwitchesControl()
    {
        $uno = new GoFStub('Clicky');
        $uno->join('foo');
        $uno->join('bar');
        $uno->join('baz');

        $player = $uno->getCurrentPlayer();
        $this->assertEquals('foo', $player->getPlayer());
        $uno->draw();
        $uno->pass();

        $player = $uno->getCurrentPlayer();
        $this->assertEquals('bar', $player->getPlayer());
        $uno->draw();
        $uno->pass();

        $player = $uno->getCurrentPlayer();
        $this->assertEquals('baz', $player->getPlayer());
        $uno->draw();
        $uno->pass();

        $player = $uno->getCurrentPlayer();
        $this->assertEquals('foo', $player->getPlayer());
    }

    public function testPlayingSwitchesControl()
    {
        $uno = new GoFStub('Clicky');
        $uno->join('foo');
        $uno->join('bar');
        $uno->join('baz');

        $player = $uno->getCurrentPlayer();
        $this->assertEquals('foo', $player->getPlayer());
        $uno->play('wb');

        $player = $uno->getCurrentPlayer();
        $this->assertEquals('bar', $player->getPlayer());
        $uno->play('wb');

        $player = $uno->getCurrentPlayer();
        $this->assertEquals('baz', $player->getPlayer());
        $uno->play('wb');

        $player = $uno->getCurrentPlayer();
        $this->assertEquals('foo', $player->getPlayer());
    }

    public function testCardCounts()
    {
        $uno = new GoFStub2('Clicky');
        $uno->join('foo');
        $uno->join('bar');
        $uno->join('baz');

        $player = $uno->getCurrentPlayer();
        $this->assertEquals(7, $player->getCardsCount());
        $uno->draw();
        $this->assertEquals(8, $player->getCardsCount());
    }
}
*/
?>
