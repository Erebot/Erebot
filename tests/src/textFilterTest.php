<?php

include_once('tests/testenv/bootstrap.php');
include_once('src/textFilter.php');

class   TextFilterArgumentsTest
extends ErebotModuleTestCase
{
    /**
     * @expectedException   EErebotIllegalAction
     */
    public function testInvalidArgumentsThrowAnException()
    {
        new ErebotTextFilter($this->_mainConfig, ErebotTextFilter::TYPE_STATIC, NULL);
    }

    /**
     * @expectedException   EErebotIllegalAction
     */
    public function testInvalidArgumentsThrowAnException2()
    {
        new ErebotTextFilter($this->_mainConfig, ErebotTextFilter::TYPE_WILDCARD, NULL);
    }

    /**
     * @expectedException   EErebotIllegalAction
     */
    public function testInvalidArgumentsThrowAnException3()
    {
        new ErebotTextFilter($this->_mainConfig, ErebotTextFilter::TYPE_REGEXP, NULL);
    }

    /**
     * @expectedException   EErebotIllegalAction
     */
    public function testInvalidArgumentsThrowAnException4()
    {
        new ErebotTextFilter($this->_mainConfig, NULL, '');
    }
}

class   TextFilterStaticTest
extends ErebotModuleTestCase
{
    const PATTERN_TYPE = ErebotTextFilter::TYPE_STATIC;
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
        $filter     = new ErebotTextFilter(
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

        $event  =   new ErebotEventTextPrivate(
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

        $event  =   new ErebotEventTextPrivate(
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

        $event  =   new ErebotEventTextPrivate(
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
    const PATTERN_TYPE = ErebotTextFilter::TYPE_WILDCARD;
    const PATTERN_TEXT = 't?st phrase here';
}

class   TextFilterWildcard2Test
extends TextFilterStaticTest
{
    const PATTERN_TYPE = ErebotTextFilter::TYPE_WILDCARD;
    const PATTERN_TEXT = 'te*here';
}

class   TextFilterWildcard3Test
extends TextFilterStaticTest
{
    const PATTERN_TYPE = ErebotTextFilter::TYPE_WILDCARD;
    const PATTERN_TEXT = 'te?? & &';
}

