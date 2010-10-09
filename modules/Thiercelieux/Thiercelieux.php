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

class Erebot_thiercelieux
{
    const STEP_STARTING     = 1;
    const STEP_FIRST_NIGHT  = 1.5;
    const STEP_NIGHTTIME    = 2;
    const STEP_DAYTIME      = 3;

    const CARD_WEREWOLF     = 0x001;
    const CARD_WITCH        = 0x002;
    const CARD_HUNTER       = 0x004;
    const CARD_LITTLE_GIRL  = 0x008;
    const CARD_CUPIDON      = 0x010;
    const CARD_ESP          = 0x020;
    const CARD_VILLAGER     = 0x040;
    const CARD_LOVER        = 0x080;
    const CARD_CAPTAIN      = 0x100;

    const KILL_SAVED        = 0;
    const KILL_WEREWOLVES   = 1;
    const KILL_WITCH        = 2;
    const KILL_TOWN         = 3;
    const KILL_HUNTER       = 4;
    const KILL_LOVER        = 5;

    const WEREWOLVES_RATIO  = 0.35;
    const DEBUG             = TRUE;

    private $card_names     = array(
        self::CARD_WEREWOLF     => 'a werewolf',
        self::CARD_WITCH        => 'a witch',
        self::CARD_HUNTER       => 'a hunter',
        self::CARD_LITTLE_GIRL  => 'a little girl',
        self::CARD_CUPIDON      => 'Cupidon',
        self::CARD_ESP          => 'an ESP',
        self::CARD_VILLAGER     => 'a villager'
    );

    private $bot    = FALSE;
    private $game   = array();
    private $delays = array(
        'start'         => 120,
        'werewolves'    => 90,
        'cupidons'      => 45,
        'thieves'       => 25,
        'witches'       => 45,
        'hunters'       => 60,
        'esps'          => 45,
    );

    public function __construct(Erebot $bot)
    {
        // Start / play
        $bot->addEvent(ErebotEvent::ON_TEXT,
            array('matchtext' => '!play', 'matchtype' => Erebot::MATCHTEXT_STATIC),
            array($this, 'handlePlay'));
        $bot->addEvent(ErebotEvent::ON_TEXT,
            array('matchtext' => '!votestatus', 'matchtype' => Erebot::MATCHTEXT_STATIC),
            array($this, 'handleVoteStatus'));

        // Werewolves during night, everyone during daylight.
        $bot->addEvent(ErebotEvent::ON_TEXT,    // unvoting
            array('matchtext' => '!vote', 'matchtype' => Erebot::MATCHTEXT_STATIC),
            array($this, 'handleVote'));
        $bot->addEvent(ErebotEvent::ON_TEXT,
            array('matchtext' => '!vote &', 'matchtype' => Erebot::MATCHTEXT_WILDCARD),
            array($this, 'handleVote'));

        // Werewolves
/*        $bot->addEvent(ErebotEvent::ON_TEXT,
            array(
                'matchtext' => '!check &',
                'matchtype' => Erebot::MATCHTEXT_WILDCARD,
                'chans'        => array()
            ),
            array($this, 'handleCheck'));
*/        $bot->addEvent(ErebotEvent::ON_TEXT,
            array('chans' => array()), array($this, 'handleRawText'));

        // Little girl
/*        $bot->addEvent(ErebotEvent::ON_TEXT,
            array(
                'matchtext' => '!watch',
                'matchtype' => Erebot::MATCHTEXT_STATIC,
                'chans'        => array()
            ),
            array($this, 'handleWatch'));
        $bot->addEvent(ErebotEvent::ON_TEXT,
            array(
                'matchtext' => '!unwatch',
                'matchtype' => Erebot::MATCHTEXT_STATIC,
                'chans'        => array()
            ),
            array($this, 'handleWatch'));
*/
        // ESP
/*        $bot->addEvent(ErebotEvent::ON_TEXT,
            array(
                'matchtext' => '!reveal &',
                'matchtype' => Erebot::MATCHTEXT_WILDCARD,
                'chans'        => array()
            ),
            array($this, 'handleReveal'));
*/
        // Witch
/*        $bot->addEvent(ErebotEvent::ON_TEXT,
            array(
                'matchtext' => '!poison &',
                'matchtype' => Erebot::MATCHTEXT_WILDCARD,
                'chans'        => array()
            ),
            array($this, 'handlePoison'));
        $bot->addEvent(ErebotEvent::ON_TEXT,
            array(
                'matchtext' => '!potion',
                'matchtype' => Erebot::MATCHTEXT_STATIC,
                'chans'        => array()
            ),
            array($this, 'handlePotion'));
*/
        // Hunter
/*        $bot->addEvent(ErebotEvent::ON_TEXT,
            array(
                'matchtext' => '!hunt &',
                'matchtype' => Erebot::MATCHTEXT_WILDCARD,
                'chans'        => array()
            ),
            array($this, 'handleHunt'));
*/
        // Cupidon
/*        $bot->addEvent(ErebotEvent::ON_TEXT,
            array(
                'matchtext' => '!love',
                'matchtype' => Erebot::MATCHTEXT_STATIC,
                'chans'        => array()
            ),
            array($this, 'handleLove'));
*/
        $this->bot = $bot;
    }

