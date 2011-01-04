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

abstract class  Erebot_Event_Match_TextAbstract
implements      Erebot_Interface_Event_Match
{
    protected $_pattern;
    protected $_requirePrefix;

    public function __construct($pattern, $requirePrefix = FALSE)
    {
        if (!Erebot_Utils::stringifiable($pattern))
            throw new Erebot_InvalidValueException('Pattern must be a string');

        if ($requirePrefix !== NULL && !is_bool($requirePrefix))
            throw new Erebot_InvalidValueException(
                'requirePrefix must be a boolean or NULL'
            );

        $this->_pattern = $pattern;
        $this->_requirePrefix = $requirePrefix;
    }

    public function getPattern()
    {
        return $this->_pattern;
    }

    public function requiresPrefix()
    {
        return $this->_requirePrefix;
    }

    public function match(Erebot_Interface_Event_Generic &$event)
    {
        if (!($event instanceof Erebot_Interface_Event_Text))
            return FALSE;

        $prefix = $event
            ->getConnection()->getConfig(NULL)
            ->getMainCfg()->getCommandsPrefix();
        return $this->_match($prefix, $event->getText());
    }

    abstract protected function _match($prefix, $text);
}

