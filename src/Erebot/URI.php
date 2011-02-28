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
 * This class can be used as both a parser and a generator for
 * Uniform Resource Identifiers (URI) as defined in RFC 3986.
 * It is primarly meant to deal with with absolute URIs but also
 * offers methods to deal with relative URIs.
 * It is mostly compatible with parse_url(), but tends to be
 * stricter when validating data.
 *
 * This implementation doesn't assume that the "userinfo" part
 * is made up of a "user:password" pair (contrary to parse_url()
 * which is based on RFC 1738), and provides a single field
 * named "userinfo" instead. Such a pair will be merged upon
 * encounter.
 *
 * All components are normalized by default when retrieved using
 * any of the getters except asParsedURL(). You may override this
 * behaviour by passing $raw=TRUE to said getters.
 * Normalization is done using the algorithms defined in RFC 3986.
 */
class   Erebot_URI
{
    protected $_scheme;
    protected $_userinfo;
    protected $_authority;
    protected $_port;
    protected $_path;
    protected $_query;
    protected $_fragment;

    public function __construct($uri)
    {
        if (is_string($uri))
            $uri = $this->_parseURI($uri, FALSE);
        if (!is_array($uri))
            throw new Erebot_InvalidValueException('Invalid URI');

        if (!isset($uri['userinfo']) && isset($uri['user'])) {
            $uri['userinfo'] = $uri['user'];
            if (isset($uri['pass']))
                $uri['userinfo'] .= ':'.$uri['pass'];
        }

        $components = array(
            'Scheme',
            'Host',
            'Port',
            'Path',
            'Query',
            'Fragment',
            'UserInfo',
        );

        foreach ($components as $component) {
            $tmp    = strtolower($component);
            $setter = 'set'.$component;
            if (isset($uri[$tmp]))
                $this->$setter($uri[$tmp]);
            else
                $this->$setter(NULL);
        }
    }

    protected function _parseURI($uri, $relative)
    {
        $result = array();

        if (!$relative) {
            // Parse scheme.
            $pos = strpos($uri, ':');
            if (!$pos)  // An URI starting with ":" is also invalid.
                throw new Erebot_InvalidValueException('No scheme found');

            $result['scheme'] = substr($uri, 0, $pos);
            $uri = (string) substr($uri, $pos + 1);
        }

        // Parse fragment.
        $pos = strpos($uri, '#');
        if ($pos !== FALSE) {
            $result['fragment'] = (string) substr($uri, $pos + 1);
            $uri = (string) substr($uri, 0, $pos);
        }

        // Parse query string.
        $pos = strpos($uri, '?');
        if ($pos !== FALSE) {
            $result['query'] = (string) substr($uri, $pos + 1);
            $uri = (string) substr($uri, 0, $pos);
        }

        // Handle "path-empty".
        if ($uri == '') {
            $result['path'] = '';
            return $result;
        }

        // Handle "hier-part".
        if (substr($uri, 0, 2) == '//') {
            // Remove leftovers from the scheme field.
            $uri = (string) substr($uri, 2);

            // Parse path.
            $result['path'] = '';
            $pos = strpos($uri, '/');
            if ($pos !== FALSE) {
                $result['path'] = substr($uri, $pos);
                $uri = (string) substr($uri, 0, $pos);
            }

            // Parse userinfo.
            $pos = strpos($uri, '@');
            if ($pos !== FALSE) {
                $result['userinfo'] = (string) substr($uri, 0, $pos);
                $uri = (string) substr($uri, $pos + 1);
            }

            // Parse port.
            $pos = strrpos($uri, ':');
            if ($pos !== FALSE) {
                $result['port'] = (string) substr($uri, $pos + 1);
                $uri = (string) substr($uri, 0, $pos);
            }

            $result['host'] = $uri;
            return $result;
        }

        // Handle "path-absolute" & "path-rootless".
        $result['path'] = $uri;
        return $result;
    }

    public function _normalizePercent($data)
    {
        $unreserved =   'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
                        'abcdefghijklmnopqrstuvwxyz'.
                        '-._~';
        return preg_replace(
            '/%([[:xdigit:]])/e',
            "(strpos(".$unreserved.", chr(hexdec('\\1'))) !== FALSE ".
            "? chr(hexdec('\\1')) ".
            ": strtoupper('\\1'))",
            $data
        );
    }

