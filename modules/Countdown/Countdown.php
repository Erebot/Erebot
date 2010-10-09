<?php

ErebotUtils::incl('src/game.php');

class   ErebotModule_Countdown
extends ErebotModuleBase
{
    protected $trigger;
    protected $startHandler;
    protected $rawHandler;
    protected $game;

    const FORMULA_FILTER    = '@^[\\(\\)\\-\\+\\*/0-9 ]+$@';

    public function reload($flags)
    {
        if ($flags & self::RELOAD_HANDLERS) {
            $registry   = $this->connection->getModule('TriggerRegistry', ErebotConnection::MODULE_BY_NAME);
            $match_any  = ErebotUtils::getVStatic($registry, 'MATCH_ANY');

            if (!($flags & self::RELOAD_INIT)) {
                $this->connection->removeEventHandler($this->startHandler);
                $registry->freeTriggers($this->trigger, $match_any);
            }

            $trigger        = $this->parseString('trigger', 'countdown');
            $this->trigger  = $registry->registerTriggers($trigger, $match_any);
            if ($this->trigger === NULL) {
                $translator = $this->getTranslator(FALSE);
                throw new Exception($translator->gettext(
                    'Could not register Countdown trigger'));
            }

            $targets    = new ErebotEventTargets(ErebotEventTargets::ORDER_ALLOW_DENY);
            $targets->addRule(
                ErebotEventTargets::TYPE_ALLOW,
                ErebotEventTargets::MATCH_ALL,
                ErebotEventTargets::MATCH_CHANNEL);

            $filter                 = new ErebotTextFilter(ErebotTextFilter::TYPE_STATIC, $trigger, TRUE);
            $this->startHandler     = new ErebotEventHandler(
                                                array($this, 'handleCountdown'),
                                                ErebotEvent::ON_TEXT,
                                                $targets, $filter);
            $this->connection->addEventHandler($this->startHandler);

            $targets            = new ErebotEventTargets(ErebotEventTargets::ORDER_ALLOW_DENY);
            $filter             = new ErebotTextFilter(ErebotTextFilter::TYPE_REGEXP, self::FORMULA_FILTER, FALSE);
            $this->rawHandler   = new ErebotEventHandler(
                                            array($this, 'handleRawText'),
                                            ErebotEvent::ON_TEXT,
                                            $targets, $filter);
            $this->connection->addEventHandler($this->rawHandler);
        }
    }

    public function handleCountdown(ErebotEvent &$event)
    {
        $chan       = $event->getChan();
        $translator = $this->getTranslator($chan);

        if (isset($this->game[$chan])) {
            // Display current status.
            $game   =&  $this->game[$chan]['game'];
            $msg    =   $translator->gettext('You must get <b><var name="target"/>'.
                            '</b> using the following numbers: '.
                            '<for from="numbers" item="number"><b><var '.
                            'name="number"/></b></for>.');
            $tpl    = new ErebotStyling($msg);
            $tpl->assign('target',      $game->getTarget());
            $tpl->assign('numbers',     $game->getNumbers());
            $this->sendMessage($chan, $tpl->render());
            $best = $game->getBestProposal();
            if ($best === NULL)
                return;

            $msg    = $translator->gettext('So far, <b><var name="nick"/></b> has '.
                            'achieved <b><var name="result"/></b> using this '.
                            'formula: <b><var name="formula"/></b>');
            $tpl    = new ErebotStyling($msg);
            $tpl->assign('nick',    $best->getOwner());
            $tpl->assign('result',  $best->getResult());
            $tpl->assign('formula', $best->getFormula());
            $this->sendMessage($chan, $tpl->render());
            return;
        }

        $minTarget  = $this->parseInt('minimum', 100);
        $maxTarget  = $this->parseInt('maximum', 999);
        $nbNumbers  = $this->parseInt('numbers', 7);
        $allowed    = $this->parseString('allowed', '1 2 3 4 5 6 7 8 9 10 25 50 75 100');
        $allowed    = array_map('intval', array_filter(explode(' ', $allowed)));

        $game   =   new Countdown($minTarget, $maxTarget, $nbNumbers, $allowed);
        $delay  =   $this->parseInt('delay', 60);
        $msg    =   $translator->gettext('A new Countdown game has been started. '.
                        'You must get <b><var name="target"/></b> using the '.
                        'following numbers <for from="numbers" item="number">'.
                        '<b><var name="number"/></b></for>. You have <var '.
                        'name="delay"/> seconds to make suggestions.');
        $tpl    = new ErebotStyling($msg);
        $tpl->assign('target',  $game->getTarget());
        $tpl->assign('numbers', $game->getNumbers());
        $tpl->assign('delay',   $delay);
        $this->sendMessage($chan, $tpl->render());

        $timer  = new ErebotTimer(array($this, 'handleTimeOut'), $delay, FALSE);
        $this->game[$chan] = array(
            'game'  => $game,
            'timer' => $timer,
        );
        $this->addTimer($timer);

        $targets =& $this->rawHandler->getTargets();
        $targets->addRule(  ErebotEventTargets::TYPE_ALLOW,
                            ErebotEventTargets::MATCH_ALL,
                            $chan);
    }

    public function handleRawText(ErebotEvent &$event)
    {
        $chan       = $event->getChan();
        $nick       = $event->getSource();
        $text       = (string) $event->getText();
        $translator = $this->getTranslator($chan);

        try {
            $formula = new CountdownFormula($nick, $text);
        }
        catch (ECountdownFormulaMustBeAString $e) {
            throw new Exception($translator->gettext(
                'Expected the formula to be a string'));
        }
        catch (ECountdownInvalidToken $e) {
            return $this->sendMessage($chan, sprintf($translator->gettext(
                'Invalid token near "%1$s" at offset %2$d'),
                $e->getExcerpt(), $e->getPosition()));
        }
        catch (ECountdownDivisionByZero $e) {
            return $this->sendMessage($chan, $translator->gettext(
                'Division by zero'));
        }
        catch (ECountdownNonIntegralDivision $e) {
            return $this->sendMessage($chan, $translator->gettext(
                'Non integral division'));
        }
        catch (ECountdownSyntaxError $e) {
            return $this->sendMessage($chan, $translator->gettext(
                'Syntax error'));
        }

        $game   =&  $this->game[$chan]['game'];
        try {
            $best = $game->proposeFormula($formula);
        }
        catch (ECountdownNoSuchNumberOrAlreadyUsed $e) {
            return $this->sendMessage($chan, $translator->gettext(
                'No such number or number already used'));
        }

        if ($best) {
            if ($formula->getResult() == $game->getTarget()) {
                $msg    =   $translator->gettext('<b>BINGO! <var name="nick"/></b> '.
                                'has achieved <b><var name="result"/></b> with '.
                                'this formula: <b><var name="formula"/></b>.');
                $tpl    = new ErebotStyling($msg);
                $tpl->assign('nick',    $nick);
                $tpl->assign('result',  $formula->getResult());
                $tpl->assign('formula', $formula->getFormula());
                $this->sendMessage($chan, $tpl->render());

                $this->removeTimer($this->game[$chan]['timer']);
                $targets =& $this->rawHandler->getTargets();
                $targets->removeRule(   ErebotEventTargets::TYPE_ALLOW,
                                        ErebotEventTargets::MATCH_ALL,
                                        $chan);
                unset($this->game[$chan]);
                return;
            }

            $msg    = $translator->gettext(
                'Congratulations <b><var name="nick"/></b>! You\'re '.
                'the closest with <b><var name="result"/></b>.');
            $tpl    = new ErebotStyling($msg);
            $tpl->assign('nick',    $nick);
            $tpl->assign('result',  $formula->getResult());
            $this->sendMessage($chan, $tpl->render());
            return;
        }

        $msg    =   $translator->gettext('Not bad <b><var name="nick"/></b>, you '.
                        'actually got <b><var name="result"/></b>, but this '.
                        'is not the best formula... Try again ;)');
        $tpl    = new ErebotStyling($msg);
        $tpl->assign('nick',    $nick);
        $tpl->assign('result',  $formula->getResult());
        $this->sendMessage($chan, $tpl->render());
    }

    public function handleTimeOut(ErebotTimer &$timer)
    {
        $chan = $game = NULL;
        foreach ($this->game as $key => &$data) {
            if ($data['timer'] === $timer) {
                $chan =     $key;
                $game =&    $data['game'];
                break;
            }
        }

        $this->removeTimer($timer);
        if ($chan === NULL)
            return;

        $targets =& $this->rawHandler->getTargets();
        $targets->removeRule(   ErebotEventTargets::TYPE_ALLOW,
                                ErebotEventTargets::MATCH_ALL,
                                $chan);
        unset($this->game[$chan]);
        unset($key, $data);

        $best =& $game->getBestProposal();
        if ($best === NULL) {
            $msg = $translator->gettext("Time's up! Nobody has made any suggestion. :(");
            $this->sendMessage($chan, $msg);
            unset($chan, $game);
            return;
        }

        $msg    =   $translator->gettext('Congratulations to <b><var name="nick"/>'.
                        '</b> who wins this Countdown game. <b><var name="'.
                        'nick"/></b> has got <b><var name="result"/></b> with '.
                        'this formula: <b><var name="formula"/></b>.');
        $tpl    = new ErebotStyling($msg);
        $tpl->assign('nick',    $best->getOwner());
        $tpl->assign('result',  $best->getResult());
        $tpl->assign('formula', $best->getFormula());
        $this->sendMessage($chan, $tpl->render());

        unset($chan, $game);
    }
}

?>
