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
        $registry   = $this->connection->getModule('TriggerRegistry', ErebotConnection::MODULE_BY_NAME);
        $match_any  = ErebotUtils::getVStatic($registry, 'MATCH_ANY');

        if (!($flags & self::RELOAD_INIT)) {
            $this->connection->removeEventHandler($this->handler);
            $registry->freeTriggers($this->trigger, $match_any);
        }

        if ($flags & self::RELOAD_HANDLERS) {
            $trigger        = $this->parseString('trigger', 'math');
            $this->trigger  = $registry->registerTriggers($trigger, $match_any);
            if ($this->trigger === NULL) {
                $translator = $this->getTranslator(FALSE);
                throw new Exception($translator->gettext(
                    'Could not register Math trigger'));
            }

            $filter         = new ErebotTextFilter(ErebotTextFilter::TYPE_WILDCARD, $trigger.' *', TRUE);
            $this->handler  = new ErebotEventHandler(
                                    array($this, 'handleMath'),
                                    ErebotEvent::ON_TEXT,
                                    NULL, $filter);
            $this->connection->addEventHandler($this->handler);
        }
    }

    public function handleMath(ErebotEvent &$event)
    {
        $formula    = ErebotUtils::gettok($event->getText(), 1);
        $target     = $event->getTarget();
        $translator = $this->getTranslator($event->getChan());

        try {
            $fp     = new MathLexer($formula);
            $msg    = '<var name="formula"/> = <b><var name="result"/></b>';
            $tpl    = new ErebotStyling($msg);
            $tpl->assign('formula', $formula);
            $tpl->assign('result',  $fp->getResult());
            $this->sendMessage($target, $tpl->render());
        }
        catch (EMathDivisionByZero $e) {
            $this->sendMessage($target, $translator->gettext('Division by zero'));
        }
        catch (EMathExponentTooBig $e) {
            $this->sendMessage($target, $translator->gettext('Exponent is too big for computation'));
        }
        catch (EMathNegativeExponent $e) {
            $this->sendMessage($target, $translator->gettext('Operator ^ undefined with negative exponents'));
        }
        catch (EMathNoModulusOnReals $e) {
            $this->sendMessage($target, $translator->gettext('Operator % undefined on real numbers'));
        }
        catch (EMathSyntaxError $e) {
            $this->sendMessage($target, $translator->gettext('Syntax error'));
        }
    }
}

?>
