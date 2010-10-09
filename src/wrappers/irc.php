<?php

if (!defined('STREAM_CAST_AS_STREAM')) {
    define('STREAM_CAST_AS_STREAM',     NULL);
    define('STREAM_CAST_FOR_SELECT',    NULL);
}

ErebotUtils::incl('../exceptions/ConnectionFailure.php');

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
    protected   $eof;
    protected   $buffer;

    public function __construct()
    {
        $this->socket   = NULL;
        $this->eof      = FALSE;
        $this->buffer   = '';
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
        return ($this->eof || feof($this->socket));
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

        $query_string = isset($url['query']) ? $url['query'] : '';
        parse_str($query_string, $params);

        if (!strcasecmp($url['scheme'], 'ircs')) {
            $ctx_options    =   stream_context_get_options($this->context);

            if (isset($params['verify_peer']))
                $ctx_options['ssl']['verify_peer'] =
                    $this->parseBool($params['verify_peer']);
            else if (!isset($ctx_options['ssl']['verify_peer']))
                $ctx_options['ssl']['verify_peer'] = TRUE;

            if (isset($params['allow_self_signed']))
                $ctx_options['ssl']['allow_self_signed'] =
                    $this->parseBool($params['allow_self_signed']);
            else if (!isset($ctx_options['ssl']['allow_self_signed']))
                $ctx_options['ssl']['allow_self_signed'] = TRUE;

            if (isset($params['ciphers']))
                $ctx_options['ssl']['ciphers'] = $params['ciphers'];
            else if (!isset($options['ssl']['ciphers']))
                $ctx_options['ssl']['ciphers'] = 'HIGH';

            $port           = 994;
            $this->context  = stream_context_create($ctx_options);
            $proto          = 'tls';
        }
        else {
            $port       = 194;
            $proto      = 'tcp';
        }

        if (isset($url['port']))
            $port = $url['port'];

        $opened = $proto.'://'.$url['host'].':'.$port;
        $this->socket   =   stream_socket_client(
                                $opened, $errno, $errstr,
                                ini_get('default_socket_timeout'),
                                STREAM_CLIENT_CONNECT, $this->context
                            );
        if ($this->socket === FALSE &&
            $options & STREAM_REPORT_ERRORS)
            trigger_error('Not a valid stream', E_USER_ERROR);
        return ($this->socket !== FALSE);
    }

    public function stream_read($count)
    {
        if ($this->eof)
            return FALSE;

        $read = array($this->socket);
        $null = NULL;

        while (stream_select($read, $null, $null, 0) == 1) {
            $received   = fread($this->socket, 4096);
            if ($received === FALSE || $received == '') {
                $this->eof = TRUE;
                break;
            }
            $this->buffer .= $received;
        }

        $pos = strpos($this->buffer, "\r\n");
        if ($pos !== FALSE) {
            $text           = substr($this->buffer, 0, $pos + 2);
            $this->buffer   = substr($this->buffer, $pos + 2);
            return $text;
        }

        return ($this->eof ? FALSE : '');
    }

    public function stream_set_option($option, $arg1, $arg2)
    {
        switch ($option) {
            case STREAM_OPTION_BLOCKING:
                return stream_set_blocking($this->socket, $arg1);

            case STREAM_OPTION_READ_TIMEOUT:
                return stream_set_timeout($this->socket, $arg1, $arg2);

            case STREAM_OPTION_WRITE_BUFFER:
                return FALSE; /// @TODO do something with stream_set_write_buffer(); ??

            default:
                return FALSE;
        }
    }

    public function stream_write($data)
    {
        return fwrite($this->socket, $data);
    }

    protected function parseBool($str)
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

stream_wrapper_register('irc',  'ErebotWrapperIRC', STREAM_IS_URL);
stream_wrapper_register('ircs', 'ErebotWrapperIRC', STREAM_IS_URL);

?>
