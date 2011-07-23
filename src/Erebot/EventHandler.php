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
 *      An event handler which will call a callback function/method
 *      whenever a set of conditions are met.
 *
 *  Such conditions may be related to the event being of a certain type,
 *  being addressed to a certain target and/or having a certain content.
 */
class       Erebot_EventHandler
implements  Erebot_Interface_EventHandler
{
    /// Method/function to call when this handler is triggered.
    protected $_callback;
    /// Filtering object to decide whether some event can be handled or not.
    protected $_filter;

    /**
     * Constructs an event handler.
     *
     * \param callback $callback
     *      The callback function/method which will be called
     *      when an event is received which meets the $constraints,
     *      is part of valid $targets and passed the $filters
     *      successfully.
     *
     * \param NULL|Erebot_Interface_Event_Match $filter
     *      (optional) A filter which must be matched for the callback
     *      associated with this handler to be called.
     */
    public function __construct(
        Erebot_Interface_Callable       $callback,
        Erebot_Interface_Event_Match    $filter = NULL
    )
    {
        $this->setCallback($callback);
        $this->setFilter($filter);
    }

    /// Destructor.
    public function __destruct()
    {
    }

    public function setCallback(Erebot_Interface_Callable $callback)
    {
        $this->_callback = $callback;
    }

    /// \copydoc Erebot_Interface_EventHandler::getCallback()
    public function getCallback()
    {
        return $this->_callback;
    }

    /// \copydoc Erebot_Interface_EventHandler::setFilter()
    public function setFilter(Erebot_Interface_Event_Match $filter = NULL)
    {
        $this->_filter = $filter;
    }

    /// \copydoc Erebot_Interface_EventHandler::getFilter()
    public function getFilter()
    {
        return $this->_filter;
    }

    /// \copydoc Erebot_Interface_EventHandler::handleEvent()
    public function handleEvent(Erebot_Interface_Event_Base_Generic $event)
    {
        $matched = TRUE;

        if ($this->_filter !== NULL)
            $matched = $this->_filter->match($event);

        return ($matched ? $this->_callback->invoke($this, $event) : NULL);
    }
}