    public function toURI($raw = FALSE)
    {
        // 5.3.  Component Recomposition
        $result = "";

        // In our case, the scheme will always be set
        // because we only deal with absolute URIs here.
        // The condition is checked anyway to keep the code
        // in line with the algorithm described in RFC 3986.
        if ($this->_scheme !== NULL)
            $result .= $this->getScheme($raw).':';

        if ($this->_host !== NULL) {
            $result .= '//';
            if ($this->_userinfo !== NULL)
                $result .= $this->getUserInfo($raw)."@";

            $result    .= $this->getHost($raw);
            $port       = $this->getPort($raw);
            if ($port !== NULL)
                $result .= ':'.$port;
        }

        $result .= $this->getPath($raw);

        if ($this->_query !== NULL)
            $result .= '?'.$this->getQuery($raw);

        if ($this->_fragment !== NULL)
            $result .= '#'.$this->getFragment($raw);

        return $result;
    }

    public function __toString()
    {
        return $this->toURI();
    }

    public function getScheme($raw = FALSE)
    {
        if ($raw)
            return $this->_scheme;
        return ($this->_scheme !== NULL) ? strtolower($this->_scheme) : NULL;
    }

    public function setScheme($scheme)
    {
        // scheme        = ALPHA *( ALPHA / DIGIT / "+" / "-" / "." )
        if (!preg_match('/^[-[:alpha:][:alnum:]\\+\\.]*$/Di', $scheme))
            throw new Erebot_InvalidValueException('Invalid scheme');
        $this->_scheme = $scheme;
    }

    public function getUserInfo($raw = FALSE)
    {
        if ($raw)
            return $this->_userinfo;
        return $this->_normalizePercent($this->_userinfo);
    }

    public function setUserInfo($userinfo)
    {
        /*
        userinfo      = *( unreserved / pct-encoded / sub-delims / ":" )
        pct-encoded   = "%" HEXDIG HEXDIG
        unreserved    = ALPHA / DIGIT / "-" / "." / "_" / "~"
        sub-delims    = "!" / "$" / "&" / "'" / "(" / ")"
                      / "*" / "+" / "," / ";" / "="
        */
        $pattern =  '(?:'.
                        '[-[:alnum:]\\._~!\\$&\'\\(\\)\\*\\+,;=:]|'.
                        '%[[:xdigit:]]{2}'.
                    ')*';
        if ($userinfo !== NULL && !preg_match('/^'.$pattern.'$/Di', $userinfo))
            throw new Erebot_InvalidValueException('Invalid user information');
        $this->_userinfo = $userinfo;
    }

    public function getHost($raw = FALSE)
    {
        if ($raw)
            return $this->_host;
        return  ($this->_host !== NULL)
                ? strtolower($this->_normalizePercent($this->_host))
                : NULL;
    }

