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
 *      A class to handle numeric events.
 *
 * This class will call a given callback method/function
 * whenever the bot receives a numeric event for the
 * code this instance is meant to handle.
 */
class NumericHandler implements \Erebot\Interfaces\NumericHandler
{
    /// Numeric code handled by this instance.
    protected $numeric;
    /// Method/function to call when this handler is triggered.
    protected $callback;

    /**
     * Constructs a numeric event handler.
     *
     * \param callable $callback
     *      A callback function/method which will be called
     *      whenever the bot receives a message with the given
     *      \a $numeric code.
     *
     * \param int|Erebot::NumericReference $numeric
     *      The particular numeric code this numeric handler will
     *      react to, or a reference to it.
     */
    public function __construct(callable $callback, $numeric)
    {
        $this->setCallback($callback);
        $this->setNumeric($numeric);
    }

    public function setNumeric($numeric)
    {
        $this->numeric = $numeric;
        return $this;
    }

    public function getNumeric()
    {
        return $this->numeric;
    }

    public function setCallback(callable $callback)
    {
        $this->callback = $callback;
        return $this;
    }

    public function getCallback()
    {
        return $this->callback;
    }

    public function handleNumeric(\Erebot\Interfaces\Event\Numeric $numeric)
    {
        $ourNumeric = ($this->numeric instanceof \Erebot\NumericReference)
            ? $this->numeric->getValue()
            : $this->numeric;

        if ($numeric->getCode() !== $ourNumeric) {
            return null;
        }

        $cb = $this->callback;
        return $cb($this, $numeric);
    }
}
