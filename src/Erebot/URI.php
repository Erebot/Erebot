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
 * It is mostly compatible with parse_url(), but tends to be
 * stricter when validating data.
 *
 * This implementation doesn't assume that the "userinfo" part
 * is made up of a "user:password" pair (contrary to parse_url()
 * which is based on RFC 1738), and provides a single field
 * named "userinfo" instead. Such a pair will be merged upon
 * encounter.
 *
 * All components are normalized when retrieved using any
 * of the getters except asParsedURL(), using the algorithms
 * defined in RFC 3986.
 */
class   Erebot_URI
{
    static protected $_base = NULL;
    protected $_scheme;
    protected $_userinfo;
    protected $_authority;
    protected $_port;
    protected $_path;
    protected $_query;
    protected $_fragment;

    public function __construct($uri)
    {
        if (self::$_base === NULL) {
            self::$_base = array(
                'digit'         =>  '0123456789',
                'alpha'         =>  'abcdefghijklmnopqrstuvwxyz'.
                                    'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                'gen-delims'    =>  ':/?#[]@',
                'sub-delims'    =>  '!$&\'()*+,;=',
            );
            self::$_base['hexdig']      =   self::$_base['digit'].
                                            'abcdefABCDEF';
            self::$_base['reserved']    =   self::$_base['gen-delims'].
                                            self::$_base['sub-delims'];
            self::$_base['unreserved']  =   self::$_base['alpha'].
                                            self::$_base['digit'].
                                            '-._~';
        }

        if (is_string($uri))
            $uri = $this->_parseURI($uri);
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

    protected function _parseURI($uri)
    {
        $result = array();

        // Parse scheme.
        $pos = strpos($uri, ':');
        if (!$pos)  // An URI starting with ":" is also invalid.
            throw new Erebot_InvalidValueException('No scheme found');

        $result['scheme'] = substr($uri, 0, $pos);
        $len = strspn(
            $result['scheme'],
            self::$_base['digit'].self::$_base['alpha'].'+-.'
        );
        if ($len != strlen($result['scheme']))
            throw new Erebot_InvalidValueException('Invalid scheme');
        $uri = (string) substr($uri, $pos + 1);

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
        if ($uri == '')
            return $result;

        // Handle "hier-part".
        if (substr($uri, 0, 2) == '//') {
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
                $port = (string) substr($uri, $pos + 1);
                if ($path != '') {
                    if (strspn($port, self::$_base['digit']) != strlen($port))
                        throw new Erebot_InvalidValueException('Invalid port');
                    $result['port'] = (int) $port;
                }
                $uri = (string) substr($uri, 0, $pos);
            }

            $result['host'] = $uri;
            return $result;
        }

        // Handle "path-absolute" & "path-rootless".
        $result['path'] = $uri;
        return $result;
    }

    public function __toString()
    {
        $result = "";

        if ($this->_scheme !== NULL)
            $result .= $this->_scheme.':';

        if ($this->_host !== NULL) {
            $result .= '//';
            if ($this->_userinfo !== NULL)
                $result .= $this->_userinfo."@";

            $result .= $this->_host;
            $port = $this->getPort();
            if ($port !== NULL)
                $result .= ':'.$port;
        }

        $result .= $this->getPath();

        if ($this->_query !== NULL)
            $result .= '?'.$this->_query;

        if ($this->_fragment !== NULL)
            $result .= '#'.$this->_fragment;

        return $result;
    }

    public function getScheme()
    {
        return ($this->_scheme !== NULL) ? strtolower($this->_scheme) : NULL;
    }

    public function setScheme($scheme)
    {
        $this->_scheme = $scheme;
    }

    public function getUserInfo()
    {
        return $this->_userinfo;
    }

    public function setUserInfo($userinfo)
    {
        $this->_userinfo = $userinfo;
    }

    public function getHost()
    {
        return ($this->_host !== NULL) ? strtolower($this->_host) : NULL;
    }

    public function setHost($host)
    {
        $this->_host = $host;
    }

    public function getPort()
    {
        if ($this->_port === NULL)
            return NULL;

        // Try to canonicalize the port.
        $tcp = getservbyname($this->_scheme, 'tcp');
        $udp = getservbyname($this->_scheme, 'udp');
        if (($tcp != $this->_port && $udp === FALSE) ||
            ($udp != $this->_port && $tcp === FALSE))
            return $this->_port;
        return NULL;
    }

    public function setPort($port)
    {
        if (!is_int($port))
            throw new Erebot_InvalidValueException('Port should be an integer');
        if ($port <= 0 || $port > 65535)
            throw new Erebot_InvalidValueException(
                'Expected: 0 < port <= 65535'
            );
        $this->_port = $port;
    }

    public function getPath()
    {
        // §5.2.4.  Remove Dot Segments
        $input  = explode("/", $this->_path);
        $output = array();

        while (count($input)) {
            $segment = array_shift($input);
            if ($segment == '' || $segment == '.')
                continue;
            if ($segment == '..')
                if (count($output))
                    array_pop($output);
                continue;
            }
            array_push($output, $segment);
        }

        $path = implode('/', $output);
        if ($path != '' || $this->_host !== NULL)
            $path = '/'.$path;
        return $this->_path;
    }

    public function setPath($path)
    {
        $this->_path = $path;
    }

    public function getQuery()
    {
        return $this->_query;
    }

    public function setQuery($query)
    {
        $this->_query = $query;
    }

    public function getFragment()
    {
        return $this->_fragment;
    }

    public function setFragment($fragment)
    {
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
    }
}

