<?php

class Erebot_gof
{
    protected   $bot;
    protected   $chans;

    const TRIGGER_CREATE            = '!gof';
    const TRIGGER_CHOOSE            = 'co';
    const TRIGGER_JOIN              = 'jo';
    const TRIGGER_PASS              = 'pa';
    const TRIGGER_PLAY              = 'pl';
    const TRIGGER_SHOW_CARDS_COUNT  = 'ca';
    const TRIGGER_SHOW_DISCARD      = 'cd';
    const TRIGGER_SHOW_ORDER        = 'od';
    const TRIGGER_SHOW_SCORES       = 'sc';
    const TRIGGER_SHOW_TIME         = 'ti';
    const TRIGGER_SHOW_TURN         = 'tu';

    const COLOR_RED                 = '00,04';
    const COLOR_GREEN               = '00,03';
    const COLOR_YELLOW              = '01,08';

    const START_DELAY               = 20;
    const NEXT_DELAY                = 5;

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

    protected function getLogo()
    {
        return  Erebot::CODE_BOLD.
                Erebot::CODE_COLOR.'03Gang '.
                Erebot::CODE_COLOR.'08of '.
                Erebot::CODE_COLOR.'04Four'.
                Erebot::CODE_COLOR.
                Erebot::CODE_BOLD;
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

        return  Erebot::CODE_COLOR.$colorCodes[$color].
                Erebot::CODE_BOLD.$text.
                Erebot::CODE_BOLD.
                Erebot::CODE_COLOR;
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
        $output = Erebot::CODE_BOLD;
        $nbCol  = count($order);

        for ($i = 0; $i < $len; $i++)
            $output .=  Erebot::CODE_COLOR.
                        $order[$i % $nbCol].
                        $text[$i];
        $output .=  Erebot::CODE_COLOR.
                    Erebot::CODE_BOLD;
        return $output;
    }

    protected function prepareNextTurn($chan)
    {
        $infos  =&  $this->chans[$chan];
        $last   =   array_shift($infos['order']);
        array_push($infos['order'], $last);

        $next   = reset($infos['order']);
        if (!strcasecmp($next, $infos['leader'])) {
            $infos['discard'] = NULL;
            $this->bot->sendCommand('PRIVMSG '.$chan.' :No player dared to raise the heat! '.
                                    'You can now start a new combination '.
                                    Erebot::CODE_BOLD.$next.Erebot::CODE_BOLD.' :)');
        }

        $this->showTurn($chan, NULL);
        $this->bot->sendCommand('PRIVMSG '.$next.' :Your cards: '.
                                $this->describeCards($infos['players'][$next]['cards']));
        return $next;
    }

    public function __construct(Erebot $bot)
    {
        $this->bot      = $bot;
        $this->chans    = array();

        $bot->addEvent(ErebotEvent::ON_TEXT,
            array('matchtext' => self::TRIGGER_CREATE, 'matchtype' => Erebot::MATCHTEXT_STATIC),
            array($this, 'handleCreate'));

        $bot->addEvent(ErebotEvent::ON_TEXT,
            array('matchtext' => self::TRIGGER_CHOOSE.' &', 'matchtype' => Erebot::MATCHTEXT_WILDCARD),
            array($this, 'handleChoose'));

        $bot->addEvent(ErebotEvent::ON_TEXT,
            array('matchtext' => self::TRIGGER_JOIN, 'matchtype' => Erebot::MATCHTEXT_STATIC),
            array($this, 'handleJoin'));

        $bot->addEvent(ErebotEvent::ON_TEXT,
            array('matchtext' => self::TRIGGER_PASS, 'matchtype' => Erebot::MATCHTEXT_STATIC),
            array($this, 'handlePass'));

        $bot->addEvent(ErebotEvent::ON_TEXT,
            array('matchtext' => self::TRIGGER_PLAY.' &', 'matchtype' => Erebot::MATCHTEXT_WILDCARD),
            array($this, 'handlePlay'));

        $bot->addEvent(ErebotEvent::ON_TEXT,
            array('matchtext' => self::TRIGGER_SHOW_CARDS_COUNT, 'matchtype' => Erebot::MATCHTEXT_STATIC),
            array($this, 'handleShowCardsCount'));

        $bot->addEvent(ErebotEvent::ON_TEXT,
            array('matchtext' => self::TRIGGER_SHOW_DISCARD, 'matchtype' => Erebot::MATCHTEXT_STATIC),
            array($this, 'handleShowDiscard'));

        $bot->addEvent(ErebotEvent::ON_TEXT,
            array('matchtext' => self::TRIGGER_SHOW_ORDER, 'matchtype' => Erebot::MATCHTEXT_STATIC),
            array($this, 'handleShowOrder'));

        $bot->addEvent(ErebotEvent::ON_TEXT,
            array('matchtext' => self::TRIGGER_SHOW_SCORES, 'matchtype' => Erebot::MATCHTEXT_STATIC),
            array($this, 'handleShowScores'));

        $bot->addEvent(ErebotEvent::ON_TEXT,
            array('matchtext' => self::TRIGGER_SHOW_TIME, 'matchtype' => Erebot::MATCHTEXT_STATIC),
            array($this, 'handleShowTime'));

        $bot->addEvent(ErebotEvent::ON_TEXT,
            array('matchtext' => self::TRIGGER_SHOW_TURN, 'matchtype' => Erebot::MATCHTEXT_STATIC),
            array($this, 'handleShowTurn'));
    }

