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
 *      A filter that matches based on the type of event provided.
 */
class Type implements \Erebot\Interfaces\Event\Match
{
    /// Type to use in comparisons, as a string.
    protected $type;

    /**
     * Creates a new instance of this filter.
     *
     * \param string|object $type
     *      Type to match incoming events against.
     */
    public function __construct($type)
    {
        $this->setType($type);
    }

    /**
     * Returns the type associated with this filter.
     *
     * \retval array
     *      Array of types (as strings) associated with this filter.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the type used in comparisons.
     *
     * \param $types string|object|array
     *      Type(s) to match incoming events against,
     *      either as a string or as an instance
     *      of the type of match against.
     *      An array of strings/objects may also be passed;
     *      The filter will match if the incoming event
     *      is of any of the types given.
     *
     * \throw Erebot::InvalidValueException
     *      The given type is invalid.
     */
    public function setType($types)
    {
        if (!is_array($types)) {
            $types = array($types);
        }

        $finalTypes = array();
        foreach ($types as $type) {
            if (is_object($type)) {
                $type = get_class($type);
            }

            if (!is_string($type)) {
                throw new \Erebot\InvalidValueException('Not a valid type');
            }

            $finalTypes[] = $type;
        }
        $this->type = $finalTypes;
    }

    public function match(\Erebot\Interfaces\Event\Base\Generic $event)
    {
        foreach ($this->type as $type) {
            if ($event instanceof $type) {
                return true;
            }
        }
        return false;
    }
}
