<?php
/*
    This file is part of Erebot, a modular IRC bot written in PHP.

    Copyright © 2010 François Poirotte

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

namespace Erebot;

/**
 * \brief
 *      Represents the identity of an IRC user.
 */
class Identity implements \Erebot\Interfaces\Identity
{
    /// Nickname for this user identity, either a string or \b null.
    protected $nick;

    /// Identity string for this user identity, either a string or \b null.
    protected $ident;

    /// Host part for this user identity, either a string or \b null.
    protected $host;

    /**
     * Creates a new object holding some user's identity.
     *
     * \param string $user
     *      A string, representing some user.
     *      This can be either a mask, such as "foo!ident@host"
     *      or just a nickname, such as "foo".
     *
     * \throw Erebot::InvalidValueException
     *      The given $user does not represent a valid identity.
     */
    public function __construct($user)
    {
        if (!is_string($user)) {
            throw new \Erebot\InvalidValueException('Not a valid identity');
        }

        $ident  = null;
        $host   = null;
        $nick   = null;
        $pos    = strpos($user, '!');
        if ($pos !== false) {
            $parts  = explode('@', substr($user, $pos + 1));
            if (count($parts) != 2) {
                throw new \Erebot\InvalidValueException('Invalid mask');
            }

            $nick   = substr($user, 0, $pos);
            $ident  = $parts[0];
            $host   = $parts[1];

            if ($nick === false || $ident == '' || $host == '') {
                throw new \Erebot\InvalidValueException('Invalid mask');
            }
        } elseif (strpos($user, '@') !== false) {
            // If there is a "@" but no "!", this is also invalid.
            throw new \Erebot\InvalidValueException('Invalid mask');
        } else {
            $nick = $user;
        }

        $this->nick    = $nick;
        $this->ident   = $ident;

        if ($host === null) {
            $this->host = null;
        } else {
            $this->host = self::canonicalizeHost(
                $host,
                \Erebot\Interfaces\Identity::CANON_IPV6,
                false
            );
        }
    }

    public function getNick()
    {
        return $this->nick;
    }

    public function getIdent()
    {
        return $this->ident;
    }

    /**
     * Strips leading '0' in front of a numeric string.
     *
     * \param string $number
     *      Numeric string whose leading '0' should be
     *      stripped. Passed by reference and modified
     *      in-place.
     *
     * \param opaque $key
     *      Unused.
     *
     * \return
     *      Nothing (\a $number is modified in-place).
     *
     * \note
     *      This method is meant to be used with the
     *      <a target="_blank" href="http://php.net/array_walk">array_walk()</a>
     *      PHP function.
     */
    protected static function stripLeading(&$number, $key)
    {
        $stripped = ltrim($number, '0');
        $number = ($stripped == '' ? '0' : $stripped);
    }

