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
 *      A class that holds a reference to an IRC numeric.
 *
 * This class holds a reference to an IRC numeric using
 * its name (eg. "RPL_WELCOME"). The actual numeric code
 * associated with this reference may vary dynamically depending
 * on the numeric profile currently loaded for the connection.
 */
class       Erebot_NumericReference
implements  Erebot_Interface_NumericReference
{
    /// IRC connection this reference is valid for.
    protected $_connection;

    /// Name of the numeric (eg. "RPL_WELCOME").
    protected $_name;

    /**
     * Constructs a new reference to a numeric.
     *
     * \param Erebot_Interface_Connection $connection
     *      Connection object this reference is valid for.
     *
     * \param string $name
     *      Name associated with the numeric this object
     *      should reference (such as "RPL_WELCOME").
     */
    public function __construct(
        Erebot_Interface_Connection $connection,
                                    $name
    )
    {
        $this->_connection  = $connection;
        $this->_name        = $name;
    }

    /// \copydoc Erebot_Interface_NumericReference::getName()
    public function getName()
    {
        return $this->_name;
    }

    /// \copydoc Erebot_Interface_NumericReference::getValue()
    public function getValue()
    {
        $profile = $this->_connection->getNumericProfile();
        return $profile[$this->_name];
    }
}
