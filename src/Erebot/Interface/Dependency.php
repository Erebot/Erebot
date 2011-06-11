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
 *      Interface to express a dependency on a versioned item.
 */
interface Erebot_Interface_Dependency
{
    /**
     * Constructs a dependency.
     *
     * \param string $dependency
     *      A dependency specification like "foo >= 42".
     *      The general form of a dependency specification
     *      is "<name> [<operator> <version>]", where the
     *      \b operator and \b version may be ommitted
     *      to indicate that \b name must be installed,
     *      without depending on a specific version.
     */
    public function __construct($dependency);

    /**
     * Returns the name of the dependency.
     *
     * \retval string
     *      The dependency's name.
     */
    public function getName();

    /**
     * Returns the operator for the dependency.
     *
     * \retval string
     *      The dependency's operator.
     *
     * \retval NULL
     *      No specific version is required.
     */
    public function getOperator();

    /**
     * Returns the version for the dependency.
     *
     * \retval string
     *      The dependency's version.
     *
     * \retval NULL
     *      No specific version is required.
     */
    public function getVersion();

    /**
     * Returns the full dependency specification,
     * as a string.
     *
     * \retval string
     *      The dependency specification.
     */
    public function __toString();
}