    private function checkVictory($chan)
    {
        if (!isset($this->game[$chan])) return;

        $lovers     = $this->getPlayersByCard($chan, self::CARD_LOVER);
        $nb_lovers    = count($lovers);

        if ($nb_lovers == count($this->game[$chan]['players']))
            return self::CARD_LOVER;

        $wolves         = $this->getPlayersByCard($chan, self::CARD_WEREWOLF);
        $villagers        = $this->getPlayersByCard($chan, self::CARD_VILLAGER);
        $lovers            = $this->getPlayersByCard($chan, self::CARD_LOVER);

        $nb_wolves        = count($wolves);
        $nb_villagers    = count($villagers);
        $nb_lovers        = count($lovers);

        if (!$nb_wolves) {
            $res = self::CARD_VILLAGER;
            if ($nb_lovers)
                $res |= self::CARD_LOVER;
            return $res;
        }

        if ($nb_wolves > $nb_villagers) {
            $res = self::CARD_WEREWOLF;
            if ($nb_lovers)
                $res |= self::CARD_LOVER;
            return $res;
        }

        if ($nb_wolves == 1 && $nb_villagers == 1)
            return self::CARD_WEREWOLF;

        return FALSE;
    }

    private function declareWinner($chan, $victory)
    {
        if ($victory === FALSE)
            return FALSE;

        if ($victory & self::CARD_WEREWOLF) {
            $wolves = $this->getPlayersByCard($chan, self::CARD_WEREWOLF);
            $this->bot->sendCommand('PRIVMSG '.
                $chan." :Beware! The werewolves (".
                implode(', ', $this->getNicks($chan, $wolves)).
                ") won this game!");
        }

        if ($victory & self::CARD_VILLAGER) {
            $villagers = $this->getPlayersByCard($chan, self::CARD_VILLAGER);
            $this->bot->sendCommand('PRIVMSG '.
                $chan." :Hurrah! The villagers (".
                implode(', ', $this->getNicks($chan, $villagers)).
                ") won this game!");
        }

        if ($victory & self::CARD_LOVER) {
            $lovers = $this->getPlayersByCard($chan, self::CARD_LOVER);
            if ($victory == self::CARD_LOVER)
                $this->bot->sendCommand('PRIVMSG '.
                    $chan." :Congratulations to our lovers (".
                    implode(', ', $lovers).") who made it 'til the end.");
            else $this->bot->sendCommand('PRIVMSG '.$chan.
                " :Congratulations to ".implode(', ',
                    $this->getNicks($chan, $lovers)).
                " who are the super-winners of this game".
                " by staying alive 'til the end!");
        }

        $this->stopGame($chan);
        return TRUE;
    }

    private function getNicks($chan, $arr)
    {
        $nicks = array();
        if (!is_array($arr)) $arr = array($arr);
        foreach ($arr as $nick)
            $nicks[] = $this->game[$chan]['players'][$nick]['nick'];
        return $nicks;
    }

