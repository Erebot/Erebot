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

class   ErebotModule_IrcConnector
extends ErebotModuleBase
{
    protected $_password;
    protected $_nickname;
    protected $_identity;
    protected $_hostname;
    protected $_realname;

    public function reload($flags)
    {
        if ($flags & self::RELOAD_HANDLERS) {
            $handler = new ErebotEventHandler(
                array($this, 'handleConnect'),
                'ErebotEventLogon');
            $this->_connection->addEventHandler($handler);
        }
    }

    protected function sendCredentials()
    {
        $this->_password = $this->parseString('password', '');
        $this->_nickname = $this->parseString('nickname');
        $this->_identity = $this->parseString('identity', 'Erebot');
        $this->_hostname = $this->parseString('hostname', 'Erebot');
        $this->_realname = $this->parseString('realname', 'Erebot');

        $config =&  $this->_connection->getConfig(NULL);
        $url    =   parse_url($config->getConnectionURL());

        if ($this->_password != '')
            $this->sendCommand('PASS '.$this->_password);
        $this->sendCommand('NICK '.$this->_nickname);
        $this->sendCommand('USER '.$this->_identity.' '.$this->_hostname.
                            ' '.$url['host'].' :'.$this->_realname);
    }

    public function handleConnect(iErebotEvent &$event)
    {
        $metadata = stream_get_meta_data($this->_connection->getSocket());
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
            $this->_connection->addRawHandler($handler);
            $handler = new ErebotRawHandler(
                            array($this, 'handleSTARTTLSFailure'),
                            ERR_STARTTLSFAIL);
            $this->_connection->addRawHandler($handler);
            $this->sendCommand('STARTTLS');
        }
    }

    public function handleSTARTTLSSuccess(iErebotRaw &$raw)
    {
        /// @HACK: this is an evil hack to start sending a TLS handshake.
        $wrong = stream_set_write_buffer($this->_connection->getSocket(), 1);
        if ($wrong)
            $this->_connection->disconnect(NULL, TRUE);
        else
            $this->sendCredentials();
    }

    public function handleSTARTTLSFailure(iErebotRaw &$raw)
    {
        $this->_connection->disconnect(NULL, TRUE);
    }

    public function getNetPassword()
    {
        return $this->_password;
    }

    public function getBotNickname()
    {
        return $this->_nickname;
    }

    public function getBotIdentity()
    {
        return $this->_identity;
    }

    public function getBotHostname()
    {
        return $this->_hostname;
    }

    public function getBotRealname()
    {
        return $this->_realname;
    }
}

