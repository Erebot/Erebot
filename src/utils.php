<?php

include_once(dirname(__FILE__).'/exceptions/Exception.php');
include_once(dirname(__FILE__).'/exceptions/NotFound.php');

class ErebotUtils
{
    const STRIP_NONE        = 0x00;
    const STRIP_COLORS      = 0x01;
    const STRIP_BOLD        = 0x02;
    const STRIP_UNDERLINE   = 0x04;
    const STRIP_REVERSE     = 0x08;
    const STRIP_RESET       = 0x10;
    const STRIP_EXT_COLORS  = 0x20;
    const STRIP_ALL         = 0xFF;

    public static function incl($file, $required = FALSE)
    {
        if ((!strncasecmp(PHP_OS, 'Win', 3) && strpos($file, ':') !== FALSE) ||
            substr($file, 0, 1) == DIRECTORY_SEPARATOR)
            $path = $file;

        else {
            $bt     = debug_backtrace();
            $path   = dirname($bt[0]['file']).DIRECTORY_SEPARATOR.$file;
        }

        if ($required)
            return require_once($path);

        return include_once($path);
    }

    public static function getCallerObject()
    {
        $bt     = debug_backtrace();
        $caller = isset($bt[2]['object']) ? $bt[2]['object'] : NULL;
        return $caller;
    }
    static public function numtok($text, $separator = ' ')
    {
        $string = preg_replace('/\\s+/', ' ', $text);
        return count(explode($separator, $string));
    }

    static public function gettok($text, $start, $length = 0, $separator = ' ')
    {
        $string = preg_replace('/\\s+/', ' ', $text);
        $parts     = explode($separator, $string);

        if (!$length)
            $parts = array_slice($parts, $start);
        else
            $parts = array_slice($parts, $start, $length);

        if (!count($parts))
            return NULL;

        return implode($separator, $parts);
    }

    public static function stripCodes($text, $strip = NULL)
    {
        if ($strip === NULL)
            $strip = self::STRIP_ALL;

        if ($strip & self::STRIP_BOLD)
            $text = str_replace("\002", '', $text);

        if ($strip & self::STRIP_COLORS)
            $text = preg_replace("/\003(?:[0-9]{1,2}(?:,[0-9]{1,2})?)?/", '', $text);

/*
STRIP_EXT_COLORS
*/

        if ($strip & self::STRIP_RESET)
            $text = str_replace("\017", '', $text);

        if ($strip & self::STRIP_REVERSE)
            $text = str_replace("\026", '', $text);

        if ($strip & self::STRIP_UNDERLINE)
            $text = str_replace("\037", '', $text);

        return $text;
    }

    /// @TODO improve this code, eg. root = 'abc/def/' & path = 'ghi'
    public static function resolveRelative($root, $path)
    {
        $prefix = '';

        // Windows
        if (!strncasecmp(PHP_OS, 'Win', 3)) {
            $pos    = strpos($root, ':');
            if ($pos !== FALSE) {
                $prefix = substr($root, 0, $pos + 2);
                $root   = substr($root, $pos + 1);
            }
        }

        if (substr($root, -1) == DIRECTORY_SEPARATOR)
            $root = substr($root, 0, -1);

        $path_parts = explode(DIRECTORY_SEPARATOR, $path);
        $root_parts = explode(DIRECTORY_SEPARATOR, $root);

        foreach ($path_parts as $part) {
            if ($part == '.')
                continue;

            if ($part != '..')
                $root_parts[] = $part;

            else if (count($root_parts) > 0)
                array_pop($root_parts);
        }

        return $prefix.implode(DIRECTORY_SEPARATOR, $root_parts);
    }

    public static function extractNick($source)
    {
        if (strpos($source, '!') === FALSE)
            return $source;
        return substr($source, 0, strpos($source, '!'));
    }

    public static function isUTF8($text)
    {
        // From http://w3.org/International/questions/qa-forms-utf-8.html
        // Pointed out by bitseeker on http://php.net/utf8_encode
        return preg_match('%^(?:
              [\x09\x0A\x0D\x20-\x7E]            # ASCII
            | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
            |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
            | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
            |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
            |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
            | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
            |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
        )*$%xs', $text);
    }

    public static function toUTF8($text, $from='iso-8859-1')
    {
        if (ErebotUtils::isUTF8($text))
            return $text;

        if (!strcasecmp($from, 'iso-8859-1') &&
            function_exists('utf8_encode'))
            return utf8_encode($text);

        if (function_exists('iconv'))
            return iconv($from, 'UTF-8//TRANSLIT', $text);

        if (function_exists('recode'))
            return recode($from.'..utf-8', $text);

        if (function_exists('mb_convert_encoding'))
            return mb_convert_encoding($text, 'UTF-8', $from);

        if (function_exists('html_entity_decode'))
            return html_entity_decode(
                htmlentities($text, ENT_QUOTES, $from),
                ENT_QUOTES, 'UTF-8');

        throw new EErebotNotImplemented('No way to convert to UTF-8');
    }

    public static function getVStatic($class, $variable)
    {
        if (is_object($class))
            $class = get_class($class);
        $refl = new ReflectionClass($class);
        if (!$refl->hasConstant($variable))
            throw new EErebotNotFound('No such constant');
        return $refl->getConstant($variable);
    }
}

?>
