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

        if ($host === NULL)
            $this->_host = NULL;
        else {
            $this->_host    = self::_canonicalizeHost(
                $host,
                Erebot_Interface_Identity::CANON_IPV6,
                FALSE
            );
        }
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

    static protected function _stripLeading(&$number, $key)
    {
        $stripped = ltrim($number, '0');
        $number = ($stripped == '' ? '0' : $stripped);
    }

    static protected function _canonicalizeHost($host, $c10n, $uncompressed)
    {
        if ($c10n != Erebot_Interface_Identity::CANON_IPV4 &&
            $c10n != Erebot_Interface_Identity::CANON_IPV6) {
            throw new Erebot_InvalidValueException(
                'Invalid canonicalization value'
            );
        }

        $decOctet       = '(?:\\d|[1-9]\\d|1\\d{2}|2[0-4]\\d|25[0-5])';
        $dotAddress     = $decOctet.'(?:\\.'.$decOctet.'){3}';

        // If it's an IPv4 address, handle it here.
        // Must appear before the test for hostnames (see RFC 1123, §2.1).
        if (preg_match('/^'.$dotAddress.'$/Di', $host)) {
            $parts  = explode('.', $host, 4);
            $prefix = ($uncompressed ? '0:0:0:0:0' : ':');
            if ($c10n == Erebot_Interface_Identity::CANON_IPV4) {
                array_walk($parts, array('self', '_stripLeading'));
                return $prefix.':ffff:'.implode('.', $parts);
            }

            $mapped = array(
                sprintf('%02x%02x', $parts[0], $parts[1]),
                sprintf('%02x%02x', $parts[2], $parts[3]),
            );
            array_walk($mapped, array('self', '_stripLeading'));
            return $prefix.':ffff:'.implode(':', $mapped);
        }

        // Adapted from the grammar & rules in RFC 1034, section 3.5,
        // with an update from the RFC 1123, section 2.1 regarding the
        // first character.
        $label      = '[A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9]?';
        $hostname   = '(?:'.$label.'\.)*'.$label;

        // If this is some hostname, we simply lowercase it.
        if (preg_match('/^'.$hostname.'$/Di', $host)) {
            // RFC 1123 says the top-level label in a FQDN
            // can never be all-numeric (avoids ambiguity
            // with IPv4 addresses in dotted notation).
            $last = strrchr($host, '.');
            if (strspn($last, '.1234567890') != strlen($last))
                return strtolower($host);
        }

        $half           = '[[:xdigit:]]{1,4}';
        $long           = '(?:'.$half.':'.$half.'|('.$dotAddress.'))';
        $colonAddress   =
            '(?:'.
            '(?:'.$half.':){6}'.$long.'|'.
            '::(?:'.$half.':){5}'.$long.'|'.
            '(?:'.$half.')?::(?:'.$half.':){4}'.$long.'|'.
            '(?:(?:'.$half.':)?'.$half.')?::(?:'.$half.':){3}'.$long.'|'.
            '(?:(?:'.$half.':){0,2}'.$half.')?::(?:'.$half.':){2}'.$long.'|'.
            '(?:(?:'.$half.':){0,3}'.$half.')?::'.$half.':'.$long.'|'.
            '(?:(?:'.$half.':){0,4}'.$half.')?::'.$long.'|'.
            '(?:(?:'.$half.':){0,5}'.$half.')?::'.$half.'|'.
            '(?:(?:'.$half.':){0,6}'.$half.')?::'.
            ')';

        // Is it an IPv6? maybe not...
        if (!preg_match('/^'.$colonAddress.'$/Di', $host, $matches))
            throw new Erebot_InvalidValueException('Unrecognized "host"');

        // It's an IPv6 alright! Let's handle it.
        if (count($matches) > 1) {
            // IPv6 mapped IPv4.
            $mapped = end($matches);
            $parts  = explode('.', $mapped, 4);
            $mapped = array(
                sprintf('%02x%02x', $parts[0], $parts[1]),
                sprintf('%02x%02x', $parts[2], $parts[3]),
            );
            array_walk($mapped, array('self', '_stripLeading'));
            $host = str_replace(end($matches), implode(':', $mapped), $host);
        }

        // Handle "::".
        $pos = strpos($host, '::');
        if ($pos !== FALSE) {
            if (substr($host, 0, 2) == '::')
                $host = '0'.$host;
            if (substr($host, -2) == '::')
                $host .= '0';
            $repeat = 8 - substr_count($host, ':');
            $host = str_replace('::', ':'.str_repeat('0:', $repeat), $host);
        }

        // Remove superfluous leading zeros.
        $parts = explode(':', $host, 8);
        array_walk($parts, array('self', '_stripLeading'));
        if ($c10n == Erebot_Interface_Identity::CANON_IPV4) {
            $parts[7]   = (hexdec($parts[6]) << 16) + hexdec($parts[7]);
            $parts[6]   = long2ip(array_pop($parts));
        }

        if ($uncompressed)
            return strtolower(implode(':', $parts));

        // Compress the zeros.
        $host = 'x:' . implode(':', $parts) . ':x';
        for ($i = 8; $i > 0; $i--) {
            $s          = ':'.str_repeat('0:', $i);
            $pos        = strpos($host, $s);
            if ($pos !== FALSE) {
                $host = (string) substr($host, 0, $pos) . '::' .
                        (string) substr($host, $pos + strlen($s));
                break;
            }
        }

        $host = substr(
            $host,
            (substr($host, 0, 3) == 'x::') ?  1 :  2,
            (substr($host, -3) == '::x')   ? -1 : -2
        );
        return strtolower($host);
    }

    /// \copydoc Erebot_Interface_Identity::getHost()
    public function getHost($c10n)
    {
        if ($this->_host === NULL)
            return NULL;
        if ($c10n == Erebot_Interface_Identity::CANON_IPV6)
            return $this->_host;
        return self::_canonicalizeHost($this->_host, $c10n, FALSE);
    }

    /// \copydoc Erebot_Interface_Identity::getMask()
    public function getMask($c10n)
    {
        $ident  = ($this->_ident === NULL) ? '*' : $this->_ident;
        $host   = ($this->_host === NULL) ? '*' : $this->getHost($c10n);
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
        $nick = explode('!', $pattern, 2);
        if (count($nick) != 2)
            return FALSE;

        $ident = explode('@', $nick[1], 2);
        if (count($ident) != 2)
            return FALSE;

        $host   = $ident[1];
        $ident  = $ident[0];
        $nick   = $nick[0];

        if ($ident == '' || $host == '')
            return FALSE;

        if (!preg_match(self::_patternize($nick, TRUE), $this->_nick))
            return FALSE;

        $thisIdent = ($this->_ident === NULL) ? '' : $this->_ident;
        if (!preg_match(self::_patternize($ident, TRUE), $thisIdent))
            return FALSE;

        $thisHost = (
            ($this->_host === NULL) ?
            '' :
            self::_canonicalizeHost(
                $this->_host,
                Erebot_Interface_Identity::CANON_IPV6,
                TRUE
            )
        );

        // Detect a raw IPv4. The patterns allows the use of "*" where
        // a number is usually expected, as well as "a.b.c.d/netmask".
        // Must appear before the test for hostnames (see RFC 1123, §2.1).
        $decOctet           = '(?:\\d|[1-9]\\d|1\\d{2}|2[0-4]\\d|25[0-5]|\\*)';
        $dotAddress         = $decOctet.'(?:\\.'.$decOctet.'){3}(?:/[0-9]*)?';
        $dotAddress         = '#^'.$dotAddress.'$#Di';
        $isDottedAddress    = (bool) preg_match($dotAddress, $host);

        // It's some hostname (not an IPv4).
        if (strpos($host, ':') === FALSE && !$isDottedAddress) {
            return (bool) preg_match(
                self::_patternize($host, FALSE),
                $thisHost
            );
        }

        // Handle wildcards for IPv6 mapped IPv4.
        $host = explode('/', $host, 2);
        if (strpos($host[0], '*') !== FALSE) {
            if (count($host) == 2) {
                throw new Erebot_InvalidValueException(
                    "Wildcard characters and netmasks ".
                    "don't go together very well"
                );
            }

            $replace    = '';
            $host[1]    = 128;
            for ($i = 0; $i < 4; $i++) {
                $sep = (($i == 3) ? ($isDottedAddress ? '' : ':') : '.');
                if (substr($host[0], -2) == $sep . '*') {
                    $host[1]   -= 8;
                    $host[0]    = substr($host[0], 0, -2);
                    $replace    = $sep.'0'.$replace;
                }
            }
            $host[0]   .= $replace;
            // We could check whether some wildcards remain or not,
            // but self::_canonicalizeHost will raise an exception
            // for such a pattern anyway.
        }
        // No netmask given, assume /128.
        else if (count($host) == 1)
            $host[] = 128;
        else
            $host[1] = ((int) $host[1]) + ($isDottedAddress ? 96 : 0);

        if ($host[1] < 0 || $host[1] > 128) {
            throw new Erebot_InvalidValueException(
                'Invalid netmask value ('.$host[1].')'
            );
        }

        $host[0] = self::_canonicalizeHost(
            $host[0],
            Erebot_Interface_Identity::CANON_IPV6,
            TRUE
        );

        $pattParts      = explode(':', $host[0]);
        $thisParts  = explode(':', $thisHost);
        while ($host[1] > 0) {
            $mask       = 0x10000 - (1 << (16 - min($host[1], 16)));
            $pattValue  = hexdec(array_shift($pattParts)) & $mask;
            $thisValue  = hexdec(array_shift($thisParts)) & $mask;
            if ($pattValue != $thisValue)
                return FALSE;
            $host[1] -= 16;
        }
        return TRUE;
    }

    protected function _patternize($pattern, $matchDot)
    {
        $realPattern = '';
        $mapping = array('[^\\.]', '.');
        for ($i = 0, $len = strlen($pattern); $i < $len; $i++) {
            switch ($pattern[$i]) {
                case '?':
                case '*':
                    if ($matchDot)
                        $realPattern .= $mapping[1];
                    else {
                        // For wildcards when not running in $matchDot mode:
                        // allow them to match a dot when followed with a '*'
                        // (ie. '?*' or '**').
                        if ((($i + 1) < $len && $pattern[$i + 1] == '*')) {
                            $realPattern .= $mapping[1];
                            $i++;
                        }
                        else
                            $realPattern .= $mapping[0];
                    }

                    if ($pattern[$i] == '*')
                        $realPattern .= '*';
                    continue;

                default:
                    $realPattern .= preg_quote($pattern[$i], '#');
            }
        }
        return '#^'.$realPattern.'$#Di';
    }
}