    private function checkVotes($chan)
    {
        $players = array_keys($this->game[$chan]['players']);

        if ($this->game[$chan]['step'] == self::STEP_DAYTIME) {
            $source     = self::KILL_TOWN;
            $voters     = $players;
        }

        else {
            $source     = self::KILL_WEREWOLVES;
            $voters     = $this->getPlayersByCard($chan, self::CARD_WEREWOLF);
        }

        $count        = count($voters);
        $votes         = array_combine($players, array_fill(0, count($players), 0));
        $nb_votes     = 0;

        foreach ($voters as $voter) {
            if (isset($this->game[$chan]['players'][$voter]['vote'])) {
                $weight = 1;
                // Captains' voice has twice as much
                // weight as that of the others.
                if ($this->game[$chan]['players'][$voter]['card'] & self::CARD_CAPTAIN)
                    $weight++;
                $votes[$this->game[$chan]['players'][$voter]['vote']] += $weight;
                $nb_votes++;
            }
        }
        array_multisort($votes, SORT_DESC, SORT_NUMERIC);

        $target    = FALSE;
        $keys     = array_keys($votes);

        // Absolute majority.
        if (($votes[$keys[0]]) * 2 > $count)
            $target = $keys[0];

        // Relative majority.
        else if (    $nb_votes == $count &&
                    isset($keys[1]) &&
                    $votes[$keys[0]] > $votes[$keys[1]]
                )
            $target = $keys[0];

        if ($target !== FALSE) {
            $this->game[$chan]['victims'][$target] = $source;
            return TRUE;
        }

        return FALSE;
    }

    private function kill($chan, $nick, $source)
    {
        if (!isset($this->game[$chan]['players'][$nick]))
            return FALSE;

        $realNick = $this->game[$chan]['players'][$nick]['nick'];
        switch ($source) {
            case self::KILL_SAVED:
                $this->bot->sendCommand('PRIVMSG '.$chan.
                    ' :Werewolves tried to eat '.$realNick.
                    ', but a witch saved him.');
                return;

            case self::KILL_WEREWOLVES:
                $this->bot->sendCommand('PRIVMSG '.$chan.
                    ' :Werewolves enjoyed eating '.$realNick.'.');
                break;

            case self::KILL_TOWN:
                $this->bot->sendCommand('PRIVMSG '.$chan.
                    ' :On popular demand, '.$realNick.
                    ' was sentenced to death!');
                break;

            case self::KILL_WITCH:
                $this->bot->sendCommand('PRIVMSG '.$chan.
                    ' :Some wicked witch wished werewolves were dead '.
                    'and used poison to get rid of '.$realNick.'.');
                break;

            case self::KILL_HUNTER:
                $this->bot->sendCommand('PRIVMSG '.$chan.
                    ' :Oops... '.$realNick.' got killed by friendly fire.');
                break;

            case self::KILL_LOVER:
                $this->bot->sendCommand('PRIVMSG '.$chan.
                    ' :'.$realNick.' fell in despair over his/her'.
                    " lover's death and committed suicide.");
                break;

            default: return FALSE;
        }

        $card = $this->game[$chan]['players'][$nick]['card'];
        $this->bot->sendCommand('PRIVMSG '.$chan.' :'.
            $this->game[$chan]['players'][$nick]['nick'].
            ' was '.$this->card_names[$card].'.');

        if ($card & self::CARD_LOVER) {
            $lovers = $this->getLoveChain($chan, $nick);
            foreach ($lovers as $lover)
                if ($this->kill($chan, $lover, self::KILL_LOVER))
                    return TRUE;
        }

        $this->game[$chan]['dead'][$nick] = &$this->game[$chan]['players'][$nick];
        unset($this->game[$chan]['players'][$nick]);

        $victory = $this->checkVictory($chan);
        if ($this->declareWinner($chan, $victory))
            return TRUE;

        if ($card & self::CARD_HUNTER) {

        }

        return FALSE;
    }

