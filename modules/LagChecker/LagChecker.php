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
                            'handlePong'        => ErebotEvent::ON_PONG,
                            'handleDisconnect'  => ErebotEvent::ON_DISCONNECT,
                            'handleConnect'     => ErebotEvent::ON_CONNECT,
                        );

            foreach ($handlers as $callback => $event_type) {
                $handler    =   new ErebotEventHandler(
                                    array($this, $callback),
                                    $event_type);
                $this->connection->addEventHandler($handler);
            }

            try {
                $registry   =   $this->connection->getModule(
                                    'TriggerRegistry',
                                    ErebotConnection::MODULE_BY_NAME);
            }
            catch (EErebotNotFound $e) {
                $registry = NULL;
            }

            if ($registry !== NULL) {
                $trigger    = $this->parseString('trigger', 'lag');
                $match_any  = ErebotUtils::getVStatic($registry, 'MATCH_ANY');

                $this->trigger  =   $registry->registerTriggers(
                    $trigger, $match_any);
                if ($this->trigger === NULL) {
                    $translator = $this->getTranslator(FALSE);
                    throw new Exception($translator->gettext(
                        'Unable to register trigger for Lag Checker'));
                }

                $filter         =   new ErebotTextFilter(
                                        ErebotTextFilter::TYPE_STATIC,
                                        $trigger, TRUE);
                $handler        =   new ErebotEventHandler(
                                        array($this, 'handleGetLag'),
                                        ErebotEvent::ON_TEXT,
                                        NULL, $filter);
                $this->connection->addEventHandler($handler);
            }
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

    public function handlePong(ErebotEvent &$event)
    {
        if ($event->getText() != ((string) $this->last_sent))
            return;

        $this->last_rcvd = microtime(TRUE);
        $this->removeTimer($this->timer_pong);
        unset($this->timer_pong);
        $this->timer_pong = NULL;
    }

    public function handleDisconnect(ErebotEvent &$event)
    {
        if ($event->getText() === NULL) {
            $timer = new ErebotTimer(array($this, 'disconnect'), 1, FALSE);
            $this->disconnect($timer);
            $event->preventDefault(TRUE);
        }
    }

    public function disconnect(ErebotTimer &$timer)
    {
        $this->connection->disconnect();
$config =&  $this->connection->getConfig(NULL);
printf("Lost connection to '%s' ... attempting reconnection in %d seconds.\n",
    $config->getConnectionURL(), $this->delay_reco);
unset($config);

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

    public function reconnect(ErebotTimer &$timer)
    {
$config =&  $this->connection->getConfig(NULL);
printf("Attempting reconnection to '%s'.\n", $config->getConnectionURL());
unset($config);
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

    public function handleGetLag(ErebotEvent &$event)
    {
        $lag        = $this->getLag();
        $target     = $event->getTarget();
        $translator = $this->getTranslator($event->getChan());

        if ($lag === NULL)
            $this->sendMessage($target, $translator->gettext(
                'No lag measure has been done yet'));
        else
            $this->sendMessage($target, $translator->gettext(
                'Last lag measure revealed %f secs of latency'), $lag);
    }

    public function handleConnect(ErebotEvent &$event)
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
