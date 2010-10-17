<?php

include_once('src/utils.php');
include_once('src/exceptions/Exception.php');
include_once('src/exceptions/IllegalAction.php');
#include_once('src/textFilter.php');
include_once('src/events/events.php');

include_once('src/ifaces/core.php');
include_once('src/ifaces/connection.php');
include_once('src/ifaces/mainConfig.php');
include_once('src/ifaces/serverConfig.php');
include_once('src/ifaces/networkConfig.php');

include_once('tests/connectionStub.php');
include_once('tests/mainConfigStub.php');
include_once('tests/serverConfigStub.php');
include_once('tests/networkConfigStub.php');
include_once('tests/coreStub.php');

class   TextFilterArgumentsTest
extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->config = new ErebotStubbedMainConfig(NULL, NULL);
    }

    /**
     * @expectedException   EErebotIllegalAction
     */
    public function testInvalidArgumentsThrowAnException()
    {
        new ErebotTextFilter($this->config, ErebotTextFilter::TYPE_STATIC, NULL);
    }

    /**
     * @expectedException   EErebotIllegalAction
     */
    public function testInvalidArgumentsThrowAnException2()
    {
        new ErebotTextFilter($this->config, ErebotTextFilter::TYPE_WILDCARD, NULL);
    }

    /**
     * @expectedException   EErebotIllegalAction
     */
    public function testInvalidArgumentsThrowAnException3()
    {
        new ErebotTextFilter($this->config, ErebotTextFilter::TYPE_REGEXP, NULL);
    }

    /**
     * @expectedException   EErebotIllegalAction
     */
    public function testInvalidArgumentsThrowAnException4()
    {
        new ErebotTextFilter($this->config, NULL, '');
    }
}

class   TextFilterStaticTest
extends PHPUnit_Framework_TestCase
{
    const PATTERN_TYPE = ErebotTextFilter::TYPE_STATIC;
    const PATTERN_TEXT = 'test phrase here';

    protected function getFilter($config, $prefixing)
    {
        $reflect    = new ReflectionObject($this);
        $filter     = new ErebotTextFilter(
                            $config,
                            $reflect->getConstant('PATTERN_TYPE'),
                            $reflect->getConstant('PATTERN_TEXT'),
                            $prefixing);
        return $filter;
    }

    protected function validate($prefix_mode, $with_prefix, $without_prefix)
    {
        $config     = new ErebotStubbedMainConfig(NULL, NULL);
        $filter     = $this->getFilter($config, $prefix_mode);
        $prefix     = $config->getCommandsPrefix();
        $bot        = new ErebotStubbedCore();
        $config     = ErebotStubbedServerConfig::create(array());
        $connection = new ErebotStubbedConnection($bot, $config);

        $event  =   new ErebotEventTextPrivate(
                        $connection, 'foo',
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
                        $connection, 'foo',
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
                        $connection, 'foo',
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

