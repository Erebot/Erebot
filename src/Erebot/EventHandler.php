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

    /// \copydoc Erebot_Interface_EventHandler::__construct()
    public function __construct(
                                        $callback,
        Erebot_Interface_Event_Match    $filter = NULL
    )
    {
        $reflector  = new ReflectionParameter($callback, 0);
        $cls        = $reflector->getClass();
        if ($cls === NULL ||
            !$cls->implementsInterface('Erebot_Interface_Event_Base_Generic'))
            throw new Erebot_InvalidValueException('Invalid callback');

        $this->_callback    =&  $callback;
        $this->_filter      =&  $filter;
    }

    public function __destruct()
    {
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

        return ($matched ? call_user_func($this->_callback, $event) : NULL);
    }
}

