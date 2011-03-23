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
 * Abstract class for a filter which groups several (sub-)filters together.
 *
 * A "fluent" interface is provided using the addFilter
 * and removeFilter methods.
 * This abstract class also implements the ArrayAccess & Countable
 * interfaces, so that you may count() the number of subfilters
 * associated with it and/or get/set those subfilters using the usual
 * array notation.
 */
abstract class  Erebot_Event_Match_CollectionAbstract
implements      Erebot_Interface_Event_Match,
                ArrayAccess,
                Countable
{
    /// Subfilters of this filter.
    protected $_submatchers;

    /**
     * Creates a new instance of this filter.
     *
     * \note
     *      You may pass instances that implement the
     *      Erebot_Interface_Event_Match interface as
     *      initial subfilters of this filter.
     */
    public function __construct(/* ... */)
    {
        $args = func_get_args();
        foreach ($args as $arg) {
            if (!($arg instanceof Erebot_Interface_Event_Match))
                throw new Erebot_InvalidValueException('Not a valid matcher');
        }
        $this->_submatchers = $args;
    }

    // Documented in the interface (Erebot_Interface_Countable).
    public function count()
    {
        return count($this->_submatchers);
    }

    // Documented in the interface (Erebot_Interface_ArrayAccess).
    public function offsetExists($offset)
    {
        return isset($this->_submatchers[$offset]);
    }

    // Documented in the interface (Erebot_Interface_ArrayAccess).
    public function offsetGet($offset)
    {
        return $this->_submatchers[$offset];
    }

    // Documented in the interface (Erebot_Interface_ArrayAccess).
    public function offsetSet($offset, $value)
    {
        if (!($value instanceof Erebot_Interface_Event_Match))
            throw new Erebot_InvalidValueException('Not a valid matcher');
        $this->_submatchers[$offset] = $value;
    }

    // Documented in the interface (Erebot_Interface_ArrayAccess).
    public function offsetUnset($offset)
    {
        unset($this->_submatchers[$offset]);
    }

    /**
     * Adds one or more subfilters to this filter.
     *
     * \note
     *      You can pass one or more filters to this method
     *      to add them to this filter's subfilters.
     *
     * \note
     *      Duplicates of the same subfilter are silently ignored.
     */
    public function & add()
    {
        if (!in_array($filter, $this->_submatchers, TRUE))
            $this->_submatchers[] = $filter;
        return $this;
    }

    /**
     * Removes one or more subfilters from this filter.
     *
     * \note
     *      You can pass one or more filters to this method
     *      to remove them from this filter's subfilters.
     *
     * \note
     *      Attempts to remove a filter which is not a
     *      subfilter of this one are silently ignored.
     */
    public function & remove()
    {
        $key = array_search($filter, $this->_submatchers, TRUE);
        if ($key !== FALSE)
            unset($this->_submatchers[$key]);
        return $this;
    }
}

