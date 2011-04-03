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
 *      A filter that negates the result of a sub-filter.
 *
 * This filter uses a sub-filter. It does not match whenever
 * the sub-filter matches and vice versa. If can be used as
 * a logical-NOT to express more complex filters.
 */
class       Erebot_Event_Match_Not
implements  Erebot_Interface_Event_Match
{
    /// Subfilter to negate.
    protected $_filter;

    /**
     * Creates a new instance of this filter.
     *
     * \param Erebot_Interface_Event_Match $filter
     *      Subfilter to negate.
     */
    public function __construct(Erebot_Interface_Event_Match $filter)
    {
        $this->setFilter($filter);
    }

    /**
     * Returns the negated subfilter.
     *
     * \retval Erebot_Interface_Event_Match
     *      Negated subfilter.
     */
    public function getFilter()
    {
        return $this->_filter;
    }

    /**
     * Sets the negated subfilter for this filter.
     *
     * \param Erebot_Interface_Event_Match $filter
     *      Subfilter to negate.
     */
    public function setFilter(Erebot_Interface_Event_Match $filter)
    {
        $this->_filter = $filter;
    }

    // Documented in the interface.
    public function match(Erebot_Interface_Event_Base_Generic $event)
    {
        return (!$this->_filter->match($event));
    }
}