    protected function isPlaying($chan, $nick)
    {
        return isset($this->chans[$chan]['players'][$nick]);
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

    protected function getScore($cards)
    {
        $count      = count($cards);
        $factors    = array(
                        16  => 5,
                        14  => 4,
                        11  => 3,
                        8   => 2,
                        1   => 1,
                    );
        foreach ($factors as $threshold => $factor)
            if ($count >= $threshold)
                return $count * $factor;
        return 0;
    }

    protected function describeCards($cards)
    {
        $cardsTexts = array_map(array($this, 'getCardText'), $cards);
        return implode(' ', $cardsTexts);
    }

    public function dealDeck($chan)
    {
        $infos  =&  $this->chans[$chan];
        $colors =   array('r', 'g', 'y');
        $deck   =   array();

        foreach ($colors as $color)
            for ($n = 0; $n < 2; $n++)
                for ($i = 1; $i <= 10; $i++)
                    $deck[] = $color.$i;
        $deck[] = 'm1';
        $deck[] = 'gp';
        $deck[] = 'yp';
        $deck[] = 'rd';
        shuffle($deck);

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
        $nick = $event->nick();
        $chan = $event->chan();

        if (isset($this->chans[$chan])) {
            $this->bot->sendCommand('PRIVMSG '.$chan.' :A '.$this->getLogo().' managed by '.
                                    Erebot::CODE_BOLD.$this->chans[$chan]['admin'].Erebot::CODE_BOLD.
                                    ' is already running. Say "jo" to join it.');
        }

        else {
            $this->chans[$chan] = array();
            $infos              =&  $this->chans[$chan];

            $infos['admin']     = $nick;
            $infos['players']   = array();
            $infos['startTime'] = NULL;
            $infos['rounds']    = 0;
            $infos['direction'] = self::DIR_COUNTERCLOCKWISE;
            $infos['lastWinner']    = NULL;
            $infos['lastLooser']    = NULL;

            $this->bot->sendCommand('PRIVMSG '.$chan.' :Ok! A new '.$this->getLogo().' game '.
                                    'has been created in '.$chan.'. Say "jo" to join it.');
        }

        $event->haltdef();
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
                if ($qa['count'] > $qb['count'])
                    return +1;
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

    public function startGame($name, $delay)
    {
        list($id, $chan) = explode(' ', $name);
        if (!isset($this->chans[$chan])) return;
        $infos =& $this->chans[$chan];

        $starter        = $this->dealDeck($chan);
        $last_winner    = $infos['lastWinner'];
        $last_looser    = $infos['lastLooser'];
        $infos['rounds']++;

        if ($last_winner !== NULL) {
            $starter            = $last_winner;
            $infos['bestCard']  = array_pop($infos['players'][$last_looser]['cards']);
            $this->bot->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().': Ok, this is round #'.
                                    Erebot::CODE_BOLD.$infos['rounds'].Erebot::CODE_BOLD.
                                    ', starting after '.$this->getPlayTime($chan).'. '.
                                    Erebot::CODE_BOLD.$last_winner.Erebot::CODE_BOLD.
                                    ', you must now choose a card to give to '.
                                    Erebot::CODE_BOLD.$last_looser.Erebot::CODE_BOLD.
                                    ". You'll receive ".$this->getCardText($infos['bestCard']).
                                    '. Please choose with: co <card>.');
        }

        else {
            $infos['startTime'] = time();
            $infos['veryFirst'] = TRUE;
            $this->bot->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().': The game starts now. '.
                                    Erebot::CODE_BOLD.$starter.Erebot::CODE_BOLD.
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
            $this->bot->sendCommand('PRIVMSG '.$player.' :Your cards: '.
                                    $this->describeCards($data['cards']));
        }

        $infos['discard']   = NULL;
        $infos['order']     = $players;
    }

    public function handleChoose(ErebotEvent &$event)
    {
        $chan       =   $event->chan();
        if (!isset($this->chans[$chan])) return;
        $infos      =&  $this->chans[$chan];

        if (!isset($infos['lastWinner'])) return;
        if (strcasecmp($event->nick(), $infos['lastWinner'])) return;

        $card   = strtolower(Erebot::gettok($event->text(), 1, 1));
        $nick   = $infos['lastWinner'];
        $key    = array_search($card, $infos['players'][$nick]['cards']);
        if ($key === FALSE) {
            $this->bot->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo()." You don't that card...");
            return $event->haltdef();
        }

        $winner = $infos['lastWinner'];
        $looser = $infos['lastLooser'];

        $infos['players'][$looser]['cards'][] = $card;
        unset($infos['players'][$winner]['cards'][$key]);
        $infos['players'][$winner]['cards'][] = $infos['bestCard'];

        $this->bot->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().': Exchanges: '.
                                Erebot::CODE_BOLD.$winner.Erebot::CODE_BOLD.' receives a '.
                                $this->getCardText($infos['bestCard']).' and gives a '.
                                $this->getCardText($card).' to '.
                                Erebot::CODE_BOLD.$looser.Erebot::CODE_BOLD.'. '.
                                Erebot::CODE_BOLD.$winner.Erebot::CODE_BOLD.
                                ', you may now start this round.');

