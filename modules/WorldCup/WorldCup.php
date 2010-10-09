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

class   ErebotModule_WorldCup
extends ErebotModuleBase
{
    static protected $_metadata = array(
        'requires'  =>  array('TriggerRegistry'),
    );
    protected $_matches;
    protected $_channels;
    protected $_handlers;
    protected $_trigger;

    const LIST_URL = "http://coupe-du-monde.tf1.fr/matchcast-%d/";

    public function reload($flags)
    {
        if ($flags & self::RELOAD_MEMBERS) {
            $this->_channels = array();
            $this->_matches  = array();
        }

        if ($flags & self::RELOAD_HANDLERS) {
            $delay_get = $this->parseInt('check_new_matches',   180);
            $delay_send= $this->parseInt('send_notifications',  30);

            $registry   = $this->_connection->getModule('TriggerRegistry',
                                ErebotConnection::MODULE_BY_NAME);
            $matchAny  = ErebotUtils::getVStatic($registry, 'MATCH_ANY');

            if (!($flags & self::RELOAD_INIT)) {
                foreach ($this->_handlers as $handler)
                    $this->_connection->removeEventHandler($handler);
                $registry->freeTriggers($this->_trigger, $matchAny);
            }
            else {
                $timer_get = new ErebotTimer(array($this, 'getListOfLiveMatches'), $delay_get, TRUE);
                $timer_send= new ErebotTimer(array($this, 'sendEvents'), $delay_send, TRUE);
                $this->addTimer($timer_get);
                $this->addTimer($timer_send);
            }

            $trigger        = $this->parseString('trigger', 'worldcup');
            $this->_trigger  = $registry->registerTriggers($trigger, $matchAny);
            if ($this->_trigger === NULL)
                throw new Exception($this->_translator->gettext(
                    'Could not register World Cup trigger'));

            $filter         = new ErebotTextFilter($this->_mainCfg);
            $filter->addPattern(ErebotTextFilter::TYPE_STATIC, $trigger.' on', TRUE);

            $this->_handlers['add'] = new ErebotEventHandler(
                                        array($this, 'handleAdd'),
                                        'ErebotEventTextChan',
                                        NULL, $filter);
            $this->_connection->addEventHandler($this->_handlers['add']);

            $filter         = new ErebotTextFilter($this->_mainCfg);
            $filter->addPattern(ErebotTextFilter::TYPE_STATIC, $trigger.' off', TRUE);

            $this->_handlers['del'] = new ErebotEventHandler(
                                        array($this, 'handleRemove'),
                                        'ErebotEventTextChan',
                                        NULL, $filter);
            $this->_connection->addEventHandler($this->_handlers['del']);
        }
    }

    public function getListOfLiveMatches(iErebotTimer &$timer)
    {
        $logging    =&  ErebotLogging::getInstance();
        $logger     =   $logging->getLogger(__FILE__);
        $logger->info('Retrieving the list of currently airing matches');

        for ($i = 1; ; $i++) {
            $cast = sprintf(self::LIST_URL, $i);
            $source     = file_get_contents($cast, 0);
            $internal   = libxml_use_internal_errors(TRUE);

            $domdoc     = new DOMDocument();
            $domdoc->validateOnParse        = FALSE;
            $domdoc->preserveWhitespace     = FALSE;
            $domdoc->strictErrorChecking    = FALSE;
            $domdoc->substituteEntities     = FALSE;
            $domdoc->resolveExternals       = FALSE;
            $domdoc->recover                = TRUE;
            $domdoc->loadHTML($source);

            libxml_clear_errors();
            libxml_use_internal_errors($internal);

            $frames     = $domdoc->getElementsByTagName('iframe');
            $len = $frames->length;
            if (!$len)
                break;

            $live = NULL;
            for ($j = 0; $j < $len; $j++) {
                $frame = $frames->item($j);
                $valueNode  = $frame->attributes->getNamedItem('src');
                if ($valueNode === NULL)
                    continue;

                $value      = (string) $valueNode->nodeValue;
                if (strpos($value, 'livematchcast') !== FALSE) {
                    $live = $value;
                    break;
                }
            }

            $strong = $domdoc->getElementsByTagName('strong')->item(0);
            if ($strong === NULL || $live === NULL) {
                while (TRUE) {
                    if (!isset($this->_matches[$cast]))
                        break;
                    unset($this->_matches[$cast]);
                    $i++;
                    $cast = sprintf(self::LIST_URL, $i);
                }
                break;
            }

            if (isset($this->_matches[$cast])) {
                $logger->info('Still airing: %s', array(
                    $this->_matches[$cast]['label']));
                continue;
            }

            $this->_matches[$cast] = array(
                'last_check'    => 0,
                'label'         => (string) $strong->nodeValue,
                'live_url'      => $this->getRealLiveURL($live),
            );

            $logger->info('New match: %s', array(
                $this->_matches[$cast]['label']));
        }

        $logger->info('Done getting the list of currently airing matches');
    }

    protected function getRealLiveURL($live)
    {
        $lines = file($live, 0);
        $real = NULL;
        foreach ($lines as $line) {
            if (strpos($line, 'var urlLiveMatchCast') !== FALSE) {
                $tokens = explode('"', $line);
                return "http://tf1.eurosport.fr".$tokens[1];
            }
        }
        
        return $real;
    }

    protected function getLatestEvents(&$infos)
    {
        $logging    =&  ErebotLogging::getInstance();
        $logger     =   $logging->getLogger(__FILE__);
        $logger->info('Retrieving events for: %s', array($infos['label']));

        $source     = file_get_contents($infos['live_url'], 0);
        $sxml       = new SimpleXMLElement($source);
        $res = array();

        $highlights = $sxml->commentlist->highlight;
        $len = count($highlights);
        $logger->info('There are %(events)d events for %(match)s', array(
            'events'    => $len,
            'match'     => $infos['label'],
        ));

        $last_check = $infos['last_check'];
        $infos['last_check'] = min($len, $last_check + 3);

        for ($i = $last_check; $i < $infos['last_check']; $i++) {
            $highlight = $highlights[$len - $i - 1];
            $res[] = array(
                'time'      => (string) $highlight->time,
                'comment'   => (string) $highlight->name,
                'round'     => (string) $highlight->round->name,
            );
        }

        return $res;
    }

    public function sendEvents(iErebotTimer &$timer)
    {
        if (!count($this->_channels))
            return;

        $logging    =&  ErebotLogging::getInstance();
        $logger     =   $logging->getLogger(__FILE__);
        $logger->info('Sending events');

        $events = array();
        foreach ($this->_matches as &$match) {
            $events = $this->getLatestEvents($match);
            $logger->info('Got %(count)d events for %(match)s', array(
                'count' => count($events),
                'match' => $match['label'],
            ));

            foreach ($events as $event) {
                foreach ($this->_channels as $channel) {
                    $this->sendMessage($channel,
                        "[".$event['round']."] (".$match['label'].") ".
                        $event['time']." -- ".$event['comment']
                    );
                }
            }
        }
        unset($match);

        $logger->info('Done sending events');
    }

    public function handleAdd(ErebotEventTextChan &$event)
    {
        $chan = $event->getChan();
        $translator = $this->getTranslator($chan);

        if (!in_array($chan, $this->_channels)) {
            $this->_channels[] = $chan;
            $msg = $translator->gettext('Alright, I\'ll keep you posted on currently airing matches.');
        }
        else
            $msg = $translator->gettext('This channel already receives notifications on the matches.');
        $this->sendMessage($chan, $msg);
    }

    public function handleRemove(ErebotEventTextChan &$event)
    {
        $chan = $event->getChan();
        $translator = $this->getTranslator($chan);

        $key = array_search($chan, $this->_channels);
        if ($key !== FALSE) {
            unset($this->_channels[$key]);
            $msg = $translator->gettext('Alright, you\'ll not receive notifications anymore.');
        }
        else
            $msg = $translator->gettext('Hmm... this chan didn\'t subscribe to notifications.');
        $this->sendMessage($chan, $msg);
    }
}

