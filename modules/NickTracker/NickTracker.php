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

class   ErebotModule_NickTracker
extends ErebotModuleBase
{
    protected $_nicks;

    public function reload($flags)
    {
        if ($flags & self::RELOAD_MEMBERS) {
            $this->_nicks = array();
        }

        if ($flags & self::RELOAD_HANDLERS) {
            $handler = new ErebotEventHandler(
                array($this, 'handleNick'),
                'ErebotEventNick');
            $this->_connection->addEventHandler($handler);

            $handler = new ErebotEventHandler(
                array($this, 'handlePartOrQuit'),
                'ErebotEventQuit');
            $this->_connection->addEventHandler($handler);
        }
    }

    public function startTracking($nick)
    {
        if (!is_string($nick)) {
            $translator = $this->getTranslator(NULL);
            throw new EErebotInvalidValue($translator->gettext(
                'Not a valid nick'));
        }

        $this->_nicks[] = $nick;
        end($this->_nicks);
        $key = key($this->_nicks);
        return $key;
    }

    public function stopTracking($token)
    {
        if (!isset($this->_nicks[$token])) {
            $translator = $this->getTranslator(NULL);
            throw new EErebotNotFound($translator->gettext('No such token'));
        }
        unset($this->_nicks[$token]);
    }

    public function getNick($token)
    {
        if (!isset($this->_nicks[$token])) {
            $translator = $this->getTranslator(NULL);
            throw new EErebotNotFound($translator->gettext('No such token'));
        }
        return $this->_nicks[$token];
    }

    public function handleNick(iErebotEvent &$event)
    {
        $oldNick        = (string) $event->getSource();
        $newNick        = (string) $event->getTarget();

        $capabilities   =   $this->_connection->getModule(
                                'ServerCapabilities',
                                ErebotConnection::MODULE_BY_NAME);

        foreach ($this->_nicks as $token => &$nick) {
            if (!$capabilities->irccasecmp($nick, $oldNick))
                $this->_nicks[$token] = $newNick;
        }
        unset($nick);
    }

    public function handlePartOrQuit(iErebotEvent &$event)
    {
        $srcNick        = (string) $event->getSource();

        $capabilities   =   $this->_connection->getModule(
                                'ServerCapabilities',
                                ErebotConnection::MODULE_BY_NAME);

        foreach ($this->_nicks as $token => &$nick) {
            if (!$capabilities->irccasecmp($nick, $srcNick))
                unset($this->_nicks[$token]);
        }
        unset($nick);
    }

    public function handleKick(iErebotEvent &$event)
    {
        $srcNick        = (string) $event->getTarget();

        $capabilities   =   $this->_connection->getModule(
                                'ServerCapabilities',
                                ErebotConnection::MODULE_BY_NAME);

        foreach ($this->_nicks as $token => &$nick) {
            if (!$capabilities->irccasecmp($nick, $srcNick))
                unset($this->_nicks[$token]);
        }
        unset($nick);
    }
}

