<?php

ErebotUtils::incl('src/game.php');
ErebotUtils::incl('../../src/styling.php');

class   ErebotModule_Uno
extends ErebotModuleBase
{
    protected $chans;

    const COLOR_RED                     = '00,04';
    const COLOR_GREEN                   = '00,03';
    const COLOR_BLUE                    = '00,12';
    const COLOR_YELLOW                  = '01,08';

    public function reload($flags)
    {
        if ($flags & self::RELOAD_METADATA) {
            $this->addMetadata(self::META_DEPENDS, 'TriggerRegistry');
            $this->addMetadata(self::META_DEPENDS, 'NickTracker');
            $this->addMetadata(self::META_DEPENDS, 'Helper');
        }

        if ($flags & self::RELOAD_MEMBERS) {
            $this->chans    = array();
        }

        if ($flags & self::RELOAD_HANDLERS) {
            $this->handlers = array();

            $registry   = $this->connection->getModule('TriggerRegistry',
                                ErebotConnection::MODULE_BY_NAME);
            $match_any  = ErebotUtils::getVStatic($registry, 'MATCH_ANY');

            if (!($flags & self::RELOAD_INIT)) {
                $this->connection->removeEventHandler($this->creator['handler']);
                $registry->freeTriggers($this->creator['trigger'], $match_any);
            }

            $trigger_create             = $this->parseString('trigger_create', 'uno');
            $this->creator['trigger']   = $registry->registerTriggers($trigger_create, $match_any);
            if ($this->creator['trigger'] === NULL)
                throw new Exception($this->translator->gettext(
                    'Could not register UNO creation trigger'));

            $filter     = new ErebotTextFilter($this->mainCfg);
            $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $trigger_create, TRUE);
            $filter->addPattern(ErebotTextFilter::TYPE_WILDCARD,    $trigger_create.' *', TRUE);
            $this->creator['handler']   =   new ErebotEventHandler(
                                                array($this, 'handleCreate'),
                                                'ErebotEventTextChan',
                                                NULL, $filter);
            $this->connection->addEventHandler($this->creator['handler']);
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

        $translator     = $this->getTranslator($chan);
        $trigger_create = $this->parseString('trigger_create', 'uno');

        $commands   =   array(
                            'challenge'     =>  $this->parseString('trigger_challenge',     'ch'),
                            'choose'        =>  $this->parseString('trigger_choose',        'co'),
                            'draw'          =>  $this->parseString('trigger_draw',          'pe'),
                            'join'          =>  $this->parseString('trigger_join',          'jo'),
                            'pass'          =>  $this->parseString('trigger_pass',          'pa'),
                            'play'          =>  $this->parseString('trigger_play',          'pl'),
                            'show_cards'    =>  $this->parseString('trigger_show_cards',    'ca'),
                            'show_discard'  =>  $this->parseString('trigger_show_discard',  'cd'),
                            'show_order'    =>  $this->parseString('trigger_show_order',    'od'),
                            'show_time'     =>  $this->parseString('trigger_show_time',     'ti'),
                            'show_turn'     =>  $this->parseString('trigger_show_turn',     'tu'),
                        );

        $bot        =&  $this->connection->getBot();
        $moduleName =   $bot->moduleClassToName($this);
        $nbArgs     =   count($words);

        if ($nbArgs == 1 && $words[0] == strtolower($moduleName)) {
            $msg = $translator->gettext('
Provides the <b><var name="trigger_create"/></b> command which starts
a new Uno game. Once a game has been created, other commands become
available to interact with the bot (<for item="command" from="commands"><b><var
name="command"/></b></for>). Use "!help <var name="module"/>
&lt;<u>command</u>&gt;" to get help on some &lt;<u>command</u>&gt;.
');
            $formatter = new ErebotStyling($msg, $translator);
            $formatter->assign('trigger_create', $trigger_create);
            $formatter->assign('commands',  $commands);
            $formatter->assign('module',    $moduleName);
            $this->sendMessage($target, $formatter->render());
            return TRUE;
        }

        else if (($words[0] == $moduleName || isset($this->chans[$chan])) &&
                $nbArgs > 1) {
            foreach ($commands as $cmd => $trigger) {
                if (!strcasecmp($trigger, $words[1])) {
                    switch ($cmd) {
                        case 'challenge':
                            $msg = $translator->gettext('
You may only use this command after someone played a <var name="w+4"/>
and no other penalty had been played before. It shows you the hand of the
player you challenged. If that person played a <var name="w+4"/> while he
or she had a card of the proper color (except for special cards like +2,
Skip or Reverse), that player must draw 4 cards. Otherwise, you must draw
the 4 initial cards, plus 2 additional cards.
'); break;

                        case 'choose':
                            $msg = $translator->gettext('
Select the new color after you played a <var name="w"/> or <var name="w+4"/>,
eg. "<var name="choose"/> &lt;<u>color</u>&gt;". Valid &lt;<u>color</u>&gt;s:
<b>r</b> (red), <b>b</b> (blue), <b>g</b> (green) &amp; <b>y</b> (yellow).
The new color may also be selected directly when playing the card,
eg. "<var name="play"/> w+4b".
'); break;

                        case 'draw':
                            $msg = $translator->gettext('
Draw a new card. You may choose to play the card you just
drew afterwards, using the "<var name="play"/>" command.
This command can also be used to draw penalty cards and pass your turn.
'); break;

                        case 'join':
                            $msg = $translator->gettext('
Join the current <var name="logo"/> game.
The bot will send you the list of your cards in a separate query.
'); break;

                        case 'pass':
                            $msg = $translator->gettext('
Pass your turn. Note that you must first draw a card with <var name="draw"/>
before you pass.
This command can also be used to draw penalty cards and pass your turn.
'); break;

                        case 'play':
                            $msg = $translator->gettext('
Play a card. The card must be described using its mnemonic. Eg.
"<var name="play"/> r1" to play <var name="r1"/><var name="reset"/>,
"<var name="play"/> r+2" to play <var name="r+2"/><var name="reset"/>,
"<var name="play"/> rs" to play <var name="rs"/><var name="reset"/>,
"<var name="play"/> rr" to play <var name="rr"/><var name="reset"/>,
"<var name="play"/> w" to play <var name="w"/><var name="reset"/> and
"<var name="play"/> w+4" to play <var name="w+4"/><var name="reset"/>.
'); break;

                        case 'show_cards':
                            $msg = $translator->gettext('
Displays the number of cards in each players\'s hand.
Also displays your hand in a separate query.
'); break;

                        case 'show_discard':
                            $msg = $translator->gettext('
Displays the top card of the discard.
'); break;

                        case 'show_order':
                            $msg = $translator->gettext('
Displays the order in which players take turns to play.
'); break;

                        case 'show_time':
                            $msg = $translator->gettext('
Displays the ellapsed time since the beginning of the game.
'); break;

                        case 'show_turn':
                            $msg = $translator->gettext('
Displays the nickname of the player whose turn it is.
'); break;

                        default:
                            throw new EErebotInvalidValue('Unknown command');
                    }
                    $formatter = new ErebotStyling($msg, $translator);
                    $formatter->assign('w', $this->getCardText('w'));
                    $formatter->assign('w+4', $this->getCardText('w+4'));
                    $formatter->assign('r1', $this->getCardText('r1'));
                    $formatter->assign('r+2', $this->getCardText('r+2'));
                    $formatter->assign('rs', $this->getCardText('rs'));
                    $formatter->assign('rr', $this->getCardText('rr'));
                    $formatter->assign('logo', $this->getLogo());
                    $formatter->assign('reset', $formatter::CODE_RESET);
                    foreach ($commands as $cmd => $trg)
                        $formatter->assign($cmd, $trg);
                    $this->sendMessage($target, $formatter->render());
                    return TRUE;
                }
            }
        }
    }

    protected function & getNickTracker()
    {
        return $this->connection->getModule('NickTracker',
                    ErebotConnection::MODULE_BY_NAME);
    }

    protected function getLogo()
    {
        return  ErebotStyling::CODE_BOLD.
                ErebotStyling::CODE_COLOR.'04U'.
                ErebotStyling::CODE_COLOR.'03N'.
                ErebotStyling::CODE_COLOR.'12O'.
                ErebotStyling::CODE_COLOR.'08!'.
                ErebotStyling::CODE_COLOR.
                ErebotStyling::CODE_BOLD;
    }

    protected function getColoredCard($color, $text)
    {
        $text       = ' '.$text.' ';
        $colorCodes =   array(
                            'r' => self::COLOR_RED,
                            'g' => self::COLOR_GREEN,
                            'b' => self::COLOR_BLUE,
                            'y' => self::COLOR_YELLOW,
                        );

        if (!isset($colorCodes[$color]))
            throw new Exception(sprintf('Unknown color! (%s, %s)',
                                        $color, $text));

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
                        self::COLOR_BLUE,
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

    protected function getCardText($card)
    {
        if ($card[0] == 'w') {
            $text = 'Wild'.(substr($card, 1, 1) == '+' ? ' +4' : '');
            return $this->wildify($text);
        }

        $colors = array(
            'r' => 'Red',
            'g' => 'Green',
            'b' => 'Blue',
            'y' => 'Yellow',
        );

        $words  = array(
            '+' => '+2',
            'r' => 'Reverse',
            's' => 'Skip',
        );

        if (!isset($card[1]))
            return $this->getColoredCard($card[0], $colors[$card[0]]);

        if (isset($words[$card[1]]))
            return $this->getColoredCard($card[0], $colors[$card[0]].' '.$words[$card[1]]);

        return $this->getColoredCard($card[0], $colors[$card[0]].' '.$card[1]);
    }

    protected function getCurrentPlayer($chan)
    {
        if (!isset($this->chans[$chan]['game']))
            return NULL;
        if (count($this->chans[$chan]['game']->getPlayers()) < 2)
            return NULL;
        return $this->chans[$chan]['game']->getCurrentPlayer();
    }

    protected function showTurn(iErebotEvent &$event)
    {
        $synEvent = new ErebotEventTextChan(
                        $event->getConnection(),
                        $event->getChan(),
                        NULL, '');
        $this->handleShowTurn($synEvent);
    }

    public function handleCreate(iErebotEvent &$event)
    {
        $tracker    =&  $this->getNickTracker();
        $nick       =   $event->getSource();
        $chan       =   $event->getChan();
        $text       =   strtolower($event->getText());
        $translator =   $this->getTranslator($chan);

        if (isset($this->chans[$chan])) {
            $infos      =&  $this->chans[$chan];
            $creator    =   $infos['game']->getCreator();
            $message    =   $translator->gettext('<var name="logo"/> A game '.
                                'is already running, managed by <var name="'.
                                'creator"/>. The following rules apply: <for '.
                                'from="rules" item="rule"><var name="rule"/>'.
                                '</for>. Say "<b><var name="trigger"/></b>" '.
                                'to join it.');
            $tpl        = new ErebotStyling($message, $translator);

            $tpl->assign('logo',    $this->getLogo());
            $tpl->assign('creator', $tracker->getNick($creator));
            $tpl->assign('rules',   $infos['game']->getRules(TRUE));
            $tpl->assign('trigger', $infos['triggers']['join']);
            $this->sendMessage($chan, $tpl->render());
            return $event->preventDefault(TRUE);
        }

        $registry   =   $this->connection->getModule('TriggerRegistry', ErebotConnection::MODULE_BY_NAME);
        $triggers   =   array(
                            'challenge'     =>  $this->parseString('trigger_challenge',     'ch'),
                            'choose'        =>  $this->parseString('trigger_choose',        'co'),
                            'draw'          =>  $this->parseString('trigger_draw',          'pe'),
                            'join'          =>  $this->parseString('trigger_join',          'jo'),
                            'pass'          =>  $this->parseString('trigger_pass',          'pa'),
                            'play'          =>  $this->parseString('trigger_play',          'pl'),
                            'show_cards'    =>  $this->parseString('trigger_show_cards',    'ca'),
                            'show_discard'  =>  $this->parseString('trigger_show_discard',  'cd'),
                            'show_order'    =>  $this->parseString('trigger_show_order',    'od'),
                            'show_time'     =>  $this->parseString('trigger_show_time',     'ti'),
                            'show_turn'     =>  $this->parseString('trigger_show_turn',     'tu'),
                        );
        $token  = $registry->registerTriggers($triggers, $chan);
        if ($token === NULL) {
            $message = $translator->gettext('Unable to register triggers for '.
                                            '<var name="logo"/> game!');
            $tpl = new ErebotStyling($message, $translator);
            $tpl->assign('logo', $this->getLogo());
            $this->sendMessage($chan, $tpl->render());
            return $event->preventDefault(TRUE);
        }

        $this->chans[$chan] = array();
        $infos  =&  $this->chans[$chan];
        $rules  =   ErebotUtils::gettok($text, 1);

        if (trim($rules) == '')
            $rules = $this->parseString('default_rules', '');

        $creator                    =   $tracker->startTracking($nick);
        $infos['triggers_token']    =   $token;
        $infos['triggers']          =&  $triggers;
        $infos['game']              =   new Uno($creator, $rules);

        $filter     = new ErebotTextFilter($this->mainCfg);
        $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $triggers['challenge'], NULL);
        $infos['handlers']['challenge']     =   new ErebotEventHandler(
                                                    array($this, 'handleChallenge'),
                                                    'ErebotEventTextChan',
                                                    NULL, $filter);

        $filter     = new ErebotTextFilter($this->mainCfg);
        $filter->addPattern(ErebotTextFilter::TYPE_WILDCARD,    $triggers['choose'].' *', NULL);
        $infos['handlers']['choose']        =   new ErebotEventHandler(
                                                    array($this, 'handleChoose'),
                                                    'ErebotEventTextChan',
                                                    NULL, $filter);

        $filter     = new ErebotTextFilter($this->mainCfg);
        $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $triggers['draw'], NULL);
        $infos['handlers']['draw']          =   new ErebotEventHandler(
                                                    array($this, 'handleDraw'),
                                                    'ErebotEventTextChan',
                                                    NULL, $filter);

        $filter     = new ErebotTextFilter($this->mainCfg);
        $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $triggers['join'], NULL);
        $infos['handlers']['join']          =   new ErebotEventHandler(
                                                    array($this, 'handleJoin'),
                                                    'ErebotEventTextChan',
                                                    NULL, $filter);

        $filter     = new ErebotTextFilter($this->mainCfg);
        $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $triggers['pass'], NULL);
        $infos['handlers']['pass']          =   new ErebotEventHandler(
                                                    array($this, 'handlePass'),
                                                    'ErebotEventTextChan',
                                                    NULL, $filter);

        $filter     = new ErebotTextFilter($this->mainCfg);
        $filter->addPattern(ErebotTextFilter::TYPE_WILDCARD,    $triggers['play'].' *', NULL);
        $infos['handlers']['play']          =   new ErebotEventHandler(
                                                    array($this, 'handlePlay'),
                                                    'ErebotEventTextChan',
                                                    NULL, $filter);

        $filter     = new ErebotTextFilter($this->mainCfg);
        $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $triggers['show_cards'], NULL);
        $infos['handlers']['show_cards']    =   new ErebotEventHandler(
                                                    array($this, 'handleShowCardsCount'),
                                                    'ErebotEventTextChan',
                                                    NULL, $filter);

        $filter     = new ErebotTextFilter($this->mainCfg);
        $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $triggers['show_discard'], NULL);
        $infos['handlers']['show_discard']  =   new ErebotEventHandler(
                                                    array($this, 'handleShowDiscard'),
                                                    'ErebotEventTextChan',
                                                    NULL, $filter);

        $filter     = new ErebotTextFilter($this->mainCfg);
        $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $triggers['show_order'], NULL);
        $infos['handlers']['show_order']    =   new ErebotEventHandler(
                                                    array($this, 'handleShowOrder'),
                                                    'ErebotEventTextChan',
                                                    NULL, $filter);

        $filter     = new ErebotTextFilter($this->mainCfg);
        $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $triggers['show_time'], NULL);
        $infos['handlers']['show_time']     =   new ErebotEventHandler(
                                                    array($this, 'handleShowTime'),
                                                    'ErebotEventTextChan',
                                                    NULL, $filter);

        $filter     = new ErebotTextFilter($this->mainCfg);
        $filter->addPattern(ErebotTextFilter::TYPE_STATIC,      $triggers['show_turn'], NULL);
        $infos['handlers']['show_turn']     =   new ErebotEventHandler(
                                                    array($this, 'handleShowTurn'),
                                                    'ErebotEventTextChan',
                                                    NULL, $filter);

        foreach ($infos['handlers'] as &$handler)
            $this->connection->addEventHandler($handler);

        $message = $translator->gettext('<var name="logo"/> A new game has been '.
                        'created in <var name="chan"/>. The following rules '.
                        'apply: <for from="rules" item="rule"><var '.
                        'name="rule"/></for>. Say "<b><var name="trigger"/>'.
                        '</b>" to join it.');
        $tpl = new ErebotStyling($message, $translator);
        $tpl->assign('logo',    $this->getLogo());
        $tpl->assign('chan',    $chan);
        $tpl->assign('rules',   $infos['game']->getRules(TRUE));
        $tpl->assign('trigger', $infos['triggers']['join']);
        $this->sendMessage($chan, $tpl->render());
        return $event->preventDefault(TRUE);
    }

    public function handleChallenge(iErebotEvent &$event)
    {
        $tracker    =&  $this->getNickTracker();
        $chan       =   $event->getChan();
        $nick       =   $event->getSource();
        $current    =   $this->getCurrentPlayer($chan);
        $game       =&  $this->chans[$chan]['game'];
        $translator =   $this->getTranslator($chan);

        if ($current === NULL) return;
        $currentNick    =   $tracker->getNick($current->getPlayer());
        if (strcasecmp($nick, $currentNick)) return;

        // We must fetch the last player's entry before calling challenge()
        // because challenge() may change the current player.
        $lastPlayer = $game->getLastPlayer();
        try {
            $challenge = $game->challenge();
        }
        catch (EUnoCannotBeChallenged $e) {
            $message = $translator->gettext('<var name="logo"/> Previous move '.
                            'cannot be challenged!');
            $tpl = new ErebotStyling($message, $translator);
            $tpl->assign('logo', $this->getLogo());
            $this->sendMessage($chan, $tpl->render());
            return $event->preventDefault();
        }

        $lastNick   = $tracker->getNick($lastPlayer->getPlayer());
        $message = $translator->gettext('<var name="logo"/> '.
                        '<b><var name="nick"/></b> challenges '.
                        '<b><var name="last_nick"/></b>\'s '.
                        '<var name="card"/>.');
        $tpl = new ErebotStyling($message, $translator);
        $tpl->assign('logo',        $this->getLogo());
        $tpl->assign('nick',        $nick);
        $tpl->assign('last_nick',   $lastNick);
        $tpl->assign('card',        $this->getCardText('w+4'));
        $this->sendMessage($chan, $tpl->render());

        $cardsTexts = array_map(array($this, 'getCardText'), $challenge['hand']);
        sort($cardsTexts);

        $message = $translator->gettext('<b><var name="nick"/></b>\'s cards: '.
                        '<for from="cards" item="card" separator=" ">'.
                        '<var name="card"/></for>');
        $tpl = new ErebotStyling($message, $translator);
        $tpl->assign('nick',    $lastNick);
        $tpl->assign('cards',   $cardsTexts);
        $this->sendMessage($nick, $tpl->render());

        if (!$challenge['legal']) {
            $message = $translator->gettext('<b><var name="nick"/></b>\'s move '.
                            '<b>WAS NOT</b> legal. <b><var name="nick"/></b> '.
                            'must pick <b><var name="count"/></b> cards!');
            $tpl = new ErebotStyling($message, $translator);
            $tpl->assign('nick',    $lastNick);
            $tpl->assign('count',   count($challenge['cards']));
            $this->sendMessage($chan, $tpl->render());

            $cardsTexts = array_map(array($this, 'getCardText'), $challenge['cards']);
            sort($cardsTexts);

            $message = $translator->gettext('You drew: <for from="cards" item="card" '.
                            'separator=" "><var name="card"/></for>');
            $tpl = new ErebotStyling($message, $translator);
            $tpl->assign('cards',   $cardsTexts);
            $this->sendMessage($lastNick, $tpl->render());
        }
        else {
            $message = $translator->gettext('<b><var name="last_nick"/></b>\'s move '.
                            'was legal. <b><var name="nick"/></b> must pick '.
                            '<b><var name="count"/></b> cards!');
            $tpl = new ErebotStyling($message, $translator);
            $tpl->assign('last_nick',   $lastNick);
            $tpl->assign('nick',        $nick);
            $tpl->assign('count',       count($challenge['cards']));
            $this->sendMessage($chan, $tpl->render());

            $cardsTexts = array_map(array($this, 'getCardText'), $challenge['cards']);
            sort($cardsTexts);

            $message = $translator->gettext('You drew: <for from="cards" item="card" '.
                            'separator=" "><var name="card"/></for>');
            $tpl = new ErebotStyling($message, $translator);
            $tpl->assign('cards',   $cardsTexts);
            $this->sendMessage($nick, $tpl->render());
        }

        $this->showTurn($event);
        $event->preventDefault(TRUE);
    }

    public function handleChoose(iErebotEvent &$event)
    {
        $tracker    =&  $this->getNickTracker();
        $chan       =   $event->getChan();
        $nick       =   $event->getSource();
        $current    =   $this->getCurrentPlayer($chan);
        $translator =   $this->getTranslator(FALSE);

        if ($current === NULL) return;
        $currentNick    =   $tracker->getNick($current->getPlayer());
        if (strcasecmp($nick, $currentNick)) return;

        $color  = ErebotUtils::gettok($event->getText(), 1, 1);
        $color  = strtolower($color);

        try {
            $this->chans[$chan]['game']->chooseColor($color);
            $message    = $translator->gettext('<var name="logo"/> '.
                'The color is now <var name="color"/>');
            $tpl        = new ErebotStyling($message, $translator);
            $tpl->assign('color', $this->getCardText($color));
            $this->sendMessage($chan, $tpl->render());
        }
        catch (EUno $e) {
            $message    = $translator->gettext('Hmm, yes '.
                '<b><var name="nick"/></b>, what is it?');
            $tpl        = new ErebotStyling($message, $translator);
            $tpl->assign('nick', $nick);
            $this->sendMessage($chan, $tpl->render());
        }

        return $event->preventDefault(TRUE);
    }

    public function handleDraw(iErebotEvent &$event)
    {
        $tracker    =&  $this->getNickTracker();
        $chan       =   $event->getChan();
        $nick       =   $event->getSource();
        $current    =   $this->getCurrentPlayer($chan);
        $translator =   $this->getTranslator($chan);

        if ($current === NULL) return;
        $currentNick = $tracker->getNick($current->getPlayer());
        if (strcasecmp($nick, $currentNick)) return;

        $game =& $this->chans[$chan]['game'];
        try {
            $drawnCards = $game->draw();
        }
        catch (EUnoWaitingForColor $e) {
            $message = $translator->gettext(
                '<var name="logo"/> <b><var name="nick"/></b>, '.
                'please choose a color with <b><var name="cmd"/> '.
                '&lt;r|b|g|y&gt;</b>');
            $tpl = new ErebotStyling($message, $translator);
            $tpl->assign('logo', $this->getLogo());
            $tpl->assign('nick', $nick);
            $tpl->assign('cmd', $this->chans[$chan]['triggers']['choose']);
            $this->sendMessage($chan, $tpl->render());
        }
        catch (EUnoAlreadyDrew $e) {
            $message = $translator->gettext('You already drew a card');
            $this->sendMessage($chan, $message);
            return $event->preventDefault(TRUE);
        }

        $nbDrawnCards = count($drawnCards);
        if ($nbDrawnCards > 1) {
            $message = $translator->gettext('<b><var name="nick"/></b> passes turn, '.
                            'and has to pick <b><var name="count"/></b> cards!');
            $tpl = new ErebotStyling($message, $translator);
            $tpl->assign('nick', $nick);
            $tpl->assign('count', $nbDrawnCards);
            $this->sendMessage($chan, $tpl->render());

            $this->showTurn($event);

            $player = $game->getCurrentPlayer();
            $cardsTexts = array_map(array($this, 'getCardText'), $player->getCards());
            sort($cardsTexts);

            $message = $translator->gettext('Your cards: <for from="cards" item="card" '.
                            'separator=" "><var name="card"/></for>');
            $tpl = new ErebotStyling($message, $translator);
            $tpl->assign('cards', $cardsTexts);
            $this->sendMessage($tracker->getNick($player->getPlayer()), $tpl->render());
        }
        else {
            $message = $translator->gettext('<b><var name="nick"/></b> '.
                            'draws a card');
            $tpl = new ErebotStyling($message, $translator);
            $tpl->assign('nick', $nick);
            $this->sendMessage($chan, $tpl->render());
        }

        $cardsTexts = array_map(array($this, 'getCardText'), $drawnCards);
        sort($cardsTexts);

        $message = $translator->gettext('You drew: <for from="cards" item="card" '.
                        'separator=" "><var name="card"/></for>');
        $tpl = new ErebotStyling($message, $translator);
        $tpl->assign('cards', $cardsTexts);

        $this->sendMessage($nick, $tpl->render());
        return $event->preventDefault(TRUE);
    }

    public function handleJoin(iErebotEvent &$event)
    {
        $tracker    =&  $this->getNickTracker();
        $nick       =   $event->getSource();
        $chan       =   $event->getChan();
        $translator =   $this->getTranslator($chan);

        if (!isset($this->chans[$chan])) return;
        $game =& $this->chans[$chan]['game'];

        $players =& $game->getPlayers();
        foreach ($players as &$player) {
            if (!strcasecmp($tracker->getNick($player->getPlayer()), $nick)) {
                $message    = $translator->gettext('<var name="logo"/> You\'re '.
                                    'already in the game <b><var name="nick"'.
                                    '/></b>!');
                $tpl        = new ErebotStyling($message, $translator);
                $tpl->assign('logo', $this->getLogo());
                $tpl->assign('nick', $nick);
                $this->sendMessage($chan, $tpl->render());
                return $event->preventDefault(TRUE);
            }
        }

        $message = $translator->gettext('<b><var name="nick"/></b> joins this '.
                        '<var name="logo"/> game.');
        $tpl = new ErebotStyling($message, $translator);
        $tpl->assign('nick', $nick);
        $tpl->assign('logo', $this->getLogo());
        $this->sendMessage($chan, $tpl->render());

        $token  =   $tracker->startTracking($nick);
        $player =&  $game->join($token);
        $cards  =   $player->getCards();
        $cards  =   array_map(array($this, 'getCardText'), $cards);
        sort($cards);

        $message = $translator->gettext('Your cards: <for from="cards" item="card" '.
                        'separator=" "><var name="card"/></for>');
        $tpl = new ErebotStyling($message, $translator);
        $tpl->assign('cards', $cards);
        $this->sendMessage($nick, $tpl->render());

        // If this is the second player.
        $players =& $game->getPlayers();
        if (count($players) == 2) {
            $names = array();
            foreach ($players as &$player) {
                $names[] = $tracker->getNick($player->getPlayer());
            }
            unset($player);

            // Display playing order.
            $this->handleShowOrder($event);

            $player         = $game->getCurrentPlayer();
            $currentNick    = $tracker->getNick($player->getPlayer());
            $message        = $translator->gettext('<b><var name="nick"/></b> deals '.
                                    'the first card from the stock');
            $tpl            = new ErebotStyling($message, $translator);
            $tpl->assign('nick', $currentNick);
            $this->sendMessage($chan, $tpl->render());

            $firstCard  =   $game->getFirstCard();
            $discard    =   $this->getCardText($firstCard);
            $message    =   $translator->gettext('<var name="logo"/> Current discard: '.
                                '<var name="discard"/>');

            $tpl        =   new ErebotStyling($message, $translator);
            $tpl->assign('logo',    $this->getLogo());
            $tpl->assign('discard', $discard);
            $this->sendMessage($chan, $tpl->render());

            $skippedPlayer  = $game->play($firstCard);
            if ($skippedPlayer) {
                $skippedNick    = $tracker->getNick($skippedPlayer->getPlayer());
                $message        = $translator->gettext('<var name="logo"/> '.
                                    '<b><var name="nick"/></b> skips his turn!');
                $tpl            = new ErebotStyling($message, $translator);
                $tpl->assign('nick', $skippedNick);
                $tpl->assign('logo', $this->getLogo());
                $this->sendMessage($chan, $tpl->render());
            }

            $this->showTurn($event);
            return $event->preventDefault(TRUE);
        }

        return $event->preventDefault(TRUE);
    }

    public function handlePass(iErebotEvent &$event)
    {
        $tracker    =&  $this->getNickTracker();
        $chan       =   $event->getChan();
        $nick       =   $event->getSource();
        $current    =   $this->getCurrentPlayer($chan);
        $translator =   $this->getTranslator($chan);

        if ($current === NULL) return;
        $currentNick    =   $tracker->getNick($current->getPlayer());
        if (strcasecmp($nick, $currentNick)) return;

        $game       =&  $this->chans[$chan]['game'];
        try {
            $drawnCards = $game->pass();
        }
        catch (EUnoWaitingForColor $e) {
            $message = $translator->gettext(
                '<var name="logo"/> <b><var name="nick"/></b>, '.
                'please choose a color with <b><var name="cmd"/> '.
                '&lt;r|b|g|y&gt;</b>');
            $tpl = new ErebotStyling($message, $translator);
            $tpl->assign('logo', $this->getLogo());
            $tpl->assign('nick', $nick);
            $tpl->assign('cmd', $this->chans[$chan]['triggers']['choose']);
            $this->sendMessage($chan, $tpl->render());
        }
        catch (EUnoMustDrawBeforePass $e) {
            $message = $translator->gettext('You must draw a card first');
            $this->sendMessage($chan, $message);
            return $event->preventDefault(TRUE);
        }

        $nbDrawnCards = count($drawnCards);
        if ($nbDrawnCards > 1)
            $message = $translator->gettext('<b><var name="nick"/></b> passes turn, '.
                'and has to pick <b><var name="count"/></b> cards!');
        else
            $message = $translator->gettext('<b><var name="nick"/></b> '.
                'passes turn');

        $tpl = new ErebotStyling($message, $translator);
        $tpl->assign('nick', $nick);
        $tpl->assign('count', $nbDrawnCards);
        $this->sendMessage($chan, $tpl->render());

        if (count($drawnCards)) {
            $cardsTexts = array_map(array($this, 'getCardText'), $drawnCards);
            sort($cardsTexts);

            $message = $translator->gettext('You drew: <for from="cards" item="card" '.
                            'separator=" "><var name="card"/></for>');
            $tpl = new ErebotStyling($message, $translator);
            $tpl->assign('cards', $cardsTexts);
            $this->sendMessage($nick, $tpl->render());
        }

        $this->showTurn($event);

        $player = $game->getCurrentPlayer();
        $cardsTexts = array_map(array($this, 'getCardText'), $player->getCards());
        sort($cardsTexts);

        $message = $translator->gettext('Your cards: <for from="cards" item="card" '.
                        'separator=" "><var name="card"/></for>');
        $tpl = new ErebotStyling($message, $translator);
        $tpl->assign('cards', $cardsTexts);
        $this->sendMessage($tracker->getNick($player->getPlayer()), $tpl->render());

        return $event->preventDefault(TRUE);
    }

    public function handlePlay(iErebotEvent &$event)
    {
        $tracker    =&  $this->getNickTracker();
        $chan       =   $event->getChan();
        $nick       =   $event->getSource();
        $current    =   $this->getCurrentPlayer($chan);
        $translator =   $this->getTranslator($chan);

        if ($current === NULL) return;
        $currentNick    =   $tracker->getNick($current->getPlayer());
        if (strcasecmp($nick, $currentNick)) return;

        $game =&    $this->chans[$chan]['game'];
        $card =     $event->getText()->getTokens(1);
        $card =     str_replace(' ', '', $card);

        $waitingForColor    = FALSE;
        $skippedPlayer      = NULL;

        try {
            $skippedPlayer = $game->play($card);
        }
        catch (EUnoWaitingForColor $e) {
            $waitingForColor = TRUE;
        }
        catch (EUnoInvalidMove $e) {
            $message = $translator->gettext('This move is not valid');
            $this->sendMessage($chan, $message);
            return $event->preventDefault(TRUE);
        }
        catch (EUnoMoveNotAllowed $e) {
            switch ($e->getCode()) {
                case 1:
                    $message = $translator->gettext('You cannot play multiple reverses/skips in a non 1vs1 game');
                    break;

                case 2:
                    $message = $translator->gettext('You cannot play multiple cards');
                    break;

                case 3:
                    $message = $translator->gettext('You may only play the card you just drew');
                    break;

                case 4:
                    $allowed = $e->getAllowedCards();
                    if (!$allowed) {
                        $message = $translator->gettext('You cannot play that move now');
                        $this->sendMessage($chan, $message);
                        return $event->preventDefault(TRUE);
                    }
                    else {
                        $cardsTexts = array_map(array($this, 'getCardText'), $allowed);
                        sort($cardsTexts);

                        $message = $translator->gettext(
                            'You may only play one of the following cards: '.
                            '<for from="cards" item="card" separator=" ">'.
                            '<var name="card"/></for>');
                        $tpl = new ErebotStyling($message, $translator);
                        $tpl->assign('cards', $cardsTexts);
                        $this->sendMessage($chan, $tpl->render());
                    }
                    return $event->preventDefault(TRUE);

                default:
                    $message = $translator->gettext('You cannot play that move');
                    break;
            }
            $this->sendMessage($chan, $message);
            return $event->preventDefault(TRUE);
        }
        catch (EUnoMissingCards $e) {
            $message = $translator->gettext('You do not have the cards required '.
                            'for that move');
            $this->sendMessage($chan, $message);
            return $event->preventDefault(TRUE);
        }

        $played     = $game->extractCard($card, NULL);
        $message    = $translator->gettext(
                        '<b><var name="nick"/></b> plays <var name="card"/> '.
                        '<b><var name="count"/> times!</b>');
        $tpl = new ErebotStyling($message, $translator);
        $tpl->assign('nick',    $nick);
        $tpl->assign('card',    $this->getCardText($played['card']));
        $tpl->assign('count',   $played['count']);
        $this->sendMessage($chan, $tpl->render());

        $cardsCount = $current->getCardsCount();
        $next       = $game->getCurrentPlayer($chan);
        if ($cardsCount == 1) {
            $message    = $translator->gettext('<b><var name="nick"/></b> has '.
                                                '<var name="logo"/>');

            $tpl = new ErebotStyling($message, $translator);
            $tpl->assign('logo', $this->getLogo());
            $tpl->assign('nick', $nick);
            $this->sendMessage($chan, $tpl->render());
        }
        else if (!$cardsCount) {
            if ($game->getPenalty()) {
                $drawnCards = count($game->draw());
                $message    = $translator->gettext('<var name="logo"/> '.
                                    '<b><var name="nick"/></b> must draw '.
                                    '<b><var name="count"/></b> cards.');

                $tpl = new ErebotStyling($message, $translator);
                $tpl->assign('logo', $this->getLogo());
                $tpl->assign('nick', $tracker->getNick($next->getPlayer()));
                $tpl->assign('count', $drawnCards);
                $this->sendMessage($chan, $tpl->render());
            }

            $message    = $translator->gettext('<var name="logo"/> game '.
                'finished in <var name="duration"/>. The winner is '.
                '<b><var name="nick"/></b>!');

            $tpl = new ErebotStyling($message, $translator);
            $tpl->assign('logo',        $this->getLogo());
            $tpl->assign('duration',    $translator->formatDuration(
                                            $game->getElapsedTime()));
            $tpl->assign('nick',        $nick);
            $this->sendMessage($chan, $tpl->render());

            $score      = 0;
            $players    = $game->getPlayers();
            foreach ($players as &$player) {
                $token = $player->getPlayer();
                if ($player !== $current) {
                    $score += $player->getScore();

                    $cards  =   array_map(array($this, 'getCardText'),
                                            $player->getCards());
                    sort($cards);

                    $message = $translator->gettext('<var name="nick"/> still had '.
                        '<for from="cards" item="card" separator=" ">'.
                        '<var name="card"/></for>');
                    $tpl = new ErebotStyling($message, $translator);
                    $tpl->assign('nick', $tracker->getNick($token));
                    $tpl->assign('cards', $cards);
                    $this->sendMessage($chan, $tpl->render());
                }
                $tracker->stopTracking($token);
            }
            unset($player);

            $message = $translator->gettext('<var name="nick"/> wins with '.
                '<b><var name="score"/></b> points');
            $tpl = new ErebotStyling($message, $translator);
            $tpl->assign('nick', $nick);
            $tpl->assign('score', $score);
            $this->sendMessage($chan, $tpl->render());

            $tracker->stopTracking($game->getCreator());
  
            $registry   =   $this->connection->getModule(
                'TriggerRegistry', ErebotConnection::MODULE_BY_NAME);
            $registry->freeTriggers($this->chans[$chan]['triggers_token']);

            foreach ($this->chans[$chan]['handlers'] as &$handler)
                $this->connection->removeEventHandler($handler);
            unset($handler);

            unset($this->chans[$chan]);
            return $event->preventDefault(TRUE);
        }

        if ($skippedPlayer) {
            $skippedNick    = $tracker->getNick($skippedPlayer->getPlayer());
            $message        = $translator->gettext('<var name="logo"/> '.
                                '<b><var name="nick"/></b> skips his turn!');
            $tpl            = new ErebotStyling($message, $translator);
            $tpl->assign('nick', $skippedNick);
            $tpl->assign('logo', $this->getLogo());
            $this->sendMessage($chan, $tpl->render());
        }

        if ($waitingForColor) {
            $message = $translator->gettext(
                '<var name="logo"/> <b><var name="nick"/></b>, '.
                'please choose a color with <b><var name="cmd"/> '.
                '&lt;r|b|g|y&gt;</b>');
            $tpl = new ErebotStyling($message, $translator);
            $tpl->assign('logo', $this->getLogo());
            $tpl->assign('nick', $nick);
            $tpl->assign('cmd', $this->chans[$chan]['triggers']['choose']);
            $this->sendMessage($chan, $tpl->render());
        }

        else {
            if (substr($played['card'], 0, 1) == 'w') {
                $message = $translator->gettext('<var name="logo"/> '.
                    'The color is now <var name="color"/>');
                $tpl = new ErebotStyling($message, $translator);
                $tpl->assign('logo',    $this->getLogo());
                $tpl->assign('color',   $this->getCardText($played['color']));
                $this->sendMessage($chan, $tpl->render());
            }

            if ($game->getPenalty()) {
                $message = $translator->gettext('<var name="logo"/> '.
                    'Next player must respond correctly or pick '.
                    '<b><var name="count"/></b> cards');
                $tpl = new ErebotStyling($message, $translator);
                $tpl->assign('logo',    $this->getLogo());
                $tpl->assign('count',   $game->getPenalty());
                $this->sendMessage($chan, $tpl->render());
            }
        }
 
        $this->showTurn($event);

        $cards  =   array_map(array($this, 'getCardText'), $next->getCards());
        sort($cards);

        $message = $translator->gettext('Your cards: <for from="cards" item="card" '.
                        'separator=" "><var name="card"/></for>');
        $tpl = new ErebotStyling($message, $translator);
        $tpl->assign('cards', $cards);
        $this->sendMessage($tracker->getNick($next->getPlayer()), $tpl->render());

        return $event->preventDefault(TRUE);
    }

    public function handleShowCardsCount(iErebotEvent &$event)
    {
        $tracker    =&  $this->getNickTracker();
        $chan       =   $event->getChan();
        $nick       =   $event->getSource();
        $translator =   $this->getTranslator($chan);

        if (!isset($this->chans[$chan]['game'])) return;
        $game       =&  $this->chans[$chan]['game'];
        $players    =&  $game->getPlayers();
        $counts     =   array();
        $ingame     =   NULL;

        foreach ($players as &$player) {
            $pnick          = $tracker->getNick($player->getPlayer());
            $counts[$pnick] = $player->getCardsCount();
            if ($nick == $pnick)
                $ingame =& $player;
        }
        unset($player);

        $message = $translator->gettext('<var name="logo"/> Cards: <for from="counts" '.
                        'item="count" key="nick"><b><var name="nick"/></b>: '.
                        '<var name="count"/></for>');
        $tpl = new ErebotStyling($message, $translator);
        $tpl->assign('logo',    $this->getLogo());
        $tpl->assign('counts',  $counts);
        $this->sendMessage($chan, $tpl->render());

        if ($ingame !== NULL) {
            $cards  =   array_map(array($this, 'getCardText'), $ingame->getCards());
            sort($cards);

            $message = $translator->gettext('Your cards: <for from="cards" item="card" '.
                            'separator=" "><var name="card"/></for>');
            $tpl = new ErebotStyling($message, $translator);
            $tpl->assign('cards', $cards);
            $this->sendMessage($nick, $tpl->render());
        }

        return $event->preventDefault(TRUE);
    }

    public function handleShowDiscard(iErebotEvent &$event)
    {
        $chan       =   $event->getChan();
        $translator =   $this->getTranslator($chan);

        if (!isset($this->chans[$chan]['game'])) return;
        $game       =&  $this->chans[$chan]['game'];

        $card       =   $game->getLastPlayedCard();
        if ($card === NULL) {
            $message = $translator->gettext('No card has been played yet');
            $this->sendMessage($chan, $message);
            return $event->preventDefault(TRUE);
        }

        $count      = $game->getRemainingCardsCount();
        $discard    = $this->getCardText($card['card']);
        if ($count === NULL)
            $message = $translator->gettext('<var name="logo"/> Current discard: '.
                            '<var name="discard"/>');
        else
            $message = $translator->gettext('<var name="logo"/> Current discard: '.
                            '<var name="discard"/> (<b><var name="count"/></b>'.
                            ' cards left in the stock)');

        $tpl = new ErebotStyling($message, $translator);
        $tpl->assign('logo',    $this->getLogo());
        $tpl->assign('discard', $discard);
        $tpl->assign('count',   $count);
        $this->sendMessage($chan, $tpl->render());
        return $event->preventDefault(TRUE);
    }

    public function handleShowOrder(iErebotEvent &$event)
    {
        $tracker    =&  $this->getNickTracker();
        $chan       =   $event->getChan();
        $translator =   $this->getTranslator($chan);

        if (!isset($this->chans[$chan]['game'])) return;
        $game       =&  $this->chans[$chan]['game'];
        $players    =&  $game->getPlayers();
        $nicks      =   array();
        foreach ($players as &$player) {
            $nicks[] = $tracker->getNick($player->getPlayer());
        }
        unset($player);

        $message = $translator->gettext('<var name="logo"/> Playing order: <for '.
                        'from="nicks" item="nick"><b><var name="nick"/>'.
                        '</b></for>');
        $tpl = new ErebotStyling($message, $translator);
        $tpl->assign('logo',    $this->getLogo());
        $tpl->assign('nicks',   $nicks);
        $this->sendMessage($chan, $tpl->render());
        return $event->preventDefault(TRUE);
    }

    public function handleShowTime(iErebotEvent &$event)
    {
        $chan       =   $event->getChan();
        $current    =   $this->getCurrentPlayer($chan);
        $translator =   $this->getTranslator($chan);

        if ($current === NULL) return;
        $game       =&  $this->chans[$chan]['game'];

        $message    = $translator->gettext('<var name="logo"/> game running since '.
                                        '<var name="duration"/>');
        $tpl = new ErebotStyling($message, $translator);
        $tpl->assign('logo',        $this->getLogo());
        $tpl->assign('duration',    $translator->formatDuration(
                                        $game->getElapsedTime()));
        $this->sendMessage($chan, $tpl->render());
        return $event->preventDefault(TRUE);
    }

    public function handleShowTurn(iErebotEvent &$event)
    {
        $tracker    =&  $this->getNickTracker();
        $chan       =   $event->getChan();
        $nick       =   $event->getSource();
        $current    =   $this->getCurrentPlayer($chan);
        $translator =   $this->getTranslator($chan);

        if ($current === NULL) return;
        $currentNick = $tracker->getNick($current->getPlayer());

        if (!strcasecmp($nick, $currentNick))
            $message = $translator->gettext('<var name="logo"/> <b><var name="nick"'.
                            '/></b>: it\'s your turn sleepyhead!');
        else
            $message = $translator->gettext('<var name="logo"/> It\'s <b><var name='.
                            '"nick"/></b>\'s turn.');

        $tpl = new ErebotStyling($message, $translator);
        $tpl->assign('logo', $this->getLogo());
        $tpl->assign('nick', $currentNick);
        $this->sendMessage($chan, $tpl->render());
        return $event->preventDefault(TRUE);
    }
}

?>
