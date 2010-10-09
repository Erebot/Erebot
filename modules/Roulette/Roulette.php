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

include_once('modules/Roulette/src/game.php');

class   ErebotModule_Roulette
extends ErebotModuleBase
{
    static protected $_metadata = array(
        'requires'  =>  array('TriggerRegistry', 'Helper'),
    );
    protected $_roulette = NULL;

    public function reload($flags)
    {
        if ($flags & self::RELOAD_MEMBERS) {
            $nb_chambers    = $this->parseInt('nb_chambers', 6);
            try {
                $this->_roulette = new Roulette($nb_chambers);                
            }
            catch (ERouletteAtLeastTwoChambers $e) {
                throw new Exception($this->_translator->gettext(
                    'There must be at least 2 chambers'));
            }
        }

        if ($flags & self::RELOAD_HANDLERS) {
            $registry   = $this->_connection->getModule('TriggerRegistry',
                            ErebotConnection::MODULE_BY_NAME);
            $matchAny  = ErebotUtils::getVStatic($registry, 'MATCH_ANY');

            if (!($flags & self::RELOAD_INIT)) {
                $this->_connection->removeEventHandler($this->_handler);
                $registry->freeTriggers($this->_trigger, $matchAny);
            }

            $trigger        = $this->parseString('trigger', 'roulette');
            $this->_trigger  = $registry->registerTriggers($trigger, $matchAny);
            if ($this->_trigger === NULL)
                throw new Exception($this->_translator->gettext(
                    'Could not register Roulette trigger'));

            $targets    = new ErebotEventTargets(ErebotEventTargets::ORDER_ALLOW_DENY);
            $targets->addRule(
                ErebotEventTargets::TYPE_ALLOW,
                ErebotEventTargets::MATCH_ALL,
                ErebotEventTargets::MATCH_CHANNEL);

            $filter         = new ErebotTextFilter(
                                    $this->_mainCfg,
                                    ErebotTextFilter::TYPE_STATIC,
                                    $trigger, TRUE);
            $this->_handler  = new ErebotEventHandler(
                                                array($this, 'handleRoulette'),
                                                'ErebotEventTextChan',
                                                $targets, $filter);
            $this->_connection->addEventHandler($this->_handler);
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
        $trigger    = $this->parseString('trigger', 'roulette');

        $bot        =&  $this->_connection->getBot();
        $moduleName =   strtolower($bot->moduleClassToName($this));
        $nbArgs     =   count($words);

        if ($nbArgs == 1 && $words[0] == $moduleName) {
            $msg = $translator->gettext('
Provides the <b><var name="trigger"/></b> command which makes you play
in the russian roulette game.
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
Makes you press the trigger of the russian roulette gun.
");
            $formatter = new ErebotStyling($msg, $translator);
            $formatter->assign('trigger', $trigger);
            $this->sendMessage($target, $formatter->render());
            return TRUE;
        }
    }

    public function handleRoulette(iErebotEvent &$event)
    {
        $nick       = $event->getSource();
        $chan       = $event->getChan();
        $action     = NULL;
        $chamber    = $this->_roulette->getPassedChambersCount()+1;
        $total      = $this->_roulette->getChambersCount();
        $translator = $this->getTranslator($chan);

        try {
            $state = $this->_roulette->next($nick);
        }
        catch (ERouletteCannotGoTwiceInARow $e) {
            $this->sendMessage($chan, $translator->gettext(
                'You cannot go twice in a row'));
            return $event->preventDefault(TRUE);
        }

        switch ($state) {
            case Roulette::STATE_RELOAD:
                $message    = $translator->gettext('spins the cylinder');
                $tpl        = new ErebotStyling($message, $translator);
                $action     = $tpl->render();
                // Fall through
            case Roulette::STATE_NORMAL:
                $message = $translator->gettext('+click+');
                $tpl = new ErebotStyling($message, $translator);
                $ending = $tpl->render();
                break;

            case Roulette::STATE_BANG:
                $message    = $translator->gettext('<b>*BANG*</b>');
                $tpl        = new ErebotStyling($message, $translator);
                $ending     = $tpl->render();

                $message    = $translator->gettext('reloads');
                $tpl        = new ErebotStyling($message, $translator);
                $action     = $tpl->render();
                break;
        }

        $message = $translator->gettext('<var name="nick"/>: chamber '.
            '<var name="chamber"/> of <var name="total"/> =&gt; '.
            '<var name="message"/>');

        $tpl = new ErebotStyling($message, $translator);
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

