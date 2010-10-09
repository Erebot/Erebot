<?php
ErebotUtils::incl('../../src/styling.php');

class   ErebotModule_GoF
extends ErebotModuleBase
{
    protected $chans;
    protected $creator;

    const COLOR_RED                 = '00,04';
    const COLOR_GREEN               = '00,03';
    const COLOR_YELLOW              = '01,08';

    const MOVE_SINGLE               = 'a single';
    const MOVE_PAIR                 = 'a pair';
    const MOVE_TRIO                 = 'three of a kind';
    const MOVE_STRAIGHT             = 'a straight';
    const MOVE_FLUSH                = 'a flush';
    const MOVE_FULL_HOUSE           = 'a full house';
    const MOVE_STRAIGHT_FLUSH       = 'a straight flush';
    const MOVE_GANG                 = 'a gang';

    const DIR_COUNTERCLOCKWISE      = FALSE;
    const DIR_CLOCKWISE             = TRUE;

    public function reload($flags)
    {
        if ($flags & self::RELOAD_METADATA) {
            $this->addMetadata(self::META_DEPENDS, 'TriggerRegistry');
            $this->addMetadata(self::META_DEPENDS, 'NickTracker');
        }

        if ($flags & self::RELOAD_MEMBERS) {
            $this->chans    = array();
        }

        if ($flags & self::RELOAD_HANDLERS) {
            $registry   = $this->connection->getModule('TriggerRegistry', ErebotConnection::MODULE_BY_NAME);
            $match_any  = ErebotUtils::getVStatic($registry, 'MATCH_ANY');

            if (!($flags & self::RELOAD_INIT)) {
                
                $this->connection->removeEventHandler($this->creator['handler']);
                $registry->freeTriggers($this->creator['trigger'], $match_any);
            }

            $trigger_create             = $this->parseString('trigger_create', 'gof');
            $this->creator['trigger']   = $registry->registerTriggers($trigger_create, $match_any);
            if ($this->creator['trigger'] === NULL) {
                $translator = $this->getTranslator(FALSE);
                throw new Exception($translator->gettext(
                    'Could not register Gang of Four creation trigger'));
            }

            $filter     = new ErebotTextFilter();
            $filter->addPattern(ErebotTextFilter::TYPE_STATIC, $trigger_create, TRUE);
            $this->creator['handler']   =   new ErebotEventHandler(
                                                array($this, 'handleCreate'),
                                                'ErebotEventTextChan',
                                                NULL, $filter);
            $this->connection->addEventHandler($this->creator['handler']);
        }
    }

    protected function getToken($chan, $nick)
    {
        if (!isset($this->chans[$chan]['players']))
            return NULL;

        $tracker = $this->getNickTracker();
        foreach (array_keys($this->chans[$chan]['players']) as $player) {
            if ($tracker->getNick($player) == $nick)
                return $player;
        }
        return NULL;
    }

    protected function & getNickTracker()
    {
        return $this->connection->getModule('NickTracker',
                    ErebotConnection::MODULE_BY_NAME);
    }

    protected function getLogo()
    {
        return  ErebotStyling::CODE_BOLD.
                ErebotStyling::CODE_COLOR.'03Gang '.
                ErebotStyling::CODE_COLOR.'08of '.
                ErebotStyling::CODE_COLOR.'04Four'.
                ErebotStyling::CODE_COLOR.
                ErebotStyling::CODE_BOLD;
    }

    protected function getColoredCard($color, $text)
    {
        $text       = ' '.$text.' ';
        $colorCodes =   array(
                            'r' => self::COLOR_RED,
                            'g' => self::COLOR_GREEN,
                            'y' => self::COLOR_YELLOW,
                        );

        if (!isset($colorCodes[$color]))
            throw new Exception('Unknown color!');

        return  ErebotStyling::CODE_COLOR.$colorCodes[$color].
                ErebotStyling::CODE_BOLD.$text.
                ErebotStyling::CODE_BOLD.
                ErebotStyling::CODE_COLOR;
    }

    protected function wildify($text)
    {
        $order  =   array(
                        self::COLOR_RED,
                        self::COLOR_GREEN,
                        self::COLOR_YELLOW,
                    );
        $text   = ' '.$text.' ';
        $len    = strlen($text);
        $output = ErebotStyling::CODE_BOLD;
        $nbCol  = count($order);

        for ($i = 0; $i < $len; $i++)
            $output .=  ErebotStyling::CODE_COLOR.
                        $order[$i % $nbCol].
                        $text[$i];
        $output .=  ErebotStyling::CODE_COLOR.
                    ErebotStyling::CODE_BOLD;
        return $output;
    }

    protected function cleanup($chan)
    {
        $registry   =&  $this->connection->getModule('TriggerRegistry', ErebotConnection::MODULE_BY_NAME);
        $tracker    =&  $this->getNickTracker();
        $infos      =&  $this->chans[$chan];

        foreach ($infos['players'] as $token => &$data)
            $tracker->stopTracking($token);
        unset($data);

        $tracker->stopTracking($infos['admin']);

        foreach ($infos['handlers'] as &$handler)
            $this->connection->removeEventHandler($handler);
        unset($handler);

        $registry->freeTriggers($infos['triggers_token'], $chan);
        unset($infos);
        unset($this->chans[$chan]);
    }

    protected function prepareNextTurn($chan)
    {
        $tracker    =&  $this->getNickTracker();
        $infos      =&  $this->chans[$chan];
        $last       =   array_shift($infos['order']);
        array_push($infos['order'], $last);

        $next       = reset($infos['order']);
        if ($next == $infos['leader']) {
            $infos['discard'] = NULL;
            $this->sendCommand('PRIVMSG '.$chan.' :No player dared to raise the heat! '.
                                'You can now start a new combination '.
                                ErebotStyling::sprintf('%b', $tracker->getNick($next)).' :)');
        }

        $this->showTurn($chan, NULL);
        $this->sendCommand('PRIVMSG '.$tracker->getNick($next).' :Your cards: '.
                            $this->describeCards($infos['players'][$next]['cards']));
        return $next;
    }

    protected function getCardText($card)
    {
        if ($card == 'd')
            return $this->getColoredCard('r', 'Dragon');

        $colors = array(
            'r' => 'Red',
            'g' => 'Green',
            'y' => 'Yellow',
        );

        $texts = array(
            'd' => 'Dragon',
            'p' => 'Phoenix',
        );

        if ($card == 'm1')
            return $this->wildify('Multi 1');

        if (isset($texts[$card[1]]))
            return $this->getColoredCard($card[0], $colors[$card[0]].' '.$texts[$card[1]]);

        return $this->getColoredCard($card[0], $colors[$card[0]].' '.substr($card, 1));
    }

    protected function describeCards($cards)
    {
        $cardsTexts = array_map(array($this, 'getCardText'), $cards);
        return implode(' ', $cardsTexts);
    }

    public function dealDeck($chan)
    {
        $infos  =&  $this->chans[$chan];

        $players    = array_keys($infos['players']);
        $count      = count($players);

        foreach ($players as $player)
            unset($infos['players'][$player]['cards']);

        $starter = NULL;
        foreach ($deck as $index => $card) {
            if ($count == 3 && ($index % 4) == 3) {
                if ($card == 'm1')
                    $starter = $players[($index + 1) % 4];
                continue;
            }

            $infos['players'][$players[$index % 4]]['cards'][] = $card;
            if ($card == 'm1')
                $starter = $players[$index % 4];
        }

        foreach ($infos['players'] as &$data)
            usort($data['cards'], array($this, 'compareCards'));

        return $starter;
    }

    public function handleCreate(ErebotEvent &$event)
    {
        $tracker    =&  $this->getNickTracker();
        $nick       =   $event->getSource();
        $chan       =   $event->getChan();

        if (isset($this->chans[$chan])) {
            $admin = $this->chans[$chan]['admin'];
            $this->sendCommand('PRIVMSG '.$chan.' :A '.$this->getLogo().' managed by '.
                                ErebotStyling::sprintf('%b', $tracker->getNick($admin)).
                                ' is already running. Say "'.
                                $this->chans[$chan]['triggers']['join'].'" to join it.');
        }

        else {
            $registry   =   $this->connection->getModule('TriggerRegistry', ErebotConnection::MODULE_BY_NAME);
            $triggers   =   array(
                                'choose'        =>  $this->parseString('trigger_choose',        'co'),
                                'join'          =>  $this->parseString('trigger_join',          'jo'),
                                'pass'          =>  $this->parseString('trigger_pass',          'pa'),
                                'play'          =>  $this->parseString('trigger_play',          'pl'),
                                'show_cards'    =>  $this->parseString('trigger_show_cards',    'ca'),
                                'show_discard'  =>  $this->parseString('trigger_show_discard',  'cd'),
                                'show_order'    =>  $this->parseString('trigger_show_order',    'od'),
                                'show_scores'   =>  $this->parseString('trigger_show_scores',   'sc'),
                                'show_time'     =>  $this->parseString('trigger_show_time',     'ti'),
                                'show_turn'     =>  $this->parseString('trigger_show_turn',     'tu'),
                            );

            $token  = $registry->registerTriggers($triggers, $chan);
            if ($token === NULL) {
                $this->sendMessage($chan, 'Unable to register triggers for '.$this->getLogo().' game!');
                return $event->preventDefault(TRUE);
            }

            $this->chans[$chan]['triggers_token']   =   $token;
            $this->chans[$chan]['triggers']         =&  $triggers;
            $infos                                  =&  $this->chans[$chan];

            $filter     = new ErebotTextFilter();
            $filter->addPattern(ErebotTextFilter::TYPE_WILDCARD,    $infos['triggers']['choose'].' *', NULL);
            $infos['handlers']['choose']        =   new ErebotEventHandler(
                                                    array($this, 'handleChoose'),
                                                    'ErebotEventTextChan',
                                                    NULL, $filter);

            $filter     = new ErebotTextFilter();
            $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $infos['triggers']['join'], NULL);
            $infos['handlers']['join']          =   new ErebotEventHandler(
                                                        array($this, 'handleJoin'),
                                                        'ErebotEventTextChan',
                                                        NULL, $filter);

            $filter     = new ErebotTextFilter();
            $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $infos['triggers']['pass'], NULL);
            $infos['handlers']['pass']          =   new ErebotEventHandler(
                                                        array($this, 'handlePass'),
                                                        'ErebotEventTextChan',
                                                        NULL, $filter);

            $filter     = new ErebotTextFilter();
            $filter->addPattern(ErebotTextFilter::TYPE_WILDCARD,    $infos['triggers']['play'].' *', NULL);
            $infos['handlers']['play']          =   new ErebotEventHandler(
                                                        array($this, 'handlePlay'),
                                                        'ErebotEventTextChan',
                                                        NULL, $filter);

            $filter     = new ErebotTextFilter();
            $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $infos['triggers']['show_cards'], NULL);
            $infos['handlers']['show_cards']    =   new ErebotEventHandler(
                                                        array($this, 'handleShowCardsCount'),
                                                        'ErebotEventTextChan',
                                                        NULL, $filter);

            $filter     = new ErebotTextFilter();
            $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $infos['triggers']['show_discard'], NULL);
            $infos['handlers']['show_discard']  =   new ErebotEventHandler(
                                                        array($this, 'handleShowDiscard'),
                                                        'ErebotEventTextChan',
                                                        NULL, $filter);

            $filter     = new ErebotTextFilter();
            $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $infos['triggers']['show_order'], NULL);
            $infos['handlers']['show_order']    =   new ErebotEventHandler(
                                                        array($this, 'handleShowOrder'),
                                                        'ErebotEventTextChan',
                                                        NULL, $filter);

            $filter     = new ErebotTextFilter();
            $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $infos['triggers']['show_scores'], NULL);
            $infos['handlers']['show_scores']   =   new ErebotEventHandler(
                                                        array($this, 'handleShowScores'),
                                                        'ErebotEventTextChan',
                                                        NULL, $filter);

            $filter     = new ErebotTextFilter();
            $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $infos['triggers']['show_time'], NULL);
            $infos['handlers']['show_time']     =   new ErebotEventHandler(
                                                        array($this, 'handleShowTime'),
                                                        'ErebotEventTextChan',
                                                        NULL, $filter);

            $filter     = new ErebotTextFilter();
            $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $infos['triggers']['show_turn'], NULL);
            $infos['handlers']['show_turn'] =   new ErebotEventHandler(
                                                    array($this, 'handleShowTurn'),
                                                    'ErebotEventTextChan',
                                                    NULL, $filter);

            foreach ($infos['handlers'] as &$handler)
                $this->connection->addEventHandler($handler);
            unset($handler);

            $infos['admin']         =   $tracker->startTracking($nick);
            $infos['players']       =   array();
            $infos['startTime']     =   NULL;
            $infos['rounds']        =   0;
            $infos['direction']     =   self::DIR_COUNTERCLOCKWISE;
            $infos['lastWinner']    =   NULL;
            $infos['lastLooser']    =   NULL;

            $this->sendCommand('PRIVMSG '.$chan.' :Ok! A new '.$this->getLogo().' game '.
                                'has been created in '.$chan.'. Say "'.
                                $infos['triggers']['join'].'" to join it.');
        }

        $event->preventDefault(TRUE);
    }

    protected function qualify($move)
    {
        usort($move, array($this, 'compareCards'));
        $getBase    = create_function('$card', 'return substr($card, 1);');
        $getColor   = create_function('$card', 'return $card[0];');
        $count      = count($move);
        $bases      = array_map($getBase, $move);
        $colors     = array_map($getColor, $move);
        $diff       = count(array_unique($bases));

        if ($diff == 1) {
            $types  =   array(
                            1 => self::MOVE_SINGLE,
                            2 => self::MOVE_PAIR,
                            3 => self::MOVE_TRIO,
                        );

            $type   = ($count >= 4 ? self::MOVE_GANG : $types[$count]);
            return  array(
                        'type'      => $type,
                        'count'     => $count,
                        'cards'     => array_reverse($move),
                        'base'      => $bases[0],
                    );
        }

        if ($count != 5)
            return NULL;

        if ($diff == 2) {
            $counts = array_count_values($bases);
            asort($counts, SORT_NUMERIC);
            $cards  = array();

            foreach (array_keys($counts) as $base) {
                $keys = array_keys($bases, $base);
                foreach ($keys as &$key)
                    $cards[] = $move[$key];
            }

            return  array(
                        'type'      => self::MOVE_FULL_HOUSE,
                        'cards'     => array_reverse($cards),
                    );
        }

        $restricted = array('gp', 'yp', 'rd');
        $inter      = array_intersect($restricted, $move);
        if (count($inter))
            return NULL;

        $diff2      =   array_values(array_unique(
                            array_diff($colors, array('m'))));
        $count2     = count($diff2);
        $temp       = 0;
        foreach ($bases as $base)
            $temp  += 1 << $base;
        $temp       = rtrim(decbin($temp), '0');

        if ($temp == '11111') {
            return  array(
                        'type'      =>  ($count2 == 1) ?
                                        self::MOVE_STRAIGHT_FLUSH :
                                        self::MOVE_STRAIGHT,
                        'cards'     => array_reverse($move),
                    );
        }

        if ($count2 == 1) {
            return  array(
                        'type'      => self::MOVE_FLUSH,
                        'cards'     => array_reverse($move),
                    );
        }

        return NULL;
    }

    protected function compareCards($a, $b) {
        $levels = array();
        for ($i = 1; $i <= 10; $i++)
            $levels[] = "$i";
        $levels[] = 'p';
        $levels[] = 'd';
        $levels = array_flip($levels);

        $lvl_a  = $levels[substr($a, 1)];
        $lvl_b  = $levels[substr($b, 1)];
        if ($lvl_a != $lvl_b)
            return $lvl_a - $lvl_b;

        $colors = array('g', 'y', 'r', 'm');
        $colors = array_flip($colors);
        return $colors[$a[0]] - $colors[$b[0]];
    }

    protected function compareHands($a, $b)
    {
        $qb = $this->qualify($b);
        if ($qb === NULL) return NULL;
        if ($a  === NULL) return -1;
        $qa = $this->qualify($a);

        switch ($qa['type']) {
            case self::MOVE_SINGLE:
            case self::MOVE_PAIR:
            case self::MOVE_TRIO:
                switch ($qb['type']) {
                    case $qa['type']:
                        for ($i = 0, $nb = count($qa['cards']); $i < $nb; $i++) {
                            $cmp = $this->compareCards($qa['cards'][$i], $qb['cards'][$i]);
                            if ($cmp) return $cmp;
                        }
                        return 0;

                    case self::MOVE_GANG:
                        return -1;
                    default:
                        return NULL;
                }

            case self::MOVE_GANG:
                if ($qb['type'] != self::MOVE_GANG)
                    return +1;
                if ($qa['count'] != $qb['count'])
                    return ($qa['count'] - $qb['count']);
                return ((int) $qa['base'] - (int) $qb['base']);

            default:
                $orders =   array(
                                self::MOVE_STRAIGHT,
                                self::MOVE_FLUSH,
                                self::MOVE_FULL_HOUSE,
                                self::MOVE_STRAIGHT_FLUSH,
                                self::MOVE_GANG,
                            );
                $orders = array_flip($orders);

                if (!isset($orders[$qb['type']]))
                    return NULL;

                $oa = $orders[$qa['type']];
                $ob = $orders[$qb['type']];
                if ($oa != $ob)
                    return ($oa - $ob);

                switch ($qa['type']) {
                    case self::MOVE_STRAIGHT:
                    case self::MOVE_FLUSH:
                    case self::MOVE_FULL_HOUSE:
                    case self::MOVE_STRAIGHT_FLUSH:
                        for ($i = 0; $i < 5; $i++) {
                            $cmp = $this->compareCards($qa['cards'][$i], $qb['cards'][$i]);
                            if ($cmp) return $cmp;
                        }
                        return 0;
                }
        }
    }

    public function startGame(ErebotTimer &$timer)
    {
        $chan = NULL;
        foreach ($this->chans as $name => &$infos) {
            if (isset($infos['timer']) && $infos['timer'] == $timer) {
                $chan = $name;
                break;
            }
        }
        if ($chan === NULL) return;

        $tracker        =&  $this->getNickTracker();
        $starter        =   $this->dealDeck($chan);
        $last_winner    =   $infos['lastWinner'];
        $last_looser    =   $infos['lastLooser'];
        $infos['rounds']++;

        if ($last_winner !== NULL) {
            $starter            = $last_winner;
            $infos['bestCard']  = array_pop($infos['players'][$last_looser]['cards']);
            $this->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().': Ok, this is round #'.
                                ErebotStyling::sprintf('%b', $infos['rounds']).
                                ', starting after '.$this->getPlayTime($chan).'. '.
                                ErebotStyling::sprintf('%b', $tracker->getNick($last_winner)).
                                ', you must now choose a card to give to '.
                                ErebotStyling::sprintf('%b', $tracker->getNick($last_looser)).
                                ". You'll receive ".$this->getCardText($infos['bestCard']).
                                '. Please choose with: '.$infos['triggers']['choose'].' <card>.');
        }

        else {
            $infos['startTime'] = time();
            $infos['veryFirst'] = TRUE;
            $this->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().': The game starts now. '.
                                ErebotStyling::sprintf('%b', $tracker->getNick($starter)).
                                ', you must start this game. If you have the '.
                                $this->getCardText('m1').', you MUST play it '.
                                '(alone or in a combination).');
        }

        $players = array_keys($infos['players']);
        while (strcasecmp(reset($players), $starter)) {
            $last = array_shift($players);
            array_push($players, $last);
        }

        if ($infos['direction'] == self::DIR_COUNTERCLOCKWISE) {
            $players    = array_reverse($players);
            $last       = array_pop($players);
            array_unshift($players, $last);
        }

        foreach ($infos['players'] as $player => &$data) {
            if ($player == $last_looser) continue;
            $this->sendCommand('PRIVMSG '.$tracker->getNick($player).' :Your cards: '.
                                $this->describeCards($data['cards']));
        }

        $infos['discard']   = NULL;
        $infos['order']     = $players;
    }

    public function handleChoose(ErebotEvent &$event)
    {
        $tracker    =&  $this->getNickTracker();
        $chan       =   $event->getChan();
        $nick       =   $event->getSource();
        if (!isset($this->chans[$chan])) return;
        $infos      =&  $this->chans[$chan];

        $token      =   $this->getToken($chan, $nick);
        if (!isset($infos['lastWinner']) || $token === NULL) return;

        $winner     = $infos['lastWinner'];
        $looser     = $infos['lastLooser'];
        if ($token != $winner) return;

        $card       =   strtolower(str_replace(' ', '',
                            ErebotUtils::gettok($event->getText(), 1, 0)));
        $key        =   array_search($card, $infos['players'][$token]['cards']);
        if ($key === FALSE) {
            $this->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo()." You don't that card...");
            return $event->preventDefault(TRUE);
        }

        $infos['players'][$looser]['cards'][] = $card;
        unset($infos['players'][$winner]['cards'][$key]);
        $infos['players'][$winner]['cards'][] = $infos['bestCard'];

        $this->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().': Exchanges: '.
                            ErebotStyling::sprintf('%b', $nick).' receives a '.
                            $this->getCardText($infos['bestCard']).' and gives a '.
                            $this->getCardText($card).' to '.
                            ErebotStyling::sprintf('%b', $tracker->getNick($looser)).'. '.
                            ErebotStyling::sprintf('%b', $nick).
                            ', you may now start this round.');

        usort($infos['players'][$winner]['cards'], array($this, 'compareCards'));
        usort($infos['players'][$looser]['cards'], array($this, 'compareCards'));

        $cards =& $infos['players'][$winner]['cards'];
        $this->sendCommand('PRIVMSG '.$nick.' :Your cards: '.$this->describeCards($cards));

        $cards =& $infos['players'][$looser]['cards'];
        $this->sendCommand('PRIVMSG '.$tracker->getNick($looser).
                            ' :Your cards: '.$this->describeCards($cards));

        unset($infos['lastWinner']);
        unset($infos['lastLooser']);
        unset($infos['bestCard']);
        $event->preventDefault(TRUE);
    }

    public function handleJoin(ErebotEvent &$event)
    {
        $tracker    =&  $this->getNickTracker();
        $nick       =   $event->getSource();
        $chan       =   $event->getChan();

        if (!isset($this->chans[$chan])) return;
        $infos      =&  $this->chans[$chan];

        if ($this->getToken($chan, $nick) !== NULL) return;

        $count = count($infos['players']);
        if ($count >= 4) {
            $this->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().' '.
                                'This game may only be played by 3-4 players.');
            return $event->preventDefault(TRUE);
        }

        if ($infos['startTime'] === NULL) {
            $this->sendCommand('PRIVMSG '.$chan.' :'.ErebotStyling::sprintf('%b', $nick).
                                ' joins this '.$this->getLogo().' game.');

            if ($count == 2) {
                $startDelay = $this->parseInt('start_delay', 20);
                if ($startDelay < 0)
                    $startDelay = 20;

                $infos['timer'] = new ErebotTimer(array($this, 'startGame'), $startDelay, FALSE);
                $this->addTimer($infos['timer']);
                $this->sendCommand('PRIVMSG '.$chan.' :The game will start in '.
                                    $startDelay.' seconds.');
            }

            $token  = $tracker->startTracking($nick);
            $infos['players'][$token] = array('score' => 0);
        }
        $event->preventDefault(TRUE);
    }

    public function handlePass(ErebotEvent &$event)
    {
        $tracker    =&  $this->getNickTracker();
        $chan       =   $event->getChan();
        $nick       =   $event->getSource();
        if (!isset($this->chans[$chan]['order'])) return;
        $infos      =&  $this->chans[$chan];

        $token      =   $this->getToken($chan, $nick);
        if ($token === NULL) return;
        $current    =   reset($infos['order']);
        if ($token != $current) return;
        if ($infos['discard'] === NULL) return;

        $this->sendCommand('PRIVMSG '.$chan.' :'.ErebotStyling::sprintf('%b', $nick).' passes turn.');

        $this->prepareNextTurn($chan);
        $event->preventDefault(TRUE);
    }

    public function handlePlay(ErebotEvent &$event)
    {
        $tracker    =&  $this->getNickTracker();
        $chan       =   $event->getChan();
        $nick       =   $event->getSource();
        $move       =   strtolower(str_replace(' ', '',
                            ErebotUtils::gettok($event->getText(), 1, 0)));
        if (!isset($this->chans[$chan]['order'])) return;
        $infos =& $this->chans[$chan];

        if (isset($infos['lastWinner'])) return;

        $token      = $this->getToken($chan, $nick);
        if ($token === NULL) return;

        $current    = reset($infos['order']);
        $next       = next($infos['order']);
        if ($token != $current) return;

        if (!preg_match('/^(?:[gyr][0-9]+|m1|gp|yp|rd)+$/', $move)) {
            $this->sendCommand('PRIVMSG '.$chan.' :Hmm? What move was that?');
            return $event->preventDefault(TRUE);
        }
        preg_match_all('/(?:[gyr][0-9]+|m1|gp|yp|rd)/', $move, $matches);
        $cards  = $matches[0];
        $res    = $this->compareHands($infos['discard'], $cards);

        if ($res === NULL) {
            $this->sendCommand('PRIVMSG '.$chan.' :Hmm? What move was that?');
            return $event->preventDefault(TRUE);
        }

        if ($res >= 0) {
            $this->sendCommand('PRIVMSG '.$chan.' :This is not enough to win!');
            return $event->preventDefault(TRUE);
        }

        $pcards = $infos['players'][$token]['cards'];
        foreach ($cards as $card) {
            $key = array_search($card, $pcards);
            if ($key === FALSE) {
                $this->sendCommand('PRIVMSG '.$chan.' :You do not have '.
                                    'the cards required for that move!');
                return $event->preventDefault(TRUE);
            }

            unset($pcards[$key]);
        }

        if (isset($infos['veryFirst'])) {
            if (!in_array('m1', $cards) && in_array('m1', $pcards)) {
                $this->sendCommand('PRIVMSG '.$chan.' :This is the very first round. '.
                                    'You must play the '.$this->getCardText('m1').
                                    ' (alone or in a combination).');
                return $event->preventDefault(TRUE);
            }
            unset($infos['veryFirst']);
        }

        if (count($infos['players'][$next]['cards']) == 1) {
            if (count($cards) == 1 && reset($cards) != end($infos['players'][$token]['cards'])) {
                $this->sendCommand('PRIVMSG '.$chan.' :'.
                                    ErebotStyling::sprintf('%b', $nick).', '.
                                    ErebotStyling::sprintf('%b', $tracker->getNick($next)).
                                    ' has only 1 card left. You '.ErebotStyling::sprintf('%b', 'MUST').
                                    ' play your best card or a combination on this turn!');
                return $event->preventDefault(TRUE);
            }
        }

        $infos['players'][$token]['cards'] = $pcards;
        $qualif = $this->qualify($cards);

        $infos['discard']   =   $qualif['cards'];
        $infos['leader']    =   $token;
        $extra              =   (count($pcards) == 1) ? "This is ".
                                ErebotStyling::sprintf('%b', $nick).
                                "'s last card!" : '';
        $this->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().': '.
                            ErebotStyling::sprintf('%b', $nick).
                            ' plays '.$qualif['type'].': '.
                            $this->describeCards($qualif['cards']).
                            '. '.$extra);

        if (empty($pcards)) {
            $this->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().': '.
                                ErebotStyling::sprintf('%b', $nick).
                                ' wins this round!');
            $over       = FALSE;
            $loosers    = array('score' => 0, 'nicks' => array());

            foreach ($infos['players'] as $player => &$data) {
                $score = $this->getScore($data['cards']);
                $data['score'] += $score;

                if ($score > $loosers['score']) {
                    $loosers['score']   = $score;
                    $loosers['tokens']  = array($player);
                }
                else if ($score == $loosers['score'])
                    $loosers['tokens'][] = $player;

                if ($data['score'] >= 100)
                    $over = TRUE;
            }
            $this->showScores($chan);

            if ($over) {
                $winners = array('score' => 100, 'tokens' => array());
                foreach ($infos['players'] as $player => &$data) {
                    if ($data['score'] < $winners['score']) {
                        $winners['score']   = $data['score'];
                        $winners['tokens']  = array($player);
                    }
                    else if ($data['score'] == $winners['score'])
                        $winners['tokens'][] = $player;
                }

                if (empty($winners['tokens']))
                    $this->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().
                                        ': This game ended on a DRAW after '.
                                        $this->getPlayTime($chan).'!');
                else {
                    $nicks = array_map(array($tracker, 'getNick'), $winners['tokens']);

                    $this->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().': After '.
                                        $this->getPlayTime($chan).', '.
                                        ErebotStyling::sprintf('%b', $infos['rounds']).
                                        ' rounds and with only '.
                                        ErebotStyling::sprintf('%b', $winners['score']).
                                        ' points, the winners are: '.
                                        ErebotStyling::sprintf('%b', implode(', ', $nicks)));
                }

                $this->cleanup($chan);
                return $event->preventDefault(TRUE);
            }

            else {
                if (count($loosers['tokens']) == 1)
                    $looser = $loosers['tokens'][0];
                else {
                    $totalLoosers = array('score' => 0, 'tokens' => array());

                    foreach ($infos['players'] as $player => &$data) {
                        if ($data['score'] > $totalLoosers['score']) {
                            $totalLoosers['score'] = $data['score'];
                            $totalLoosers['tokens'] = array($player);
                        }
                        else if ($data['score'] == $totalLoosers['score'])
                            $totalLoosers['tokens'][] = $player;
                    }

                    if (count($totalLoosers['tokens']) == 1)
                        $looser = $totalLoosers['tokens'][0];

                    else {
                        $players    = array_keys($infos['players']);
                        $key        = array_search($nick, $players);
                        $count      = count($players);
                        $looser     = NULL;

                        for ($i = $count-1; $i > 0; $i--) {
                            if (in_array($players[($key + $i) % $count], $totalLoosers['tokens'])) {
                                $looser = $players[($key + $i) % $count];
                                break;
                            }
                        }

                        if ($looser === NULL)
                            throw new Exception('Internal error');
                    }
                }

                $infos['lastWinner']    = $token;
                $infos['lastLooser']    = $looser;
                $infos['direction']     =   ($infos['direction'] == self::DIR_CLOCKWISE) ?
                                            self::DIR_COUNTERCLOCKWISE : self::DIR_CLOCKWISE;
                unset($infos['leader']);

                $pauseDelay = $this->parseInt('pause_delay', 5);
                if ($pauseDelay < 0)
                    $pauseDelay = 5;

                $infos['timer'] = new ErebotTimer(array($this, 'startGame'), $pauseDelay, FALSE);
                $this->addTimer($infos['timer']);
                $this->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().': '.
                                    'The next round will start in '.$pauseDelay.' seconds.');
            }
        }
        else $this->prepareNextTurn($chan);

        $event->preventDefault(TRUE);
    }

    public function handleShowCardsCount(ErebotEvent &$event)
    {
        $tracker    =&  $this->getNickTracker();
        $chan       =   $event->getChan();
        $nick       =   $event->getSource();
        if (!isset($this->chans[$chan])) return;
        $infos  =& $this->chans[$chan];

        if (isset($infos['order'])) {
            $output = '';
            foreach ($infos['order'] as $player) {
                $output .=  ErebotStyling::sprintf('%b', $tracker->getNick($player)).': '.
                            count($infos['players'][$player]['cards']).', ';
            }
            $output = substr($output, 0, -2);
            $this->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().': Hands: '.$output);
        }

        $token      = $this->getToken($chan, $nick);
        if ($token !== NULL)
            $this->sendCommand('PRIVMSG '.$nick.' :Your cards: '.
                                $this->describeCards($infos['players'][$token]['cards']));
        $event->preventDefault(TRUE);
    }

    public function handleShowDiscard(ErebotEvent &$event)
    {
        $tracker    =&  $this->getNickTracker();
        $chan       =   $event->getChan();
        $nick       =   $event->getSource();

        if (!isset($this->chans[$chan])) return;
        $infos  =&  $this->chans[$chan];

        if (empty($infos['discard'])) {
            $token  = $this->getToken($chan, $nick);

            if (isset($infos['leader']) && $token !== NULL && $token == $infos['leader'])
                $this->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().' '.
                                    "You've got the upper hand ".
                                    ErebotStyling::sprintf('%b', $nick).
                                    ', you may now start a new hand :)');
            else
                $this->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().': '.
                                    'No card has been played in this round yet.');
        }

        else {
            $qualif     = $this->qualify($infos['discard']);
            $discard    = $this->describeCards($qualif['cards']);
            $this->sendCommand('PRIVMSG '.$chan.' :Current discard: '.$discard.
                                ' ('.$qualif['type'].', played by '.
                                ErebotStyling::sprintf('%b', $tracker->getNick($infos['leader'])).')');
        }

        $event->preventDefault(TRUE);
    }

    public function handleShowOrder(ErebotEvent &$event)
    {
        $tracker    =&  $this->getNickTracker();
        $chan       =   $event->getChan();
        if (!isset($this->chans[$chan]['order'])) return;

        $nicks = array_map(array($tracker, 'getNick'), $this->chans[$chan]['order']);
        $this->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().': playing turn: '.
                            ErebotStyling::sprintf('%b', implode(' ', $nicks)));
        $event->preventDefault(TRUE);
    }

    public function getPlayTime($chan)
    {
        $duration   = time() - $this->chans[$chan]['startTime'];
        return ErebotStyling::sprintfDuration($duration);
    }

    protected function showScores($chan)
    {
        $tracker    =&  $this->getNickTracker();

        if (!isset($this->chans[$chan]['order'])) return;
        $infos =& $this->chans[$chan];

        $output = $this->getLogo().': Scores: ';
        foreach ($infos['order'] as $player)
            $output .=  ErebotStyling::sprintf('%b', $tracker->getNick($player)).
                        ': '.$infos['players'][$player]['score'].', ';
        $output = substr($output, 0, -2);
        $this->sendCommand('PRIVMSG '.$chan.' :'.$output);
    }

    public function handleShowScores(ErebotEvent &$event)
    {
        $chan = $event->getChan();
        if (!isset($this->chans[$chan])) return;
        $this->showScores($chan);
        $event->preventDefault(TRUE);
    }

    public function handleShowTime(ErebotEvent &$event)
    {
        $chan = $event->getChan();
        if (!isset($this->chans[$chan])) return;

        if (!isset($this->chans[$chan]['startTime']))
            $this->sendCommand('PRIVMSG '.$chan.' :The '.$this->getLogo().' game has not yet started!');
        else
            $this->sendCommand('PRIVMSG '.$chan.' :This '.$this->getLogo().' game '.
                                ' has been running for '.$this->getPlayTime($chan));
        $event->preventDefault(TRUE);
    }

    protected function showTurn($chan, $from)
    {
        $tracker    =&  $this->getNickTracker();
        $infos      =&  $this->chans[$chan];
        $current    =   reset($infos['order']);
        if ($from !== NULL && $from == $current)
            $this->sendCommand('PRIVMSG '.$chan.' :'.$tracker->getNick($current).
                                    ": it's your turn sleepyhead!");
        else {
            $next = next($infos['order']);
            if (count($infos['players'][$next]['cards']) == 1) {
                if ($infos['discard'] === NULL)
                    $this->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().': '.
                                        ErebotStyling::sprintf('%b', $tracker->getNick($current)).
                                        ", since there's only 1 card left in ".
                                        ErebotStyling::sprintf('%b', $tracker->getNick($next))."'s hand, ".
                                        'you MUST start with a combination or your highest card!');
                else
                    $this->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().': '.
                                        ErebotStyling::sprintf('%b', $tracker->getNick($current)).
                                        ", since there's only 1 card left in ".
                                        ErebotStyling::sprintf('%b', $tracker->getNick($next)).
                                        "'s hand, ".'you MUST play a combination or your '.
                                        'highest card on this turn!');
            }

            else
                $this->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().": It's ".
                                    ErebotStyling::sprintf('%b', $tracker->getNick($current))."'s turn.");
        }
    }

    public function handleShowTurn(ErebotEvent &$event)
    {
        $chan       =   $event->getChan();
        if (!isset($this->chans[$chan]['order'])) return;
        $this->showTurn($chan, $this->getToken($chan, $event->getSource()));
        $event->preventDefault(TRUE);
    }
}

?>
