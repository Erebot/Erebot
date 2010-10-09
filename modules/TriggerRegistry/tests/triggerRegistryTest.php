<?php

#include_once(dirname(dirname(__FILE__)).'/TriggerRegistry.php');

/**
 * @runTestsInSeparateProcesses
 */
class   TriggerRegistryTest
extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->connection   = new ErebotStubbedConnection(
                                array('TriggerRegistry'));
        $this->module       = $this->connection->getModule('TriggerRegistry',
                                ErebotStubbedConnection::MODULE_BY_NAME);
    }

    public function tearDown()
    {
        unset($this->connection);
        unset($this->module);
    }

    /**
     * @expectedException   EErebotInvalidValue
     */
    public function testRegisterWithInvalidValueForChannel()
    {
        $this->module->registerTriggers('test', NULL);
    }

    /**
     * @expectedException   EErebotInvalidValue
     */
    public function testUnregisterWithInvalidValueForChannel()
    {
        $this->module->freeTriggers(NULL);
    }

    /**
     * @expectedException   EErebotNotFound
     */
    public function testUnregisterInexistentTrigger()
    {
        $this->module->freeTriggers('inexistent trigger');
    }

    public function testRegisterGeneralTrigger()
    {
        $any = ErebotUtils::getVStatic($this->module, 'MATCH_ANY');
        $token1 = $this->module->registerTriggers('test', $any);
        $this->assertNotSame(NULL, $token1);

        $token2 = $this->module->registerTriggers('test', $any);
        $this->assertSame(NULL, $token2);

        $this->assertContains('test', $this->module->getTriggers($token1));
        $this->module->freeTriggers($token1);
    }
}

?>