    private function getPlayersByCard($chan, $card)
    {
        $res = array();

        if (!isset($this->game[$chan]))
            return $res;

        if ($this->game[$chan]['step'] == self::STEP_STARTING)
            return $res;

        foreach ($this->game[$chan]['players'] as $nick => &$data) {
            if ($data['card'] & $card)
                $res[] = $nick;
        }
        return $res;
    }

    private function getChanByPlayer($nick)
    {
        $nick = strtolower($nick);
        foreach ($this->game as $chan => $data)
            if (isset($this->game[$chan]['players'][$nick]))
                return $chan;
        return FALSE;
    }

    private function completeNick($chan, $nick)
    {
        $players     = array_keys($this->game[$chan]['players']);
        $players    = array_filter($players,
            create_function('$a', 'return (!strncmp($a, "'.
                addcslashes($nick, '\\').'", '.strlen($nick).'));'));
        if (count($players)) // Forces reindexing.
            return array_values($players);
        return array($nick);
    }

    private function buildLoveChains($chan)
    {
        $players = array_keys($this->game[$chan]['players']);
        $chains  = array();
        foreach ($players as $player) {
            $chains[$player] = array();
        }
        $this->game[$chan]['lovechains'] = &$chains;
    }

    private function getLoveChain($chan, $nick)
    {
        if (!isset($this->game[$chan]['lovechains'][$nick]))
            return array();
        return $this->game[$chan]['lovechains'][$nick];
    }

    private function actionDay($chan)
    {
        // Revoice players & reinitialize votes.
        $players = array_keys($this->game[$chan]['players']);
        foreach ($players as $player) {
            $this->bot->sendCommand('MODE '.$chan.' +v '.$player);
            unset($this->game[$chan]['players'][$player]['vote']);
        }

        $this->bot->sendCommand('PRIVMSG '.$chan.' :The town wakes up...');

        $victims = $this->getNicks($chan, array_keys(array_filter(
                        $this->game[$chan]['victims'])));
        $this->bot->sendCommand('PRIVMSG '.$chan.' :...only to find out '.
            implode(' & ', $victims).' '.(count($victims) > 1 ? 'are' : 'is').
            ' missing.');

        foreach ($this->game[$chan]['victims'] as $nick => $source) {
            if ($this->kill($chan, $nick, $source)) return;
            unset($this->game[$chan]['victims'][$nick]);
        }

        $this->bot->sendCommand('PRIVMSG '.$chan.
            ' :Now is the time to hunt down werewolves!');
    }

    private function actionNight($chan)
    {
        // Unvoice players & reinitialize votes.
        $players = array_keys($this->game[$chan]['players']);
        foreach ($players as $player) {
            $this->bot->sendCommand('MODE '.$chan.' +v '.$player);
            unset($this->game[$chan]['players'][$player]['vote']);
        }

        $wolves     = $this->getPlayersByCard($chan, self::CARD_WEREWOLF);
        $cupidons     = $this->getPlayersByCard($chan, self::CARD_CUPIDON);
        $girls         = $this->getPlayersByCard($chan, self::CARD_LITTLE_GIRL);

        foreach ($girls as $girl) {
            $this->game[$chan]['players'][$girl]['watching'] = FALSE;
            $this->bot->sendCommand('PRIVMSG '.$girl.
                " :You may seeminglessly listen to what werewolves say ".
                "by typing !watch IN THIS WINDOW.");
            $this->bot->sendCommand('PRIVMSG '.$girl.
                " :But be careful! If the werewolves find out you're spying".
                "on them, you'll automatically become their next target.");
            $this->bot->sendCommand('PRIVMSG'.$girl.
                " :You may stop listening to what werewolves say ".
                "by typing !unwatch in this window.");
        }

        foreach ($wolves as $wolf) {
            $this->bot->sendCommand('PRIVMSG '.$wolf.
                " :You must decide who will die tonight. ".
                "Use !vote <nick> to eat <nick>. ".
                "You have ".$this->delays['werewolves'].
                " seconds to decide.");

            $other_wolves = array_filter($wolves,
                create_function('$a', 'return $a != "'.$wolf.'";'));
            if (count($other_wolves))
                $this->bot->sendCommand('PRIVMSG '.$wolf.
                    " :You may discuss with other werewolves (".
                    implode(', ', $this->getNicks($chan, $other_wolves)).
                    ") by typing directly in this window.");

            if (count($girls))
                $this->bot->sendCommand('PRIVMSG '.$wolf.
                    " :You may also check whether <nick> is ".
                    "spying on werewolves by issuing !check <nick>.");
        }

        if (count($wolves)) {
            $this->addTimer(array($this, 'handleTimer'), $chan, 'Werewolves');
        }

        if ($this->game[$chan]['step'] == self::STEP_FIRST_NIGHT) {
            foreach ($cupidons as $cupidon) {
                $this->bot->sendCommand('PRIVMSG '.$cupidon.
                    " :You must now choose 2 people who are to fall in love. ".
                    "You may be one of them. Use !love <nick1> <nick2> to choose.");
                $this->bot->sendCommand('PRIVMSG '.$cupidon.
                    "Lovers may not vote against each other. ".
                    "They must survive until the end to win the game.");
            }

            if (count($cupidons)) {
                $this->addTimer(array($this, 'handleTimer'), $chan, 'Cupidons');
            }
        }
    }

