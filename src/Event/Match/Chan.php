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
 *      A filter that compares the target channel for an event
 *      with some predefined value.
 *
 * \note
 *      Events that do not relate to a channel never match.
 */
class Chan implements \Erebot\Interfaces\Event\Match
{
    /// Channel to use in the comparison, as a string.
    protected $chan;

    /**
     * Creates a new instance of the filter.
     *
     * \param $chan string|object
     *      Channel to match incoming events against.
     *
     * \throw Erebot::InvalidValueException
     *      The given chan is invalid.
     */
    public function __construct($chan)
    {
        $this->setChan($chan);
    }

    /**
     * Returns the channel associated with this filter.
     *
     * \retval string
     *      Channel associated with this filter.
     */
    public function getChan()
    {
        return $this->chan;
    }

    /**
     * Sets the channel used in comparisons.
     *
     * \param $chan string|object
     *      Channel to match incoming events against.
     *
     * \throw Erebot::InvalidValueException
     *      The given chan is invalid.
     */
    public function setChan($chan)
    {
        if (!\Erebot\Utils::stringifiable($chan)) {
            throw new \Erebot\InvalidValueException('Not a channel');
        }

        $this->chan = $chan;
    }

    public function match(\Erebot\Interfaces\Event\Base\Generic $event)
    {
        if (!($event instanceof \Erebot\Interfaces\Event\Base\Chan)) {
            return false;
        }

        $collator = $event->getConnection()->getCollator();
        return (
            $collator->compare(
                $event->getChan(),
                (string) $this->chan
            ) == 0
        );
    }
}
