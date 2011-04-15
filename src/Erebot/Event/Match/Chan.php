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
 *      A filter that compares the target channel for an event
 *      with some predefined value.
 *
 * \note
 *      Events that do not relate to a channel never match.
 */
class       Erebot_Event_Match_Chan
implements  Erebot_Interface_Event_Match
{
    /// Channel to use in the comparison, as a string.
    protected $_chan;

    /**
     * Creates a new instance of the filter.
     *
     * \param $chan string|object
     *      Channel to match incoming events against.
     *
     * \throw Erebot_InvalidValueException
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
        return $this->_chan;
    }

    /**
     * Sets the channel used in comparisons.
     *
     * \param $chan string|object
     *      Channel to match incoming events against.
     *
     * \throw Erebot_InvalidValueException
     *      The given chan is invalid.
     */
    public function setChan($chan)
    {
        if (!Erebot_Utils::stringifiable($chan))
            throw new Erebot_InvalidValueException('Not a channel');

        $this->_chan = $chan;
    }

    // Documented in the interface.
    public function match(Erebot_Interface_Event_Base_Generic $event)
    {
        if (!($event instanceof Erebot_Interface_Event_Base_Chan))
            return FALSE;

        return (
            $event->getConnection()->irccasecmp(
                $event->getChan(),
                (string) $this->_chan
            ) == 0
        );
    }
}

