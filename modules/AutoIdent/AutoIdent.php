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

class   ErebotModule_AutoIdent
extends ErebotModuleBase
{
    protected $_password;

    public function reload($flags)
    {
        if ($flags & self::RELOAD_HANDLERS) {
            $targets        = new ErebotEventTargets(ErebotEventTargets::ORDER_ALLOW_DENY);
            $this->_password = $this->parseString('password');

            $nicknames  = explode(' ', $this->parseString('nickserv', 'nickserv'));
            foreach ($nicknames as &$nickname) {
                $targets->addRule(ErebotEventTargets::TYPE_ALLOW, $nickname);
            }
            unset($nickname);

            $pattern    =   $this->parseString('pattern');
            $pattern    =   '/'.str_replace('/', '\\/', $pattern).'/i';

            /// @TODO use only one handler...
            $filter     =   new ErebotTextFilter(
                                $this->_mainCfg,
                                ErebotTextFilter::TYPE_REGEXP,
                                $pattern);
            $handler    =   new ErebotEventHandler(
                                array($this, 'handleIdentRequest'),
                                array(
                                    'ErebotEventTextPrivate',
                                    'ErebotEventNoticePrivate',
                                ),
                                $targets, $filter);
            $this->_connection->addEventHandler($handler);
        }
    }

    public function handleIdentRequest(iErebotEventSource &$event)
    {
        $this->sendMessage($event->getSource(), 'IDENTIFY '.$this->_password);
    }
}

