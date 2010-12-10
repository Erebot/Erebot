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

require_once(
    dirname(dirname(__FILE__)) .
    DIRECTORY_SEPARATOR . 'testenv' .
    DIRECTORY_SEPARATOR . 'bootstrap.php'
);

class   TextFilterArgumentsTest
extends ErebotModuleTestCase
{
    /**
     * @expectedException   Erebot_IllegalActionException
     */
    public function testInvalidArgumentsThrowAnException()
    {
        new Erebot_TextFilter(
            $this->_mainConfig,
            Erebot_TextFilter::TYPE_STATIC,
            NULL
        );
    }

    /**
     * @expectedException   Erebot_IllegalActionException
     */
    public function testInvalidArgumentsThrowAnException2()
    {
        new Erebot_TextFilter(
            $this->_mainConfig,
            Erebot_TextFilter::TYPE_WILDCARD,
            NULL
        );
    }

    /**
     * @expectedException   Erebot_IllegalActionException
     */
    public function testInvalidArgumentsThrowAnException3()
    {
        new Erebot_TextFilter(
            $this->_mainConfig,
            Erebot_TextFilter::TYPE_REGEXP,
            NULL
        );
    }

    /**
     * @expectedException   Erebot_IllegalActionException
     */
    public function testInvalidArgumentsThrowAnException4()
    {
        new Erebot_TextFilter($this->_mainConfig, NULL, '');
    }
}

class   TextFilterStaticTest
extends ErebotModuleTestCase
{
    const PATTERN_TYPE = Erebot_TextFilter::TYPE_STATIC;
    const PATTERN_TEXT = 'test phrase here';

    public function setUp()
    {
        parent::setUp();
        $this->_mainConfig
            ->expects($this->any())
            ->method('getCommandsPrefix')
            ->will($this->returnValue('!'));
    }

    protected function getFilter($prefixing)
    {
        $reflect    = new ReflectionObject($this);
        $filter     = new Erebot_TextFilter(
                            $this->_mainConfig,
                            $reflect->getConstant('PATTERN_TYPE'),
                            $reflect->getConstant('PATTERN_TEXT'),
                            $prefixing);
        return $filter;
    }

    protected function validate($prefix_mode, $with_prefix, $without_prefix)
    {
        $filter     = $this->getFilter($prefix_mode);
        $prefix     = $this->_mainConfig->getCommandsPrefix();

        $event  =   new Erebot_Event_PrivateText(
                        $this->_connection, 'foo',
                        $prefix.'test phrase here'
                    );
        if ($with_prefix)
            $this->assertEquals(TRUE, $filter->match($event),
                "Failed to accept a command with a prefix ($prefix).\n".
                "Internal state:\n".print_r($filter->getPatterns(), TRUE));
        else
            $this->assertEquals(FALSE, $filter->match($event),
                "Failed to reject a command with a prefix ($prefix).\n".
                "Internal state:\n".print_r($filter->getPatterns(), TRUE));

        $event  =   new Erebot_Event_PrivateText(
                        $this->_connection, 'foo',
                        'test phrase here'
                    );
        if ($without_prefix)
            $this->assertEquals(TRUE, $filter->match($event),
                "Failed to accept a command without any prefix.\n".
                "Internal state:\n".print_r($filter->getPatterns(), TRUE));
        else
            $this->assertEquals(FALSE, $filter->match($event),
                "Failed to reject a command without any prefix.\n".
                "Internal state:\n".print_r($filter->getPatterns(), TRUE));

        $event  =   new Erebot_Event_PrivateText(
                        $this->_connection, 'foo',
                        'ttest phrase here'
                    );
        $this->assertEquals(FALSE, $filter->match($event),
            "Failed to reject a command with an invalid prefix.\n".
            "Internal state:\n".print_r($filter->getPatterns(), TRUE));
    }

    public function testTextWithPrefix()
    {
        $this->validate(TRUE, TRUE, FALSE);
    }

    public function testTextWithoutPrefix()
    {
        $this->validate(FALSE, FALSE, TRUE);
    }

    public function testTextWithOrWithoutPrefix()
    {
        $this->validate(NULL, TRUE, TRUE);
    }
}

class   TextFilterWildcardTest
extends TextFilterStaticTest
{
    const PATTERN_TYPE = Erebot_TextFilter::TYPE_WILDCARD;
    const PATTERN_TEXT = 't?st phrase here';
}

class   TextFilterWildcard2Test
extends TextFilterStaticTest
{
    const PATTERN_TYPE = Erebot_TextFilter::TYPE_WILDCARD;
    const PATTERN_TEXT = 'te*here';
}

class   TextFilterWildcard3Test
extends TextFilterStaticTest
{
    const PATTERN_TYPE = Erebot_TextFilter::TYPE_WILDCARD;
    const PATTERN_TEXT = 'te?? & &';
}

