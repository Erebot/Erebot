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

abstract class  Erebot_Event_Match_CollectionAbstract
implements      Erebot_Interface_Event_Match,
                ArrayAccess,
                Countable
{
    protected $_matches;

    public function __construct(/* ... */)
    {
        $args = func_get_args();
        foreach ($args as &$arg) {
            if (!($arg instanceof Erebot_Interface_Event_Match))
                throw new Erebot_InvalidValueException('Not a valid matcher');
        }
        unset($arg);
        $this->_matches = $args;
    }

    // Countable interface.
    public function count()
    {
        return count($this->_matches);
    }

    // ArrayAccess interface.
    public function offsetExists($offset)
    {
        return isset($this->_matches[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->_matches[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (!($value instanceof Erebot_Interface_Event_Match))
            throw new Erebot_InvalidValueException('Not a valid matcher');
        $this->_matches[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->_matches[$offset]);
    }

    // "Fluent" interface.
    public function & addFilter(Erebot_Interface_Event_Match $filter)
    {
        if (!in_array($filter, $this->_matches, TRUE))
            $this->_matches[] = $filter;
        return $this;
    }

    // "Fluent" interface.
    public function & removeFilter(Erebot_Interface_Event_Match $filter)
    {
        $key = array_search($filter, $this->_matches, TRUE);
        if ($key !== FALSE)
            unset($this->_matches[$key]);
        return $this;
    }
}

