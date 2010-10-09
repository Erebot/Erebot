<?php

class   ErebotModule_IrcConnector
extends ErebotModuleBase
{
    protected $password;
    protected $nickname;
    protected $identity;
    protected $hostname;
    protected $realname;

    public function reload($flags)
    {
        if ($flags & self::RELOAD_HANDLERS) {
            $handler = new ErebotEventHandler(
                array($this, 'handleConnect'),
                ErebotEvent::ON_LOGON);
            $this->connection->addEventHandler($handler);
        }
    }

    public function handleConnect(ErebotEvent &$event)
    {
        $this->password     =   $this->parseString('password', '');
        $this->nickname     =   $this->parseString('nickname');
        $this->identity     =   $this->parseString('identity', 'Erebot');
        $this->hostname     =   $this->parseString('hostname', 'Erebot');
        $this->realname     =   $this->parseString('realname', 'Erebot');

        $config =&  $this->connection->getConfig(NULL);
        $url    =   parse_url($config->getConnectionURL());

        if ($this->password != '')
            $this->sendCommand('PASS '.$this->password);
        $this->sendCommand('NICK '.$this->nickname);
        $this->sendCommand('USER '.$this->identity.' '.$this->hostname.
                            ' '.$url['host'].' :'.$this->realname);
    }

    public function getNetPassword()
    {
        return $this->password;
    }

    public function getBotNickname()
    {
        return $this->nickname;
    }

    public function getBotIdentity()
    {
        return $this->identity;
    }

    public function getBotHostname()
    {
        return $this->hostname;
    }

    public function getBotRealname()
    {
        return $this->realname;
    }
}

?>
