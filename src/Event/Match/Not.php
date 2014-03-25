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

namespace Erebot\Event\Match;

/**
 * \brief
 *      A filter that negates the result of a sub-filter.
 *
 * This filter uses a sub-filter. It does not match whenever
 * the sub-filter matches and vice versa. If can be used as
 * a logical-NOT to express more complex filters.
 */
class Not implements \Erebot\Interfaces\Event\Match
{
    /// Subfilter to negate.
    protected $filter;

    /**
     * Creates a new instance of this filter.
     *
     * \param Erebot::Interfaces::Event::Match $filter
     *      Subfilter to negate.
     */
    public function __construct(\Erebot\Interfaces\Event\Match $filter)
    {
        $this->setFilter($filter);
    }

    /**
     * Returns the negated subfilter.
     *
     * \retval Erebot::Interfaces::Event::Match
     *      Negated subfilter.
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Sets the negated subfilter for this filter.
     *
     * \param Erebot::Interfaces::Event::Match $filter
     *      Subfilter to negate.
     */
    public function setFilter(\Erebot\Interfaces\Event\Match $filter)
    {
        $this->filter = $filter;
    }

    public function match(\Erebot\Interfaces\Event\Base\Generic $event)
    {
        return (!$this->filter->match($event));
    }
}