        usort($infos['players'][$winner]['cards'], array($this, 'compareCards'));
        usort($infos['players'][$looser]['cards'], array($this, 'compareCards'));

        $cards =& $infos['players'][$winner]['cards'];
        $this->bot->sendCommand('PRIVMSG '.$winner.' :Your cards: '.$this->describeCards($cards));

        $cards =& $infos['players'][$looser]['cards'];
        $this->bot->sendCommand('PRIVMSG '.$looser.' :Your cards: '.$this->describeCards($cards));

        unset($infos['lastWinner']);
        unset($infos['lastLooser']);
        unset($infos['bestCard']);
        $event->haltdef();
    }

    public function handleJoin(ErebotEvent &$event)
    {
        $nick = $event->nick();
        $chan = $event->chan();

        if (!isset($this->chans[$chan])) return;
        $infos =& $this->chans[$chan];

        if (isset($infos['players'][$nick])) return;

        $count = count($infos['players']);
        if ($count >= 4) {
            $this->bot->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().' '.
                                    'This game may only be played by 3-4 players.');
            return $event->haltdef();
        }

        if ($infos['startTime'] === NULL) {
            $this->bot->sendCommand('PRIVMSG '.$chan.' :'.Erebot::CODE_BOLD.$nick.Erebot::CODE_BOLD.
                                    ' joins this '.$this->getLogo().' game.');

            if ($count == 2) {
                $this->bot->addTimer(array($this, 'startGame'), self::START_DELAY, 'GOF '.$chan);
                $this->bot->sendCommand('PRIVMSG '.$chan.' :The game will start in '.
                                        self::START_DELAY.' seconds.');
            }

            $infos['players'][$nick] = array('score' => 0);
        }
        $event->haltdef();
    }

    public function handlePass(ErebotEvent &$event)
    {
        $chan   = $event->chan();
        $nick   = $event->nick();
        if (!isset($this->chans[$chan])) return;
        $infos =& $this->chans[$chan];

        $current    = reset($infos['order']);
        if (strcasecmp($nick, $current)) return;
        if ($infos['discard'] === NULL) return;

        $this->bot->sendCommand('PRIVMSG '.$chan.' :'.Erebot::CODE_BOLD.$nick.
                                Erebot::CODE_BOLD.' passes turn.');

        $this->prepareNextTurn($chan);
        $event->haltdef();
    }

    public function handlePlay(ErebotEvent &$event)
    {
        $chan   = $event->chan();
        $nick   = $event->nick();
        $move   = strtolower(Erebot::gettok($event->text(), 1, 1));
        if (!isset($this->chans[$chan])) return;
        $infos =& $this->chans[$chan];

        if (isset($infos['lastWinner'])) return;

        $current    = reset($infos['order']);
        $next       = next($infos['order']);
        if (strcasecmp($nick, $current)) return;

        if (!preg_match('/^(?:[gyr][0-9]+|m1|gp|yp|rd)+$/', $move)) {
            $this->bot->sendCommand('PRIVMSG '.$chan.' :Hmm? What move was that?');
            return $event->haltdef();
        }
        preg_match_all('/(?:[gyr][0-9]+|m1|gp|yp|rd)/', $move, $matches);
        $cards  = $matches[0];
        $res    = $this->compareHands($infos['discard'], $cards);

        if ($res === NULL) {
            $this->bot->sendCommand('PRIVMSG '.$chan.' :Hmm? What move was that?');
            return $event->haltdef();
        }

        if ($res >= 0) {
            $this->bot->sendCommand('PRIVMSG '.$chan.' :This is not enough to win!');
            return $event->haltdef();
        }

        $pcards = $infos['players'][$nick]['cards'];
        foreach ($cards as $card) {
            $key = array_search($card, $pcards);
            if ($key === FALSE) {
                $this->bot->sendCommand('PRIVMSG '.$chan.' :You do not have '.
                                        'the cards required for that move!');
                return $event->haltdef();
            }

            unset($pcards[$key]);
        }

        if (isset($infos['veryFirst'])) {
            if (!in_array('m1', $cards) && in_array('m1', $pcards)) {
                $this->bot->sendCommand('PRIVMSG '.$chan.' :This is the very first round. '.
                                        'You must play the '.$this->getCardText('m1').
                                        ' (alone or in a combination).');
                return $event->haltdef();
            }
            unset($infos['veryFirst']);
        }

        if (count($infos['players'][$next]['cards']) == 1) {
            if (count($cards) == 1 && reset($cards) != end($infos['players'][$nick]['cards'])) {
                $this->bot->sendCommand('PRIVMSG '.$chan.' :'.
                                        Erebot::CODE_BOLD.$nick.Erebot::CODE_BOLD.', '.
                                        Erebot::CODE_BOLD.$next.Erebot::CODE_BOLD.' has only 1 card left. '.
                                        'You '.Erebot::CODE_BOLD.'MUST'.Erebot::CODE_BOLD.
                                        ' play your best card or a combination on this turn!');
                return $event->haltdef();
            }
        }

        $infos['players'][$nick]['cards'] = $pcards;
        $qualif = $this->qualify($cards);

        $infos['discard']   = $qualif['cards'];
        $infos['leader']    = $nick;
        $extra              = (count($pcards) == 1 ? "This is ".
                              Erebot::CODE_BOLD.$nick.Erebot::CODE_BOLD.
                              "'s last card!" : '');
        $this->bot->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().': '.
                                Erebot::CODE_BOLD.$nick.Erebot::CODE_BOLD.
                                ' plays '.$qualif['type'].': '.
                                $this->describeCards($qualif['cards'])).". ".$extra;

        if (empty($pcards)) {
            $this->bot->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().': '.
                                    Erebot::CODE_BOLD.$nick.Erebot::CODE_BOLD.
                                    ' wins this round!');
            $over       = FALSE;
            $loosers    = array('score' => 0, 'nicks' => array());

            foreach ($infos['players'] as $player => &$data) {
                $score = $this->getScore($data['cards']);
                $data['score'] += $score;

                if ($score > $loosers['score']) {
                    $loosers['score']   = $score;
                    $loosers['nicks']   = array($player);
                }
                else if ($score == $loosers['score'])
                    $loosers['nicks'][] = $player;

                if ($data['score'] >= 100)
                    $over = TRUE;
            }
            $this->showScores($chan);

            if ($over) {
                $winners = array('score' => 100, 'nicks' => array());
                foreach ($infos['players'] as $player => &$data) {
                    if ($data['score'] < $winners['score']) {
                        $winners['score']   = $data['score'];
                        $winners['nicks']   = array($player);
                    }
                    else if ($data['score'] == $winners['score'])
                        $winners['nicks'][] = $player;
                }

                if (empty($winners['nicks']))
                    $this->bot->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().
                                            ': This game ended on a DRAW after '.
                                            $this->getPlayTime($chan).'!');
                else
                    $this->bot->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().': After '.
                                            $this->getPlayTime($chan).', '.Erebot::CODE_BOLD.
                                            $infos['rounds'].Erebot::CODE_BOLD.' rounds and with only '.
                                            Erebot::CODE_BOLD.$winners['score'].Erebot::CODE_BOLD.
                                            ' points, the winners are: '.Erebot::CODE_BOLD.
                                            implode(Erebot::CODE_BOLD.', '.Erebot::CODE_BOLD,
                                            $winners['nicks']));
                unset($infos);
                unset($this->chans[$chan]);
            }

            else {
                if (count($loosers['nicks']) == 1)
                    $looser = $loosers['nicks'][0];
                else {
                    $totalLoosers = array('score' => 0, 'nicks' => array());

                    foreach ($infos['players'] as $player => &$data) {
                        if ($data['score'] > $totalLoosers['score']) {
                            $totalLoosers['score'] = $data['score'];
                            $totalLoosers['nicks'] = array($player);
                        }
                        else if ($data['score'] == $totalLoosers['score'])
                            $totalLoosers['nicks'][] = $player;
                    }

                    if (count($totalLoosers['nicks']) == 1)
                        $looser = $totalLoosers['nicks'][0];

                    else {
                        $players    = array_keys($infos['players']);
                        $key        = array_search($nick, $players);
                        $count      = count($players);
                        $looser     = NULL;

                        for ($i = $count-1; $i > 0; $i--) {
                            if (in_array($players[($key + $i) % $count], $totalLoosers['nicks'])) {
                                $looser = $players[($key + $i) % $count];
                                break;
                            }
                        }

                        if ($looser === NULL)
                            throw new Exception('Internal error');
                    }
                }

                $infos['lastWinner']    = $nick;
                $infos['lastLooser']    = $looser;
                $infos['direction']     =   ($infos['direction'] == self::DIR_CLOCKWISE) ?
                                            self::DIR_COUNTERCLOCKWISE : self::DIR_CLOCKWISE;
                unset($infos['leader']);
                $this->bot->addTimer(array($this, 'startGame'), self::NEXT_DELAY, 'GOF '.$chan);
                $this->bot->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().': '.
                                        'The next round will start in '.self::NEXT_DELAY.' seconds.');
            }
        }
        else $this->prepareNextTurn($chan);

        $event->haltdef();
    }

    public function handleShowCardsCount(ErebotEvent &$event)
    {
        $chan   = $event->chan();
        $nick   = $event->nick();
        if (!isset($this->chans[$chan])) return;
        $infos  =& $this->chans[$chan];

        if (isset($infos['order'])) {
            $output = '';
            foreach ($infos['order'] as $player) {
                $output .=  Erebot::CODE_BOLD.$player.Erebot::CODE_BOLD.': '.
                            count($infos['players'][$player]['cards']).', ';
            }
            $output = substr($output, 0, -2);
            $this->bot->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().': Hands: '.$output);
        }

        if ($this->isPlaying($chan, $nick))
            $this->bot->sendCommand('PRIVMSG '.$nick.' :Your cards: '.
                                    $this->describeCards($infos['players'][$nick]['cards']));
        $event->haltdef();
    }

    public function handleShowDiscard(ErebotEvent &$event)
    {
        $chan   =   $event->chan();
        $nick   =   $event->nick();

        if (!isset($this->chans[$chan])) return;
        $infos  =&  $this->chans[$chan];

        if (empty($infos['discard'])) {
            if (isset($infos['leader']) && !strcasecmp($nick, $infos['leader']))
                $this->bot->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().' '.
                                        "You've got the upper hand ".
                                        Erebot::CODE_BOLD.$nick.Erebot::CODE_BOLD.
                                        ', you may now start a new hand :)');
            else
                $this->bot->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().': '.
                                        'No card has been played in this round yet.');
        }

        else {
            $qualif     = $this->qualify($infos['discard']);
            $discard    = $this->describeCards($qualif['cards']);
            $this->bot->sendCommand('PRIVMSG '.$chan.' :Current discard: '.$discard.
                                    ' ('.$qualif['type'].', played by '.Erebot::CODE_BOLD.
                                    $infos['leader'].Erebot::CODE_BOLD.')');
        }

        $event->haltdef();
    }

    public function handleShowOrder(ErebotEvent &$event)
    {
        $chan = $event->chan();
        if (!isset($this->chans[$chan]['order'])) return;
        $this->bot->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().': playing turn: '.
                                Erebot::CODE_BOLD.implode(' ', $this->chans[$chan]['order']));
        $event->haltdef();
    }

    public function getPlayTime($chan)
    {
        $duration   = time() - $this->chans[$chan]['startTime'];
        $seconds    = $duration % 60;
        $duration   = (int) ($duration / 60);
        $minutes    = $duration % 60;
        $duration   = (int) ($duration / 60);
        $hours      = $duration % 24;
        $days       = (int) ($duration / 24);

        $duration   = array();
        if ($days)      $duration[] = $days.' days';
        if ($hours)     $duration[] = $hours.' hours';
        if ($minutes)   $duration[] = $minutes.' minutes';
        if ($seconds)   $duration[] = (count($duration) ? 'and ' : '').$seconds.' seconds';
        $duration = implode(' ', $duration);
        return $duration;
    }

    protected function showScores($chan)
    {
        if (!isset($this->chans[$chan]['order'])) return;
        $infos =& $this->chans[$chan];

        $output = $this->getLogo().': Scores: ';
        foreach ($infos['order'] as $player)
            $output .=  Erebot::CODE_BOLD.$player.Erebot::CODE_BOLD.': '.
                        $infos['players'][$player]['score'].', ';
        $output = substr($output, 0, -2);
        $this->bot->sendCommand('PRIVMSG '.$chan.' :'.$output);
    }

    public function handleShowScores(ErebotEvent &$event)
    {
        $chan = $event->chan();
        if (!isset($this->chans[$chan])) return;
        $this->showScores($chan);
        $event->haltdef();
    }

    public function handleShowTime(ErebotEvent &$event)
    {
        $chan = $event->chan();
        if (!isset($this->chans[$chan])) return;

        if (!isset($this->chans[$chan]['startTime']))
            $this->bot->sendCommand('PRIVMSG '.$chan.' :The '.$this->getLogo().' game has not yet started!');
        else
            $this->bot->sendCommand('PRIVMSG '.$chan.' :This '.$this->getLogo().' game '.
                                    ' has been running for '.$this->getPlayTime($chan));
        $event->haltdef();
    }

    protected function showTurn($chan, $from)
    {
        $infos  =&  $this->chans[$chan];
        $nick   =   reset($infos['order']);
        if ($from == $nick)
            $this->bot->sendCommand('PRIVMSG '.$chan.' :'.$nick.": it's your turn sleepyhead!");
        else {
            $next = next($infos['order']);
            if (count($infos['players'][$next]['cards']) == 1) {
                if ($infos['discard'] === NULL)
                    $this->bot->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().': '.
                                            Erebot::CODE_BOLD.$nick.Erebot::CODE_BOLD.
                                            ", since there's only 1 card left in ".
                                            Erebot::CODE_BOLD.$next.Erebot::CODE_BOLD."'s hand, ".
                                            'you MUST start with a combination or your highest card!');
                else
                    $this->bot->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().': '.
                                            Erebot::CODE_BOLD.$nick.Erebot::CODE_BOLD.
                                            ", since there's only 1 card left in ".
                                            Erebot::CODE_BOLD.$next.Erebot::CODE_BOLD."'s hand, ".
                                            'you MUST play a combination or your highest card '.
                                            'on this turn!');
            }

            else
                $this->bot->sendCommand('PRIVMSG '.$chan.' :'.$this->getLogo().": It's ".
                                        Erebot::CODE_BOLD.$nick.Erebot::CODE_BOLD."'s turn.");
        }
    }

    public function handleShowTurn(ErebotEvent &$event)
    {
        $chan = $event->chan();
        if (!isset($this->chans[$chan]['order'])) return;
        $this->showTurn($chan, $event->nick());
        $event->haltdef();
    }
}

?>
