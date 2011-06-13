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
 *      Proxies data through an HTTP proxy.
 */
class   Erebot_Proxy_HTTP
extends Erebot_Proxy_Base
{
    /// \copydoc Erebot_Proxy_Base::proxify()
    public function proxify(Erebot_URI $proxyURI, Erebot_URI $nextURI)
    {
        $credentials    = $proxyURI->getUserInfo();
        $host           = $nextURI->getHost();
        $port           = $nextURI->getPort();
        $scheme         = $nextURI->getScheme();

        if ($port === NULL)
            $port = getservbyname($scheme, 'tcp');
        if (!is_int($port) || $port <= 0 || $port > 65535)
            throw new Erebot_InvalidValueException('Invalid port');

        $request = "";
        $request .= sprintf("CONNECT %s:%d HTTP/1.0\r\n", $host, $port);
        $request .= sprintf("Host: %s:%d\r\n", $host, $port);
        $request .= sprintf("User-Agent: Erebot/%s\r\n", Erebot_Interface_Core::VERSION);

        if ($credentials !== NULL) {
            $request .= sprintf(
                "Proxy-Authorization: basic %s\r\n",
                base64_encode($credentials)
            );
        }
        $request .= "\r\n";

        for (
            $written = 0, $len = strlen($request);
            $written < $len;
            $written += $fwrite
        ) {
            $fwrite = fwrite($this->_socket, substr($request, $written));
            if ($fwrite === FALSE)
                throw new Erebot_Exception('Connection closed by proxy');
        }

        $line = stream_get_line($this->_socket, 4096, "\r\n");
        if ($line === FALSE)
            throw new Erebot_InvalidValueException('Invalid response from proxy');
        $this->_logger->debug("%s", addcslashes($line, "\000..\037"));
        $contents = array_filter(explode(" ", $line));

        switch ((int) $contents[1]) {
            case 200:
                break;
            case 407:
                throw new Erebot_Exception('Proxy authentication required');
            default:
                throw new Erebot_Exception('Connection rejected by proxy');
        }

        // Avoid an endless loop by limiting the number of headers.
        // No HTTP server will send more than 2^10 headers anyway.
        $max = (1 << 10);
        for ($i = 0; $i < $max; $i++) {
            $line = stream_get_line($this->_socket, 4096, "\r\n");
            if ($line === FALSE)
                throw new Erebot_InvalidValueException('Invalid response from proxy');
            if ($line == "")
                break;
            $this->_logger->debug("%s", addcslashes($line, "\000..\037"));
        }
        if ($i === $max)
            throw new Erebot_InvalidValueException('Endless loop detected in proxy response');
    }
}
