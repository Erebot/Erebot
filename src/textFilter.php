<?php

ErebotUtils::incl('ifaces/textFilter.php');

class       ErebotTextFilter
implements  iErebotTextFilter
{
    const MATCH_ALL     = NULL;

    const TYPE_STATIC   = 0;
    const TYPE_WILDCARD = 1;
    const TYPE_REGEXP   = 2;

    const PREFIXES = "!?&'@*%.:\\";

    protected $patterns;

    public function __construct($type = NULL, $pattern = NULL, $require_prefix = FALSE)
    {
        if (($type === NULL && $pattern !== NULL) ||
            ($type !== NULL && $pattern === NULL))
            throw new EErebotIllegalAction('Either both or none of type & pattern must be given');

        $this->patterns = array(array(), array(), array());
        if ($type !== NULL)
            $this->addPattern($type, $pattern, $require_prefix);
    }

    public function addPattern($type, $pattern, $require_prefix = FALSE)
    {
        $pattern = $this->rewritePattern($type, $pattern, $require_prefix);
        $this->patterns[$type][] = $pattern;
    }

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
                    $prefix = '['.preg_quote(self::PREFIXES).']'.
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

    public function getPatterns($type = NULL)
    {
        if ($type === NULL)
            return $this->patterns;

        if (!is_int($type) || !isset($this->patterns[$type]))
            throw new EErebotInvalidValue('Invalid pattern type');

        return $this->patterns[$type];
    }

    public function match(iErebotEvent &$event)
    {
        if (!in_array('iErebotEventText', class_implements($event)))
            return TRUE;

        $text   = $event->getText();

        foreach ($this->patterns[self::TYPE_REGEXP] as &$regexp) {
            if (preg_match($regexp, $text) == 1)
                return TRUE;
        }

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
                if (strpos(self::PREFIXES, $normal[0]) !== FALSE &&
                    $real_static == substr($normal, 1))
                    return TRUE;
                continue;
            }

            // Prefix not required, try without a prefix first.
            if ($real_static == $normal)
                return TRUE;

            if (strpos(self::PREFIXES, $normal[0]) !== FALSE &&
                $real_static == substr($normal, 1))
                return TRUE;
        }

        foreach ($this->patterns[self::TYPE_WILDCARD] as &$wild) {
            if (preg_match($wild, $normal) == 1)
                return TRUE;
        }

        return FALSE;
    }

    public static function getRecognizedPrefixes()
    {
        return str_split(self::PREFIXES);
    }
}

?>
