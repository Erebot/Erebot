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

class       Erebot_Proxy_SOCKS
implements  Erebot_Interface_Proxy
{
    static public function getDefaultPort()
    {
        // As assigned by IANA.
        return 1080;
    }

    static public function requiresSSL()
    {
        return FALSE;
    }

    static public function proxify(Erebot_URI $proxyURI, Erebot_URI $nextURI, $socket)
    {
        $logging    = Plop::getInstance();
        $logger     = $logging->getLogger(__FILE__ . DIRECTORY_SEPARATOR);

        if (!is_resource($socket))
            throw new Erebot_InvalidValueException('Not a socket');

        $reflector      = new ReflectionClass(
            'Erebot_Proxy_'.strtoupper($nextURI->getScheme())
        );
        $port           = $nextURI->getPort();
        if ($port === NULL)
            $port = call_user_func(
                array($reflector->getName(), 'getDefaultPort')
            );
        if (!is_int($port) || $port <= 0 || $port > 65535)
            throw new Erebot_InvalidValueException('Invalid port');

        // No authentication or username/password-based authentication.
        self::_write($socket, "\x05\x02\x00\x02");
        $line = self::_read($socket, 2);

        if ($line[0] != "\x05")
            throw new Erebot_InvalidValueException('Bad SOCKS version');

        switch (ord($line[1])) {
            case 0: // No authentication
                break;
            case 2: // Username/password-based authentication
                self::_userpass($proxyURI, $socket);
                break;
            default:
                throw new Erebot_InvalidValueException('No acceptable method');
        }

        // CONNECT.
        $host = $nextURI->getHost();
        self::_write(
            $socket,
            "\x05\x01\x00\x03".
            pack("Ca*n", strlen($host), $host, $port)
        );

        $line = self::_read($socket, 4);
        if ($line[0] != "\x05")
            throw new Erebot_InvalidValueException('Bad SOCKS version');

        switch (ord($line[1])) {
            case 0:
                break;

            case 1:
                throw new Erebot_InvalidValueException('General SOCKS server failure');

            case 2:
                throw new Erebot_InvalidValueException('Connection not allowed by ruleset');

            case 3:
                throw new Erebot_InvalidValueException('Network unreachable');

            case 4:
                throw new Erebot_InvalidValueException('Host unreachable');

            case 5:
                throw new Erebot_InvalidValueException('Connection refused');

            case 6:
                throw new Erebot_InvalidValueException('TTL expired');

            case 7:
                throw new Erebot_InvalidValueException('Command not supported');

            case 8:
                throw new Erebot_InvalidValueException('Address type not supported');

            default:
                throw new Erebot_InvalidValueException('Unknown error');
        }

        switch (ord($line[3])) {
            case 1: // IPv4.
                self::_read($socket, 4);
                break;

            case 3: // Domain name.
                $len = ord(self::_read($socket, 1));
                self::_read($socket, $len);
                break;

            case 4: // IPv6.
                self::_read($socket, 16);
                break;

            default:
                throw new Erebot_InvalidValueException('Address type not supported');
        }

        // Consume the port.
        self::_read($socket, 2);
    }

    static protected function _userpass($proxyURI, $socket)
    {
        $username = $proxyURI->asParsedURL(PHP_URL_USER);
        $password = $proxyURI->asParsedURL(PHP_URL_PASS);

        if ($username === NULL || $password === NULL)
            throw new Erebot_InvalidValueException('No username or password supplied');

        $ulen = strlen($username);
        $plen = strlen($password);
        if ($ulen > 255)
            throw new Erebot_InvalidValueException('Username too long (max. 255)');

        if ($plen > 255)
            throw new Erebot_InvalidValueException('Password too long (max. 255)');

        self::_write($socket, "\x01".pack("Ca*Ca*", $ulen, $username, $plen, $password));
        $line = self::_read($socket, 2);

        if ($line[0] != "\x01")
            throw new Erebot_InvalidValueException('Bad subnegociation version');

        if ($line[1] != "\x00")
            throw new Erebot_InvalidValueException('Bad username or password');
    }

    static protected function _write($socket, $line)
    {
        for (
            $written = 0, $len = strlen($line);
            $written < $len;
            $written += $fwrite
        ) {
            $fwrite = fwrite($socket, substr($line, $written));
            if ($fwrite === FALSE)
                throw new Erebot_Exception('Connection closed by proxy');
        }
        return $written;
    }

    static protected function _read($socket, $len)
    {
        $contents   = "";
        $clen       = 0;
        while (!feof($socket) && $clen < $len) {
            $read = fread($socket, $len - $clen);
            if ($read === FALSE)
                throw new Erebot_Exception('Connection closed by proxy');
            $contents  .= $read;
            $clen       = strlen($contents);
        }
        return $contents;
    }
}
