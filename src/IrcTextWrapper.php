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

namespace Erebot;

/**
 * \brief
 *      A wrapper that correctly splits messages
 *      received from an IRC server (data part).
 *
 * \see
 *      RFC 1459 and RFC 2812 (§2.3.1) for information
 *      on the format recognized by this wrapper.
 */
class IrcTextWrapper implements \Erebot\Interfaces\IrcTextWrapper
{
    /// The parts wrapped by this instance.
    protected $parts;

    /// Position in the text.
    protected $position;

    /**
     * Constructs a new instance of a text wrapper.
     *
     * \param string|array $parts
     *      The text to wrap.
     */
    public function __construct($parts)
    {
        if (is_array($parts)) {
            $spaces = 0;
            foreach ($parts as $part) {
                if (strpos($part, ' ') !== false) {
                    $spaces++;
                }
            }
            if ($spaces > 1) {
                throw new \Erebot\InvalidValueException(
                    'Multiple tokens containing spaces'
                );
            }
            if (!count($parts)) {
                throw new \Erebot\InvalidValueException(
                    'At least one token must be passed'
                );
            }
        } elseif (is_string($parts)) {
            // Prepend a single space to ease single token handling.
            $msg    = ' '.$parts;
            $pos    = strpos($msg, ' :');

            // Split the message correctly.
            // If there is no colon, then the message
            // has already been split correctly above.
            if ($pos !== false) {
                // Single token, just remove the leading ' :'.
                if (!$pos) {
                    $parts = array((string) substr($msg, 2));
                } else {
                    // Build up the parts from all words before ' :',
                    // removing the leading space and then add the last
                    // token formed by everything after the first leading ':'.
                    $parts = explode(' ', substr($msg, 1, $pos - 1));
                    $parts[] = (string) substr($msg, $pos + 2);
                }
            } else {
                // No significative colon.
                $parts = explode(' ', (string) substr($msg, 1));
            }
        } else {
            throw new \Erebot\InvalidValueException(
                'A string or an array was expected'
            );
        }

        $this->parts       = $parts;
        $this->position    = 0;
    }

    public function __toString()
    {
        $last   = count($this->parts) - 1;
        $text   = '';
        foreach ($this->parts as $index => $part) {
            if ($index == $last) {
                if (strpos($part, ' ') !== false || !strncmp($part, ':', 1)) {
                    $text .= ':';
                }
            } elseif (!strncmp($part, ':', 1)) {
                throw new \Exception('Oops!'); // Will trigger a fatal error.
            }
            $text .= $part;
            if ($index != $last) {
                $text .= ' ';
            }
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
        return count($this->parts);
    }

    /**
     * \copydoc Iterator::current()
     * \see
     *      docs/additions/iface_Iterator.php
     */
    public function current()
    {
        return $this->parts[$this->position];
    }

    /**
     * \copydoc Iterator::key()
     * \see
     *      docs/additions/iface_Iterator.php
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * \copydoc Iterator::next()
     * \see
     *      docs/additions/iface_Iterator.php
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * \copydoc Iterator::rewind()
     * \see
     *      docs/additions/iface_Iterator.php
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * \copydoc Iterator::valid()
     * \see
     *      docs/additions/iface_Iterator.php
     */
    public function valid()
    {
        return ($this->position < count($this->parts));
    }

    /**
     * \copydoc ArrayAccess::offsetExists()
     * \see
     *      docs/additions/iface_ArrayAccess.php
     */
    public function offsetExists($offset)
    {
        return isset($this->parts[$offset]);
    }

    /**
     * \copydoc ArrayAccess::offsetGet()
     * \see
     *      docs/additions/iface_ArrayAccess.php
     */
    public function offsetGet($offset)
    {
        if (!is_int($offset)) {
            return null;
        }
        if ($offset < 0) {
            $offset += count($this->parts);
        }
        return $this->parts[$offset];
    }

    /**
     * \copydoc ArrayAccess::offsetSet()
     * \see
     *      docs/additions/iface_ArrayAccess.php
     */
    public function offsetSet($offset, $value)
    {
        throw new \RuntimeException('The wrapped text is read-only');
    }

    /**
     * \copydoc ArrayAccess::offsetUnset()
     * \see
     *      docs/additions/iface_ArrayAccess.php
     */
    public function offsetUnset($offset)
    {
        unset($this->parts[$offset]);
        $this->parts = array_values($this->parts);
    }
}
