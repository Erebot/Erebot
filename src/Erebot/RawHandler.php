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
 *      A class to handle raw numeric events.
 *
 * This class will call a given callback method/function
 * whenever the bot receives a raw numeric event for the
 * raw code this instance is meant to handle.
 */
class       Erebot_RawHandler
implements  Erebot_Interface_RawHandler
{
    /// Raw numeric handled by this instance.
    protected $_raw;
    /// Method/function to call when this handler is triggered.
    protected $_callback;

    /// \copydoc Erebot_Interface_RawHandler::__construct()
    public function __construct($callback, $raw)
    {
        $reflector  = new ReflectionParameter($callback, 0);
        $cls        = $reflector->getClass();
        if ($cls === NULL || !$cls->implementsInterface('Erebot_Interface_Event_Raw'))
            throw new Erebot_InvalidValueException('Invalid signature');

        $this->_raw         = $raw;
        $this->_callback    = $callback;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
    }

    /// \copydoc Erebot_Interface_RawHandler::getRaw()
    public function getRaw()
    {
        return $this->_raw;
    }

    /// \copydoc Erebot_Interface_RawHandler::getCallback()
    public function getCallback()
    {
        return $this->_callback;
    }

    /// \copydoc Erebot_Interface_RawHandler::handleRaw()
    public function handleRaw(Erebot_Interface_Event_Raw $raw)
    {
        if ($raw->getRaw() != $this->_raw)
            return NULL;

        return call_user_func($this->_callback, $raw);
    }
}

