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

class   TriggerRegistryTest
extends PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        $bot                =   new ErebotStubbedCore();
        $config             =   ErebotStubbedServerConfig::create(array(
                                    'TriggerRegistry' => NULL,
                                ));
        $this->connection   =   new ErebotStubbedConnection($bot, $config);

        $this->module       = $this->connection->getModule('TriggerRegistry',
                                ErebotStubbedConnection::MODULE_BY_NAME);
    }

    public function __destruct()
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
