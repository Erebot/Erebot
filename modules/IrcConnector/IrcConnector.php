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
                'ErebotEventLogon');
            $this->connection->addEventHandler($handler);
        }
    }

    protected function sendCredentials()
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

    public function handleConnect(iErebotEvent &$event)
    {
        $metadata = stream_get_meta_data($this->connection->getSocket());
        // If no upgrade should be performed or
        // if the connection is already encrypted.
        if (!$this->parseBool('upgrade', FALSE) ||
            !strcasecmp($metadata['wrapper_type'], 'ircs'))
            $this->sendCredentials();
        // Otherwise, start a TLS negociation.
        else {        
            $handler = new ErebotRawHandler(
                            array($this, 'handleSTARTTLSSuccess'),
                            RPL_STARTTLSOK);
            $this->connection->addRawHandler($handler);
            $handler = new ErebotRawHandler(
                            array($this, 'handleSTARTTLSFailure'),
                            ERR_STARTTLSFAIL);
            $this->connection->addRawHandler($handler);
            $this->sendCommand('STARTTLS');
        }
    }

    public function handleSTARTTLSSuccess(iErebotRaw &$raw)
    {
        /// @HACK: this is an evil hack to start sending a TLS handshake.
        $wrong = stream_set_write_buffer($this->connection->getSocket(), 1);
        if ($wrong)
            $this->connection->disconnect(NULL, TRUE);
        else
            $this->sendCredentials();
    }

    public function handleSTARTTLSFailure(iErebotRaw &$raw)
    {
        $this->connection->disconnect(NULL, TRUE);
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