    /**
     * Canonicalizes a host.
     *
     * Despite its name, this method may be applied to either
     * a hostname or an IP address (v4 or v6).
     *
     * \param string $host
     *      Hostname or IP address to canonicalize.
     *
     * \param opaque $c10n
     *      The type of canonicalization to apply. This is
     *      either Erebot::Interfaces::Identity::CANON_IPV4
     *      or Erebot::Interfaces::Identity::CANON_IPV6 depending
     *      on whether IPv6-mapped-IPv4 addresses should be
     *      rendered in dotted form or in the regular IPv6 form,
     *      respectively.
     *
     * \param bool $uncompressed
     *      Whether to compress IP addresses or not.
     *      In compressed form, leading zeros in a colon group
     *      are omitted and a series of groups with all-zeros
     *      is represented with just "::".
     *
     * \retval string
     *      The original hostname or IP address in canonicalized
     *      (and optionally compressed) form.
     *
     * \note
     *      The only transformation that is applied to hostnames
     *      as part of this canonicalization method is one that
     *      lowercases them.
     *
     * \see
     *      See the various RFCs related to IP addresses
     *      for an exact description of the transformations
     *      that apply when compressing an IPv6 address.
     */
    protected static function canonicalizeHost($host, $c10n, $uncompressed)
    {
        if ($c10n != \Erebot\Interfaces\Identity::CANON_IPV4 &&
            $c10n != \Erebot\Interfaces\Identity::CANON_IPV6) {
            throw new \Erebot\InvalidValueException(
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
            if ($c10n == \Erebot\Interfaces\Identity::CANON_IPV4) {
                array_walk($parts, array('self', 'stripLeading'));
                return $prefix.':ffff:'.implode('.', $parts);
            }

            $mapped = array(
                sprintf('%02x%02x', $parts[0], $parts[1]),
                sprintf('%02x%02x', $parts[2], $parts[3]),
            );
            array_walk($mapped, array('self', 'stripLeading'));
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
            if ($last === false || strspn($last, '.1234567890') != strlen($last)) {
                return strtolower($host);
            }
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
        if (!preg_match('/^'.$colonAddress.'$/Di', $host, $matches)) {
            throw new \Erebot\InvalidValueException(
                'Unrecognized "host" ('.$host.')'
            );
        }

        // It's an IPv6 alright! Let's handle it.
        if (count($matches) > 1) {
            // IPv6 mapped IPv4.
            $mapped = end($matches);
            $parts  = explode('.', $mapped, 4);
            $mapped = array(
                sprintf('%02x%02x', $parts[0], $parts[1]),
                sprintf('%02x%02x', $parts[2], $parts[3]),
            );
            array_walk($mapped, array('self', 'stripLeading'));
            $host = str_replace(end($matches), implode(':', $mapped), $host);
        }

        // Handle "::".
        $pos = strpos($host, '::');
        if ($pos !== false) {
            if (substr($host, 0, 2) == '::') {
                $host = '0'.$host;
            }
            if (substr($host, -2) == '::') {
                $host .= '0';
            }
            $repeat = 8 - substr_count($host, ':');
            $host = str_replace('::', ':'.str_repeat('0:', $repeat), $host);
        }

        // Remove superfluous leading zeros.
        $parts = explode(':', $host, 8);
        array_walk($parts, array('self', 'stripLeading'));
        if ($c10n == \Erebot\Interfaces\Identity::CANON_IPV4) {
            $parts[7]   = (hexdec($parts[6]) << 16) + hexdec($parts[7]);
            $parts[6]   = long2ip(array_pop($parts));
        }

        if ($uncompressed) {
            return strtolower(implode(':', $parts));
        }

        // Compress the zeros.
        $host = 'x:' . implode(':', $parts) . ':x';
        for ($i = 8; $i > 1; $i--) {
            $s          = ':'.str_repeat('0:', $i);
            $pos        = strpos($host, $s);
            if ($pos !== false) {
                $host = (string) substr($host, 0, $pos) . '::' .
                        (string) substr($host, $pos + strlen($s));
                break;
            }
        }

        $host = str_replace(array('x::', '::x'), '::', $host);
        $host = str_replace(array('x:', ':x'), '', $host);
        return strtolower($host);
    }

    public function getHost($c10n)
    {
        if ($this->host === null) {
            return null;
        }
        if ($c10n == \Erebot\Interfaces\Identity::CANON_IPV6) {
            return $this->host;
        }
        return self::canonicalizeHost($this->host, $c10n, false);
    }

    public function getMask($c10n)
    {
        $ident  = ($this->ident === null) ? '*' : $this->ident;
        $host   = ($this->host === null) ? '*' : $this->getHost($c10n);
        return $this->nick.'!'.$ident.'@'.$host;
    }

    public function __toString()
    {
        return $this->nick;
    }

    /**
     * Indicates whether this identity matches a given pattern.
     *
     * \param string $pattern
     *      The pattern this identity should be tested against.
     *      The '?' and '*' wildcard characters are supported.
     *
     * \param Erebot::Interfaces::IrcCollator $collator
     *      Collator object to use to compare IRC nicknames.
     *
     * \retval bool
     *      \b true if this identity matches the given pattern,
     *      \b false otherwise.
     */
    public function match($pattern, \Erebot\Interfaces\IrcCollator $collator)
    {
        $nick = explode('!', $pattern, 2);
        if (count($nick) != 2) {
            return false;
        }

        $ident = explode('@', $nick[1], 2);
        if (count($ident) != 2) {
            return false;
        }

        $host   = $ident[1];
        $ident  = $ident[0];
        $nick   = $nick[0];

        if ($ident == '' || $host == '') {
            return false;
        }

        $nick       = $collator->normalizeNick($nick);
        $thisNick   = $collator->normalizeNick($this->nick);
        if (!preg_match(self::patternize($nick, true), $thisNick)) {
            return false;
        }

        $thisIdent = ($this->ident === null) ? '' : $this->ident;
        if (!preg_match(self::patternize($ident, true), $thisIdent)) {
            return false;
        }

        $thisHost = (
            ($this->host === null) ?
            '' :
            self::canonicalizeHost(
                $this->host,
                \Erebot\Interfaces\Identity::CANON_IPV6,
                true
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
        if (strpos($host, ':') === false && !$isDottedAddress) {
            return (bool) preg_match(
                self::patternize($host, false),
                $thisHost
            );
        }

        // Handle wildcards for IPv6 mapped IPv4.
        $host = explode('/', $host, 2);
        if (strpos($host[0], '*') !== false) {
            if (count($host) == 2) {
                throw new \Erebot\InvalidValueException(
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
            // but self::canonicalizeHost will raise an exception
            // for such a pattern anyway.
        } elseif (count($host) == 1) {
            // No netmask given, assume /128.
            $host[] = 128;
        } else {
            $host[1] = ((int) $host[1]) + ($isDottedAddress ? 96 : 0);
        }

        if ($host[1] < 0 || $host[1] > 128) {
            throw new \Erebot\InvalidValueException(
                'Invalid netmask value ('.$host[1].')'
            );
        }

        $host[0] = self::canonicalizeHost(
            $host[0],
            \Erebot\Interfaces\Identity::CANON_IPV6,
            true
        );

        $pattParts  = explode(':', $host[0]);
        $thisParts  = explode(':', $thisHost);
        while ($host[1] > 0) {
            $mask       = 0x10000 - (1 << (16 - min($host[1], 16)));
            $pattValue  = hexdec(array_shift($pattParts)) & $mask;
            $thisValue  = hexdec(array_shift($thisParts)) & $mask;
            if ($pattValue != $thisValue) {
                return false;
            }
            $host[1] -= 16;
        }
        return true;
    }

    /**
     * Turn a basic pattern (optionally) containing wirldcards
     * into a regular expression pattern, intended to match
     * hostnames or IP addresses.
     *
     * \param string $pattern
     *      Basic pattern, possibly containing
     *      wildcard characters ('*' and '?').
     *
     * \param bool $matchDot
     *      Whether the '?' and '*' wildcard characters
     *      should also match dots '.' (\b true) or not (\b false).
     *
     * \retval string
     *      A regular expression pattern that matches the
     *      criteria expressed in the original pattern.
     */
    protected static function patternize($pattern, $matchDot)
    {
        $realPattern = '';
        $mapping = array('[^\\.]', '.');
        for ($i = 0, $len = strlen($pattern); $i < $len; $i++) {
            switch ($pattern[$i]) {
                case '?':
                case '*':
                    if ($matchDot) {
                        $realPattern .= $mapping[1];
                    } else {
                        // For wildcards when not running in $matchDot mode:
                        // allow them to match a dot when followed with a '*'
                        // (ie. '?*' or '**').
                        if ((($i + 1) < $len && $pattern[$i + 1] == '*')) {
                            $realPattern .= $mapping[1];
                            $i++;
                        } else {
                            $realPattern .= $mapping[0];
                        }
                    }

                    if ($pattern[$i] == '*') {
                        $realPattern .= '*';
                    }
                    continue;

                default:
                    $realPattern .= preg_quote($pattern[$i], '#');
            }
        }
        return '#^'.$realPattern.'$#Di';
    }
}