    public function setHost($host)
    {
        /*
        host          = IP-literal / IPv4address / reg-name
        IP-literal    = "[" ( IPv6address / IPvFuture  ) "]"
        IPvFuture     = "v" 1*HEXDIG "." 1*( unreserved / sub-delims / ":" )
        IPv6address   =                            6( h16 ":" ) ls32
                      /                       "::" 5( h16 ":" ) ls32
                      / [               h16 ] "::" 4( h16 ":" ) ls32
                      / [ *1( h16 ":" ) h16 ] "::" 3( h16 ":" ) ls32
                      / [ *2( h16 ":" ) h16 ] "::" 2( h16 ":" ) ls32
                      / [ *3( h16 ":" ) h16 ] "::"    h16 ":"   ls32
                      / [ *4( h16 ":" ) h16 ] "::"              ls32
                      / [ *5( h16 ":" ) h16 ] "::"              h16
                      / [ *6( h16 ":" ) h16 ] "::"
        h16           = 1*4HEXDIG
        ls32          = ( h16 ":" h16 ) / IPv4address
        IPv4address   = dec-octet "." dec-octet "." dec-octet "." dec-octet
        dec-octet     = DIGIT                 ; 0-9
                      / %x31-39 DIGIT         ; 10-99
                      / "1" 2DIGIT            ; 100-199
                      / "2" %x30-34 DIGIT     ; 200-249
                      / "25" %x30-35          ; 250-255
        reg-name      = *( unreserved / pct-encoded / sub-delims )
        pct-encoded   = "%" HEXDIG HEXDIG
        unreserved    = ALPHA / DIGIT / "-" / "." / "_" / "~"
        sub-delims    = "!" / "$" / "&" / "'" / "(" / ")"
                      / "*" / "+" / "," / ";" / "="
        */
        $decOctet       = '(?:\\d|[1-9]\\d|1\\d{2}|2[0-4]\\d|25[0-5])';
        $IPv4address    = $decOctet.'(?:\\.'.$decOctet.'){3}';
        $h16            = '[[:xdigit:]]{1,4}';
        $ls32           = '(?:'.$h16.':'.$h16.'|'.$IPv4address.')';
        $IPv6address    =   '(?:'.
                                '(?:'.$h16.':){6}'.$ls32.'|'.
                                '::(?:'.$h16.':){5}'.$ls32.'|'.
                                '(?:'.$h16.')?::(?:'.$h16.':){4}'.$ls32.'|'.
                                '(?:(?:'.$h16.')?'.$h16.')?::(?:'.$h16.':){3}'.$ls32.'|'.
                                '(?:(?:'.$h16.'){0,2}'.$h16.')?::(?:'.$h16.':){2}'.$ls32.'|'.
                                '(?:(?:'.$h16.'){0,3}'.$h16.')?::'.$h16.':'.$ls32.'|'.
                                '(?:(?:'.$h16.'){0,4}'.$h16.')?::'.$ls32.'|'.
                                '(?:(?:'.$h16.'){0,5}'.$h16.')?::'.$h16.'|'.
                                '(?:(?:'.$h16.'){0,6}'.$h16.')?::'.
                            ')';
        $IPvFuture      = 'v[[:xdigit:]]+\\.[-[:alnum:]\\._~!\\$&\'\\(\\)*\\+,;=]+';
        $IPliteral      = '\\[(?:'.$IPv6address.'|'.$IPvFuture.')\\]';
        $regName        = '(?:[-[:alnum:]\\._~!\\$&\'\\(\\)*\\+,;=]|%[[:xdigit:]]{2})*';
        $pattern        = '(?:'.$IPliteral.'|'.$IPv4address.'|'.$regName.')';
        if ($host !== NULL && !preg_match('/^'.$pattern.'$/Di', $host))
            throw new Erebot_InvalidValueException('Invalid host');
        $this->_host = $host;
    }

    public function getPort($raw = FALSE)
    {
        if ($raw)
            return $this->_port;

        if ($this->_port == '')
            return NULL;

        $port = (int) $this->_port;

        // Try to canonicalize the port.
        $tcp = getservbyname($this->_scheme, 'tcp');
        $udp = getservbyname($this->_scheme, 'udp');
        if (($tcp != $port && $udp === FALSE) ||
            ($udp != $port && $tcp === FALSE))
            return $port;
        return NULL;
    }

    public function setPort($port)
    {
        // port          = *DIGIT
        if (is_int($port))
            $port = (string) $port;
        if ($port !== NULL && strspn($port, '0123456789') != strlen($port))
            throw new Erebot_InvalidValueException('Invalid port');
        $this->_port = $port;
    }

    protected function _removeDotSegments($path)
    {
        if ($path === NULL)
            throw new Erebot_InvalidValueException('Path not set');

        // §5.2.4.  Remove Dot Segments
        $input  = $path;
        $output = '';

        while ($input != '') {
            if (substr($input, 0, 3) == '../')
                $input = (string) substr($input, 3);

            else if (substr($input, 0, 2) == './')
                $input = (string) substr($input, 2);

            else if (substr($input, 0, 3) == '/./')
                $input = substr($input, 2);

            else if ($input == '/.')
                $input = '/';

            else if (substr($input, 0, 4) == '/../') {
                $input  = (string) substr($input, 3);
                $pos    = strrpos($output, '/');
                if ($pos === FALSE)
                    $output = '';
                else
                    $output = substr($output, 0, $pos);
            }

            else if ($input == '/..') {
                $input  = '/';
                $pos    = strrpos($output, '/');
                if ($pos === FALSE)
                    $output = '';
                else
                    $output = substr($output, 0, $pos);
            }

            else if ($input == '.' || $input == '..')
                $input = '';

            else {
                $pos = strpos($input, '/', 1);
                if ($pos === FALSE) {
                    $output    .= $input;
                    $input      = '';
                }
                else {
                    $output    .= substr($input, 0, $pos);
                    $input      = substr($input, $pos);
                }
            }
        }

        return $output;
    }

