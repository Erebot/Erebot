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
 *      A filter which matches when the source of the event
 *      equals some predefined value.
 *
 * \note
 *      Events that have no "source" never match.
 */
class       Erebot_Event_Match_Source
implements  Erebot_Interface_Event_Match
{
    /// Source to use in the comparison, as a string.
    protected $_source;

    /**
     * Creates a new instance of the filter.
     *
     * \param $source string|object
     *      Source to match incoming events against.
     *
     * \throw Erebot_InvalidValueException
     *      The given source is invalid.
     */
    public function __construct($source)
    {
        $this->setSource($source);
    }

    /**
     * Returns the source associated with this filter.
     *
     * \retval string
     *      Source associated with this filter.
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Sets the source used in comparisons.
     *
     * \param $source string|object
     *      Source to match incoming events against.
     *
     * \throw Erebot_InvalidValueException
     *      The given source is invalid.
     */
    public function setSource($source)
    {
        if ($source !== NULL && !Erebot_Utils::stringifiable($source))
            throw new Erebot_InvalidValueException('Not a valid nickname');

        $this->_source = $source;
    }

    /// \copydoc Erebot_Interface_Event_Match::match()
    public function match(Erebot_Interface_Event_Base_Generic $event)
    {
        if (!($event instanceof Erebot_Interface_Event_Base_Source))
            return FALSE;

        if ($this->_source === NULL)
            return TRUE;

        return (
            $event->getConnection()->irccasecmp(
                $event->getSource(),
                (string) $this->_source
            ) == 0
        );
    }
}

