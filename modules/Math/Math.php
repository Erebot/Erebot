<?php

ErebotUtils::incl('../../src/styling.php');
ErebotUtils::incl('src/lexer.php');

class   ErebotModule_math
extends ErebotModuleBase
{
    protected $trigger;
    protected $handler;

    public function reload($flags)
    {
        if ($flags & self::RELOAD_METADATA) {
            $this->addMetadata(self::META_DEPENDS, 'TriggerRegistry');
            $this->addMetadata(self::META_DEPENDS, 'Helper');
        }

        if (!($flags & self::RELOAD_INIT)) {
            $registry   = $this->connection->getModule('TriggerRegistry',
                                ErebotConnection::MODULE_BY_NAME);
            $match_any  = ErebotUtils::getVStatic($registry, 'MATCH_ANY');
            $this->connection->removeEventHandler($this->handler);
            $registry->freeTriggers($this->trigger, $match_any);
        }

        if ($flags & self::RELOAD_HANDLERS) {
            $registry   = $this->connection->getModule('TriggerRegistry',
                                ErebotConnection::MODULE_BY_NAME);
            $match_any  = ErebotUtils::getVStatic($registry, 'MATCH_ANY');

            $trigger        = $this->parseString('trigger', 'math');
            $this->trigger  = $registry->registerTriggers($trigger, $match_any);
            if ($this->trigger === NULL) {
                $translator = $this->getTranslator(FALSE);
                throw new Exception($translator->gettext(
                    'Could not register Math trigger'));
            }

            $filter         = new ErebotTextFilter(
                                    $this->mainCfg,
                                    ErebotTextFilter::TYPE_WILDCARD,
                                    $trigger.' *', TRUE);
            $this->handler  = new ErebotEventHandler(
                                    array($this, 'handleMath'),
                                    'iErebotEventMessageText',
                                    NULL, $filter);
            $this->connection->addEventHandler($this->handler);
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
        $trigger    = $this->parseString('trigger', 'math');

        $bot        =&  $this->connection->getBot();
        $moduleName =   strtolower($bot->moduleClassToName($this));
        $nbArgs     =   count($words);

        if ($nbArgs == 1 && $words[0] == $moduleName) {
            $msg = $translator->gettext('
Provides the <b><var name="trigger"/></b> command which allows you
to submit formulae to the bot for computation.
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
<b>Usage:</b> !<var name='trigger'/> &lt;<u>formula</u>&gt;.
Computes the given formula and displays the result.
The four basic operators (+, -, *, /), parenthesis, exponentiation (^)
and modules (%) are supported.
");
            $formatter = new ErebotStyling($msg, $translator);
            $formatter->assign('trigger', $trigger);
            $this->sendMessage($target, $formatter->render());

            return TRUE;
        }
    }

    public function handleMath(iErebotEventMessageText &$event)
    {
        if ($event instanceof iErebotEventPrivate) {
            $target = $event->getSource();
            $chan   = NULL;
        }
        else
            $target = $chan = $event->getChan();

        $formula    = ErebotUtils::gettok($event->getText(), 1);
        $translator = $this->getTranslator($chan);

        try {
            $fp     = new MathLexer($formula);
            $msg    = '<var name="formula"/> = <b><var name="result"/></b>';
            $tpl    = new ErebotStyling($msg, $translator);
            $tpl->assign('formula', $formula);
            $tpl->assign('result',  $fp->getResult());
            $this->sendMessage($target, $tpl->render());
        }
        catch (EMathDivisionByZero $e) {
            $this->sendMessage($target,
                $translator->gettext('Division by zero'));
        }
        catch (EMathExponentTooBig $e) {
            $this->sendMessage($target,
                $translator->gettext('Exponent is too big for computation'));
        }
        catch (EMathNegativeExponent $e) {
            $this->sendMessage($target,
                $translator->gettext('^ is undefined for negative exponents'));
        }
        catch (EMathNoModulusOnReals $e) {
            $this->sendMessage($target,
                $translator->gettext('% is undefined on real numbers'));
        }
        catch (EMathSyntaxError $e) {
            $this->sendMessage($target,
                $translator->gettext('Syntax error'));
        }
    }
}

?>