    private function startGame($chan)
    {
        $count    = count($this->game[$chan]['players']);
        $i = (int) ($count * self::WEREWOLVES_RATIO);
        if (!$i) {
            $this->bot->sendCommand('PRIVMSG '.$chan.
                ' :Not enough players... Stopping the game.');
            $this->stopGame($chan);
            return FALSE;
        }

        $wolves    = array_fill(0, $i,             self::CARD_WEREWOLF);
        $people = array_fill(0, $count - $i,     self::CARD_VILLAGER);
        $chars    = array_merge($wolves, $people);
        shuffle($chars);

        $this->game[$chan]['step'] = self::STEP_FIRST_NIGHT;
        $this->game[$chan]['dead'] = array();

        $this->bot->sendCommand('PRIVMSG '.$chan.
            ' :Ok, the game started... Everyone goes to bed '.
            'not knowing what may await them.');

        if (!self::DEBUG)
            $this->bot->sendCommand('MODE '.$chan.' +iN');

        $i = 0;
        foreach ($this->game[$chan]['players'] as $nick => &$data) {
            $data['card'] = $chars[$i++];
            $this->bot->sendCommand('PRIVMSG '.$nick." :You're ".
                $this->card_names[$data['card']]);
        }

        $this->actionNight($chan);
    }

    private function stopGame($chan)
    {
        if (!self::DEBUG)
            $this->bot->sendCommand('MODE '.$chan.' -iN');

        foreach ($this->game[$chan]['players'] as $player => $null)
            $this->bot->sendCommand('MODE '.$chan.' -v '.$player);
        unset($this->game[$chan]);

        $timers = array('Start', 'Werewolves', 'Cupidons', 'ESP', 'Witch', 'Hunter');
        foreach ($timers as $timer) {
            $this->removeTimer($chan, $timer);
        }
    }

    public function handleTimer($name, $delay)
    {
        list($id, $type, $chan) = explode(' ', $name);
        if (!isset($this->game[$chan]['step'])) return;

        switch ($this->game[$chan]['step']) {
            case self::STEP_STARTING:
                $this->startGame($chan);
                break;

            case self::STEP_FIRST_NIGHT:
            case self::STEP_NIGHTTIME:
                switch ($type) {
                    case 'Werewolves':
                        $res = self::CARD_VILLAGER;
                        $wolves = $this->getPlayersByCard($chan, self::CARD_WEREWOLF);
                        $lovers = $this->getPlayersByCard($chan, self::CARD_LOVER);
                        if (count($lovers))
                            $res |= self::CARD_LOVER;
                        $this->bot->sendCommand('PRIVMSG '.$chan.
                            " :The werewolves (".implode(', ',
                            $this->getNicks($chan, $wolves)).
                            ") died from famine.");
                        $this->declareWinner($chan, $res);
                        break;
                }
                break;

            default:
                $this->stopGame($chan);
                $this->bot->sendCommand('PRIVMSG '.$chan.
                    " :Bad state in ".__METHOD__);
        }
    }