    protected function _merge($path)
    {
        // 5.2.3.  Merge Paths
        if ($this->_host !== NULL && $this->_path == '')
            return '/'.$path;

        $pos = strrpos($this->_path, '/');
        if ($pos === FALSE)
            return $path;
        return substr($this->_path, 0, $pos + 1).$path;
    }

    public function getPath($raw = FALSE)
    {
        if ($raw)
            return $this->_path;
        return $this->_normalizePercent($this->_removeDotSegments($this->_path));
    }

    protected function _validatePath($path, $relative)
    {
        /*
        path          = path-abempty    ; begins with "/" or is empty
                      / path-absolute   ; begins with "/" but not "//"
                      / path-noscheme   ; begins with a non-colon segment
                      / path-rootless   ; begins with a segment
                      / path-empty      ; zero characters
        path-abempty  = *( "/" segment )
        path-absolute = "/" [ segment-nz *( "/" segment ) ]
        path-noscheme = segment-nz-nc *( "/" segment )
                      ; only used for a relative URI
        path-rootless = segment-nz *( "/" segment )
                      ; only used for an absolute URI
        path-empty    = 0<pchar>
        segment       = *pchar
        segment-nz    = 1*pchar
        segment-nz-nc = 1*( unreserved / pct-encoded / sub-delims / "@" )
                      ; non-zero-length segment without any colon ":"
        pchar         = unreserved / pct-encoded / sub-delims / ":" / "@"
        unreserved    = ALPHA / DIGIT / "-" / "." / "_" / "~"
        sub-delims    = "!" / "$" / "&" / "'" / "(" / ")"
                      / "*" / "+" / "," / ";" / "="
        pct-encoded   = "%" HEXDIG HEXDIG
        */
        $pchar          =   '(?:'.
                                '[-[:alnum:]\\._~!\\$&\'\\(\\)\\*\\+,;=:@]|'.
                                '%[[:xdigit:]]'.
                            ')';
        $segment        = '(?:'.$pchar.'*)';
        $segmentNz      = '(?:'.$pchar.'+)';
        $segmentNzNc    =   '(?:'.
                                '[-[:alnum:]\\._~!\\$&\'\\(\\)\\*\\+,;=@]|'.
                                '%[[:xdigit:]]'.
                            ')+';
        $pathAbempty    = '(?:/'.$segment.')*';
        $pathAbsolute   = '/(?:'.$segmentNz.'(?:/'.$segment.')*)?';
        $pathNoscheme   = $segmentNzNc.'(?:/'.$segment.')*';
        $pathRootless   = $segmentNz.'(?:/'.$segment.')*';
        $pathEmpty      = '(?!'.$pchar.')';

        $pattern =  $pathAbempty.'|'.$pathAbsolute;
        if ($relative)
            $pattern .= '|'.$pathNoscheme;
        else
            $pattern .= '|'.$pathRootless;
        $pattern .= '|'.$pathEmpty;

        return (bool) preg_match('#^'.$pattern.'$#Di', $path);
    }

    protected function _setPath($path, $relative)
    {
        if (!$this->_validatePath($path, $relative))
            throw new Erebot_InvalidValueException(
                'Invalid path; use relative() for relative paths'
            );
        $this->_path = $path;
    }

    public function setPath($path)
    {
        $this->_setPath($path, FALSE);
    }

    public function getQuery($raw = FALSE)
    {
        return $this->_query;
    }

    public function setQuery($query)
    {
        /*
        pchar         = unreserved / pct-encoded / sub-delims / ":" / "@"
        query         = *( pchar / "/" / "?" )
        pct-encoded   = "%" HEXDIG HEXDIG
        unreserved    = ALPHA / DIGIT / "-" / "." / "_" / "~"
        sub-delims    = "!" / "$" / "&" / "'" / "(" / ")"
                      / "*" / "+" / "," / ";" / "="
        */
        $pattern    =   '(?:'.
                            '[-[:alnum:]\\._~!\\$&\'\\(\\)\\*\\+,;=/\\?]|'.
                            '%[[:xdigit:]]{2}'.
                        ')*';
        if ($query !== NULL && !preg_match('#^'.$pattern.'$#Di', $query))
            throw new Erebot_InvalidValueException('Invalid query');
        $this->_query = $query;
    }

