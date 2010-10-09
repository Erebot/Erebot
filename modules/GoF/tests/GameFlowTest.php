<?php
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
