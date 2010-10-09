<?php

ErebotUtils::incl('../../src/styling.php');

class   ErebotModule_WorldCup
extends ErebotModuleBase
{
    protected $matches;
    protected $channels;
    protected $handlers;
    protected $trigger;

    const LIST_URL = "http://coupe-du-monde.tf1.fr/matchcast-%d/";

    public function reload($flags)
    {
        if ($flags & self::RELOAD_MEMBERS) {
            $this->channels = array();
            $this->matches  = array();
        }

        if ($flags & self::RELOAD_METADATA) {
            $this->addMetadata(self::META_DEPENDS, 'TriggerRegistry');
        }

        if ($flags & self::RELOAD_HANDLERS) {
            $delay_get = $this->parseInt('check_new_matches',   180);
            $delay_send= $this->parseInt('send_notifications',  30);

            $registry   = $this->connection->getModule('TriggerRegistry',
                                ErebotConnection::MODULE_BY_NAME);
            $match_any  = ErebotUtils::getVStatic($registry, 'MATCH_ANY');

            if (!($flags & self::RELOAD_INIT)) {
                foreach ($this->handlers as $handler)
                    $this->connection->removeEventHandler($handler);
                $registry->freeTriggers($this->trigger, $match_any);
            }
            else {
                $timer_get = new ErebotTimer(array($this, 'getListOfLiveMatches'), $delay_get, TRUE);
                $timer_send= new ErebotTimer(array($this, 'sendEvents'), $delay_send, TRUE);
                $this->addTimer($timer_get);
                $this->addTimer($timer_send);
            }

            $trigger        = $this->parseString('trigger', 'worldcup');
            $this->trigger  = $registry->registerTriggers($trigger, $match_any);
            if ($this->trigger === NULL)
                throw new Exception($this->translator->gettext(
                    'Could not register World Cup trigger'));

            $filter         = new ErebotTextFilter();
            $filter->addPattern(ErebotTextFilter::TYPE_STATIC, $trigger.' on', TRUE);

            $this->handlers['add']  = new ErebotEventHandler(
                                        array($this, 'handleAdd'),
                                        'ErebotEventTextChan',
                                        NULL, $filter);
            $this->connection->addEventHandler($this->handlers['add']);

            $filter         = new ErebotTextFilter();
            $filter->addPattern(ErebotTextFilter::TYPE_STATIC, $trigger.' off', TRUE);

            $this->handlers['del']  = new ErebotEventHandler(
                                        array($this, 'handleRemove'),
                                        'ErebotEventTextChan',
                                        NULL, $filter);
            $this->connection->addEventHandler($this->handlers['del']);
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
                    if (!isset($this->matches[$cast]))
                        break;
                    unset($this->matches[$cast]);
                    $i++;
                    $cast = sprintf(self::LIST_URL, $i);
                }
                break;
            }

            if (isset($this->matches[$cast])) {
                $logger->info('Still airing: %s', array(
                    $this->matches[$cast]['label']));
                continue;
            }

            $this->matches[$cast] = array(
                'last_check'    => 0,
                'label'         => (string) $strong->nodeValue,
                'live_url'      => $this->getRealLiveURL($live),
            );

            $logger->info('New match: %s', array(
                $this->matches[$cast]['label']));
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
        if (!count($this->channels))
            return;

        $logging    =&  ErebotLogging::getInstance();
        $logger     =   $logging->getLogger(__FILE__);
        $logger->info('Sending events');

        $events = array();
        foreach ($this->matches as &$match) {
            $events = $this->getLatestEvents($match);
            $logger->info('Got %(count)d events for %(match)s', array(
                'count' => count($events),
                'match' => $match['label'],
            ));

            foreach ($events as $event) {
                foreach ($this->channels as $channel) {
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

        if (!in_array($chan, $this->channels)) {
            $this->channels[] = $chan;
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

        $key = array_search($chan, $this->channels);
        if ($key !== FALSE) {
            unset($this->channels[$key]);
            $msg = $translator->gettext('Alright, you\'ll not receive notifications anymore.');
        }
        else
            $msg = $translator->gettext('Hmm... this chan didn\'t subscribe to notifications.');
        $this->sendMessage($chan, $msg);
    }
}

?>
