<?php

ErebotUtils::incl('src/game.php');

class   ErebotModule_Roulette
extends ErebotModuleBase
{
    protected $roulette = NULL;

    public function reload($flags)
    {
        $translator = $this->getTranslator(FALSE);

        if ($flags & self::RELOAD_MEMBERS) {
            $nb_chambers    = $this->parseInt('nb_chambers', 6);
            try {
                $this->roulette = new Roulette($nb_chambers);                
            }
            catch (ERouletteAtLeastTwoChambers $e) {
                throw new Exception($translator->gettext(
                    'There must be at least 2 chambers'));
            }
        }

        if ($flags & self::RELOAD_HANDLERS) {
            $registry   = $this->connection->getModule('TriggerRegistry', ErebotConnection::MODULE_BY_NAME);
            $match_any  = ErebotUtils::getVStatic($registry, 'MATCH_ANY');

            if (!($flags & self::RELOAD_INIT)) {
                $this->connection->removeEventHandler($this->handler);
                $registry->freeTriggers($this->trigger, $match_any);
            }

            $trigger        = $this->parseString('trigger', 'roulette');
            $this->trigger  = $registry->registerTriggers($trigger, $match_any);
            if ($this->trigger === NULL)
                throw new Exception($translator->gettext(
                    'Could not register Roulette trigger'));

            $targets    = new ErebotEventTargets(ErebotEventTargets::ORDER_ALLOW_DENY);
            $targets->addRule(
                ErebotEventTargets::TYPE_ALLOW,
                ErebotEventTargets::MATCH_ALL,
                ErebotEventTargets::MATCH_CHANNEL);

            $filter         = new ErebotTextFilter(ErebotTextFilter::TYPE_STATIC, $trigger, TRUE);
            $this->handler  = new ErebotEventHandler(
                                                array($this, 'handleRoulette'),
                                                ErebotEvent::ON_TEXT,
                                                $targets, $filter);
            $this->connection->addEventHandler($this->handler);
        }
    }

    public function handleRoulette(ErebotEvent &$event)
    {
        $nick       = $event->getSource();
        $chan       = $event->getChan();
        $action     = NULL;
        $chamber    = $this->roulette->getPassedChambersCount()+1;
        $total      = $this->roulette->getChambersCount();
        $translator = $this->getTranslator($chan);

        try {
            $state = $this->roulette->next($nick);
        }
        catch (ERouletteCannotGoTwiceInARow $e) {
            $this->sendMessage($chan, $translator->gettext(
                'You cannot go twice in a row'));
            return $event->preventDefault(TRUE);
        }

        switch ($state) {
            case Roulette::STATE_RELOAD:
                $message    = $translator->gettext('spins the cylinder');
                $tpl        = new ErebotStyling($message);
                $action     = $tpl->render();
                // Fall through
            case Roulette::STATE_NORMAL:
                $message = $translator->gettext('+click+');
                $tpl = new ErebotStyling($message);
                $ending = $tpl->render();
                break;

            case Roulette::STATE_BANG:
                $message    = $translator->gettext('<b>*BANG*</b>');
                $tpl        = new ErebotStyling($message);
                $ending     = $tpl->render();

                $message    = $translator->gettext('reloads');
                $tpl        = new ErebotStyling($message);
                $action     = $tpl->render();
                break;
        }

        $message = $translator->gettext('<var name="nick"/>: chamber '.
            '<var name="chamber"/> of <var name="total"/> =&gt; '.
            '<var name="message"/>');

        $tpl = new ErebotStyling($message);
        $tpl->assign('nick',    $nick);
        $tpl->assign('chamber', $chamber);
        $tpl->assign('total',   $total);
        $tpl->assign('message', $ending);
        $this->sendMessage($chan, $tpl->render());

        if ($action !== NULL)
            $this->sendCommand("PRIVMSG $chan :\001ACTION $action\001");
        return $event->preventDefault(TRUE);
    }
}

?>
