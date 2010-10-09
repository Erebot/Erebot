<?php

ErebotUtils::incl('ifaces/textFilter.php');

/**
 * \brief
 *      A class to filter events out based on their content (message).
 */
class       ErebotTextFilter
implements  iErebotTextFilter
{
    protected $prefix;
    protected $patterns;

    // Documented in the interface.
    public function __construct(iErebotMainConfig &$config,
        $type = NULL, $pattern = NULL, $require_prefix = FALSE)
    {
        $this->prefix = $config->getCommandsPrefix();

        if (($type === NULL && $pattern !== NULL) ||
            ($type !== NULL && $pattern === NULL))
            throw new EErebotIllegalAction('Either both or none of type & pattern must be given');

        $this->patterns = array(array(), array(), array());
        if ($type !== NULL)
            $this->addPattern($type, $pattern, $require_prefix);
    }

    // Documented in the interface.
    public function addPattern($type, $pattern, $require_prefix = FALSE)
    {
        $pattern = $this->rewritePattern($type, $pattern, $require_prefix);
        $this->patterns[$type][] = $pattern;
    }

    // Documented in the interface.
    public function removePattern($type, $pattern, $require_prefix = FALSE)
    {
        $pattern    = $this->rewritePattern($type, $pattern, $require_prefix);
        $keys       = array_keys($this->patterns[$type], $pattern);
        if (!count($keys))
            throw new EErebotNotFound('No such pattern');

        foreach ($keys as $key)
            unset($this->patterns[$type][$key]);
    }

    protected function rewritePattern($type, $pattern, $require_prefix)
    {
        // Sanity checks.
        if (!is_int($type) || !isset($this->patterns[$type]))
            throw new EErebotInvalidValue('Invalid pattern type');

        if (!is_string($pattern))
            throw new EErebotInvalidValue('Pattern must be a string');

        if ($require_prefix !== NULL && !is_bool($require_prefix))
            throw new EErebotInvalidValue('Require_prefix must be a boolean or NULL');

        // Actual rewrites.
        if ($type != self::TYPE_REGEXP)
            $pattern = preg_replace('/\s+/', ' ', $pattern);

        if ($type == self::TYPE_WILDCARD) {
            if (strpos($pattern, '?') === FALSE &&
                strpos($pattern, '&') === FALSE &&
                strpos($pattern, '*') === FALSE)
                $type = self::TYPE_STATIC;
            else {
                $translation_table = array(
                    '\\*'   => '.*',
                    '\\?'   => '.',
                    '\\\\&' => '&',
                    '&'     => '[^\\040]+',
                );

                if ($require_prefix === FALSE)
                    $prefix = '';
                else
                    $prefix = '(?:'.preg_quote($this->prefix).')'.
                                ($require_prefix === NULL ? '?' : '');
                $pattern    =   "#^".$prefix.strtr(
                                    preg_quote($pattern, '#'),
                                    $translation_table)."$#i";
            }
        }

        if ($type == self::TYPE_STATIC) {
            if ($require_prefix === NULL)
                $pattern = 'N'.$pattern;
            else if ($require_prefix)
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
            return $this->patterns;

        if (!is_int($type) || !isset($this->patterns[$type]))
            throw new EErebotInvalidValue('Invalid pattern type');

        return $this->patterns[$type];
    }

    // Documented in the interface.
    public function match(iErebotEvent &$event)
    {
        if (!in_array('iErebotEventText', class_implements($event)))
            return TRUE;

        $text   = $event->getText();

        foreach ($this->patterns[self::TYPE_REGEXP] as &$regexp) {
            if (preg_match($regexp, $text) == 1)
                return TRUE;
        }

        $prefix_len = strlen($this->prefix);
        $normal = preg_replace('/\s+/', ' ', $text);
        foreach ($this->patterns[self::TYPE_STATIC] as $static) {
            $require_prefix = substr($static, 0, 1);
            $real_static    = substr($static, 1);

            // Prefix forbidden.
            if ($require_prefix == 'F') {
                if ($real_static == $normal)
                    return TRUE;
                continue;
            }


            // Prefix required.
            if ($require_prefix == 'T') {
                if (!strncmp($this->prefix, $normal, $prefix_len) &&
                    $real_static == substr($normal, $prefix_len))
                    return TRUE;
                continue;
            }

            // Prefix not required, try without a prefix first.
            if ($real_static == $normal)
                return TRUE;

                if (!strncmp($this->prefix, $normal, $prefix_len) &&
                    $real_static == substr($normal, $prefix_len))
                return TRUE;
        }

        foreach ($this->patterns[self::TYPE_WILDCARD] as &$wild) {
            if (preg_match($wild, $normal) == 1)
                return TRUE;
        }

        return FALSE;
    }
}

?>
