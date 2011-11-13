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
 *      A class that holds a reference to an IRC raw numeric.
 *
 * This class holds a reference to an IRC raw numeric using
 * its name (eg. "RPL_WELCOME"). The actual numeric code
 * associated with this reference may vary dynamically depending
 * on the raw profiles currently loaded for the connection.
 */
class Erebot_RawReference
{
    /// IRC connection this reference is valid for.
    protected $_connection;

    /// Name of the raw numeric (eg. "RPL_WELCOME").
    protected $_rawName;

    /**
     * Constructs a new raw reference.
     *
     * \param Erebot_Interface_Connection $connection
     *      Connection object this reference is valid for.
     *
     * \param string $rawName
     *      Name associated with the raw numeric this object
     *      should reference (such as "RPL_WELCOME").
     */
    public function __construct(
        Erebot_Interface_Connection $connection,
                                    $rawName
    )
    {
        $this->_connection  = $connection;
        $this->_rawName     = $rawName;
    }

    /**
     * Returns the name of the raw numeric
     * this object holds a reference for.
     *
     * \retval string
     *      Name of the raw numeric, such
     *      as "RPL_WELCOME".
     */
    public function getName()
    {
        return $this->_rawName;
    }

    /**
     * Retrieve the actual numeric code currently
     * associated with this raw reference.
     *
     * \retval int
     *      Numeric code associated with this raw
     *      reference, such as 001 for "RPL_WELCOME".
     *
     * \note
     *      The actual value may vary through time
     *      as different raw profiles are loaded
     *      for the connection associated with this
     *      reference. Do not use a cache for this
     *      value.
     */
    public function getValue()
    {
        $loader = $this->_connection->getRawProfileLoader();
        return $loader->getRawByName($this->_rawName);
    }
}
