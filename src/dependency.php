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
 *      Represents a dependency on some item.
 */
class ErebotDependency
{
    /// Name of the item we depend on.
    protected $_name;
    /// Constraint on the version (comparison operator).
    protected $_operator;
    /// Constraint on the version (version information).
    protected $_version;

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
    public function __construct($dependency)
    {
        $opTokens   = ' !<>=';
        $opMapping  =   array(
                            "<"     => "<",
                            "lt"    => "<",
                            "<="    => "<=",
                            "le"    => "<=",
                            ">"     => ">",
                            "gt"    => ">",
                            ">="    => ">=",
                            "ge"    => ">=",
                            "=="    => "=",
                            "="     => "=",
                            "eq"    => "=",
                            "!="    => "!=",
                            "<>"    => "!=",
                            "ne"    => "!=",
                        );

        $dependency     = trim($dependency);
        $depNameEnd     = strcspn($dependency, $opTokens);
        $depName        = substr($dependency, 0, $depNameEnd);

        $len = strlen($dependency);
        if ($depNameEnd == $len)
            $depOp = $depVer = NULL;

        else {
            $depVerStart    = $len - strcspn(strrev($dependency), $opTokens);
            if ($depVerStart <= $depNameEnd)
                throw new EErebotInvalidValue(
                    'Invalid dependency specification');

            $depVer         = strtolower(substr($dependency, $depVerStart));
            $depOp          = strtolower(
                trim(
                    substr(
                        $dependency,
                        $depNameEnd,
                        $depVerStart - $depNameEnd
                    ),
                    ' '
                )
            );

            if (!isset($opMapping[$depOp]))
                throw new EErebotInvalidValue(
                    'Invalid dependency operator ('.$depOp.')');

            if ($depVer == '')
                throw new EErebotInvalidValue(
                    'Invalid dependency specification');
        }

        $this->_name        = $depName;
        $this->_operator    = ($depOp === NULL ? NULL : $opMapping[$depOp]);
        $this->_version     = $depVer;
    }

    /**
     * Returns the name of the dependency.
     *
     * \retval
     *      The dependency's name.
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Returns the operator for the dependency.
     *
     * \retval string
     *      The dependency's operator.
     *
     * \retval NULL
     *      No specific version is required.
     */
    public function getOperator()
    {
        return $this->_operator;
    }

    /**
     * Returns the version for the dependency.
     *
     * \retval string
     *      The dependency's version.
     *
     * \retval NULL
     *      No specific version is required.
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     * Returns the full dependency specification,
     * as a string.
     *
     * \retval
     *      The dependency specification.
     */
    public function __toString()
    {
        if ($this->_version === NULL)
            return $this->_name;
        return  $this->_name." ".
                $this->_operator." ".
                $this->_version;
    }
}

