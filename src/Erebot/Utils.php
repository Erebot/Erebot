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
 *      Utility methods for Erebot.
 */
class Erebot_Utils
{
    /// Do not strip anything from the text.
    const STRIP_NONE        = 0x00;
    /// Strip (mIRC/pIRCh) colors from the text.
    const STRIP_COLORS      = 0x01;
    /// Strip the bold attribute from the text.
    const STRIP_BOLD        = 0x02;
    /// Strip the underline attribute from the text.
    const STRIP_UNDERLINE   = 0x04;
    /// Strip the reverse attribute from the text.
    const STRIP_REVERSE     = 0x08;
    /// Strip the reset control character from the text.
    const STRIP_RESET       = 0x10;
    /// Strip extended colors from the text.
    const STRIP_EXT_COLORS  = 0x20;
    /// Strip all forms of styles from the text.
    const STRIP_ALL         = 0xFF;

    /// Return the value of a constant.
    const VSTATIC_CONST     = 0x01;

    /// Return the value of a static variable.
    const VSTATIC_VAR       = 0x02;

    /**
     * Returns the object (if any) associated with the method
     * that called the method which called Erebot_Utils::getCallerObject().
     *
     * Consider the following example:
     * \code
     *      class Foo {
     *          public function bar() {
     *              var_dump(Erebot_Utils::getCallerObject());
     *          }
     *      }
     *      class Bar {
     *          public function baz() {
     *              $foo = new Foo();
     *              $foo->bar();
     *          }
     *      }
     *      $bar = new Bar();
     *      $bar->baz(); // Prints something like "object(Bar)#2 (0) {}".
     * \endcode
     *
     * \retval object
     *      The caller object of the method which called
     *      Erebot_Utils::getCallerObject().
     */
    static public function getCallerObject()
    {
        $bt     = debug_backtrace();
        $caller = isset($bt[2]['object']) ? $bt[2]['object'] : NULL;
        return $caller;
    }

    /**
     * Returns the number of chunks (tokens) obtained
     * after splitting the given text with the given
     * separator.
     *
     * \param string $text
     *      The text to split and from which the number
     *      of tokens should be returned.
     *
     * \param NULL|string $separator
     *      (optional) The separator to use to split
     *      the text. Defaults to a whitespace (' ').
     *
     * \retval int
     *      The number of tokens obtained after splitting
     *      the text.
     */
    static public function numtok($text, $separator = ' ')
    {
        $string = preg_replace('/\\s+/', ' ', trim($text, $separator));
        return count(explode($separator, $string));
    }

    /**
     * Returns a string with some of the chunks (tokens)
     * obtained after splitting the given text with the
     * given separator. The $start and $length parameters
     * are used to determine what chunks are returned.
     *
     * \param string $text
     *      The text to split and from which the tokens
     *      should be returned.
     *
     * \param int $start
     *      Offset of the first chunk to return (starting at 0).
     *      If negative, it starts at the end of the text.
     *
     * \param NULL|int $length
     *      (optional) Number of chunks to return in the new string.
     *      If set to 0 (the default), returns all chunks from
     *      $start onward until the end of the text.
     *
     * \param NULL|string $separator
     *      (optional) The separator to use to split
     *      the text. Defaults to a whitespace (' ').
     *
     * \retval string
     *      A new string with at most $length tokens (if $length > 0)
     *      and its whitespaces squeezed.
     */
    static public function gettok($text, $start, $length = 0, $separator = ' ')
    {
        $string = preg_replace('/\\s+/', ' ', trim($text, $separator));
        $parts     = explode($separator, $string);

        if (!$length)
            $parts = array_slice($parts, $start);
        else
            $parts = array_slice($parts, $start, $length);

        if (!count($parts))
            return NULL;

        return implode($separator, $parts);
    }

    /**
     * Strips IRC styles from a text.
     *
     * \param string $text
     *      The text from which styles must be stripped.
     *
     * \param int $strip
     *      A bitwise OR of the codes of the styles we want to strip.
     *      The default is to strip all forms of styles from the text.
     *      See also the Erebot_Utils::STRIP_* constants.
     *
     * \retval string
     *      The text with all the styles specified in $strip stripped.
     */
    static public function stripCodes($text, $strip = self::STRIP_ALL)
    {
        if (!is_int($strip))
            throw new Erebot_InvalidValueException("Invalid stripping flags");

        if ($strip & self::STRIP_BOLD)
            $text = str_replace("\002", '', $text);

        if ($strip & self::STRIP_COLORS)
            $text = preg_replace(
                "/\003(?:[0-9]{1,2}(?:,[0-9]{1,2})?)?/",
                '', $text
            );

        /// @TODO strip extended colors.

        if ($strip & self::STRIP_RESET)
            $text = str_replace("\017", '', $text);

        if ($strip & self::STRIP_REVERSE)
            $text = str_replace("\026", '', $text);

        if ($strip & self::STRIP_UNDERLINE)
            $text = str_replace("\037", '', $text);

        return $text;
    }

