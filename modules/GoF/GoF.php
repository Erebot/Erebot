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

class   ErebotModule_GoF
extends ErebotModuleBase
{
    static protected $_metadata = array(
        'requires'  =>  array('TriggerRegistry', 'NickTracker'),
    );
    protected $_chans;
    protected $_creator;

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
        if ($flags & self::RELOAD_MEMBERS) {
            $this->_chans    = array();
        }

        if ($flags & self::RELOAD_HANDLERS) {
            $registry   = $this->_connection->getModule('TriggerRegistry', ErebotConnection::MODULE_BY_NAME);
            $matchAny  = ErebotUtils::getVStatic($registry, 'MATCH_ANY');

            if (!($flags & self::RELOAD_INIT)) {
                
                $this->_connection->removeEventHandler($this->_creator['handler']);
                $registry->freeTriggers($this->_creator['trigger'], $matchAny);
            }

            $trigger_create             = $this->parseString('trigger_create', 'gof');
            $this->_creator['trigger']   = $registry->registerTriggers($trigger_create, $matchAny);
            if ($this->_creator['trigger'] === NULL) {
                $translator = $this->getTranslator(FALSE);
                throw new Exception($translator->gettext(
                    'Could not register Gang of Four creation trigger'));
            }

            $filter     = new ErebotTextFilter($this->_mainCfg);
            $filter->addPattern(ErebotTextFilter::TYPE_STATIC, $trigger_create, TRUE);
            $this->_creator['handler']  =   new ErebotEventHandler(
                                                array($this, 'handleCreate'),
                                                'ErebotEventTextChan',
                                                NULL, $filter);
            $this->_connection->addEventHandler($this->_creator['handler']);
        }
    }

    protected function getToken($chan, $nick)
    {
        if (!isset($this->_chans[$chan]['players']))
            return NULL;

        $tracker = $this->getNickTracker();
        foreach (array_keys($this->_chans[$chan]['players']) as $player) {
            if ($tracker->getNick($player) == $nick)
                return $player;
        }
        return NULL;
    }

    protected function & getNickTracker()
    {
        return $this->_connection->getModule('NickTracker',
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
        $registry   =&  $this->_connection->getModule('TriggerRegistry', ErebotConnection::MODULE_BY_NAME);
        $tracker    =&  $this->getNickTracker();
        $infos      =&  $this->_chans[$chan];

        foreach ($infos['players'] as $token => &$data)
            $tracker->stopTracking($token);
        unset($data);

        $tracker->stopTracking($infos['admin']);

        foreach ($infos['handlers'] as &$handler)
            $this->_connection->removeEventHandler($handler);
        unset($handler);

        $registry->freeTriggers($infos['triggers_token'], $chan);
        unset($infos);
        unset($this->_chans[$chan]);
    }

    protected function prepareNextTurn($chan)
    {
        $tracker    =&  $this->getNickTracker();
        $infos      =&  $this->_chans[$chan];
        $last       =   array_shift($infos['order']);
        array_push($infos['order'], $last);
        $translator = $this->getTranslator($chan);

        $next       = reset($infos['order']);
        $nextNick   = $tracker->getNick($next);
        if ($next == $infos['leader']) {
            $infos['discard'] = NULL;
            $msg = $translator->gettext('No player dared to raise the heat! '.
                'You can now start a new combination <b><var name="nick"/></b>'.
                ' :)');
            $tpl = new ErebotStyling($msg, $translator);
            $tpl->assign('nick', $nextNick);
            $this->sendMessage($chan, $tpl->render());
        }

        $this->showTurn($chan, NULL);
        $msg = $translator->gettext('Your cards: <for from="cards" '.
            'item="card" separator=" "><var name="card"/></for>.');
        $tpl = new ErebotStyling($msg, $translator);
        $cards = $infos['players'][$next]['cards'];
        $cards = array_map(array($this, 'getCardText'), $cards);
        $tpl->assign('cards', $cards);
        $this->sendMessage($nextNick, $tpl->render());
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

    public function dealDeck($chan)
    {
        $infos  =&  $this->_chans[$chan];

        $players    = array_keys($infos['players']);
        $count      = count($players);

        foreach ($players as $player)
            unset($infos['players'][$player]['cards']);

        $starter = NULL;

        $deck        = array();
        $colors             = str_split('gyr');

        // Add colored cards.
        foreach ($colors as $color) {
            for ($i = 0; $i < 2; $i++) {
                for ($j = 1; $j <= 10; $j++)
                    $deck[] = $color.$j;
            }
        }

        // Add special cards.
        $deck[] = 'm1';  // Multi-colored 1
        $deck[] = 'gp';  // Green phoenix
        $deck[] = 'yp';  // Yellow phoenix
        $deck[] = 'rd';  // Red dragon

        shuffle($deck);

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
        $translator =   $this->getTranslator($chan);

        if (isset($this->_chans[$chan])) {
            $admin = $this->_chans[$chan]['admin'];
            $msg = $translator->gettext('A <var name="logo"/> managed '.
                'by <b><var name="admin"/></b> is already running. '.
                'Say "<var name="trigger"/>" to join it.');
            $tpl = new ErebotStyling($msg, $translator);
            $tpl->assign('logo', $this->getLogo());
            $tpl->assign('admin', $tracker->getNick($admin));
            $tpl->assign('trigger', $this->_chans[$chan]['triggers']['join']);
            $this->sendMessage($chan, $tpl->render());
            return $event->preventDefault(TRUE);
        }

        $registry   =   $this->_connection->getModule('TriggerRegistry', ErebotConnection::MODULE_BY_NAME);
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
            $msg = $translator->gettext('Unable to register triggers for '.
                '<var name="logo"/> game!');
            $tpl = new ErebotStyling($msg, $translator);
            $tpl->assign('logo', $this->getLogo());
            $this->sendMessage($chan, $tpl->render());
            return $event->preventDefault(TRUE);
        }

        $this->_chans[$chan]['triggers_token']  =   $token;
        $this->_chans[$chan]['triggers']        =&  $triggers;
        $infos                                  =&  $this->_chans[$chan];

        $filter     = new ErebotTextFilter($this->_mainCfg);
        $filter->addPattern(ErebotTextFilter::TYPE_WILDCARD,    $infos['triggers']['choose'].' *', NULL);
        $infos['handlers']['choose']        =   new ErebotEventHandler(
                                                array($this, 'handleChoose'),
                                                'ErebotEventTextChan',
                                                NULL, $filter);

        $filter     = new ErebotTextFilter($this->_mainCfg);
        $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $infos['triggers']['join'], NULL);
        $infos['handlers']['join']          =   new ErebotEventHandler(
                                                    array($this, 'handleJoin'),
                                                    'ErebotEventTextChan',
                                                    NULL, $filter);

        $filter     = new ErebotTextFilter($this->_mainCfg);
        $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $infos['triggers']['pass'], NULL);
        $infos['handlers']['pass']          =   new ErebotEventHandler(
                                                    array($this, 'handlePass'),
                                                    'ErebotEventTextChan',
                                                    NULL, $filter);

        $filter     = new ErebotTextFilter($this->_mainCfg);
        $filter->addPattern(ErebotTextFilter::TYPE_WILDCARD,    $infos['triggers']['play'].' *', NULL);
        $infos['handlers']['play']          =   new ErebotEventHandler(
                                                    array($this, 'handlePlay'),
                                                    'ErebotEventTextChan',
                                                    NULL, $filter);

        $filter     = new ErebotTextFilter($this->_mainCfg);
        $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $infos['triggers']['show_cards'], NULL);
        $infos['handlers']['show_cards']    =   new ErebotEventHandler(
                                                    array($this, 'handleShowCardsCount'),
                                                    'ErebotEventTextChan',
                                                    NULL, $filter);

        $filter     = new ErebotTextFilter($this->_mainCfg);
        $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $infos['triggers']['show_discard'], NULL);
        $infos['handlers']['show_discard']  =   new ErebotEventHandler(
                                                    array($this, 'handleShowDiscard'),
                                                    'ErebotEventTextChan',
                                                    NULL, $filter);

        $filter     = new ErebotTextFilter($this->_mainCfg);
        $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $infos['triggers']['show_order'], NULL);
        $infos['handlers']['show_order']    =   new ErebotEventHandler(
                                                    array($this, 'handleShowOrder'),
                                                    'ErebotEventTextChan',
                                                    NULL, $filter);

        $filter     = new ErebotTextFilter($this->_mainCfg);
        $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $infos['triggers']['show_scores'], NULL);
        $infos['handlers']['show_scores']   =   new ErebotEventHandler(
                                                    array($this, 'handleShowScores'),
                                                    'ErebotEventTextChan',
                                                    NULL, $filter);

        $filter     = new ErebotTextFilter($this->_mainCfg);
        $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $infos['triggers']['show_time'], NULL);
        $infos['handlers']['show_time']     =   new ErebotEventHandler(
                                                    array($this, 'handleShowTime'),
                                                    'ErebotEventTextChan',
                                                    NULL, $filter);

        $filter     = new ErebotTextFilter($this->_mainCfg);
        $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $infos['triggers']['show_turn'], NULL);
        $infos['handlers']['show_turn'] =   new ErebotEventHandler(
                                                array($this, 'handleShowTurn'),
                                                'ErebotEventTextChan',
                                                NULL, $filter);

        foreach ($infos['handlers'] as &$handler)
            $this->_connection->addEventHandler($handler);
        unset($handler);

        $infos['admin']         =   $tracker->startTracking($nick);
        $infos['players']       =   array();
        $infos['startTime']     =   NULL;
        $infos['rounds']        =   0;
        $infos['direction']     =   self::DIR_COUNTERCLOCKWISE;
        $infos['lastWinner']    =   NULL;
        $infos['lastLoser']     =   NULL;

        $msg = $translator->gettext('Ok! A new <var name="logo"/> game has '.
            'been created in <var name="chan"/>. Say "<var name="trigger"/>" '.
            'to join it.');
        $tpl = new ErebotStyling($msg, $translator);
        $tpl->assign('logo', $this->getLogo());
        $tpl->assign('chan', $chan);
        $tpl->assign('trigger', $infos['triggers']['join']);
        $this->sendMessage($chan, $tpl->render());
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
        foreach ($this->_chans as $name => &$infos) {
            if (isset($infos['timer']) && $infos['timer'] == $timer) {
                $chan = $name;
                break;
            }
        }
        if ($chan === NULL) return;

        $translator     =   $this->getTranslator($chan);
        $tracker        =&  $this->getNickTracker();
        $starter        =   $this->dealDeck($chan);
        $last_winner    =   $infos['lastWinner'];
        $last_loser     =   $infos['lastLoser'];
        $infos['rounds']++;

        if ($last_winner !== NULL) {
            $starter            = $last_winner;
            $infos['bestCard']  = array_pop($infos['players'][$last_loser]['cards']);

            $msg = $translator->gettext('<var name="logo"/>: Ok, this is '.
                'round #<b><var name="round"/></b>, starting after '.
                '<var name="play_time"/>. <b><var name="last_winner"/></b>, '.
                'you must now choose a card to give to '.
                '<b><var name="last_loser"/></b>. You will receive '.
                '<var name="card"/>. Please choose with: '.
                '<var name="trigger"/> &lt;card&gt;.');
            $tpl = new ErebotStyling($msg, $translator);
            $tpl->assign('logo', $this->getLogo());
            $tpl->assign('round', $infos['rounds']);
            $tpl->assign('play_time', $this->getPlayTime($chan));
            $tpl->assign('last_winner', $tracker->getNick($last_winner));
            $tpl->assign('last_loser', $tracker->getNick($last_loser));
            $tpl->assign('card', $this->getCardText($infos['bestCard']));
            $tpl->assign('trigger', $infos['triggers']['choose']);
            $this->sendMessage($chan, $tpl->render());
        }

        else {
            $infos['startTime'] = time();
            $infos['veryFirst'] = TRUE;
            $msg = $translator->gettext('<var name="logo"/>: The game starts '.
                'now. <b><var name="starter"/></b>, you must start this game. '.
                'If you have the <var name="m1"/>, you MUST play it (alone '.
                'or in a combination).');
            $tpl = new ErebotStyling($msg, $translator);
            $tpl->assign('logo', $this->getLogo());
            $tpl->assign('starter', $tracker->getNick($starter));
            $tpl->assign('m1', $this->getCardText('m1'));
            $this->sendMessage($chan, $tpl->render());
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
            if ($player == $last_loser) continue;

            $cards = array_map(array($this, 'getCardText'), $data['cards']);
            $msg = $translator->gettext('Your cards: <for from="cards" '.
                'item="card" separator=" "><var name="card"/></for>.');
            $tpl = new ErebotStyling($msg, $translator);
            $tpl->assign('cards', $cards);
            $this->sendMessage($tracker->getNick($player), $tpl->render());
        }

        $infos['discard']   = NULL;
        $infos['order']     = $players;
    }

    public function handleChoose(ErebotEvent &$event)
    {
        $tracker    =&  $this->getNickTracker();
        $chan       =   $event->getChan();
        $nick       =   $event->getSource();
        if (!isset($this->_chans[$chan])) return;
        $infos      =&  $this->_chans[$chan];

        $token      =   $this->getToken($chan, $nick);
        if (!isset($infos['lastWinner']) || $token === NULL) return;

        $winner     = $infos['lastWinner'];
        $loser      = $infos['lastLoser'];
        if ($token != $winner) return;

        $translator =   $this->getTranslator($chan);
        $card       =   strtolower(str_replace(' ', '',
                            ErebotUtils::gettok($event->getText(), 1, 0)));
        $key        =   array_search($card, $infos['players'][$token]['cards']);
        if ($key === FALSE) {
            $msg = $translator->gettext('<var name="logo"/> You do not have '.
                'that card...');
            $tpl = new ErebotStyling($msg, $translator);
            $tpl->assign('logo', $this->getLogo());
            $this->sendMessage($chan, $tpl->render());
            return $event->preventDefault(TRUE);
        }

        $infos['players'][$loser]['cards'][] = $card;
        unset($infos['players'][$winner]['cards'][$key]);
        $infos['players'][$winner]['cards'][] = $infos['bestCard'];

        $msg = $translator->gettext('<var name="logo"/>: Exchanges: '.
            '<b><var name="winner"/></b> receives a <var name="received"/> '.
            'and gives a <var name="given"/> to <b><var name="loser"/></b>. '.
            '<b><var name="winner"/></b>, you may now start this round.');
        $tpl = new ErebotStyling($msg, $translator);
        $tpl->assign('logo',        $this->getLogo());
        $tpl->assign('winner',      $nick);
        $tpl->assign('loser',       $tracker->getNick($loser));
        $tpl->assign('received',    $this->getCardText($infos['bestCard']));
        $tpl->assign('given',       $this->getCardText($card));
        $tpl->assign('nick', $nextNick);
        $this->sendMessage($chan, $tpl->render());

        usort($infos['players'][$winner]['cards'], array($this, 'compareCards'));
        usort($infos['players'][$loser]['cards'], array($this, 'compareCards'));

        $cards = $infos['players'][$winner]['cards'];
        $cards = array_map(array($this, 'getCardText'), $cards);
        $msg = $translator->gettext('Your cards: <for from="cards" '.
            'item="card" separator=" "><var name="card"/></for>.');
        $tpl = new ErebotStyling($msg, $translator);
        $tpl->assign('cards', $cards);
        $this->sendMessage($nick, $tpl->render());

        $cards = $infos['players'][$loser]['cards'];
        $cards = array_map(array($this, 'getCardText'), $cards);
        $msg = $translator->gettext('Your cards: <for from="cards" '.
            'item="card" separator=" "><var name="card"/></for>.');
        $tpl = new ErebotStyling($msg, $translator);
        $tpl->assign('cards', $cards);
        $this->sendMessage($tracker->getNick($loser), $tpl->render());

        unset($infos['lastWinner']);
        unset($infos['lastLoser']);
        unset($infos['bestCard']);
        $event->preventDefault(TRUE);
    }

    public function handleJoin(ErebotEvent &$event)
    {
        $tracker    =&  $this->getNickTracker();
        $nick       =   $event->getSource();
        $chan       =   $event->getChan();

        if (!isset($this->_chans[$chan])) return;
        $infos      =&  $this->_chans[$chan];

        if ($this->getToken($chan, $nick) !== NULL) return;
        $translator =   $this->getTranslator($chan);

        $count = count($infos['players']);
        if ($count >= 4) {
            $msg = $translator->gettext('<var name="logo"/> This game may '.
                ' only be played by 3-4 players.');
            $tpl = new ErebotStyling($msg, $translator);
            $tpl->assign('logo', $this->getLogo());
            $this->sendMessage($chan, $tpl->render());
            return $event->preventDefault(TRUE);
        }

        if ($infos['startTime'] === NULL) {
            $msg = $translator->gettext('<b><var name="nick"/></b> joins '.
                'this <var name="logo"/> game.');
            $tpl = new ErebotStyling($msg, $translator);
            $tpl->assign('logo', $this->getLogo());
            $tpl->assign('nick', $nick);
            $this->sendMessage($chan, $tpl->render());

            if ($count == 2) {
                $startDelay = $this->parseInt('start_delay', 20);
                if ($startDelay < 0)
                    $startDelay = 20;

                $infos['timer'] = new ErebotTimer(array($this, 'startGame'), $startDelay, FALSE);
                $this->addTimer($infos['timer']);

                $msg = $translator->gettext('The game will start in '.
                    '<var name="delay"/> seconds.');
                $tpl = new ErebotStyling($msg, $translator);
                $tpl->assign('delay', $startDelay);
                $this->sendMessage($chan, $tpl->render());
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
        if (!isset($this->_chans[$chan]['order'])) return;
        $infos      =&  $this->_chans[$chan];

        $token      =   $this->getToken($chan, $nick);
        if ($token === NULL) return;
        $current    =   reset($infos['order']);
        if ($token != $current) return;
        if ($infos['discard'] === NULL) return;

        $translator =   $this->getTranslator($chan);
        $msg = $translator->gettext('<b><var name="nick"/></b> passes turn.');
        $tpl = new ErebotStyling($msg, $translator);
        $tpl->assign('nick', $nick);
        $this->sendMessage($chan, $tpl->render());

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
        if (!isset($this->_chans[$chan]['order'])) return;
        $infos =& $this->_chans[$chan];

        if (isset($infos['lastWinner'])) return;

        $token      = $this->getToken($chan, $nick);
        if ($token === NULL) return;

        $current    = reset($infos['order']);
        $next       = next($infos['order']);
        if ($token != $current) return;

        $translator = $this->getTranslator($chan);

        if (!preg_match('/^(?:[gyr][0-9]+|m1|gp|yp|rd)+$/', $move)) {
            $msg = $translator->gettext('Hmm? What move was that?');
            $tpl = new ErebotStyling($msg, $translator);
            $this->sendMessage($chan, $tpl->render());
            return $event->preventDefault(TRUE);
        }
        preg_match_all('/(?:[gyr][0-9]+|m1|gp|yp|rd)/', $move, $matches);
        $cards  = $matches[0];
        $res    = $this->compareHands($infos['discard'], $cards);

        if ($res === NULL) {
            $msg = $translator->gettext('Hmm? What move was that?');
            $tpl = new ErebotStyling($msg, $translator);
            $this->sendMessage($chan, $tpl->render());
            return $event->preventDefault(TRUE);
        }

        if ($res >= 0) {
            $msg = $translator->gettext('This is not enough for you to win!');
            $tpl = new ErebotStyling($msg, $translator);
            $this->sendMessage($chan, $tpl->render());
            return $event->preventDefault(TRUE);
        }

        $pcards = $infos['players'][$token]['cards'];
        foreach ($cards as $card) {
            $key = array_search($card, $pcards);
            if ($key === FALSE) {
                $msg = $translator->gettext('You do not have the cards '.
                    'required for that move!');
                $tpl = new ErebotStyling($msg, $translator);
                $this->sendMessage($chan, $tpl->render());
                return $event->preventDefault(TRUE);
            }

            unset($pcards[$key]);
        }

        if (isset($infos['veryFirst'])) {
            if (!in_array('m1', $cards) && in_array('m1', $pcards)) {
                $msg = $translator->gettext('This is the very first round. '.
                    'You must play the <var name="m1"/> (alone or in a '.
                    'combination).');
                $tpl = new ErebotStyling($msg, $translator);
                $tpl->assign('m1', $this->getCardText('m1'));
                $this->sendMessage($chan, $tpl->render());
                return $event->preventDefault(TRUE);
            }
            unset($infos['veryFirst']);
        }

        if (count($infos['players'][$next]['cards']) == 1) {
            if (count($cards) == 1 && reset($cards) != end($infos['players'][$token]['cards'])) {
                $msg = $translator->gettext('<b><var name="nick"/></b>, '.
                    '<b><var name="next_player"/></b> has only 1 card left. '.
                    'You <b>MUST</b> play your best card or a combination '.
                    'on this turn!');
                $tpl = new ErebotStyling($msg, $translator);
                $tpl->assign('nick', $nick);
                $tpl->assign('next_player', $tracker->getNick($next));
                $this->sendMessage($chan, $tpl->render());
                return $event->preventDefault(TRUE);
            }
        }

        $infos['players'][$token]['cards'] = $pcards;
        $qualif = $this->qualify($cards);

        $infos['discard']   =   $qualif['cards'];
        $infos['leader']    =   $token;
        $qcards = array_map(array($this, 'getCardText'), $qualif['cards']);

        if (count($pcards) == 1)
            $msg = $translator->gettext('<var name="logo"/>: '.
                '<b><var name="nick"/></b> plays <var name="type"/>: '.
                '<for from="cards" item="card" separator=" "><var '.
                'name="card"/></for> - This is <b><var name="nick"/></b>\'s '.
                'last card!');
        else
            $msg = $translator->gettext('<var name="logo"/>: '.
                '<b><var name="nick"/></b> plays <var name="type"/>: '.
                '<for from="cards" item="card" separator=" "><var '.
                'name="card"/></for>');

        $tpl = new ErebotStyling($msg, $translator);
        $tpl->assign('logo', $this->getLogo());
        $tpl->assign('nick', $nick);
        $tpl->assign('type', $qualif['type']);
        $tpl->assign('cards', $qcards);
        $this->sendMessage($chan, $tpl->render());

        if (empty($pcards)) {
            $msg = $translator->gettext('<var name="logo"/>: '.
                '<b><var name="nick"/></b> wins this round!');
            $tpl = new ErebotStyling($msg, $translator);
            $tpl->assign('logo', $this->getLogo());
            $tpl->assign('nick', $nick);
            $this->sendMessage($chan, $tpl->render());

            $over   = FALSE;
            $losers = array('score' => 0, 'nicks' => array());

            foreach ($infos['players'] as $player => &$data) {
                $score = $this->getScore($data['cards']);
                $data['score'] += $score;

                if ($score > $losers['score']) {
                    $losers['score']    = $score;
                    $losers['tokens']   = array($player);
                }
                else if ($score == $losers['score'])
                    $losers['tokens'][] = $player;

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

                if (empty($winners['tokens'])) {
                    $msg = $translator->gettext('<var name="logo"/>: The game '.
                        'ended on a DRAW after <var name="play_time"/>!');
                    $tpl = new ErebotStyling($msg, $translator);
                    $tpl->assign('logo',        $this->getLogo());
                    $tpl->assign('play_time',   $this->getPlayTime($chan));
                    $this->sendMessage($chan, $tpl->render());
                }
                else {
                    $nicks = array_map(array($tracker, 'getNick'), $winners['tokens']);

                    $msg = $translator->gettext('<var name="logo"/>: After '.
                        '<var name="play_time"/>, <b><var name="rounds"/></b> '.
                        'rounds and with only <b><var name="points"/></b> '.
                        'points, the winners are: <for from="winners" '.
                        'item="winner"><b><var name="winner"/></b></for>.');
                    $tpl = new ErebotStyling($msg, $translator);
                    $tpl->assign('logo',        $this->getLogo());
                    $tpl->assign('play_time',   $this->getPlayTime($chan));
                    $tpl->assign('rounds',      $infos['rounds']);
                    $tpl->assign('points',      $winners['score']);
                    $tpl->assign('winners',     $nicks);
                    $this->sendMessage($chan, $tpl->render());
                }

                $this->cleanup($chan);
                return $event->preventDefault(TRUE);
            }

            if (count($losers['tokens']) == 1)
                $loser = $losers['tokens'][0];
            else {
                $totalLosers = array('score' => 0, 'tokens' => array());

                foreach ($infos['players'] as $player => &$data) {
                    if ($data['score'] > $totalLosers['score']) {
                        $totalLosers['score'] = $data['score'];
                        $totalLosers['tokens'] = array($player);
                    }
                    else if ($data['score'] == $totalLosers['score'])
                        $totalLosers['tokens'][] = $player;
                }

                if (count($totalLosers['tokens']) == 1)
                    $loser = $totalLosers['tokens'][0];

                else {
                    $players    = array_keys($infos['players']);
                    $key        = array_search($nick, $players);
                    $count      = count($players);
                    $loser      = NULL;

                    for ($i = $count-1; $i > 0; $i--) {
                        if (in_array($players[($key + $i) % $count], $totalLosers['tokens'])) {
                            $loser = $players[($key + $i) % $count];
                            break;
                        }
                    }

                    if ($loser === NULL)
                        throw new Exception('Internal error');
                }
            }

            $infos['lastWinner']    = $token;
            $infos['lastLoser']     = $loser;
            $infos['direction']     =   ($infos['direction'] == self::DIR_CLOCKWISE) ?
                                        self::DIR_COUNTERCLOCKWISE : self::DIR_CLOCKWISE;
            unset($infos['leader']);

            $pauseDelay = $this->parseInt('pause_delay', 5);
            if ($pauseDelay < 0)
                $pauseDelay = 5;

            $infos['timer'] = new ErebotTimer(array($this, 'startGame'), $pauseDelay, FALSE);
            $this->addTimer($infos['timer']);

            $msg = $translator->gettext('<var name="logo"/>: The next '.
                'round will start in <var name="delay"/> seconds.');
            $tpl = new ErebotStyling($msg, $translator);
            $tpl->assign('logo',    $this->getLogo());
            $tpl->assign('delay',   $pauseDelay);
            $this->sendMessage($chan, $tpl->render());
            return $event->preventDefault(TRUE);
        }

        $this->prepareNextTurn($chan);
        $event->preventDefault(TRUE);
    }

    public function handleShowCardsCount(ErebotEvent &$event)
    {
        $tracker    =&  $this->getNickTracker();
        $chan       =   $event->getChan();
        $nick       =   $event->getSource();
        if (!isset($this->_chans[$chan])) return;
        $infos =& $this->_chans[$chan];
        $translator = $this->getTranslator($chan);

        if (isset($infos['order'])) {
            $hands = array();
            foreach ($infos['order'] as $player)
                $hands[$tracker->getNick($player)] =
                    count($infos['players'][$player]['cards']);
        
            $msg = $translator->gettext('<var name="logo"/>: Hands: '.
                '<for from="hands" key="nick" item="nb_cards"><b><var '.
                'name="nick"/></b>: <var name="nb_cards"/></for>.');
            $tpl = new ErebotStyling($msg, $translator);
            $tpl->assign('logo',    $this->getLogo());
            $tpl->assign('hands',   $hands);
            $this->sendMessage($chan, $tpl->render());
        }

        $token      = $this->getToken($chan, $nick);
        if ($token !== NULL) {
            $cards = $infos['players'][$token]['cards'];
            $cards = array_map(array($this, 'getCardText'), $cards);
            $msg = $translator->gettext('Your cards: <for from="cards" '.
                'item="card" separator=" "><var name="card"/></for>.');
            $tpl = new ErebotStyling($msg, $translator);
            $tpl->assign('cards', $cards);
            $this->sendMessage($nick, $tpl->render());
        }
        $event->preventDefault(TRUE);
    }

    public function handleShowDiscard(ErebotEvent &$event)
    {
        $tracker    =&  $this->getNickTracker();
        $chan       =   $event->getChan();
        $nick       =   $event->getSource();

        if (!isset($this->_chans[$chan])) return;
        $infos =& $this->_chans[$chan];
        $translator = $this->getTranslator($chan);

        if (empty($infos['discard'])) {
            $token  = $this->getToken($chan, $nick);

            if (isset($infos['leader']) && $token !== NULL &&
                $token == $infos['leader']) {
                $msg = $translator->gettext('<var name="logo"/>: You have '.
                    'the upper hand <b><var name="nick"/></b>. You may now '.
                    'start a new combination :)');
                $tpl = new ErebotStyling($msg, $translator);
                $tpl->assign('logo',    $this->getLogo());
                $tpl->assign('nick',    $nick);
                $this->sendMessage($chan, $tpl->render());
                return $event->preventDefault(TRUE);
            }

            $msg = $translator->gettext('<var name="logo"/>: No card '.
                'has been played in this round yet.');
            $tpl = new ErebotStyling($msg, $translator);
            $tpl->assign('logo',        $this->getLogo());
            $this->sendMessage($chan, $tpl->render());
            return $event->preventDefault(TRUE);
        }

        $translator = $this->getTranslator($chan);
        $qualif = $this->qualify($infos['discard']);
        $qcards = array_map(array($this, 'getCardText'), $qualif['cards']);
        $msg = $translator->gettext('Current discard: <for from="cards" '.
            'item="card" separator=" "><var name="card"/></for> (<var '.
            'name="type"/> played by <b><var name="player"/></b>)');
        $tpl = new ErebotStyling($msg, $translator);
        $tpl->assign('cards', $cards);
        $tpl->assign('type', $qualif['type']);
        $tpl->assign('player', $tracker->getNick($infos['leader']));
        $this->sendMessage($chan, $tpl->render());
        $event->preventDefault(TRUE);
    }

    public function handleShowOrder(ErebotEvent &$event)
    {
        $tracker    =&  $this->getNickTracker();
        $chan       =   $event->getChan();
        if (!isset($this->_chans[$chan]['order'])) return;

        $translator = $this->getTranslator($chan);
        $nicks = array_map(
            array($tracker, 'getNick'),
            $this->_chans[$chan]['order']
        );

        $msg = $translator->gettext('<var name="logo"/>: playing turn: '.
            '<for from="nicks" item="nick"><b><var name="nick"/></b></for>.');
        $tpl = new ErebotStyling($msg, $translator);
        $tpl->assign('logo',    $this->getLogo());
        $tpl->assign('nicks',   $nicks);
        $this->sendMessage($chan, $tpl->render());
        $event->preventDefault(TRUE);
    }

    public function getPlayTime($chan)
    {
        $duration = time() - $this->_chans[$chan]['startTime'];
        $translator = $this->getTranslator($chan);
        return $translator->formatDuration($duration);
    }

    protected function showScores($chan)
    {
        $tracker    =&  $this->getNickTracker();

        if (!isset($this->_chans[$chan]['order'])) return;
        $infos =& $this->_chans[$chan];

        $output = $this->getLogo().': Scores: ';

        $scores = array();
        foreach ($infos['order'] as $player)
            $scores[$tracker->getNick($player)] =
                $infos['players'][$player]['score'];
    
        $msg = $translator->gettext('<var name="logo"/>: Scores: '.
            '<for from="scores" key="nick" item="score"><b><var '.
            'name="nick"/></b>: <var name="score"/></for>.');
        $tpl = new ErebotStyling($msg, $translator);
        $tpl->assign('logo',    $this->getLogo());
        $tpl->assign('scores',  $scores);
        $this->sendMessage($chan, $tpl->render());
    }

    public function handleShowScores(ErebotEvent &$event)
    {
        $chan = $event->getChan();
        if (!isset($this->_chans[$chan])) return;
        $this->showScores($chan);
        $event->preventDefault(TRUE);
    }

    public function handleShowTime(ErebotEvent &$event)
    {
        $chan = $event->getChan();
        if (!isset($this->_chans[$chan])) return;
        $translator = $this->getTranslator($chan);

        if (!isset($this->_chans[$chan]['startTime'])) {
            $msg = $translator->gettext('The <var name="logo"/> game '.
                'has not yet started!');
            $tpl = new ErebotStyling($msg, $translator);
            $tpl->assign('logo',        $this->getLogo());
            $this->sendMessage($chan, $tpl->render());
            return $this->preventDefault(TRUE);
        }

        $msg = $translator->gettext('This <var name="logo"/> game '.
            'has been running for <var name="play_time"/>.');
        $tpl = new ErebotStyling($msg, $translator);
        $tpl->assign('logo',        $this->getLogo());
        $tpl->assign('play_time',   $this->getPlayTime($chan));
        $this->sendMessage($chan, $tpl->render());
        $event->preventDefault(TRUE);
    }

    protected function showTurn($chan, $from)
    {
        $tracker    =&  $this->getNickTracker();
        $infos      =&  $this->_chans[$chan];
        $current    =   reset($infos['order']);
        $translator =   $this->getTranslator($chan);

        if ($from !== NULL && $from == $current) {
            $msg = $translator->gettext('<var name="nick"/>: '.
                'it\'s your turn sleepyhead!');
            $tpl = new ErebotStyling($msg, $translator);
            $tpl->assign('nick', $tracker->getNick($current));
            $this->sendMessage($chan, $tpl->render());
            return;
        }

        $next = next($infos['order']);
        if (count($infos['players'][$next]['cards']) != 1) {
                $msg = $translator->gettext('<var name="logo"/>: It\'s '.
                    '<b><var name="nick"/></b>\'s turn.');
                $tpl = new ErebotStyling($msg, $translator);
                $tpl->assign('logo',    $this->getLogo());
                $tpl->assign('nick',    $tracker->getNick($current));
                $this->sendMessage($chan, $tpl->render());
                return;
        }

        if ($infos['discard'] === NULL) {
            $msg = $translator->gettext('<var name="logo"/>: '.
                '<b><var name="nick"/></b>, since there is only 1 card left '.
                'in <b><var name="next_player"/></b>\'s hand, you MUST start '.
                'with a combination or your best card!');
            $tpl = new ErebotStyling($msg, $translator);
            $tpl->assign('logo',        $this->getLogo());
            $tpl->assign('nick',        $tracker->getNick($current));
            $tpl->assign('next_player', $tracker->getNick($next));
            $this->sendMessage($chan, $tpl->render());
            return;
        }

        $msg = $translator->gettext('<var name="logo"/>: '.
            '<b><var name="nick"/></b>, since there is only 1 card left '.
            'in <b><var name="next_player"/></b>\'s hand, you MUST play '.
            'a combination or your best card on this turn!');
        $tpl = new ErebotStyling($msg, $translator);
        $tpl->assign('logo',        $this->getLogo());
        $tpl->assign('nick',        $tracker->getNick($current));
        $tpl->assign('next_player', $tracker->getNick($next));
        $this->sendMessage($chan, $tpl->render());
    }

    public function handleShowTurn(ErebotEvent &$event)
    {
        $chan       =   $event->getChan();
        if (!isset($this->_chans[$chan]['order'])) return;
        $this->showTurn($chan, $this->getToken($chan, $event->getSource()));
        $event->preventDefault(TRUE);
    }
}

