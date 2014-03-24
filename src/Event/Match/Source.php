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
 *      A filter which matches when the source of the event
 *      equals some predefined value.
 *
 * \note
 *      Events that have no "source" never match.
 */
class Source implements \Erebot\Interfaces\Event\Match
{
    /// Source to use in the comparison, as a string.
    protected $_source;

    /**
     * Creates a new instance of the filter.
     *
     * \param $source string|object
     *      Source to match incoming events against.
     *
     * \throw Erebot::InvalidValueException
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
     * \throw Erebot::InvalidValueException
     *      The given source is invalid.
     */
    public function setSource($source)
    {
        if ($source !== NULL && !\Erebot\Utils::stringifiable($source)) {
            throw new \Erebot\InvalidValueException('Not a valid nickname');
        }

        $this->_source = $source;
    }

    public function match(\Erebot\Interfaces\Event\Base\Generic $event)
    {
        if (!($event instanceof \Erebot\Interfaces\Event\Base\Source)) {
            return false;
        }

        if ($this->_source === null) {
            return true;
        }

        $collator = $event->getConnection()->getCollator();
        return (
            $collator->compare(
                $event->getSource(),
                (string) $this->_source
            ) == 0
        );
    }
}
