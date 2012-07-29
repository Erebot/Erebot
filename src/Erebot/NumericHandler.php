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
 *      A class to handle numeric events.
 *
 * This class will call a given callback method/function
 * whenever the bot receives a numeric event for the
 * code this instance is meant to handle.
 */
class       Erebot_NumericHandler
implements  Erebot_Interface_NumericHandler
{
    /// Numeric code handled by this instance.
    protected $_numeric;
    /// Method/function to call when this handler is triggered.
    protected $_callback;

    /**
     * Constructs a numeric event handler.
     *
     * \param Erebot_Interface_Callable $callback
     *      A callback function/method which will be called
     *      whenever the bot receives a message with the given
     *      \a $numeric code.
     *
     * \param int|Erebot_NumericReference $numeric
     *      The particular numeric code this numeric handler will
     *      react to, or a reference to it.
     */
    public function __construct(Erebot_Interface_Callable $callback, $numeric)
    {
        $this->setCallback($callback);
        $this->setNumeric($numeric);
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
    }

    /// \copydoc Erebot_Interface_NumericHandler::setNumeric()
    public function setNumeric($numeric)
    {
        $this->_numeric = $numeric;
    }

    /// \copydoc Erebot_Interface_NumericHandler::getNumeric()
    public function getNumeric()
    {
        return $this->_numeric;
    }

    /// \copydoc Erebot_Interface_NumericHandler::setCallback()
    public function setCallback(Erebot_Interface_Callable $callback)
    {
        $this->_callback = $callback;
    }

    /// \copydoc Erebot_Interface_NumericHandler::getCallback()
    public function getCallback()
    {
        return $this->_callback;
    }

    /// \copydoc Erebot_Interface_NumericHandler::handleNumeric()
    public function handleNumeric(Erebot_Interface_Event_Numeric $numeric)
    {
        $ourNumeric = ($this->_numeric instanceof Erebot_NumericReference)
            ? $this->_numeric->getValue()
            : $this->_numeric;

        if ($numeric->getCode() !== $ourNumeric)
            return NULL;

        return $this->_callback->invoke($this, $numeric);
    }
}

