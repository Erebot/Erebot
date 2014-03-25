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

namespace Erebot\Proxy;

/**
 * \brief
 *      Proxies data through a SOCKS 5 proxy.
 */
class SOCKS extends \Erebot\Proxy\Base
{
    public function proxify(\Erebot\URIInterface $proxyURI, \Erebot\URIInterface $nextURI)
    {
        $port       = $nextURI->getPort();
        $scheme     = $nextURI->getScheme();

        if ($port === null) {
            $port = getservbyname($scheme, 'tcp');
        }
        if (!is_int($port) || $port <= 0 || $port > 65535) {
            throw new \Erebot\InvalidValueException('Invalid port');
        }

        // No authentication or username/password-based authentication.
        $this->write("\x05\x02\x00\x02");
        $line = $this->read(2);

        if ($line[0] != "\x05") {
            throw new \Erebot\InvalidValueException('Bad SOCKS version');
        }

        switch (ord($line[1])) {
            case 0: // No authentication
                break;
            case 2: // Username/password-based authentication
                $this->userpass($proxyURI);
                break;
            default:
                throw new \Erebot\InvalidValueException('No acceptable method');
        }

        // CONNECT.
        $host = $nextURI->getHost();
        $this->write(
            "\x05\x01\x00\x03".
            pack("Ca*n", strlen($host), $host, $port)
        );

        $line = $this->read(4);
        if ($line[0] != "\x05") {
            throw new \Erebot\InvalidValueException('Bad SOCKS version');
        }

        $error = ord($line[1]);
        if ($error) {
            // Taken fromt eh RFC.
            $errors = array(
                1 =>
                'General SOCKS server failure',
                'Connection not allowed by ruleset',
                'Network unreachable',
                'Host unreachable',
                'Connection refused',
                'TTL expired',
                'Command not supported',
                'Address type not supported',
            );
            if (!isset($errors[$error])) {
                throw new \Erebot\InvalidValueException('Unknown error');
            }
            throw new \Erebot\InvalidValueException($errors[$error]);
        }

        switch (ord($line[3])) {
            case 1: // IPv4.
                $this->read(4);
                break;

            case 3: // Domain name.
                $len = ord($this->read(1));
                $this->read($len);
                break;

            case 4: // IPv6.
                $this->read(16);
                break;

            default:
                throw new \Erebot\InvalidValueException(
                    'Address type not supported'
                );
        }

        // Consume the port.
        $this->read(2);
    }

    /**
     * Does the authentication step.
     *
     * \param Erebot::URIInterface $proxyURI
     *      An object representing the URI of this SOCKS proxy server,
     *      containing the credentials that will be sent during the
     *      authentication step.
     */
    protected function userpass(\Erebot\URIInterface $proxyURI)
    {
        $username = $proxyURI->asParsedURL(PHP_URL_USER);
        $password = $proxyURI->asParsedURL(PHP_URL_PASS);

        if ($username === null || $password === null) {
            throw new \Erebot\InvalidValueException(
                'No username or password supplied'
            );
        }

        $ulen = strlen($username);
        $plen = strlen($password);
        if ($ulen > 255) {
            throw new \Erebot\InvalidValueException(
                'Username too long (max. 255)'
            );
        }

        if ($plen > 255) {
            throw new \Erebot\InvalidValueException(
                'Password too long (max. 255)'
            );
        }

        $this->write(
            "\x01".pack(
                "Ca*Ca*",
                $ulen,
                $username,
                $plen,
                $password
            )
        );
        $line = $this->read(2);

        if ($line[0] != "\x01") {
            throw new \Erebot\InvalidValueException(
                'Bad subnegociation version'
            );
        }

        if ($line[1] != "\x00") {
            throw new \Erebot\InvalidValueException('Bad username or password');
        }
    }

    /**
     * Passes some data to the proxy server.
     *
     * \param string $line
     *      Data to send.
     *
     * \retval int
     *      Number of bytes actually sent (this may be lower
     *      that the length of the initial $line if some bytes
     *      could not be sent).
     */
    protected function write($line)
    {
        for ($written = 0, $len = strlen($line); $written < $len; $written += $fwrite) {
            $fwrite = fwrite($this->socket, substr($line, $written));
            if ($fwrite === false) {
                throw new \Erebot\Exception('Connection closed by proxy');
            }
        }
        return $written;
    }

    /**
     * Reads some data from the proxy server.
     *
     * \param int $len
     *      Number of bytes to read from the proxy server.
     *
     * \retval string
     *      Actual data read.
     */
    protected function read($len)
    {
        $contents   = "";
        $clen       = 0;
        while (!feof($this->socket) && $clen < $len) {
            $read = fread($this->socket, $len - $clen);
            if ($read === false) {
                throw new \Erebot\Exception('Connection closed by proxy');
            }
            $contents  .= $read;
            $clen       = strlen($contents);
        }
        return $contents;
    }
}
