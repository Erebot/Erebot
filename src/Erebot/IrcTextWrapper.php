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
 *      A wrapper that correctly splits messages
 *      received from an IRC server (data part).
 *
 * \see
 *      RFC 1459 and RFC 2812 (§2.3.1) for information
 *      on the format recognized by this wrapper.
 */
class       Erebot_IrcTextWrapper
implements  Erebot_Interface_IrcTextWrapper
{
    /// The parts wrapped by this instance.
    protected $_parts;

    /// Position in the text.
    protected $_position;

    /**
     * Constructs a new instance of a text wrapper.
     *
     * \param string|array $oarts
     *      The text to wrap.
     */
    public function __construct($parts)
    {
        if (is_array($parts)) {
            $spaces = 0;
            foreach ($parts as $part) {
                if (strpos($part, ' ') !== FALSE)
                    $spaces++;
            }
            if ($spaces > 1)
                throw new Erebot_InvalidValueException(
                    'Multiple tokens containing spaces'
                );
        }
        else if (is_string($parts)) {
            // Prepend a single space to ease single token handling.
            $msg    = ' '.$parts;
            $pos    = strpos($msg, ' :');

            // Split the message correctly.
            // If there is no colon, then the message
            // has already been split correctly above.
            if ($pos !== FALSE) {
                // Single token, just remove the leading ' :'.
                if (!$pos)
                    $parts = array((string) substr($msg, 2));
                else {
                    // Build up the parts from all words before ' :',
                    // removing the leading space and then add the last
                    // token formed by everything after the first leading ':'.
                    $parts = explode(' ', substr($msg, 1, $pos - 1));
                    $parts[] = (string) substr($msg, $pos + 2);
                }
            }
            // No significative colon.
            else {
                $parts = explode(' ', (string) substr($msg, 1));
            }
        }
        else {
            throw new Erebot_InvalidValueException(
                'A string or an array was expected'
            );
        }

        $this->_parts       = $parts;
        $this->_position    = 0;
    }

    /// \copydoc Erebot_Interface_IrcTextWrapper::__toString()
    public function __toString()
    {
        $last   = count($this->_parts) - 1;
        $text   = '';
        foreach ($this->_parts as $index => $part) {
            if ($index == $last) {
                if (strpos($part, ' ') !== FALSE || !strncmp($part, ':', 1))
                    $text .= ':';
            }
            else if (!strncmp($part, ':', 1))
                throw new Exception('Oops!'); // Will trigger a fatal error.
            $text .= $part;
            if ($index != $last)
                $text .= ' ';
        }
        return $text;
    }

    /**
     * \copydoc Countable::count()
     * \see
     *      docs/additions/iface_Countable.php
     */
    public function count()
    {
        return count($this->_parts);
    }

    /**
     * \copydoc Iterator::current()
     * \see
     *      docs/additions/iface_Iterator.php
     */
    public function current()
    {
        return $this->_parts[$this->_position];
    }

    /**
     * \copydoc Iterator::key()
     * \see
     *      docs/additions/iface_Iterator.php
     */
    public function key()
    {
        return $this->_position;
    }

    /**
     * \copydoc Iterator::next()
     * \see
     *      docs/additions/iface_Iterator.php
     */
    public function next()
    {
        $this->_position++;
    }

    /**
     * \copydoc Iterator::rewind()
     * \see
     *      docs/additions/iface_Iterator.php
     */
    public function rewind()
    {
        $this->_position = 0;
    }

    /**
     * \copydoc Iterator::valid()
     * \see
     *      docs/additions/iface_Iterator.php
     */
    public function valid()
    {
        return ($this->_position < count($this->_parts));
    }

    /**
     * \copydoc ArrayAccess::offsetExists()
     * \see
     *      docs/additions/iface_ArrayAccess.php
     */
    public function offsetExists($offset)
    {
        return isset($this->_parts[$offset]);
    }

    /**
     * \copydoc ArrayAccess::offsetGet()
     * \see
     *      docs/additions/iface_ArrayAccess.php
     */
    public function offsetGet($offset)
    {
        if (!is_int($offset))
            return NULL;
        return $this->_parts[$offset];
    }

    /**
     * \copydoc ArrayAccess::offsetSet()
     * \see
     *      docs/additions/iface_ArrayAccess.php
     */
    public function offsetSet($offset, $value)
    {
        throw new RuntimeException('The wrapped text is read-only');
    }

    /**
     * \copydoc ArrayAccess::offsetUnset()
     * \see
     *      docs/additions/iface_ArrayAccess.php
     */
    public function offsetUnset($offset)
    {
        unset($this->_parts[$offset]);
        $this->_parts = array_values($this->_parts);
    }
}

