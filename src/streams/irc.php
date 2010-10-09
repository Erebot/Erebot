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

if (!defined('STREAM_CAST_AS_STREAM')) {
    define('STREAM_CAST_AS_STREAM', NULL);
    define('STREAM_CAST_FOR_SELECT', NULL);
}

include_once('src/exceptions/ConnectionFailure.php');

/**
 * \brief
 *      An IRC stream wrapper.
 *
 *  Provides a PHP stream wrapper for IRC(S) connections.
 *  It uses a simple URL-based format to create IRC connections
 *  and allows connections to be multiplexed.
 */
class ErebotWrapperIRC
{
    public      $context;
    public      $socket;

    public function __construct()
    {
        $this->socket   = NULL;
    }

    public function __destruct()
    {
    }

    public function stream_cast($use)
    {
        return $this->socket;
    }

    public function stream_close()
    {
        $res = fclose($this->socket);
        $this->socket = NULL;
        return $res;
    }

    public function stream_eof()
    {
        return feof($this->socket);
    }

    public function stream_flush()
    {
        return fflush($this->socket);
    }

    public function stream_open($path, $mode, $options, &$opened)
    {
        $url    = @parse_url($path);
        if ($url === FALSE          ||
            !isset($url['scheme'])  ||
            !isset($url['host'])) {
            if ($options & STREAM_REPORT_ERRORS)
                trigger_error('Malformed URL', E_USER_ERROR);
            return FALSE;
        }

        $queryString = isset($url['query']) ? $url['query'] : '';
        parse_str($queryString, $params);

        $ctxOptions = stream_context_get_options($this->context);

        if (isset($params['verify_peer']))
            $ctxOptions['ssl']['verify_peer'] =
                $this->_parseBool($params['verify_peer']);
        else if (!isset($ctxOptions['ssl']['verify_peer']))
            $ctxOptions['ssl']['verify_peer'] = TRUE;

        if (isset($params['allow_self_signed']))
            $ctxOptions['ssl']['allow_self_signed'] =
                $this->_parseBool($params['allow_self_signed']);
        else if (!isset($ctxOptions['ssl']['allow_self_signed']))
            $ctxOptions['ssl']['allow_self_signed'] = TRUE;

        if (isset($params['ciphers']))
            $ctxOptions['ssl']['ciphers'] = $params['ciphers'];
        else if (!isset($options['ssl']['ciphers']))
            $ctxOptions['ssl']['ciphers'] = 'HIGH';

        $this->context  = stream_context_create($ctxOptions);

        if (!strcasecmp($url['scheme'], 'ircs')) {
            $port           = 994;
            $proto          = 'tls';
        }
        else {
            $port       = 194;
            $proto      = 'tcp';
        }

        if (isset($url['port']))
            $port = $url['port'];

        $opened = $proto.'://'.$url['host'].':'.$port;
        try {
            $this->socket = stream_socket_client(
                $opened, $errno, $errstr,
                ini_get('default_socket_timeout'),
                STREAM_CLIENT_CONNECT,
                $this->context
            );
            stream_set_write_buffer($this->socket, 0);
            stream_socket_enable_crypto(
                $this->socket,
                FALSE,
                STREAM_CRYPTO_METHOD_TLS_CLIENT
            );
        }
        catch (EErebotErrorReporting $e) {
            return FALSE;
        }
        return TRUE;
    }

    public function stream_read($count)
    {
        if ($this->socket === NULL || feof($this->socket))
            return FALSE;

        $read = array($this->socket);
        $null = NULL;
        if (stream_select($read, $null, $null, 0) < 1)
            return '';

        $received   = fread($this->socket, $count);
        if ($received === FALSE || $received == '') {
            $this->stream_close();
            return FALSE;
        }

        return $received;
    }

    public function stream_set_option($option, $firstArg, $secondArg)
    {
        switch ($option) {
            case STREAM_OPTION_BLOCKING:
                return stream_set_blocking($this->socket, $firstArg);

            case STREAM_OPTION_READ_TIMEOUT:
                return stream_set_timeout($this->socket, $firstArg, $secondArg);

            case STREAM_OPTION_WRITE_BUFFER:
                // @FIXME: We shouldn't twist PHP like that...
                try {
                    $ok = stream_socket_enable_crypto(
                        $this->socket,
                        (bool) $firstArg,
                        STREAM_CRYPTO_METHOD_TLS_CLIENT
                    );
                }
                catch (Exception $e) {
                    $ok = FALSE;
                }
                return $ok;

            default:
                return FALSE;
        }
    }

    public function stream_write($data)
    {
        return fwrite($this->socket, $data);
    }

    protected function _parseBool($str)
    {
        if (!strcasecmp($str, 'True'))
            return TRUE;
        if (!strcasecmp($str, 'False'))
            return FALSE;
        if (ctype_digit($str))
            return (bool) ((int) $str);
        return TRUE;
    }
}

if (!in_array("irc", stream_get_wrappers()))
    stream_wrapper_register('irc', 'ErebotWrapperIRC', STREAM_IS_URL);

if (!in_array("ircs", stream_get_wrappers()))
    stream_wrapper_register('ircs', 'ErebotWrapperIRC', STREAM_IS_URL);

