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

include_once(dirname(dirname(__FILE__)).'/src/game.php');

class   RouletteTestHelper
extends Roulette
{
    protected $bang_value;

    public function __construct($nb_chambers)
    {
        parent::__construct($nb_chambers);
        $this->bang_value   = parent::getRandom($nb_chambers);
    }

    public function setRandom($randomValue)
    {
        $this->bang_value = $randomValue;
        $this->reset();
    }

    public function getRandom($max)
    {
        return $this->bang_value;
    }
}

class   RouletteTest
extends PHPUnit_Framework_TestCase
{
    protected $roulette     = NULL;
    const NB_CHAMBERS       = 6;

    public function setUp()
    {
        $this->roulette = new RouletteTestHelper(self::NB_CHAMBERS);
    }

    public function tearDown()
    {
        unset($this->roulette);
        $this->roulette = NULL;
    }

    /**
     * @expectedException ERouletteCannotGoTwiceInARow
     */
    public function testTheSamePersonCannotShootTwiceInARow()
    {
        $state = $this->roulette->next('test');
        if ($state == Roulette::STATE_RELOAD ||
            $state == Roulette::STATE_BANG)
            $this->markTestSkipped('The fun fired on first try.');

        $state = $this->roulette->next('test');
    }

    /**
     * @expectedException ERouletteAtLeastTwoChambers
     */
    public function testThereMustBeAtLeastTwoChambers()
    {
        $this->roulette->setChambersCount(-1);
    }

    /**
     * @expectedException ERouletteAtLeastTwoChambers
     */
    public function testThereMustBeAtLeastTwoChambers2()
    {
        $this->roulette->setChambersCount(1);
    }

    /**
     * @expectedException ERouletteAtLeastTwoChambers
     */
    public function testThereMustBeAtLeastTwoChambers3()
    {
        $this->roulette->setChambersCount('1');
    }

    public function testThereMustBeAtLeastTwoChambers4()
    {
        $this->roulette->setChambersCount(2);
    }

    public function testSettingNewChambersCountCorrectlyResets()
    {
        $this->roulette->next('foo');
        $this->roulette->setChambersCount(2);

        $this->assertSame(2, $this->roulette->getChambersCount());
        $this->assertSame(0, $this->roulette->getPassedChambersCount());
    }

    public function testBangAtDesignatedPoint()
    {
        $this->roulette->setRandom(1);
        $this->assertEquals(Roulette::STATE_BANG,   $this->roulette->next('1'));
    }

    public function testSpinTheCylinderIfInLastChamber()
    {
        $this->roulette->setRandom(self::NB_CHAMBERS);

        for ($i = 1; $i < self::NB_CHAMBERS-1; $i++)
            $this->assertEquals(Roulette::STATE_NORMAL,
                $this->roulette->next((string) $i));

        $this->assertEquals(Roulette::STATE_RELOAD,
            $this->roulette->next('reload'));
    }

    public function testBangIfInLastButOneChamber()
    {
        $this->roulette->setRandom(self::NB_CHAMBERS-1);

        for ($i = 1; $i < self::NB_CHAMBERS-1; $i++)
            $this->assertEquals(Roulette::STATE_NORMAL,
                $this->roulette->next((string) $i));

        $this->assertEquals(Roulette::STATE_BANG,
            $this->roulette->next('bang'));
    }
}

?>