    public function handleRawText(ErebotEvent &$event, $filters)
    {
        $nick    = strtolower($event->nick());
        $chan     = $this->getChanByPlayer($nick);

        if ($chan === FALSE) return;
        if ($this->game[$chan]['step'] != self::STEP_NIGHTTIME &&
            $this->game[$chan]['step'] != self::STEP_FIRST_NIGHT)
            return;

        $wolves    = $this->getPlayersByCard($chan, self::CARD_WEREWOLF);
        if (!in_array($nick, $wolves)) return;

        $girls    = $this->getPlayersByCard($chan, self::CARD_LITTLE_GIRL);
        foreach ($girls as $girl) {
            if ($this->game[$chan]['players'][$girl]['watching'])
                $wolves += $girl;
        }

        $time = date('H:n:s');
        foreach ($wolves as $wolf) {
            if ($wolf != $nick)
                $this->bot->sendCommand('PRIVMSG '.$wolf.' :['.$time.
                    '] <'.$this->game[$chan]['players'][$nick]['nick'].
                    '> '.$event->text());
        }
    }

    public function handlePlay(ErebotEvent &$event, $filters)
    {
        $nick    = $event->nick();
        $chan = $event->target();

        if (!$this->bot->isChannel($chan)) {
            $this->bot->sendCommand('PRIVMSG '.$chan.' :This command can only be used on channels.');
            return;
        }

        if (isset($this->game[$chan]['step']) &&
            $this->game[$chan]['step'] != self::STEP_STARTING) {
            $this->bot->sendCommand('PRIVMSG '.$chan.' :A game has already started!');
            return;
        }

        $modules = $this->bot->getModules();
        if (!in_array('chmodes', $modules)) {
            $this->bot->sendCommand('PRIVMSG '.$chan.' :Required module not found! (chmodes)');
            return;
        }

        $status             = Erebot_chmodes::getUserStatus(
                                $chan, $this->bot->botNick());
        $required_status    = Erebot_chmodes::STATUS_HALFOP
                            | Erebot_chmodes::STATUS_OPERATOR;

        if ($status === FALSE || !($status & $required_status)) {
            $this->bot->sendCommand('PRIVMSG '.$chan.
                ' :The bot must be (half)op on the channel!');
            return;
        }

        if ($ch = $this->getChanByPlayer($nick)) {
            $this->bot->sendCommand('PRIVMSG '.$nick.
                " :You're already part of a game on ".$ch.
                ". You can only play on 1 channel at a time.");
            return;
        }

        if (!isset($this->game[$chan]['players'])) {
            $this->game[$chan]['step'] = self::STEP_STARTING;
            $this->addTimer(array($this, 'handleTimer'), $chan, 'Start');

            $this->bot->sendCommand('PRIVMSG '.$chan.' :Hallowed be '.$nick.
                ' for he started a new game!');
            $this->bot->sendCommand('PRIVMSG '.$chan.' :You have '.
                $this->delays['start'].' secs to register before the game starts.');
            $this->bot->sendCommand('PRIVMSG '.$chan.' :Type "!play" to register yourself.');
        }

        $this->game[$chan]['players'][strtolower($nick)] = array('nick' => $nick);
        $this->bot->sendCommand('MODE '.$chan.' +v '.$nick);
        $this->bot->sendCommand('PRIVMSG '.$nick." :So be it!");
    }

