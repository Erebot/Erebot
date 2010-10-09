<?php

ErebotUtils::incl('../../src/styling.php');
ErebotUtils::incl('src/TvRetriever.php');

class   ErebotModule_Tv
extends ErebotModuleBase
{
    protected $tv;
    protected $default_mapping = 'hertzien';
    protected $custom_mappings =    array(
                                        'hertzien' => array(
                                            'TF 1',
                                            'France 2',
                                            'France 3',
                                            'Canal+',
                                            'France 5',
                                            'Arte',
                                            'M6',
                                        ),

                                        'tnt' => array(
                                            'Direct 8',
                                            'W 9',
                                            'TMC',
                                            'NT 1',
                                            'NRJ 12',
                                            'France 4',
                                            'La Chaîne parlementaire',
                                            'BFM TV',
                                            'i Télé',
                                            'Virgin 17',
                                            'Gulli',
                                        ),
                                    );

    const TIME_12H_FORMAT = '/^(0?[0-9]|1[0-2])[:h\.]?([0-5][0-9])?([ap]m)$/i';
    const TIME_24H_FORMAT = '/^([0-1]?[0-9]|2[0-3])[:h\.]?([0-5][0-9])?$/i';

    public function reload($flags)
    {
        if ($flags & self::RELOAD_METADATA) {
            $this->addMetadata(self::META_DEPENDS, 'TriggerRegistry');
            $this->addMetadata(self::META_DEPENDS, 'Helper');
        }

        if ($flags & self::RELOAD_MEMBERS) {
            $this->tv = TvRetriever::getInstance();
        }

        if ($flags & self::RELOAD_HANDLERS) {
            $registry   = $this->connection->getModule('TriggerRegistry',
                                ErebotConnection::MODULE_BY_NAME);
            $match_any  = ErebotUtils::getVStatic($registry, 'MATCH_ANY');

            if (!($flags & self::RELOAD_INIT)) {
                $this->connection->removeEventHandler($this->handler);
                $registry->freeTriggers($this->trigger, $match_any);
            }

            $this->registerHelpMethod(array($this, 'getHelp'));
            $trigger        = $this->parseString('trigger', 'tv');
            $this->trigger  = $registry->registerTriggers($trigger, $match_any);
            if ($this->trigger === NULL)
                throw new Exception($this->translator->gettext(
                    'Could not register TV trigger'));

            $filter         = new ErebotTextFilter();
            $filter->addPattern(ErebotTextFilter::TYPE_STATIC, $trigger, TRUE);
            $filter->addPattern(ErebotTextFilter::TYPE_WILDCARD,
                                $trigger.' *', TRUE);

            $this->handler  = new ErebotEventHandler(
                                    array($this, 'handleTv'),
                                    'iErebotEventMessageText',
                                    NULL, $filter);
            $this->connection->addEventHandler($this->handler);
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

        $bot        =&  $this->connection->getBot();
        $moduleName =   strtolower($bot->moduleClassToName($this));
        $nbArgs     =   count($words);

        if ($nbArgs == 1 && $words[0] == $moduleName) {
            $msg = $translator->gettext('
Provides the <b><var name="trigger"/></b> command which retrieves
information about TV schedules off the internet.
');
            $formatter = new ErebotStyling($msg);
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
            $formatter = new ErebotStyling($msg);
            $formatter->assign('trigger', $trigger);
            $this->sendMessage($target, $formatter->render());

            $msg = $translator->gettext("If none is given, the default group ".
                    "(<b><var name='default'/></b>) is used. The following ".
                    "groups are available: <for from='groups' key='group' ".
                    "item='dummy'><b><var name='group'/></b></for>.");
            $formatter = new ErebotStyling($msg);
            $formatter->assign('default', $this->default_mapping);
            $formatter->assign('groups', $this->custom_mappings);
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

        if (rtrim($channels) == '')
            $channels   = $this->custom_mappings[$this->default_mapping];
        else if (isset($this->custom_mappings[$channels]))
            $channels   = $this->custom_mappings[$channels];
        else
            $channels   = explode(',', $channels);

        $ids    = array_filter(
            array_map(
            array($this->tv, 'getIdFromChannel'),
            $channels));
        $infos  = $this->tv->getChannelsData($timestamp, $ids);

        $programs = array();
        foreach ($infos as $channel => $data) {
            $start      = substr(rtrim($data['Date_Debut']), -8, -3);
            $end        = substr(rtrim($data['Date_Fin']), -8, -3);
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

            $formatter = new ErebotStyling($msg);
            $formatter->assign('date',      date('r', $timestamp));
            $formatter->assign('programs',  $programs);
            $this->sendMessage($target, $formatter->render());
        }
    }
}

?>
