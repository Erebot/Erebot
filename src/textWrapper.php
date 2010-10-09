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
class ErebotTextWrapper
{
    /// The text wrapped by this instance.
    protected $_text;

    /**
     * Constructs a new instance of a text wrapper.
     *
     * \param $text
     *      The text to wrap.
     */
    public function __construct($text)
    {
        $this->_text = $text;
    }

    /**
     * Splits the wrapped text using the given separator
     * and returns only some of the chunks (tokens) as a
     * new string.
     * Whitespaces are squeezed together in the process,
     * no matter what separator is actually used.
     *
     * \param $start
     *      Offset of the first chunk to return (starting at 0).
     *      If negative, it starts at the end of the wrapped text.
     *
     * \param $length
     *      Number of chunks to return in the new string.
     *      If set to 0 (the default), returns all chunks from
     *      $start onward until the end of the wrapped text.
     *
     * \param $separator
     *      The separator to use while splitting the text.
     *      The default is to split it on whitespaces (' ').
     *
     * \return
     *      A string with at most $length chunks (if $length > 0)
     *      and its whitespaces squeezed.
     */
    public function getTokens($start, $length = 0, $separator = ' ')
    {
        return ErebotUtils::gettok($this->_text, $start, $length, $separator);
    }

    /**
     * Returns the number of chunks (tokens) obtained
     * by splitting the wrapped text using the given
     * separator.
     * Whitespaces are squeezed together in the process,
     * no matter what separator is actually used.
     *
     * \param $separator
     *      The separator to use while splitting the text.
     *      The default is to split it on whitespaces (' ').
     *
     * \return
     *      The number of tokens in the string.
     */
    public function countTokens($separator = ' ')
    {
        return ErebotUtils::numtok($this->_text, $separator);
    }

    /**
     * Returns the wrapped text (untouched).
     *
     * \return
     *      The text wrapped by this instance.
     */
    public function __toString()
    {
        return $this->_text;
    }
}

