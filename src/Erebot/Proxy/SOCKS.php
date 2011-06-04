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
 *      Proxies data through a SOCKS 5 proxy.
 */
class   Erebot_Proxy_SOCKS
extends Erebot_Proxy_Base
{
    /// \copydoc Erebot_Proxy_Base::proxify()
    public function proxify(Erebot_URI $proxyURI, Erebot_URI $nextURI)
    {
        $port       = $nextURI->getPort();
        $scheme     = $nextURI->getScheme();

        if ($port === NULL)
            $port = getservbyname($scheme, 'tcp');
        if (!is_int($port) || $port <= 0 || $port > 65535)
            throw new Erebot_InvalidValueException('Invalid port');

        // No authentication or username/password-based authentication.
        $this->_write("\x05\x02\x00\x02");
        $line = $this->_read(2);

        if ($line[0] != "\x05")
            throw new Erebot_InvalidValueException('Bad SOCKS version');

        switch (ord($line[1])) {
            case 0: // No authentication
                break;
            case 2: // Username/password-based authentication
                $this->_userpass($proxyURI);
                break;
            default:
                throw new Erebot_InvalidValueException('No acceptable method');
        }

        // CONNECT.
        $host = $nextURI->getHost();
        $this->_write(
            "\x05\x01\x00\x03".
            pack("Ca*n", strlen($host), $host, $port)
        );

        $line = $this->_read(4);
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
                $this->_read(4);
                break;

            case 3: // Domain name.
                $len = ord($this->_read(1));
                $this->_read($len);
                break;

            case 4: // IPv6.
                $this->_read(16);
                break;

            default:
                throw new Erebot_InvalidValueException('Address type not supported');
        }

        // Consume the port.
        $this->_read(2);
    }

    protected function _userpass($proxyURI)
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

        $this->_write("\x01".pack("Ca*Ca*", $ulen, $username, $plen, $password));
        $line = $this->_read(2);

        if ($line[0] != "\x01")
            throw new Erebot_InvalidValueException('Bad subnegociation version');

        if ($line[1] != "\x00")
            throw new Erebot_InvalidValueException('Bad username or password');
    }

    protected function _write($line)
    {
        for (
            $written = 0, $len = strlen($line);
            $written < $len;
            $written += $fwrite
        ) {
            $fwrite = fwrite($this->_socket, substr($line, $written));
            if ($fwrite === FALSE)
                throw new Erebot_Exception('Connection closed by proxy');
        }
        return $written;
    }

    protected function _read($len)
    {
        $contents   = "";
        $clen       = 0;
        while (!feof($this->_socket) && $clen < $len) {
            $read = fread($this->_socket, $len - $clen);
            if ($read === FALSE)
                throw new Erebot_Exception('Connection closed by proxy');
            $contents  .= $read;
            $clen       = strlen($contents);
        }
        return $contents;
    }
}
