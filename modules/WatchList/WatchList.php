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

class   ErebotModule_WatchList
extends ErebotModuleBase
{
    protected $_watchedNicks;

    public function reload($flags)
    {
        $handler    =   new ErebotEventHandler(
                            array($this, 'handleConnect'),
                            'ErebotEventConnect');
        $this->_connection->addEventHandler($handler);
        $watchedNicks = $this->parseString('nicks', '');
        $watchedNicks = str_replace(',', ' ', $watchedNicks);
        $this->_watchedNicks = array_filter(array_map('trim',
                                explode(' ', $watchedNicks)));
    }

    public function handleConnect(iErebotEvent &$event)
    {
        if (!count($this->_watchedNicks))
            return;

        $this->sendCommand('WATCH +'.implode(' +', $this->_watchedNicks));
    }
}

?>
