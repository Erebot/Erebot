<?php

include_once(dirname(dirname(__FILE__)).'/src/game.php');

class   CountdownParserTokenTest
extends PHPUnit_Framework_TestCase
{
    public function testParserTokenCreation()
    {
        $obj = new CountdownParser_yyToken('foo');
        $this->assertEquals('foo', (string) $obj);

        $obj[] = array('token');
        $this->assertEquals(TRUE, isset($obj[0]));
        $this->assertEquals("token", $obj[0]);

        unset($obj[0]);
        $this->assertEquals(FALSE, isset($obj[0]));

        $obj2 = new CountdownParser_yyToken($obj);
        $obj2 = new CountdownParser_yyToken('foo', $obj2);
        $obj2[] = NULL;
        $obj2[0] = NULL;
        $obj2[42] = 'foo';

        $obj[42] = $obj2;
    }
}

class   CountdownFormulaParserTest
extends PHPUnit_Framework_TestCase
{
    public function testParser()
    {
#        $obj = new CountdownParser();
#        $obj->yy_get_expected_tokens();

#        $this->assertEquals('End of Input', $obj->tokenName(0));
#        $this->assertEquals('OP_ADD', $obj->tokenName(1));
#        $this->assertEquals('Unknown', $obj->tokenName(-1));
    }
}

?>