    public function getFragment($raw = FALSE)
    {
        return $this->_fragment;
    }

    public function setFragment($fragment)
    {
        /*
        pchar         = unreserved / pct-encoded / sub-delims / ":" / "@"
        fragment      = *( pchar / "/" / "?" )
        pct-encoded   = "%" HEXDIG HEXDIG
        unreserved    = ALPHA / DIGIT / "-" / "." / "_" / "~"
        sub-delims    = "!" / "$" / "&" / "'" / "(" / ")"
                      / "*" / "+" / "," / ";" / "="
        */
        $pattern    =   '(?:'.
                            '[-[:alnum:]\\._~!\\$&\'\\(\\)\\*\\+,;=/\\?]|'.
                            '%[[:xdigit:]]{2}'.
                        ')*';
        if ($fragment !== NULL && !preg_match('#^'.$pattern.'$#Di', $fragment))
            throw new Erebot_InvalidValueException('Invalid fragment');
        $this->_fragment = $fragment;
    }

    public function asParsedURL($component = -1)
    {
        if ($component == -1) {
            $result = array();
            $fields = array(
                'scheme',
                'host',
                'port',
                'path',
                'query',
                'fragment',
            );

            foreach ($fields as $field) {
                $local = '_'.$field;
                if ($this->$local !== NULL)
                    $result[$parseURL] = $this->$local;
            }

            if ($this->_userinfo !== NULL) {
                /// @TODO: parse userinfo to fill in the user/pass fields.
                /*
                    Quirks from parse_url():
                    "a" -> user = "a"
                    "a:" -> user = "a" (even though pass should also be "")
                    "a:b:c" -> user = "a", pass = "b:c" (invalid)
                    ":b" -> pass = "b" (invalid)
                */
            }

            return $result;
        }

        switch ($component) {
            case PHP_URL_SCHEME:
                return $this->_scheme;
            case PHP_URL_HOST:
                return $this->_host;
            case PHP_URL_PORT:
                return $this->_port;
            case PHP_URL_PATH:
                return $this->_path;
            case PHP_URL_QUERY:
                return $this->_query;
            case PHP_URL_FRAGMENT:
                return $this->_fragment;
            case PHP_URL_USER:
                return NULL; /// @TODO
            case PHP_URL_PASS:
                return NULL; /// @TODO
            default:
                return NULL;
        }
    }

    public function relative($reference)
    {
        try {
            $cls    = __CLASS__;
            $result = new $cls($reference);
            return $result;
        }
        catch (Erebot_InvalidValueException $e) {
            // Nothing to do. We will try to interpret
            // the reference as a relative URI instead.
        }

        // Use the current (absolute) URI as the base.
        $result = clone $this;

        // 5.2.2.  Transform References
        // Our parser is strict.
        $parsed = $this->_parseURI($reference, TRUE);

        // No need to test the case where the scheme is defined.
        // This would be an absolute URI and has already been
        // captured by the previous try..catch block.

        // "host" == "authority" here, see the grammar
        // for reasons why this always holds true.
        if (isset($parsed['host'])) {
            $result->setHost(isset($parsed['host']) ? $parsed['host'] : NULL);
            $result->setPort(isset($parsed['port']) ? $parsed['port'] : NULL);
            $result->setUserInfo(isset($parsed['userinfo']) ? $parsed['userinfo'] : NULL);
            $result->_setPath($parsed['path'], TRUE);
            $result->setQuery(isset($parsed['query']) ? $parsed['query'] : NULL);
            $result->setFragment(isset($parsed['fragment']) ? $parsed['fragment'] : NULL);
            return $result;
        }

        // No need to copy path/authority because
        // $result is already a copy of the base.

        if ($parsed['path'] == '') {
            if (isset($parsed['query']))
                $result->setQuery($parsed['query']);
            $result->setFragment(isset($parsed['fragment']) ? $parsed['fragment'] : NULL);
            return $result;
        }

        if (substr($parsed['path'], 0, 1) == '/')
            $result->_setPath($result->_removeDotSegments($parsed['path']), TRUE);
        else
            $result->_setPath($result->_removeDotSegments($result->_merge($parsed['path'])), TRUE);
        $result->setQuery(isset($parsed['query']) ? $parsed['query'] : NULL);
        $result->setFragment(isset($parsed['fragment']) ? $parsed['fragment'] : NULL);
        return $result;
    }
}

