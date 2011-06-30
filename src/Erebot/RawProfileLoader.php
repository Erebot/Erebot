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

class       Erebot_RawProfileLoader
implements  Erebot_Interface_RawProfileLoader,
            ArrayAccess,
            Countable
{
    protected $_profiles;

    public function __construct($profiles)
    {
        $this->setProfiles($profiles);
    }

    protected function _getReflect($profile)
    {
        if (is_object($profile))
            $profile = get_class($profile);
        else if (!is_string($profile))
            throw new Erebot_InvalidValueException('Not a valid profile');

        $refl = new ReflectionClass($profile);
        if (!$refl->implementsInterface('Erebot_Interface_RawProfile')) {
            throw new Erebot_InvalidValueException(
                'The profile must implement or extend '.
                '"Erebot_Interface_RawProfile"'
            );
        }
        return $refl;
    }

    public function setProfiles($profiles)
    {
        $this->_profiles = array();
        if (!is_array($profiles))
            $profiles = array($profiles);

        foreach ($profiles as $profile) {
            $this->_profiles[] = $this->_getReflect($profile);
        }
    }

    public function getProfiles()
    {
        $names = array();
        foreach ($this->_profiles as $profile) {
            $names[] = $profile->getName();
        }
        return $names;
    }

    public function getRawByName($rawName)
    {
        if (!is_string($rawName))
            throw new Erebot_InvalidValueException('Not a valid name');

        foreach ($this->_profiles as $profile) {
            if (!$profile->hasConstant($rawName))
                continue;

            $const_value = $profile->getConstant($rawName);
            if (!is_int($const_value) || $const_value < 0 || $const_value > 999)
                continue;

            return $const_value;
        }
        return NULL;
    }

    /**
     * \copydoc Countable::count()
     * \see
     *      docs/additions/iface_Countable.php
     */
    public function count()
    {
        return count($this->_profiles);
    }

    /**
     * \copydoc ArrayAccess::offsetExists()
     * \see
     *      docs/additions/iface_ArrayAccess.php
     */
    public function offsetExists($offset)
    {
        return isset($this->_profiles[$offset]);
    }

    /**
     * \copydoc ArrayAccess::offsetGet()
     * \see
     *      docs/additions/iface_ArrayAccess.php
     */
    public function offsetGet($offset)
    {
        return $this->_profiles[$offset]->getName();
    }

    /**
     * \copydoc ArrayAccess::offsetSet()
     * \see
     *      docs/additions/iface_ArrayAccess.php
     */
    public function offsetSet($offset, $value)
    {
        $this->_profiles[$offset] = $this->_getReflect($value);
    }

    /**
     * \copydoc ArrayAccess::offsetUnset()
     * \see
     *      docs/additions/iface_ArrayAccess.php
     */
    public function offsetUnset($offset)
    {
        unset($this->_profiles[$offset]);
        $this->_profiles = array_values($this->_profiles);
    }
}

