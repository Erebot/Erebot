<?php

class   ErebotModule_LagChecker
extends ErebotModuleBase
{
    protected $timer_ping;
    protected $timer_pong;
    protected $timer_quit;

    protected $delay_ping;
    protected $delay_pong;
    protected $delay_reco;

    protected $last_sent;
    protected $last_rcvd;

    protected $trigger;

    public function reload($flags)
    {
        if ($flags & self::RELOAD_METADATA) {
            $this->addMetadata(self::META_DEPENDS, 'TriggerRegistry');
            $this->addMetadata(self::META_DEPENDS, 'Helper');
        }

        if ($flags & self::RELOAD_MEMBERS) {
            if (!($flags & self::RELOAD_INIT)) {
                $timers =   array('timer_ping', 'timer_pong', 'timer_quit');

                foreach ($timers as $timer) {
                    try {
                        $this->removeTimer($this->$timer);
                    }
                    catch (EErebotNotFound $e) {}
                    unset($this->$timer);
                    $this->$timer = NULL;
                }

                $this->trigger  = NULL;
            }

            $this->delay_ping   = $this->parseInt('check');
            $this->delay_pong   = $this->parseInt('timeout');
            $this->delay_reco   = $this->parseInt('reconnect');

            $this->timer_ping   = NULL;
            $this->timer_pong   = NULL;
            $this->timer_quit   = NULL;

            $this->last_rcvd    = NULL;
            $this->last_sent    = NULL;
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
                $this->connection->addEventHandler($handler);
            }

            $registry   =   $this->connection->getModule('TriggerRegistry',
                                    ErebotConnection::MODULE_BY_NAME);

            $trigger    = $this->parseString('trigger', 'lag');
            $match_any  = ErebotUtils::getVStatic($registry, 'MATCH_ANY');

            $this->trigger  =   $registry->registerTriggers(
                $trigger, $match_any);
            if ($this->trigger === NULL)
                throw new Exception($this->translator->gettext(
                    'Unable to register trigger for Lag Checker'));

            $filter         =   new ErebotTextFilter(
                                    $this->mainCfg,
                                    ErebotTextFilter::TYPE_STATIC,
                                    $trigger, TRUE);
            $handler        =   new ErebotEventHandler(
                                    array($this, 'handleGetLag'),
                                    'iErebotEventMessageText',
                                    NULL, $filter);
            $this->connection->addEventHandler($handler);
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

        $bot        =&  $this->connection->getBot();
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
        $this->timer_pong   =   new ErebotTimer(
                                    array($this, 'disconnect'),
                                    $this->delay_pong, FALSE);
        $this->addTimer($this->timer_pong);

        $this->last_sent    = microtime(TRUE);
        $this->last_rcvd    = NULL;
        $this->sendCommand('PING '.$this->last_sent);
    }

    public function handlePong(iErebotEvent &$event)
    {
        if ($event->getText() != ((string) $this->last_sent))
            return;

        $this->last_rcvd = microtime(TRUE);
        $this->removeTimer($this->timer_pong);
        unset($this->timer_pong);
        $this->timer_pong = NULL;
    }

    public function handleExit(iErebotEvent &$event)
    {
        if ($this->timer_ping) {
            $this->removeTimer($this->timer_ping);
            unset($this->timer_ping);
        }

        if ($this->timer_pong) {
            $this->removeTimer($this->timer_pong);
            unset($this->timer_pong);
        }
    }

    public function disconnect(iErebotTimer &$timer)
    {
        $this->connection->disconnect();

        $config     =&  $this->connection->getConfig(NULL);
        $logging    =&  ErebotLogging::getInstance();
        $logger     =   $logging->getLogger(__FILE__);
        $logger->info($this->translator->gettext(
            'Lag got too high for "%(server)s" ... '.
            'reconnecting in %(delay)d seconds'),
            array(
                'server'    => $config->getConnectionURL(),
                'delay'     => $this->delay_reco,
            ));

        $this->timer_quit   =   new ErebotTimer(
                                    array($this, 'reconnect'),
                                    $this->delay_reco, TRUE);
        $this->addTimer($this->timer_quit);

        try {
            if ($this->timer_ping !== NULL)
                $this->removeTimer($this->timer_ping);
            if ($this->timer_pong !== NULL)
                $this->removeTimer($this->timer_pong);
        }
        catch (EErebot $e) {}

        unset($this->timer_ping, $this->timer_pong);
        $this->timer_ping   = NULL;
        $this->timer_pong   = NULL;
    }

    public function reconnect(iErebotTimer &$timer)
    {
        $config     =&  $this->connection->getConfig(NULL);
        $logging    =&  ErebotLogging::getInstance();
        $logger     =   $logging->getLogger(__FILE__);
        $logger->info($this->translator->gettext(
                        'Attempting reconnection to "%s"'),
                        $config->getConnectionURL());

        try {
            $this->connection->connect();
            $bot =& $this->connection->getBot();
            $bot->addConnection($this->connection);

            $this->removeTimer($this->timer_quit);
            unset($this->timer_quit);
            $this->timer_quit   = NULL;
        }
        catch (EErebotConnectionFailure $e) {}
    }

    public function getLag()
    {
        if ($this->last_rcvd === NULL)
            return NULL;
        return ($this->last_rcvd - $this->last_sent);
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
        $this->timer_ping   =   new ErebotTimer(
                                    array($this, 'checkLag'),
                                    $this->delay_ping, TRUE);
        $this->addTimer($this->timer_ping);

        if ($this->trigger !== NULL) {
            try {
                $registry   =   $this->connection->getModule(
                                    'TriggerRegistry',
                                    ErebotConnection::MODULE_BY_NAME);
                $registry->freeTriggers($this->trigger);
            }
            catch (EErebot $e) {}
        }
    }
}

?>
