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

abstract class  CtcpQuotingModuleHelper
extends         Erebot_Module_Base
{
    // Expose the protected method to test.
    static public function ctcpQuote($msg)
    {
        return self::_ctcpQuote($msg);
    }
}

class   CtcpQuotingConnectionHelper
extends Erebot_Connection
{
    // Expose the protected method to test.
    static public function ctcpUnquote($msg)
    {
        return self::_ctcpUnquote($msg);
    }
}


class   CtcpQuotingTest
extends Erebot_Testenv_Module_TestCase
{
    public function provider()
    {
        return array(
            array(
                "Hi there!\020nHow are you?",
                "Hi there!\nHow are you?"
            ),
            array(
                "SED \020n\tbig\020\020\\a\0200\\\\:",
                "SED \n\tbig\020\001\000\\:",
            ),
            array(
                "USERINFO :CS student\020n\\atest\\a",
                "USERINFO :CS student\n\001test\001",
            ),
        );
    }

    /**
     * @dataProvider    provider
     * @covers          Erebot_Module_Base::_ctcpQuote
     */
    public function testQuoting($quoted, $unquoted)
    {
        $this->assertEquals(
            $quoted,
            CtcpQuotingModuleHelper::ctcpQuote($unquoted)
        );
    }

    /**
     * @dataProvider    provider
     * @covers          Erebot_Connection::_ctcpUnquote
     */
    public function testUnquoting($quoted, $unquoted)
    {
        $this->assertEquals(
            $unquoted,
            CtcpQuotingConnectionHelper::ctcpUnquote($quoted)
        );
    }
}

