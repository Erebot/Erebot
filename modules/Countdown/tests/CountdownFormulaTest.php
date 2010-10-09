<?php

include_once(dirname(dirname(__FILE__)).'/src/formula.php');

class   CountdownFormulaTest
extends PHPUnit_Framework_TestCase
{
    protected $formula = NULL;

    /**
     * The formula parser expects a string as its input.
     * @expectedException ECountdownFormulaMustBeAString
     */
    public function testFormulaParsing()
    {
        new CountdownFormula('foo', 42);
    }

    /**
     * Using an empty string should throw an error.
     * @expectedException ECountdownFormulaMustBeAString
     */
    public function testFormulaParsing2()
    {
        new CountdownFormula('foo', '');
    }

    /**
     * Parsing on a single number represented as a string
     * should return than number as an integer.
     */
    public function testFormulaParsing3()
    {
        $formula    = '42';
        $obj        = new CountdownFormula('foo', $formula);
        $this->assertSame(42, $obj->getResult());
    }

    /**
     * Must be able to parse additions.
     */
    public function testFormulaParsing4()
    {
        $formula    = '40 + 2';
        $obj        = new CountdownFormula('foo', $formula);
        $this->assertSame(42, $obj->getResult());
    }

    /**
     * Must be able to parse multiplications.
     */
    public function testFormulaParsing5()
    {
        $formula    = '6 * 7';
        $obj        = new CountdownFormula('foo', $formula);
        $this->assertSame(42, $obj->getResult());
    }

    /**
     * Must be able to parse subtractions.
     */
    public function testFormulaParsing6()
    {
        $formula    = '45 - 3';
        $obj        = new CountdownFormula('foo', $formula);
        $this->assertSame(42, $obj->getResult());
    }

    /**
     * Must be able to parse divisions.
     */
    public function testFormulaParsing7()
    {
        $formula    = '42 / 6';
        $obj        = new CountdownFormula('foo', $formula);
        $this->assertSame(7, $obj->getResult());
    }

    /**
     * Test operator priorities.
     */
    public function testFormulaParsing8()
    {
        $formula    = '2 + 2 * 20';
        $obj        = new CountdownFormula('foo', $formula);
        $this->assertSame(42, $obj->getResult());

        $formula    = '(2 + 2) * 20';
        $obj        = new CountdownFormula('foo', $formula);
        $this->assertSame(80, $obj->getResult());
    }

    public function testGettingNumbersUsedInFormula()
    {
        $formula    = '1 + 2 * 42 / 7 + 1';
        $obj        = new CountdownFormula('foo', $formula);
        $numbers    = $obj->getNumbers();
        $used       = array(1, 2, 42, 7, 1);
        sort($used);
        sort($numbers);
        $this->assertEquals($used, $numbers,
            "Failed retrieving numbers used in formula.");
    }

    public function testGetFormula()
    {
        $formula    = '1 + 2 * 42 / 7 + 1';
        $obj        = new CountdownFormula('foo', $formula);
        $this->assertEquals($formula, $obj->getFormula());
    }

    public function testGetOwner()
    {
        $formula    = '1 + 2 * 42 / 7 + 1';
        $obj        = new CountdownFormula('foo', $formula);
        $this->assertEquals('foo', $obj->getOwner());
    }
}

?>
