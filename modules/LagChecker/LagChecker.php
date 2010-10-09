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

class   ErebotModule_LagChecker
extends ErebotModuleBase
{
    static protected $_metadata = array(
        'requires'  =>  array('TriggerRegistry', 'Helper'),
    );
    protected $_timerPing;
    protected $_timerPong;
    protected $_timerQuit;

    protected $_delayPing;
    protected $_delayPong;
    protected $_delayReco;

    protected $_lastSent;
    protected $_lastRcvd;

    protected $_trigger;

    public function reload($flags)
    {
        if ($flags & self::RELOAD_MEMBERS) {
            if (!($flags & self::RELOAD_INIT)) {
                $timers =   array('_timerPing', '_timerPong', '_timerQuit');

                foreach ($timers as $timer) {
                    try {
                        $this->removeTimer($this->$timer);
                    }
                    catch (EErebotNotFound $e) {}
                    unset($this->$timer);
                    $this->$timer = NULL;
                }

                $this->_trigger = NULL;
            }

            $this->_delayPing   = $this->parseInt('check');
            $this->_delayPong   = $this->parseInt('timeout');
            $this->_delayReco   = $this->parseInt('reconnect');

            $this->_timerPing   = NULL;
            $this->_timerPong   = NULL;
            $this->_timerQuit   = NULL;

            $this->_lastRcvd    = NULL;
            $this->_lastSent    = NULL;
        }

        if ($flags & self::RELOAD_HANDLERS) {
            $handlers = array(
                            'handlePong'        => 'ErebotEventPong',
                            'handleExit'        => 'ErebotEventExit',
                            'handleConnect'     => 'ErebotEventConnect',
                        );

            foreach ($handlers as $callback => $event_type) {
                $handler    =   new ErebotEventHandler(
                                    array($this, $callback),
                                    $event_type);
                $this->_connection->addEventHandler($handler);
            }

            $registry   =   $this->_connection->getModule('TriggerRegistry',
                                    ErebotConnection::MODULE_BY_NAME);

            $trigger    = $this->parseString('trigger', 'lag');
            $matchAny  = ErebotUtils::getVStatic($registry, 'MATCH_ANY');

            $this->_trigger  =   $registry->registerTriggers(
                $trigger, $matchAny);
            if ($this->_trigger === NULL)
                throw new Exception($this->_translator->gettext(
                    'Unable to register trigger for Lag Checker'));

            $filter         =   new ErebotTextFilter(
                                    $this->_mainCfg,
                                    ErebotTextFilter::TYPE_STATIC,
                                    $trigger, TRUE);
            $handler        =   new ErebotEventHandler(
                                    array($this, 'handleGetLag'),
                                    'iErebotEventMessageText',
                                    NULL, $filter);
            $this->_connection->addEventHandler($handler);
            $this->registerHelpMethod(array($this, 'getHelp'));
        }
    }

    public function getHelp(iErebotEventMessageText &$event, $words)
    {
        if ($event instanceof iErebotEventPrivate) {
            $target = $event->getSource();
            $chan   = NULL;
        }
        else
            $target = $chan = $event->getChan();

        $translator = $this->getTranslator($chan);
        $trigger    = $this->parseString('trigger', 'lag');

        $bot        =&  $this->_connection->getBot();
        $moduleName =   strtolower($bot->moduleClassToName($this));
        $nbArgs     =   count($words);

        if ($nbArgs == 1 && $words[0] == $moduleName) {
            $msg = $translator->gettext('
Provides the <b><var name="trigger"/></b> command which prints
the current lag.
');
            $formatter = new ErebotStyling($msg, $translator);
            $formatter->assign('trigger', $trigger);
            $this->sendMessage($target, $formatter->render());
            return TRUE;
        }

        if ($nbArgs < 2)
            return FALSE;

        if ($words[1] == $trigger) {
            $msg = $translator->gettext("
<b>Usage:</b> !<var name='trigger'/>.
Display the latency of the connection, that is, the number of seconds
it takes for a message from the bot to go to the IRC server and back.
");
            $formatter = new ErebotStyling($msg, $translator);
            $formatter->assign('trigger', $trigger);
            $this->sendMessage($target, $formatter->render());

            return TRUE;
        }
    }

    public function checkLag(ErebotTimer &$timer)
    {
        $this->_timerPong   =   new ErebotTimer(
                                    array($this, 'disconnect'),
                                    $this->_delayPong, FALSE);
        $this->addTimer($this->_timerPong);

        $this->_lastSent    = microtime(TRUE);
        $this->_lastRcvd    = NULL;
        $this->sendCommand('PING '.$this->_lastSent);
    }

    public function handlePong(iErebotEvent &$event)
    {
        if ($event->getText() != ((string) $this->_lastSent))
            return;

        $this->_lastRcvd = microtime(TRUE);
        $this->removeTimer($this->_timerPong);
        unset($this->_timerPong);
        $this->_timerPong = NULL;
    }

    public function handleExit(iErebotEvent &$event)
    {
        if ($this->_timerPing) {
            $this->removeTimer($this->_timerPing);
            unset($this->_timerPing);
        }

        if ($this->_timerPong) {
            $this->removeTimer($this->_timerPong);
            unset($this->_timerPong);
        }
    }

    public function disconnect(iErebotTimer &$timer)
    {
        $this->_connection->disconnect();

        $config     =&  $this->_connection->getConfig(NULL);
        $logging    =&  ErebotLogging::getInstance();
        $logger     =   $logging->getLogger(__FILE__);
        $logger->info($this->_translator->gettext(
            'Lag got too high for "%(server)s" ... '.
            'reconnecting in %(delay)d seconds'),
            array(
                'server'    => $config->getConnectionURL(),
                'delay'     => $this->_delayReco,
            ));

        $this->_timerQuit   =   new ErebotTimer(
                                    array($this, 'reconnect'),
                                    $this->_delayReco, TRUE);
        $this->addTimer($this->_timerQuit);

        try {
            if ($this->_timerPing !== NULL)
                $this->removeTimer($this->_timerPing);
        }
        catch (EErebot $e) {
        }

        try {
            if ($this->_timerPong !== NULL)
                $this->removeTimer($this->_timerPong);
        }
        catch (EErebot $e) {
        }

        unset($this->_timerPing, $this->_timerPong);
        $this->_timerPing   = NULL;
        $this->_timerPong   = NULL;
    }

    public function reconnect(iErebotTimer &$timer)
    {
        $config     =&  $this->_connection->getConfig(NULL);
        $logging    =&  ErebotLogging::getInstance();
        $logger     =   $logging->getLogger(__FILE__);
        $logger->info($this->_translator->gettext(
                        'Attempting reconnection to "%s"'),
                        $config->getConnectionURL());

        try {
            $this->_connection->connect();
            $bot =& $this->_connection->getBot();
            $bot->addConnection($this->_connection);

            $this->removeTimer($this->_timerQuit);
            unset($this->_timerQuit);
            $this->_timerQuit   = NULL;
        }
        catch (EErebotConnectionFailure $e) {}
    }

    public function getLag()
    {
        if ($this->_lastRcvd === NULL)
            return NULL;
        return ($this->_lastRcvd - $this->_lastSent);
    }

    public function handleGetLag(iErebotEventMessageText &$event)
    {
        if ($event instanceof iErebotEventPrivate) {
            $target = $event->getSource();
            $chan   = NULL;
        }
        else
            $target = $chan = $event->getChan();

        $lag        = $this->getLag();
        $translator = $this->getTranslator($chan);

        if ($lag === NULL)
            $this->sendMessage($target, $translator->gettext(
                'No lag measure has been done yet'));
        else {
            $msg = $translator->gettext(
                'Current lag: <var name="lag"/> seconds');
            $formatter = new ErebotStyling($msg, $translator);
            $formatter->assign('lag', $lag);
            $this->sendMessage($target, $formatter->render());
        }
    }

    public function handleConnect(iErebotEvent &$event)
    {
        $this->_timerPing   =   new ErebotTimer(
                                    array($this, 'checkLag'),
                                    $this->_delayPing, TRUE);
        $this->addTimer($this->_timerPing);

        if ($this->_trigger !== NULL) {
            try {
                $registry   =   $this->_connection->getModule(
                                    'TriggerRegistry',
                                    ErebotConnection::MODULE_BY_NAME);
                $registry->freeTriggers($this->_trigger);
            }
            catch (EErebot $e) {
            }
        }
    }
}

