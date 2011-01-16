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

/**
 * Represents the identity of an IRC user.
 */
class   Erebot_Identity
{
    protected $_nick;
    protected $_ident;
    protected $_host;

    public function __construct($user)
    {
        if (!is_string($user))
            throw new Erebot_InvalidValueException('Not a valid identity');

        $ident  = NULL;
        $host   = NULL;
        $nick   = NULL;
        $pos    = strpos($user, '!');
        if ($pos !== FALSE) {
            $parts  = explode('@', substr($user, $pos + 1));
            if (count($parts) != 2)
                throw new Erebot_InvalidValueException('Invalid mask');

            $nick   = substr($user, 0, $pos);
            $ident  = $parts[0];
            $host   = $parts[1];

            if ($nick === FALSE || $ident == '' || $host == '')
                throw new Erebot_InvalidValueException('Invalid mask');
        }
        // If there is a "@" but no "!", this is also invalid.
        else if (strpos($user, '@') !== FALSE)
            throw new Erebot_InvalidValueException('Invalid mask');
        else
            $nick = $user;

        $this->_nick    = $nick;
        $this->_ident   = $ident;
        $this->_host    = $host;
    }

    public function getNick()
    {
        return $this->_nick;
    }

    public function getIdent()
    {
        return $this->_ident;
    }

    public function getHost()
    {
        return $this->_host;
    }

    public function __toString()
    {
        return $this->_nick;
    }
}
