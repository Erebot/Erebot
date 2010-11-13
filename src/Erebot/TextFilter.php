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
 *      A class to filter events out based on their content (message).
 */
class       Erebot_TextFilter
implements  Erebot_Interface_TextFilter
{
    protected $_prefix;
    protected $_patterns;

    // Documented in the interface.
    public function __construct(
        Erebot_Interface_Config_Main   &$config,
                                        $type           = NULL,
                                        $pattern        = NULL,
                                        $requirePrefix  = FALSE
    )
    {
        $this->_prefix = $config->getCommandsPrefix();

        if (($type === NULL && $pattern !== NULL) ||
            ($type !== NULL && $pattern === NULL))
            throw new Erebot_IllegalActionException(
                'Either both or none of type & pattern must be given'
            );

        $this->_patterns = array(array(), array(), array());
        if ($type !== NULL)
            $this->addPattern($type, $pattern, $requirePrefix);
    }

    // Documented in the interface.
    public function addPattern($type, $pattern, $requirePrefix = FALSE)
    {
        $pattern = $this->_rewritePattern($type, $pattern, $requirePrefix);
        $this->_patterns[$type][] = $pattern;
    }

    // Documented in the interface.
    public function removePattern($type, $pattern, $requirePrefix = FALSE)
    {
        $pattern    = $this->_rewritePattern($type, $pattern, $requirePrefix);
        $keys       = array_keys($this->_patterns[$type], $pattern);
        if (!count($keys))
            throw new Erebot_NotFoundException('No such pattern');

        foreach ($keys as $key)
            unset($this->_patterns[$type][$key]);
    }

    protected function _rewritePattern($type, $pattern, $requirePrefix)
    {
        // Sanity checks.
        if (!is_int($type) || !isset($this->_patterns[$type]))
            throw new Erebot_InvalidValueException('Invalid pattern type');

        if (!is_string($pattern))
            throw new Erebot_InvalidValueException('Pattern must be a string');

        if ($requirePrefix !== NULL && !is_bool($requirePrefix))
            throw new Erebot_InvalidValueException(
                'requirePrefix must be a boolean or NULL'
            );

        // Actual rewrites.
        if ($type != self::TYPE_REGEXP)
            $pattern = preg_replace('/\s+/', ' ', $pattern);

        if ($type == self::TYPE_WILDCARD) {
            if (strpos($pattern, '?') === FALSE &&
                strpos($pattern, '&') === FALSE &&
                strpos($pattern, '*') === FALSE)
                $type = self::TYPE_STATIC;
            else {
                $translationTable = array(
                    '\\*'   => '.*',
                    '\\?'   => '.',
                    '\\\\&' => '&',
                    '&'     => '[^\\040]+',
                );

                if ($requirePrefix === FALSE)
                    $prefix = '';
                else
                    $prefix = '(?:'.preg_quote($this->_prefix).')'.
                                ($requirePrefix === NULL ? '?' : '');
                $pattern    =   "#^".$prefix.strtr(
                    preg_quote($pattern, '#'),
                    $translationTable
                )."$#i";
            }
        }

        if ($type == self::TYPE_STATIC) {
            if ($requirePrefix === NULL)
                $pattern = 'N'.$pattern;
            else if ($requirePrefix)
                $pattern = 'T'.$pattern;
            else
                $pattern = 'F'.$pattern;
        }

        return $pattern;
    }

    // Documented in the interface.
    public function getPatterns($type = NULL)
    {
        if ($type === NULL)
            return $this->_patterns;

        if (!is_int($type) || !isset($this->_patterns[$type]))
            throw new Erebot_InvalidValueException('Invalid pattern type');

        return $this->_patterns[$type];
    }

    // Documented in the interface.
    public function match(Erebot_Interface_Event_Generic &$event)
    {
        if (!in_array('iErebotEventText', class_implements($event)))
            return TRUE;

        $text   = $event->getText();

        foreach ($this->_patterns[self::TYPE_REGEXP] as &$regexp) {
            if (preg_match($regexp, $text) == 1)
                return TRUE;
        }

        $prefixLen = strlen($this->_prefix);
        $normal = preg_replace('/\s+/', ' ', $text);
        foreach ($this->_patterns[self::TYPE_STATIC] as $static) {
            $requirePrefix  = substr($static, 0, 1);
            $realStatic     = substr($static, 1);

            // Prefix forbidden.
            if ($requirePrefix == 'F') {
                if ($realStatic == $normal)
                    return TRUE;
                continue;
            }


            // Prefix required.
            if ($requirePrefix == 'T') {
                if (!strncmp($this->_prefix, $normal, $prefixLen) &&
                    $realStatic == substr($normal, $prefixLen))
                    return TRUE;
                continue;
            }

            // Prefix not required, try without a prefix first.
            if ($realStatic == $normal)
                return TRUE;

                if (!strncmp($this->_prefix, $normal, $prefixLen) &&
                    $realStatic == substr($normal, $prefixLen))
                return TRUE;
        }

        foreach ($this->_patterns[self::TYPE_WILDCARD] as &$wild) {
            if (preg_match($wild, $normal) == 1)
                return TRUE;
        }

        return FALSE;
    }
}

