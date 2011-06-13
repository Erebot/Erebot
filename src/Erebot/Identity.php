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
 * \brief
 *      Represents the identity of an IRC user.
 */
class       Erebot_Identity
implements  Erebot_Interface_Identity
{
    /// Nickname for this user identity, either a string or NULL.
    protected $_nick;

    /// Identity string for this user identity, either a string or NULL.
    protected $_ident;

    /// Host part for this user identity, either a string or NULL.
    protected $_host;

    /**
     * Creates a new object holding some user's identity.
     *
     * \param string $user
     *      A string, representing some user.
     *      This can be either a mask, such as "foo!ident@host"
     *      or just a nickname, such as "foo".
     *
     * \throw Erebot_InvalidValueException
     *      The given $user does not represent a valid identity.
     */
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

    /// \copydoc Erebot_Interface_Identity::getNick()
    public function getNick()
    {
        return $this->_nick;
    }

    /// \copydoc Erebot_Interface_Identity::getIdent()
    public function getIdent()
    {
        return $this->_ident;
    }

    /// \copydoc Erebot_Interface_Identity::getHost()
    public function getHost()
    {
        return $this->_host;
    }

    /// \copydoc Erebot_Interface_Identity::getMask()
    public function getMask()
    {
        $ident  = ($this->_ident === NULL) ? '*' : $this->_ident;
        $host   = ($this->_host === NULL) ? '*' : $this->_host;
        return $this->_nick.'!'.$ident.'@'.$host;
    }

    /// \copydoc Erebot_Interface_Identity::__toString()
    public function __toString()
    {
        return $this->_nick;
    }

    /**
     * Indicates whether this identity matches a given pattern.
     *
     * \param string $pattern
     *      The pattern this identity should be tested against.
     *      The '?' and '*' wildcard characters are supported.
     *
     * \retval bool
     *      TRUE if this identity matches the given pattern,
     *      FALSE otherwise.
     */
    public function match($pattern)
    {
        $pattern = strtr(
            preg_quote($pattern, '#'),
            array(
                '\\?'   => '.?',
                '\\*'   => '.*',
            )
        );
        return (bool) preg_match('#^'.$pattern.'$#iD', $this->getMask());
    }
}