    /**
     * Given some user's full IRC identity (nick!ident\@host),
     * this methods extracts and returns that user's nickname.
     *
     * \param string $source
     *      Some user's full IRC identity (as "nick!ident\@host").
     *
     * \retval string
     *      The nickname of the user represented by that identity.
     *
     * \note
     *      This method will still work as expected if given
     *      only a nickname to work with. Therefore, it is safe
     *      to call this method with the result of a previous
     *      invocation. Thus, the following snippet:
     *      Erebot_Utils::extractNick(Erebot_Utils::extractNick('foo!bar\@baz'));
     *      will return "foo" as expected.
     */
    static public function extractNick($source)
    {
        if (strpos($source, '!') === FALSE)
            return $source;
        return substr($source, 0, strpos($source, '!'));
    }

    /**
     * Can be used to determine if a string contains a sequence
     * of valid UTF-8 encoded codepoints.
     *
     * \param string $text
     *      Some text to test for UTF-8 correctness.
     *
     * \retval TRUE
     *      The $text contains a valid UTF-8 sequence.
     *
     * \retval FALSE
     *      The $text is not a valid UTF-8 sequence.
     */
    static public function isUTF8($text)
    {
        // From http://w3.org/International/questions/qa-forms-utf-8.html
        // Pointed out by bitseeker on http://php.net/utf8_encode
        return (bool) preg_match(
            '%^(?:
                  [\x09\x0A\x0D\x20-\x7E]            # ASCII
                | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
                |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
                | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
                |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
                |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
                | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
                |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
            )*$%xs', $text
        );
    }

    /**
     * Transforms the given text into a UTF-8 sequence.
     *
     * \param string $text
     *      The text to convert into a UTF-8 sequence.
     *
     * \param NULL|string $from
     *      (optional) The encoding currently used by $text.
     *      A default of "iso-8859-1" is assumed.
     *
     * \retval string
     *      The original text, converted into UTF-8.
     *
     * \throw Erebot_NotImplementedException
     *      Raised if no method could be found to convert
     *      the text. See also the notes for information on
     *      how to avoid this exception being raised.
     *
     * \note
     *      This method tries different technics to convert
     *      the text. If despite its best efforts, it still
     *      fails, you may consider installing one of PHP's
     *      extension for "Human Language and Character 
     *      Encoding Support".
     */
    static public function toUTF8($text, $from='iso-8859-1')
    {
        if (self::isUTF8($text))
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
                ENT_QUOTES, 'UTF-8'
            );

        throw new Erebot_NotImplementedException('No way to convert to UTF-8');
    }

    /**
     * Returns some static data from a class/object.
     *
     * \param string|object $class
     *      Either the name of a class or an instance of a class
     *      from which the static data will be retrieved.
     *
     * \param string $name
     *      The name of the static data to return.
     *
     * \param opaque $source
     *      (optional) The kind of static data to look for (either
     *      a constant or a static variable).
     *      Use Erebot_Utils::VSTATIC_CONST or Erebot_Utils::VSTATIC_VAR
     *      to select a specific source of data.
     *      The default is to look for a constant (same as if $source
     *      had been set to Erebot_Utils::VSTATIC_CONST).
     *
     * \retval mixed
     *      The content of the static data whose name is $name.
     *
     * \throw Erebot_NotFoundException
     *      No data could be found which matches the given $name,
     *      using the specified $source of data.
     */
    static public function getVStatic(
        $class,
        $name,
        $source = self::VSTATIC_CONST
    )
    {
        if (is_object($class))
            $class = get_class($class);
        $refl = new ReflectionClass($class);

        if (($source & self::VSTATIC_CONST) == self::VSTATIC_CONST) {
            try {
                return $refl->getConstant($name);
            }
            catch (ReflectionException $e) {
            }
        }

        if (($source & self::VSTATIC_VAR) == self::VSTATIC_VAR) {
            try {
                $reflProp = $refl->getProperty($name);
                return $reflProp->getValue();
            }
            catch (ReflectionException $e) {
            }
        }
            
        throw new Erebot_NotFoundException('No such thing');
    }

    /**
     * Checks whether a variable can be safely cast to a string.
     * This is the case when the variable is already a string or
     * when it's an object with a __toString() method.
     *
     * \param mixed $item
     *      Variable to test.
     *
     * \retval bool
     *      Whether the given $item can be safely cast to a string.
     */
    static public function stringifiable($item)
    {
        if (is_string($item))
            return TRUE;
        if (is_object($item) && method_exists($item, '__toString'))
            return TRUE;
        return FALSE;
    }
}

