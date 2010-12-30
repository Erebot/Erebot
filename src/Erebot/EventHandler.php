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
    protected $_callback;
    protected $_constraints;
    protected $_targets;
    protected $_filters;

    // Documented in the interface.
    public function __construct(
                                        $callback,
                                        $constraints,
        Erebot_Interface_EventTarget    $targets        = NULL,
                                        $filters        = NULL
    )
    {
        $reflector  = new ReflectionParameter($callback, 0);
        $cls        = $reflector->getClass();
        if ($cls === NULL || !$cls->implementsInterface('Erebot_Interface_Event_Generic'))
            throw new Erebot_InvalidValueException('Invalid callback');

        if (!is_array($constraints))
            $constraints = array($constraints);

        if ($filters === NULL)
            $filters = array();
        if (!is_array($filters))
            $filters = array($filters);

        foreach ($constraints as $constraint) {
            if (!is_string($constraint))
                throw new Erebot_InvalidValueException('Invalid event type');

            if (!class_exists($constraint) && !interface_exists($constraint))
                throw new Erebot_InvalidValueException('Invalid event type');

            // We want to determine if the given type
            // (either a class or an interface) implements
            // the Erebot_Interface_Event_Generic interface.
            $reflect = new ReflectionClass($constraint);
            if (!$reflect->implementsInterface('Erebot_Interface_Event_Generic'))
                throw new Erebot_InvalidValueException('Invalid event type');
        }

        foreach ($filters as &$filter) {
            if (!($filter instanceof Erebot_TextFilter))
                throw new Erebot_InvalidValueException('Invalid filter');
        }
        unset($filter);

        $this->_callback        =&  $callback;
        $this->_constraints     =&  $constraints;
        $this->_targets         =&  $targets;
        $this->_filters         =&  $filters;
    }

    public function __destruct()
    {
    }

    // Documented in the interface.
    public function & getCallback()
    {
        return $this->_callback;
    }

    // Documented in the interface.
    public function getConstraints()
    {
        return $this->_constraints;
    }

    // Documented in the interface.
    public function & getTargets()
    {
        return $this->_targets;
    }

    public function & addFilter(Erebot_TextFilter &$filter)
    {
        if (!in_array($filter, $this->_filters))
            $this->_filters[] = $filter;
        return $this;
    }

    public function & removeFilter(
        Erebot_TextFilter  &$filter,
                            $ignoreMissing = TRUE
    )
    {
        $key = array_search($filter, $this->_filters);
        if ($key === FALSE) {
            if ($ignoreMissing)
                return $this;
            throw new Erebot_NotFoundException('Filter not found');
        }
        unset($this->_filters[$key]);
        return $this;
    }

    // Documented in the interface.
    public function & getFilters()
    {
        return $this->_filters;
    }

    // Documented in the interface.
    public function handleEvent(
        Erebot_Interface_Config_Main   &$config,
        Erebot_Interface_Event_Generic &$event
    )
    {
        foreach ($this->_constraints as $constraint) {
            if (!($event instanceof $constraint))
                return NULL;
        }

        if ($this->_targets !== NULL && !$this->_targets->match($event))
            return NULL;

        $matched = (!in_array(
            'Erebot_Interface_Event_Text',
            class_implements($event))
        );

        if (!$matched) {
            if (!count($this->_filters))
                $matched = TRUE;
            foreach ($this->_filters as &$filter) {
                if ($filter->match($event)) {
                    $matched = TRUE;
                    break;
                }
            }
            unset($filter);
        }

        if (!$matched)
            return NULL;
        return call_user_func($this->_callback, $event);
    }
}