    public function handleVote(ErebotEvent &$event, $filters)
    {
        $nb_words     = $this->bot->numtok($event->text());
        $nick        = strtolower($event->nick());
        $chan        = $event->chan();

        if ($chan === NULL)
            $chan = $this->getChanByPlayer($nick);

        if ($chan === FALSE) return;

        $voters = array();
        $alive     = array_keys($this->game[$chan]['players']);
        $step    = $this->game[$chan]['step'];
        switch ($step) {
            case self::STEP_DAYTIME:
                $voters = &$alive;
                break;

            case self::STEP_FIRST_NIGHT:
            case self::STEP_NIGHTTIME:
                if ($this->timedOut($chan, array('Werewolves'))) return;
                $voters = &$this->getPlayersByCard($chan, self::CARD_WEREWOLF);
                break;

            default:
                return;
        }

        // This person is not allowed to !vote right now.
        if (!in_array($nick, $voters)) return;

        $lovechain = $this->getLoveChain($chan, $nick);

        if ($step == self::STEP_DAYTIME)
            $targets = array_diff($alive, $lovechain);
        else $targets = array_diff($alive, $voters, $lovechain);

        // Force reindexing of (numeric) keys.
        $targets = array_values($targets);

        if ($nb_words == 1) {
            unset($this->game[$chan]['players'][$nick]['vote']);
            $this->bot->sendCommand('PRIVMSG '.$nick.' :Your vote has been unset.');
            return;
        }

        $arg = $this->bot->gettok($event->text(), 1, 1);
        $foe = $this->completeNick($chan, strtolower($arg));

        if (count($foe) > 1) {
            $this->bot->sendCommand('PRIVMSG '.$nick.
                ' :Mulitple matches found for "'.$arg.'" ('.
                implode(', ', $this->getNicks($chan, $foe)).')');
            $this->bot->sendCommand('PRIVMSG '.$nick.
                ' :Please be a little more specific...');
            return;
        }
        else $foe = $foe[0];

        if ($foe == '*')
            $foe = $targets[rand(0, count($targets) - 1)];

        if (!in_array($foe, $targets)) {
            $this->bot->sendCommand('PRIVMSG '.$nick.
                " :You may not vote against this person!");
            $this->bot->sendCommand('PRIVMSG '.$nick.' :Possible targets: '.
                implode(', ', $this->getNicks($chan, $targets)));
            return;
        }

        $this->bot->sendCommand('PRIVMSG '.$nick.
            " :You're now voting against ".
            $this->game[$chan]['players'][$foe]['nick']);
        $this->game[$chan]['players'][$nick]['vote'] = $foe;

        $res = $this->checkVotes($chan);

        if ($res) {
            if ($step == self::STEP_DAYTIME) {
                foreach ($this->game[$chan]['victims'] as $nick => $source) {
                    if ($this->kill($chan, $nick, $source)) return;
                    unset($this->game[$chan]['victims'][$nick]);
                }

                if ($this->timedOut($chan, array('Hunters'))) {
                    $this->game[$chan]['step'] = self::STEP_NIGHTTIME;
                    $this->actionNight($chan);
                }
            }

            else {
                $this->removeTimer($chan, 'Werewolves');
                if ($this->timedOut($chan, array('Witches', 'Cupidons', 'ESPs'))) {
                    $this->game[$chan]['step'] = self::STEP_DAYTIME;
                    $this->actionDay($chan);
                }
            }
        }
    }

    public function handleVoteStatus(ErebotEvent &$event, $filters)
    {
        $chan = $event->chan();
        if ($this->bot->isChannel($chan)) {
            if (!isset($this->game[$chan]))
                $this->bot->sendCommand('PRIVMSG '.$chan.
                    " :No game started on ".$chan."!");
            return;
        }
    }

    private function addTimer($callback, $chan, $type)
    {
        $type = strtolower($type);
        $name = 'Thiercelieux '.$type.' '.$chan;
        $secs = $this->delays[$type];
        $this->game[$chan]['timers'][$type] = TRUE;
        return $this->bot->addTimer($callback, $secs, $name);
    }

    private function removeTimer($chan, $type)
    {
        $res = TRUE;
        $type = strtolower($type);
        unset($this->game[$chan]['timers'][$type]);
        try { $this->bot->removeTimer('Thiercelieux '.$type.' '.$chan); }
        catch (Exception $e) { $res = FALSE; }
        return $res;
    }

    private function timedOut($chan, $timers)
    {
        if (!isset($this->game[$chan])) return NULL;

        if (!is_array($timers))
            $timers = array($timers);

        foreach ($timers as $timer) {
            $timer = strtolower($timer);
            if (isset($this->game[$chan]['timers'][$timer]))
                return FALSE;
        }
        return TRUE;
    }
}

?>
