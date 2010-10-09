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

class   CountdownStub
extends Countdown
{
    public function __construct()
    {
        parent::__construct(100, 110, 7, array(1));
        $this->min = 7;
        $this->max = 10;
    }
}

class   CountdownTest
extends PHPUnit_Framework_TestCase
{
    protected $countdown = NULL;

    public function setUp()
    {
        $this->countdown = new Countdown();
    }

    public function tearDown()
    {
        unset($this->countdown);
        $this->countdown = NULL;
    }

    /**
     * GetNumbers may only generate numbers in the following set:
     * [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 25, 50, 75, 100]
     * Any other number points to an implementation error.
     */
    public function testGetNumbers()
    {
        $numbers    = $this->countdown->getNumbers();
        $allowed    = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 25, 50, 75, 100);
        foreach ($numbers as $number)
            $this->assertContains($number, $allowed,
                "$number is not allowed. Allowed numbers are: ".
                implode(', ', $allowed).'.');
    }

    /**
     * GetTarget must return an integer between 100 & 999 (inclusive).
     */
    public function testGetTarget()
    {
        $target         = $this->countdown->getTarget();
        $expectedType   = PHPUnit_Framework_Constraint_IsType::TYPE_INT;
        $this->assertType($expectedType, $target);
        $this->assertGreaterThanOrEqual(100, $target);
        $this->assertLessThanOrEqual(   999, $target);
    }

    /**
     * @expectedException ECountdownNoSuchNumberOrAlreadyUsed
     */
    public function testCannotReuseNumber()
    {
        $numbers    = $this->countdown->getNumbers();
        $numbers[]  = $numbers[0];
        $formula    = implode(' + ', $numbers);
        $obj        = new CountdownFormula('foo', $formula);
        $this->countdown->proposeFormula($obj);
    }

    public function testSolverGivesBestFormula()
    {
        $this->markTestIncomplete('No solver yet');

        $solver     = $this->countdown->solve();
        $this->countdown->proposeFormula($solver);

        $numbers    = $this->countdown->getNumbers();
        $obj        = new CountdownFormula('foo', implode('+', $numbers));
        $this->countdown->proposeFormula($obj);
        $obj        = new CountdownFormula('foo', implode('*', $numbers));
        $this->countdown->proposeFormula($obj);
        $this->assertSame($solver, $this->countdown->getBestProposal());
    }

    public function testReturnsBestProposedFormula()
    {
        unset($this->countdown);
        $this->countdown = new CountdownStub();

        $obj        = new CountdownFormula('foo', '1+1');
        $this->countdown->proposeFormula($obj);
        $this->assertSame($obj, $this->countdown->getBestProposal());

        $numbers    = $this->countdown->getNumbers();
        $formula    = implode(' + ', $numbers);
        $obj        = new CountdownFormula('bar', $formula);
        $this->countdown->proposeFormula($obj);
        $this->assertSame($obj, $this->countdown->getBestProposal());
    }

    /**
     * @expectedException ECountdownSyntaxError
     */
    public function testInvalidSyntax()
    {
        $obj        = new CountdownFormula('foo', 'foo');
    }
}

?>
