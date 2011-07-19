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

class       Erebot_Callable
implements  Erebot_Interface_Callable
{
    protected $_callable;
    protected $_representation;

    /// \copydoc Erebot_Interface_Callable::__construct()
    public function __construct($callable)
    {
        if (!is_callable($callable, FALSE, $representation))
            throw new Erebot_InvalidValueException('Not a valid callable');
        $this->_callable        = $callable;
        $this->_representation  = $representation;
    }

    /// \copydoc Erebot_Interface_Callable::getCallable()
    public function getCallable()
    {
        return $this->_callable;
    }

    /// \copydoc Erebot_Interface_Callable::getRepresentation()
    public function getRepresentation()
    {
        return $this->_representation;
    }

    /// \copydoc Erebot_Interface_Callable::invoke()
    public function invoke(/* ... */)
    {
        $args = func_get_args();
        return $this->invokeArgs($args);
    }

    /// \copydoc Erebot_Interface_Callable::invokeArgs()
    public function invokeArgs($args)
    {
        return call_user_func_array($this->_callable, $args);
    }

    /// \copydoc Erebot_Interface_Callable::__toString()
    public function __toString()
    {
        return $this->_representation;
    }
}
