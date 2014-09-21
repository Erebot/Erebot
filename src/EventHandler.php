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
 *      An event handler which will call a callback function/method
 *      whenever a set of conditions are met.
 *
 *  Such conditions may be related to the event being of a certain type,
 *  being addressed to a certain target and/or having a certain content.
 */
class EventHandler implements \Erebot\Interfaces\EventHandler
{
    /// Callable object to use when this handler is triggered.
    protected $callback;

    /// Filtering object to decide whether the callback must be called or not.
    protected $filter;

    /**
     * Constructs an event handler.
     *
     * \param callback $callback
     *      The callback function/method which will be called
     *      when an event is received which meets the $constraints,
     *      is part of valid $targets and passed the $filters
     *      successfully.
     *
     * \param null|Erebot::Interfaces::Event::Match $filter
     *      (optional) A filter which must be matched for the callback
     *      associated with this handler to be called.
     */
    public function __construct(
        \Erebot\CallableInterface $callback,
        \Erebot\Interfaces\Event\Match $filter = null
    ) {
        $this->setCallback($callback);
        $this->setFilter($filter);
    }

    /// Destructor.
    public function __destruct()
    {
    }

    public function setCallback(\Erebot\CallableInterface $callback)
    {
        $this->callback = $callback;
        return $this;
    }

    public function getCallback()
    {
        return $this->callback;
    }

    public function setFilter(\Erebot\Interfaces\Event\Match $filter = null)
    {
        $this->filter = $filter;
        return $this;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function handleEvent(\Erebot\Interfaces\Event\Base\Generic $event)
    {
        $matched = true;

        if ($this->filter !== null) {
            $matched = $this->filter->match($event);
        }

        $cb = $this->callback;
        return ($matched ? $cb($this, $event) : null);
    }
}
