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
 *      Class used to represent anything that is callable.
 *
 * This class can represent a wild range of callable items
 * supported by PHP (functions, lambdas, methods, closures, etc.).
 */
class       Erebot_Callable
extends     Erebot_CallableAbstract
{
    /// Inner callable object, as used by PHP.
    protected $_callable;

    /// Human representation of the inner callable.
    protected $_representation;

    /**
     * Constructs a new callable object, abstracting
     * differences between the different constructs
     * PHP supports.
     *
     * \param mixed $callable
     *      A callable item. It must be compatible
     *      with the PHP callback pseudo-type.
     *
     * \throw Erebot_InvalidValueException
     *      The given item is not compatible
     *      with the PHP callback pseudo-type.
     *
     * \see
     *      More information on the callback pseudo-type can be found here:
     *      http://php.net/language.pseudo-types.php#language.types.callback
     */
    public function __construct($callable)
    {
        if (!is_callable($callable, FALSE, $representation))
            throw new Erebot_InvalidValueException('Not a valid callable');

        // This happens for anonymous functions
        // created with create_function().
        if (is_string($callable) && $representation == "")
            $representation = $callable;

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
        // HACK:    we use debug_backtrace() to get (and pass along)
        //          references for call_user_func_array().

        // Starting with PHP 5.4.0, it is possible to limit
        // the number of stack frames returned.
        if (version_compare(PHP_VERSION, '5.4', '>='))
            $bt = debug_backtrace(0, 1);
        // Starting with PHP 5.3.6, the first argument
        // to debug_backtrace() is a bitmask of options.
        else if (version_compare(PHP_VERSION, '5.3.6', '>='))
            $bt = debug_backtrace(0);
        else
            $bt = debug_backtrace(FALSE);

        if (isset($bt[0]['args']))
            $args =& $bt[0]['args'];
        else
            $args = array();
        return call_user_func_array($this->_callable, $args);
    }

    /// \copydoc Erebot_Interface_Callable::invokeArgs()
    public function invokeArgs(&$args)
    {
        return call_user_func_array($this->_callable, $args);
    }

    /// \copydoc Erebot_Interface_Callable::__toString()
    public function __toString()
    {
        return $this->_representation;
    }

    /// \copydoc Erebot_Interface_Callable::getReflector()
    public function getReflector()
    {
        $parts = explode('::', $this->_representation);

        // Did we wrap a function?
        if (count($parts) == 1)
            return new ReflectionFunction($this->_callable);

        // Did we wrap a Closure or some invokable object?
        if (!is_array($this->_callable))
            $callable = array($this->_callable, $parts[1]);
        else
            $callable = $this->_callable;
        return new ReflectionMethod($callable[0], $callable[1]);
    }
}
