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
 *      A wrapper to easily split a string using a separator and
 *      deal with other operations related to separators.
 */
class       Erebot_TextWrapper
implements  Countable,
            ArrayAccess,
            Iterator
{
    /// The text wrapped by this instance.
    protected $_text;

    /// Position in the text.
    protected $_position;

    /**
     * Constructs a new instance of a text wrapper.
     *
     * \param string $text
     *      The text to wrap.
     */
    public function __construct($text)
    {
        $this->_text        = $text;
        $this->_position    = 0;
    }

    /**
     * Splits the wrapped text using the given separator
     * and returns only some of the chunks (tokens) as a
     * new string.
     * Whitespaces are squeezed together in the process,
     * no matter what separator is actually used.
     *
     * \param int $start
     *      Offset of the first chunk to return (starting at 0).
     *      If negative, it starts at the end of the wrapped text.
     *
     * \param NULL|int $length
     *      (optional) Number of chunks to return in the new string.
     *      If set to 0 (the default), returns all chunks from
     *      $start onward until the end of the wrapped text.
     *
     * \param NULL|string $separator
     *      (optional) The separator to use while splitting the text.
     *      The default is to split it on whitespaces (' ').
     *
     * \retval string
     *      At most $length chunks (if $length > 0)
     *      and its whitespaces squeezed.
     */
    public function getTokens($start, $length = 0, $separator = ' ')
    {
        $string = preg_replace('/\\s+/', ' ', trim($this->_text, $separator));
        $parts  = explode($separator, $string);

        if (!$length)
            $parts = array_slice($parts, $start);
        else
            $parts = array_slice($parts, $start, $length);

        if (!count($parts))
            return NULL;

        return implode($separator, $parts);
    }

    /**
     * Returns the number of chunks (tokens) obtained
     * by splitting the wrapped text using the given
     * separator.
     * Whitespaces are squeezed together in the process,
     * no matter what separator is actually used.
     *
     * \param NULL|string $separator
     *      (optional) The separator to use while splitting the text.
     *      The default is to split it on whitespaces (' ').
     *
     * \retval int
     *      The number of tokens in the string.
     */
    public function countTokens($separator = ' ')
    {
        $string = preg_replace('/\\s+/', ' ', trim($this->_text, $separator));
        return count(explode($separator, $string));
    }

    /**
     * Returns the wrapped text (untouched).
     *
     * \retval string
     *      The text wrapped by this instance.
     */
    public function __toString()
    {
        return $this->_text;
    }

    // Documented in the internal PHP interface.
    // See also docs/additions/iface_Countable.php.
    public function count()
    {
        return $this->countTokens();
    }

    // Documented in the internal PHP interface.
    // See also docs/additions/iface_Iterator.php.
    public function current()
    {
        return $this->getTokens($this->_position, 1);
    }

    // Documented in the internal PHP interface.
    // See also docs/additions/iface_Iterator.php.
    public function key()
    {
        return $this->_position;
    }

    // Documented in the internal PHP interface.
    // See also docs/additions/iface_Iterator.php.
    public function next()
    {
        $this->_position++;
    }

    // Documented in the internal PHP interface.
    // See also docs/additions/iface_Iterator.php.
    public function rewind()
    {
        $this->_position = 0;
    }

    // Documented in the internal PHP interface.
    // See also docs/additions/iface_Iterator.php.
    public function valid()
    {
        return ($this->_position < $this->countTokens());
    }

    // Documented in the internal PHP interface.
    // See also docs/additions/iface_ArrayAccess.php.
    public function offsetExists($offset)
    {
        return (
            is_int($offset) &&
            $offset >= 0 &&
            $offset < $this->countTokens()
        );
    }

    // Documented in the internal PHP interface.
    // See also docs/additions/iface_ArrayAccess.php.
    public function offsetGet($offset)
    {
        if (!is_int($offset))
            return NULL;
        return $this->getTokens($offset, 1);
    }

    // Documented in the internal PHP interface.
    // See also docs/additions/iface_ArrayAccess.php.
    public function offsetSet($offset, $value)
    {
        throw new RuntimeException('The wrapped text is read-only');
    }

    // Documented in the internal PHP interface.
    // See also docs/additions/iface_ArrayAccess.php.
    public function offsetUnset($offset)
    {
        throw new RuntimeException('The wrapped text is read-only');
    }
}

