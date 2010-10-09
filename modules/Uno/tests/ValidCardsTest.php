<?php

include_once(dirname(dirname(__FILE__)).'/src/game.php');

class   UnoValidCardsTest
extends PHPUnit_Framework_TestCase
{
    public function testRejectInvalidCards()
    {
        $cards = array(
            0,
            'r',
            '0',
            'rg',
            'x0',
            'xr',
            'xs',
            'x+2',
            'w+5',
            'r+4',
            'rrr',
            'rss',
            'r++2',
            'r+22',
        );
        foreach ($cards as $card) {
            $result = Uno::extractCard($card, NULL);
            $this->assertSame(NULL, $result, $card);
        }
        foreach ($cards as $card) {
            $result = Uno::extractCard($card, TRUE);
            $this->assertSame(NULL, $result, $card);
        }
        foreach ($cards as $card) {
            $result = Uno::extractCard($card, FALSE);
            $this->assertSame(NULL, $result, $card);
        }
    }

    public function testAcceptValidCards()
    {
        $cards = array(
            'r0',   // Red serie
            'r9',
            'rr',
            'rs',
            'r+2',
            'g0',   // Green serie
            'g9',
            'gr',
            'gs',
            'g+2',
            'b0',   // Blue serie
            'b9',
            'br',
            'bs',
            'b+2',
            'y0',   // Yellow serie
            'y9',
            'yr',
            'ys',
            'y+2',
            'w',    // Wild serie
            'w+4',
        );
        foreach ($cards as $card) {
            $result = Uno::extractCard($card, NULL);
            $this->assertNotSame(NULL, $result, $card);
        }
        foreach ($cards as $card) {
            $result = Uno::extractCard($card, FALSE);
            $this->assertNotSame(NULL, $result, $card);
        }
    }

    public function testValidCardsWithoutColors()
    {
        $cards = array(
            'w',
            'w+4',
        );
        foreach ($cards as $card) {
            $result = Uno::extractCard($card, NULL);
            $this->assertNotSame(NULL, $result, $card);
        }
        foreach ($cards as $card) {
            $result = Uno::extractCard($card, FALSE);
            $this->assertNotSame(NULL, $result, $card);
        }
        foreach ($cards as $card) {
            $result = Uno::extractCard($card, TRUE);
            $this->assertSame(NULL, $result, $card);
        }
    }

    public function testValidCardsWithColors()
    {
        $cards = array(
            'wr',
            'w+4r',
        );
        foreach ($cards as $card) {
            $result = Uno::extractCard($card, NULL);
            $this->assertNotSame(NULL, $result, $card);
        }
        foreach ($cards as $card) {
            $result = Uno::extractCard($card, TRUE);
            $this->assertNotSame(NULL, $result, $card);
        }
        foreach ($cards as $card) {
            $result = Uno::extractCard($card, FALSE);
            $this->assertSame(NULL, $result, $card);
        }
    }
}

?>
