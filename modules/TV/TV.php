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

include_once('src/styling.php');
include_once('modules/TV/src/TvRetriever.php');

class   ErebotModule_Tv
extends ErebotModuleBase
{
    static protected $_metadata = array(
        'requires'  =>  array('TriggerRegistry', 'Helper'),
    );
    protected $_tv;
    protected $_customMappings = array();
    protected $_defaultGroup = NULL;

    const TIME_12H_FORMAT = '/^(0?[0-9]|1[0-2])[:h\.]?([0-5][0-9])?([ap]m)$/i';
    const TIME_24H_FORMAT = '/^([0-1]?[0-9]|2[0-3])[:h\.]?([0-5][0-9])?$/i';

    public function reload($flags)
    {
        if ($flags & self::RELOAD_MEMBERS) {
            $class = $this->parseString('retriever_class', 'TvRetriever');
            $this->_tv = $class::getInstance();

            $config         =&  $this->_connection->getConfig($this->_channel);
            $moduleConfig   =&  $config->getModule($this->_moduleName);
            $group_filter = create_function('$a',
                'return !strncasecmp($a, "group_", 6);');
            $groups = array_filter($moduleConfig->getParamsNames());
            $this->_customMappings = array();

            foreach ($groups as $param) {
                $group = substr($param, 6);
                $chans = $this->parseString($param);
                $this->_customMappings[$group] =
                    array_map('trim', explode(',', $chans));
            }

            try {
                $this->_defaultGroup = $this->parseString('default_group');
                if (!isset($this->_customMappings[$this->_defaultGroup]))
                    $this->_defaultGroup = NULL;
            }
            catch (EErebotNotFound $e) {
                $this->_defaultGroup = NULL;
            }
        }

        if ($flags & self::RELOAD_HANDLERS) {
            $registry   = $this->_connection->getModule('TriggerRegistry',
                                ErebotConnection::MODULE_BY_NAME);
            $matchAny = ErebotUtils::getVStatic($registry, 'MATCH_ANY');

            if (!($flags & self::RELOAD_INIT)) {
                $this->_connection->removeEventHandler($this->_handler);
                $registry->freeTriggers($this->_trigger, $matchAny);
            }

            $this->registerHelpMethod(array($this, 'getHelp'));
            $trigger        = $this->parseString('trigger', 'tv');
            $this->_trigger = $registry->registerTriggers($trigger, $matchAny);
            if ($this->_trigger === NULL)
                throw new Exception($this->_translator->gettext(
                    'Could not register TV trigger'));

            $filter         = new ErebotTextFilter($this->_mainCfg);
            $filter->addPattern(ErebotTextFilter::TYPE_STATIC, $trigger, TRUE);
            $filter->addPattern(ErebotTextFilter::TYPE_WILDCARD,
                                $trigger.' *', TRUE);

            $this->_handler = new ErebotEventHandler(
                                    array($this, 'handleTv'),
                                    'iErebotEventMessageText',
                                    NULL, $filter);
            $this->_connection->addEventHandler($this->_handler);
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
        $trigger    = $this->parseString('trigger', 'tv');

        $bot        =&  $this->_connection->getBot();
        $moduleName =   strtolower($bot->moduleClassToName($this));
        $nbArgs     =   count($words);

        if ($nbArgs == 1 && $words[0] == $moduleName) {
            $msg = $translator->gettext('
Provides the <b><var name="trigger"/></b> command which retrieves
information about TV schedules off the internet.
');
            $formatter = new ErebotStyling($msg, $translator);
            $formatter->assign('trigger', $trigger);
            $this->sendMessage($target, $formatter->render());
            return TRUE;
        }

        if (count($nbArgs) < 2)
            return FALSE;

        if ($words[1] == $trigger) {
            $msg = $translator->gettext("
<b>Usage:</b> !<var name='trigger'/> [<u>time</u>] [<u>channels</u>].
Returns TV schedules for the given channels at the given time.
[<u>time</u>] can be expressed using either 12h or 24h notation.
[<u>channels</u>] can be a single channel name, a list of channels
(separated by commas) or one of the pre-defined groups of channels.
");
            $formatter = new ErebotStyling($msg, $translator);
            $formatter->assign('trigger', $trigger);
            $this->sendMessage($target, $formatter->render());

            $msg = $translator->gettext("If none is given, the default group ".
                    "(<b><var name='default'/></b>) is used. The following ".
                    "groups are available: <for from='groups' key='group' ".
                    "item='dummy'><b><var name='group'/></b></for>.");
            $formatter = new ErebotStyling($msg, $translator);
            $formatter->assign('default', $this->_defaultGroup);
            $formatter->assign('groups', $this->_customMappings);
            $this->sendMessage($target, $formatter->render());

            return TRUE;
        }
    }

    public function handleTv(iErebotEventMessageText &$event)
    {
        if ($event instanceof iErebotEventPrivate) {
            $target = $event->getSource();
            $chan   = NULL;
        }
        else
            $target = $chan = $event->getChan();

        $time       = ErebotUtils::gettok($event->getText(), 1, 1);
        $getdate    = getdate();
        $tomorrow   = getdate(strtotime('midnight +1 day'));
        $translator = $this->getTranslator($chan);

        do {
            $result     = preg_match(self::TIME_12H_FORMAT, $time, $matches);
            if ($result) {
                $pm         = !strcasecmp($matches[3], 'pm');
                $hours      = ((int) $matches[1]) + ($pm ? 12 : 0);
                $minutes    = isset($matches[2]) ? (int) $matches[2] : 0;
                break;
            }

            $result     = preg_match(self::TIME_24H_FORMAT, $time, $matches);
            if ($result) {
                $hours      = (int) $matches[1];
                $minutes    = isset($matches[2]) ? (int) $matches[2] : 0;
                break;
            }

            $hours      = $getdate['hours'];
            $minutes    = $getdate['minutes'];
        } while (0);

        if ($hours < $getdate['hours'] ||
            ($hours == $getdate['hours'] && $minutes < $getdate['minutes'])) {
            $getdate['mday']    = $tomorrow['mday'];
            $getdate['mon']     = $tomorrow['mon'];
            $getdate['year']    = $tomorrow['year'];
        }

        $timestamp  = mktime($hours, $minutes, 0, $getdate['mon'],
                            $getdate['mday'], $getdate['year']);
        $channels   = strtolower(ErebotUtils::gettok(
                        $event->getText(), $result ? 2 : 1));

        if (rtrim($channels) == '') {
            if ($this->_defaultGroup)
                $channels   = $this->_customMappings[$this->_defaultGroup];
            else {
                $msg = $translator->gettext('No channel given and no default.');
                return $this->sendMessage($target, $msg);
            }
        }
        else if (isset($this->_customMappings[$channels]))
            $channels   = $this->_customMappings[$channels];
        else
            $channels   = explode(',', $channels);

        $ids    = array_filter(
            array_map(
            array($this->_tv, 'getIdFromChannel'),
            $channels));
        $infos  = $this->_tv->getChannelsData($timestamp, $ids);

        $programs = array();
        foreach ($infos as $channel => $data) {
            $start      = substr($data['Date_Debut'], -8, -3);
            $end        = substr($data['Date_Fin'], -8, -3);
            $programs[$channel]   = sprintf('%s (%s - %s)',
                                        $data['Titre'], $start, $end);
        }

        if (!count($programs))
            $this->sendMessage($target,
                $translator->gettext('No such channel(s)'));
        else {
            $msg = $translator->gettext('TV programs for <u><var name="date"/>'.
                        '</u>: <for from="programs" key="channel" item="'.
                        'timetable" separator=" - "><b><var name="channel"'.
                        '/></b>: <var name="timetable"/></for>');

            $formatter = new ErebotStyling($msg, $translator);
            $formatter->assign('date',      date('r', $timestamp));
            $formatter->assign('programs',  $programs);
            $this->sendMessage($target, $formatter->render());
        }
    }
}

