<?php
# vim: et ts=4 sts=4 sw=4

include(dirname(__FILE__).'/exceptions.php');
include(dirname(__FILE__).'/decks.php');
include(dirname(__FILE__).'/hand.php');

class   GoF
{
    protected $deck;
    protected $order;
    protected $players;
    protected $startTime;
    protected $creator;
    protected $leader;

    public function __construct($creator)
    {
        $this->creator      =&  $creator;
        $this->deck         =   new GoFDeckReal();
        $this->players      =   array();
        $this->startTime    =   NULL;
        $this->leader       =   NULL;
    }

    public function __destruct()
    {
        
    }

    public function & join($token)
    {
        $nbPlayers = count($this->players);
        if ($nbPlayers >= 4)
            throw new EGoFEnoughPlayersAlready();

        $this->players[]    = new GoFHand($token, $this->deck);
        $player             = end($this->players);
        if (count($this->players) == 3) {
            $this->startTime = time();
            shuffle($this->players);
        }
        return $player;
    }

    public function play($combo)
    {
        
    }

    public function pass()
    {
        
    }

    public function chooseCard($card)
    {
        
    }

    public function getCurrentPlayer()
    {
        return reset($this->players);
    }

    public function & getLeadingPlayer()
    {
        return $this->leader;
    }

    public function & getCreator()
    {
        return $this->creator;
    }

    public function getElapsedTime()
    {
        if ($this->startTime === NULL)
            return NULL;

        return time() - $this->startTime;
    }

    public function getLastPlayedCombo()
    {
        return $this->deck->getLastDiscardedCombo();
    }

    public function & getPlayers()
    {
        return $this->players;
    }
}

?>
