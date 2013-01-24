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

/**
 * \brief
 *      Simple parser/generator for Uniform Resource Identifiers
 *      as defined in RFC 3986.
 *
 * This class can be used as both a parser and a generator for
 * Uniform Resource Identifiers (URI) as defined in RFC 3986.
 * It is primarly meant to deal with with absolute URIs but also
 * offers methods to deal with relative URIs.
 * It is mostly compatible with parse_url(), but tends to be
 * stricter when validating data.
 *
 * This implementation doesn't assume that the "userinfo" part
 * is made up of a "username:password" pair (in contrast to
 * parse_url() which is based on RFC 1738), and provides a single
 * field named "userinfo" instead. Such pairs will be merged upon
 * encounter.
 *
 * All components are normalized by default when retrieved using
 * any of the getters except asParsedURL(). You may override this
 * behaviour by passing $raw=TRUE to said getters.
 * Normalization is done using the rules defined in RFC 3986.
 */
class       Erebot_URI
implements  Erebot_Interface_URI
{
    /// Scheme component (sometimes also erroneously called a "protocol").
    protected $_scheme;
    /// User information component (such as a "username:password" pair).
    protected $_userinfo;
    /// Host component ("authority", even though "authority" is more general).
    protected $_host;
    /// Port component.
    protected $_port;
    /// Path component.
    protected $_path;
    /// Query component.
    protected $_query;
    /// Fragment component.
    protected $_fragment;

    /**
     * Constructs an URI.
     *
     * \param mixed $uri
     *      Either a string representing the URI or an array
     *      as returned by PHP's parse_url() function.
     *
     * \throw Erebot_InvalidValueException
     *      The given URI is invalid.
     */
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

    /**
     * Parses an URI using the grammar defined in RFC 3986.
     *
     * \param string $uri
     *      URI to parse.
     *
     * \param bool $relative
     *      Whether $uri must be considered as an absolute URI (FALSE)
     *      or a relative reference (TRUE).
     *
     * \retval array
     *      An associative array containing the different components
     *      that could be parsed out of this URI.
     *      It uses the same format as parse_url(), except that the
     *      "user" and "pass" components are merged into a single
     *      "userinfo" component and only string keys are defined.
     *
     * \throw Erebot_InvalidValueException
     *      The given $uri is not valid.
     */
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
            $rpos   = strcspn(strrev($uri), ':]');
            $len    = strlen($uri);
            if ($rpos != 0 && $rpos < $len && $uri[$len - $rpos - 1] != "]") {
                $result['port'] = (string) substr($uri, -1 * $rpos);
                $uri = (string) substr($uri, 0, -1 * $rpos - 1);
            }

            $result['host'] = $uri;
            return $result;
        }

        // Handle "path-absolute" & "path-rootless".
        $result['path'] = $uri;
        return $result;
    }

    /**
     * Performs normalization of percent-encoded characters.
     *
     * \param string $data
     *      Some text containing percent-encoded characters
     *      that need to be normalized.
     *
     * \retval string
     *      The same text, after percent-encoding normalization.
     */
    static protected function _normalizePercent($data)
    {
        // 6.2.2.1.  Case Normalization
        // Percent-encoded characters must use uppercase letters.
        // 6.2.2.2.  Percent-Encoding Normalization
        $unreserved =   'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
                        'abcdefghijklmnopqrstuvwxyz'.
                        '-._~';
        return preg_replace(
            '/%([[:xdigit:]]{2})/e',
            "(strpos('".$unreserved."', chr(hexdec('\\1'))) !== FALSE ".
            "? chr(hexdec('\\1')) ".
            ": strtoupper('%\\1'))",
            $data
        );
    }

    /**
     * Returns the current URI as a string.
     *
     * \param bool $raw
     *      (optional) Whether the raw contents of the components
     *      should be used (TRUE) or a normalized alternative (FALSE).
     *      The default is to apply normalization.
     *
     * \param bool $credentials
     *      (optional) Whether the content of the "user information"
     *      component should be part of the returned string (TRUE)
     *      or not (FALSE). The default is for such credentials to
     *      appear in the result.
     *
     * \retval string
     *      The current URI as a string, eventually normalized.
     */
    public function toURI($raw = FALSE, $credentials = TRUE)
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
            if ($this->_userinfo !== NULL && $credentials)
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

    /**
     * Returns the current URI as a string,
     * in its normalized form.
     *
     * \note
     *      This method is a shortcut for Erebot_URI::toURI(FALSE).
     */
    public function __toString()
    {
        return $this->toURI();
    }

    /**
     * Returns the current URI's scheme.
     *
     * \param bool $raw
     *      (optional) Whether the value should be normalized
     *      before it's returned (FALSE) or not (TRUE).
     *      The default is to apply normalization.
     *
     * \retval string
     *      The current URI's scheme as a string,
     *      eventually normalized.
     */
    public function getScheme($raw = FALSE)
    {
        // 6.2.2.1.  Case Normalization
        // Characters must be normalized to use lowercase letters.
        if ($raw)
            return $this->_scheme;
        return strtolower($this->_scheme);
    }

    /**
     * Sets the current URI's scheme.
     *
     * \param string $scheme
     *      New scheme for this URI, as a string.
     *
     * \throw Erebot_InvalidValueException
     *      The given $scheme is not valid.
     */
    public function setScheme($scheme)
    {
        // scheme        = ALPHA *( ALPHA / DIGIT / "+" / "-" / "." )
        if (!preg_match('/^[-[:alpha:][:alnum:]\\+\\.]*$/Di', $scheme))
            throw new Erebot_InvalidValueException('Invalid scheme');
        $this->_scheme = $scheme;
    }

    /**
     * Returns the current URI's user information.
     *
     * \param bool $raw
     *      (optional) Whether the value should be normalized
     *      before it's returned (FALSE) or not (TRUE).
     *      The default is to apply normalization.
     *
     * \retval mixed
     *      The current URI's user information,
     *      eventually normalized or NULL.
     */
    public function getUserInfo($raw = FALSE)
    {
        if ($raw)
            return $this->_userinfo;
        return  ($this->_userinfo === NULL)
                ? NULL
                : $this->_normalizePercent($this->_userinfo);
    }

    /**
     * Sets the current URI's user information.
     *
     * \param mixed $userinfo
     *      New user information for this URI
     *      (either a string or NULL).
     *
     * \throw Erebot_InvalidValueException
     *      The given user information is not valid.
     */
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

    /**
     * Returns the current URI's host.
     *
     * \param bool $raw
     *      (optional) Whether the value should be normalized
     *      before it's returned (FALSE) or not (TRUE).
     *      The default is to apply normalization.
     *
     * \retval mixed
     *      The current URI's host as a string,
     *      eventually normalized or NULL.
     */
    public function getHost($raw = FALSE)
    {
        // 6.2.2.1.  Case Normalization
        // Characters must be normalized to use lowercase letters.
        if ($raw)
            return $this->_host;
        return  ($this->_host !== NULL)
                ? strtolower($this->_normalizePercent($this->_host))
                : NULL;
    }

    /**
     * Sets the current URI's host.
     *
     * \param string $host
     *      New host for this URI (either a string or NULL).
     *
     * \throw Erebot_InvalidValueException
     *      The given $host is not valid.
     */
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
        $dotAddress     = $decOctet.'(?:\\.'.$decOctet.'){3}';
        $half           = '[[:xdigit:]]{1,4}';
        $long           = '(?:'.$half.':'.$half.'|'.$dotAddress.')';
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
        $ipFuture       =   'v[[:xdigit:]]+\\.'.
                            '[-[:alnum:]\\._~!\\$&\'\\(\\)*\\+,;=]+';
        $ipLiteral      =   '\\[(?:'.$colonAddress.'|'.$ipFuture.')\\]';
        $regName        =   '(?:[-[:alnum:]\\._~!\\$&\'\\(\\)*\\+,;=]|'.
                            '%[[:xdigit:]]{2})*';
        $pattern        =   '(?:'.$ipLiteral.'|'.$dotAddress.'|'.$regName.')';
        if ($host !== NULL && !preg_match('/^'.$pattern.'$/Di', $host))
            throw new Erebot_InvalidValueException('Invalid host');
        $this->_host = $host;
    }

    /**
     * Returns the current URI's port.
     *
     * \param bool $raw
     *      (optional) Whether the value should be normalized
     *      before it's returned (FALSE) or not (TRUE).
     *      The default is to apply normalization.
     *
     * \retval mixed
     *      When normalization is in effect, the port for
     *      the current URI will be returned as an integer,
     *      or NULL.
     *      When normalization has been disabled, the port
     *      will be returned as a string or NULL.
     */
    public function getPort($raw = FALSE)
    {
        // 6.2.3.  Scheme-Based Normalization
        if ($raw)
            return $this->_port;

        if ($this->_port == '')
            return NULL;

        $port = (int) $this->_port;

        // Try to canonicalize the port.
        $tcp = getservbyname($this->_scheme, 'tcp');
        $udp = getservbyname($this->_scheme, 'udp');

        if ($tcp == $port && ($udp === FALSE || $udp == $tcp))
            return NULL;

        if ($udp == $port && ($tcp === FALSE || $udp == $tcp))
            return NULL;

        return $port;
    }

    /**
     * Sets the current URI's port.
     *
     * \param mixed $port
     *      New port for this URI (either a numeric string,
     *      an integer or NULL).
     *
     * \throw Erebot_InvalidValueException
     *      The given $port is not valid.
     */
    public function setPort($port)
    {
        // port          = *DIGIT
        if (is_int($port))
            $port = (string) $port;
        if ($port !== NULL && strspn($port, '0123456789') != strlen($port))
            throw new Erebot_InvalidValueException('Invalid port');
        $this->_port = $port;
    }

    /**
     * Removes "dot segments" ("." and "..") from a path.
     *
     * \param string $path
     *      Path on which to operate.
     *
     * \retval string
     *      The same $path, with all its dot segments
     *      substituted.
     */
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

    /**
     * Merges the given path with the current URI's path.
     *
     * \param string $path
     *      Path to merge into the current path.
     *
     * \retval string
     *      Result of that merge.
     *
     * \note
     *      Despite its name, this method does not modify
     *      the given $path nor the current object.
     */
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

    /**
     * Returns the current URI's path.
     *
     * \param bool $raw
     *      (optional) Whether the value should be normalized
     *      before it's returned (FALSE) or not (TRUE).
     *      The default is to apply normalization.
     *
     * \retval string
     *      The current URI's path as a string,
     *      eventually normalized.
     */
    public function getPath($raw = FALSE)
    {
        // 6.2.2.3.  Path Segment Normalization
        if ($raw)
            return $this->_path;

        return $this->_removeDotSegments(
            $this->_normalizePercent($this->_path)
        );
    }

    /**
     * Validates the given path.
     *
     * \param string $path
     *      Path to validate.
     *
     * \param bool $relative
     *      Whether the given $path is relative (TRUE)
     *      or not (FAlSE).
     *
     * \retval bool
     *      TRUE if the given $path is valid,
     *      FALSE otherwise.
     */
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

    /**
     * Sets the current URI's path.
     *
     * \param string $path
     *      New path for this URI.
     *
     * \param bool $relative
     *      Whether the given $path is relative (TRUE)
     *      or not (FALSE).
     *
     * \throw Erebot_InvalidValueException
     *      The given $path is not valid.
     */
    protected function _setPath($path, $relative)
    {
        if (!is_string($path) || !$this->_validatePath($path, $relative))
            throw new Erebot_InvalidValueException(
                'Invalid path; use relative() for relative paths'
            );
        $this->_path = $path;
    }

    /**
     * Sets the current URI's path.
     *
     * \param string $path
     *      New path for this URI.
     *
     * \throw Erebot_InvalidValueException
     *      The given $path is not valid.
     *
     * \note
     *      This is a very thin wrapper around the internal
     *      method Erebot_URI::_setPath().
     */
    public function setPath($path)
    {
        $this->_setPath($path, FALSE);
    }

    /**
     * Returns the current URI's query.
     *
     * \param bool $raw
     *      (optional) Whether the value should be normalized
     *      before it's returned (FALSE) or not (TRUE).
     *      The default is to apply normalization.
     *
     * \retval mixed
     *      The current URI's query as a string,
     *      eventually normalized or NULL.
     */
    public function getQuery($raw = FALSE)
    {
        if ($raw)
            return $this->_query;
        return $this->_normalizePercent($this->_query);
    }

    /**
     * Sets the current URI's query.
     *
     * \param mixed $query
     *      New query for this URI (either a string or NULL).
     *
     * \throw Erebot_InvalidValueException
     *      The given $query is not valid.
     */
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

    /**
     * Returns the current URI's fragment.
     *
     * \param bool $raw
     *      (optional) Whether the value should be normalized
     *      before it's returned (FALSE) or not (TRUE).
     *      The default is to apply normalization.
     *
     * \retval mixed
     *      The current URI's fragment as a string,
     *      eventually normalized or NULL.
     */
    public function getFragment($raw = FALSE)
    {
        if ($raw)
            return $this->_fragment;
        return $this->_normalizePercent($this->_fragment);
    }

    /**
     * Sets the current URI's fragment.
     *
     * \param mixed $fragment
     *      New fragment for this URI (either a string or NULL).
     *
     * \throw Erebot_InvalidValueException
     *      The given $fragment is not valid.
     */
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

    /**
     * Returns information about the current URI,
     * in the same format as parse_url().
     *
     * \param $component
     *      (optional) A specific component to return.
     *      Read the documentation about parse_url()
     *      for more information.
     *
     * \retval mixed
     *      Either an array, a string, an integer or NULL,
     *      depending on $component and the actual contents
     *      of this URI.
     *      Read the documentation about parse_url()
     *      for more information.
     */
    public function asParsedURL($component = -1)
    {
        if ($component == -1) {
            $result = array();
            $fields = array(
                'scheme'    => PHP_URL_SCHEME,
                'host'      => PHP_URL_HOST,
                'port'      => PHP_URL_PORT,
                'path'      => PHP_URL_PATH,
                'query'     => PHP_URL_QUERY,
                'fragment'  => PHP_URL_FRAGMENT,
            );

            foreach ($fields as $field => $alias) {
                $local = '_'.$field;
                if ($this->$local !== NULL) {
                    $result[$field] = $this->$local;
                    $result[$alias] = $result[$field];
                }
            }

            // Cleanup "port" component.
            if (isset($result['port'])) {
                if (!ctype_digit($result['port'])) {
                    unset($result['port']);
                    unset($result[PHP_URL_PORT]);
                }
                else {
                    $result['port']         =
                    $result[PHP_URL_PORT]   = (int) $result['port'];
                }
            }

            if ($this->_userinfo !== NULL) {
                $limit = strcspn($this->_userinfo, ':');
                if ($limit > 0) {
                    $user = substr($this->_userinfo, 0, $limit);
                    $result['user']         = $user;
                    $result[PHP_URL_USER]   = $user;
                }
                $pass = substr($this->_userinfo, $limit + 1);
                if ($pass !== FALSE) {
                    $result['pass']         = $pass;
                    $result[PHP_URL_PASS]   = $pass;
                }
            }

            return $result;
        }

        switch ($component) {
            case PHP_URL_SCHEME:{
                return $this->_scheme;
            }
            case PHP_URL_HOST:{
                return $this->_host;
            }
            case PHP_URL_PORT:{
                return  ($this->_port === NULL || !ctype_digit($this->_port))
                        ? NULL
                        : (int) $this->_port;
            }
            case PHP_URL_PATH:{
                return $this->_path;
            }
            case PHP_URL_QUERY:{
                return $this->_query;
            }
            case PHP_URL_FRAGMENT:{
                return $this->_fragment;
            }
            case PHP_URL_USER:{
                $user = substr(
                    $this->_userinfo, 0,
                    strcspn($this->_userinfo, ':')
                );
                return ($user == "" ? NULL : $user);
            }
            case PHP_URL_PASS:{
                $pass = substr(
                    $this->_userinfo,
                    strcspn($this->_userinfo, ':') + 1
                );
                return ($pass === FALSE ? NULL : $pass);
            }
            default:{
                return NULL;
            }
        }
    }

    /**
     * Given a relative reference, returns a new absolute URI
     * matching that reference.
     *
     * \param string $reference
     *      Some relative reference (can be an absolute
     *      or relative URI). The current absolute URI
     *      is used as the base to dereference it.
     *
     * \retval Erebot_URI
     *      A new absolute URI matching the given $reference.
     *
     * \throw Erebot_InvalidValueException
     *      The given $reference is not valid.
     */
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

        // Always copy the new fragment.
        $result->setFragment(
            isset($parsed['fragment'])
            ? $parsed['fragment']
            : NULL
        );

        // "host" == "authority" here, see the grammar
        // for reasons why this always holds true.
        if (isset($parsed['host'])) {
            $result->setHost(isset($parsed['host']) ? $parsed['host'] : NULL);
            $result->setPort(isset($parsed['port']) ? $parsed['port'] : NULL);
            $result->setUserInfo(
                isset($parsed['userinfo'])
                ? $parsed['userinfo']
                : NULL
            );
            $result->_setPath($parsed['path'], TRUE);
            $result->setQuery(
                isset($parsed['query'])
                ? $parsed['query']
                : NULL
            );
            return $result;
        }

        // No need to copy path/authority because
        // $result is already a copy of the base.

        if ($parsed['path'] == '') {
            if (isset($parsed['query']))
                $result->setQuery($parsed['query']);
            return $result;
        }

        if (substr($parsed['path'], 0, 1) == '/')
            $result->_setPath(
                $result->_removeDotSegments($parsed['path']),
                TRUE
            );
        else
            $result->_setPath(
                $result->_removeDotSegments($result->_merge($parsed['path'])),
                TRUE
            );
        $result->setQuery(isset($parsed['query']) ? $parsed['query'] : NULL);
        return $result;
    }

    static public function fromAbsPath($abspath, $strict = TRUE)
    {
        if (!strncasecmp(PHP_OS, "Win", 3)) {
            $isUnc = (substr($abspath, 0, 2) == '\\\\');
            if ($isUnc)
                $abspath = ltrim($abspath, '\\');
            $parts = explode('\\', $abspath);

            // This is actually UNCW -- "Long UNC".
            if ($isUnc && $parts[0] == '?') {
                array_shift($parts);
                if (strpos($parts[0], ':') !== FALSE) {
                    $host = 'localhost';
                    $path = implode('\\', $parts);
                }
                else if ($parts[0] != 'UNC')
                    throw new Erebot_InvalidValueException('Invalid UNC path');
                else {
                    array_shift($parts[0]);         // shift the "UNC" token.
                    $host = array_shift($parts[0]); // shift ServerName.
                    $path = implode('\\', $parts);
                }
            }

            // Regular UNC path.
            else if ($isUnc) {
                $host = array_shift($parts[0]); // shift ServerName.
                $path = implode('\\', $parts);
            }

            // Regular local path.
            else {
                $host = 'localhost';
                $path = implode('\\', $parts);
            }

            if (!$strict)
                $path = str_replace('/', '\\', $path);
            $path = str_replace('/', '%2F', $path);
            $path = str_replace('\\', '/', $path);
            $path = ltrim($path, '/');
        }

        else {
            $host = 'localhost';

            if (DIRECTORY_SEPARATOR != '/') {
                if (!$strict)
                    $abspath = str_replace('/', DIRECTORY_SEPARATOR, $abspath);
                $path = str_replace('/', '%2F', $path);
                $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
            }
            $path = ltrim($abspath, '/');
        }

        $host = strtolower(self::_normalizePercent($host));
        $cls = __CLASS__;
        $url = 'file://' . ($host == 'localhost' ? '' : $host) . '/' . $path;
        return new $cls($url);
    }
}

