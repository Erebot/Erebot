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
 *      Class that loads raw profiles.
 *
 * This class can be used to load/unload/swap raw profiles
 * on the fly. This class implements the Countable and
 * ArrayAccess magical interfaces, so you may use count()
 * and the array access operator on it for ease of use.
 *
 * Usually, you never pass instances as the profiles to load,
 * instead relying only on interfaces' names:
 * \code
 *      $p1         = 'RawProfile1_Interface';
 *      $loader     = new Erebot_RawProfileLoader($1);
 *      $loader[]   = 'RawProfile2_Interface';
 *      unset($loader[0]);
 * \endcode
 */
class       Erebot_RawProfileLoader
implements  Erebot_Interface_RawProfileLoader
{
    /// Array of profiles currently loaded.
    protected $_profiles;

    /**
     * Constructs a new raw profile loader and initializes
     * it with a set of profiles.
     *
     * \param mixed $profiles
     *      Either a single profile or an array of profiles
     *      to load. A profile can be expressed using either
     *      the name of the class/interface where it resides
     *      (as a string) or by passing an instance of the
     *      profile's class (as an object).
     */
    public function __construct($profiles)
    {
        $this->setProfiles($profiles);
    }

    /**
     * Returns a reflection object representing
     * the given profile.
     *
     * \param mixed $profile
     *      A single profile, expressed using either the name
     *      of the class/interface where it resides (a string)
     *      or by passing an instance of the profile's class
     *      (an object).
     *
     * \retval Reflector
     *      A ReflectionClass object matching
     *      the given profile.
     *
     * \throw Erebot_InvalidValueException
     *      The given $profile is invalid, either because it
     *      does not represent a profile at all or because
     *      it does not conform to the expected interface.
     */
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

    /// \copydoc Erebot_Interface_RawProfileLoader::setProfiles()
    public function setProfiles($profiles = array())
    {
        if (!is_array($profiles))
            $profiles = array($profiles);

        $newProfiles = array();
        foreach ($profiles as $profile) {
            $newProfiles[] = $this->_getReflect($profile);
        }
        $this->_profiles = $newProfiles;
    }

    /// \copydoc Erebot_Interface_RawProfileLoader::getProfiles()
    public function getProfiles()
    {
        $names = array();
        foreach ($this->_profiles as $profile) {
            $names[] = $profile->getName();
        }
        return $names;
    }

    /// \copydoc Erebot_Interface_RawProfileLoader::getRawByName()
    public function getRawByName($rawName)
    {
        if (!is_string($rawName))
            throw new Erebot_InvalidValueException('Not a valid name');

        $seen       = array();
        $rawName    = strtoupper($rawName);
        while (!in_array($rawName, $seen)) {
            $seen[] = $rawName;
            foreach (array_reverse($this->_profiles) as $profile) {
                if (!$profile->hasConstant($rawName))
                    continue;

                $constValue = $profile->getConstant($rawName);
                if (is_int($constValue) &&
                    $constValue > 0 &&
                    $constValue <= 999)
                    return $constValue;
                else if (is_string($constValue)) {
                    $rawName = strtoupper($constValue);
                    continue 2;
                }
            }
            return NULL;
        }

        throw new Erebot_InvalidValueException('Loop detected');
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
     *
     * \note
     *      The profiles are reordered internally
     *      each time one of the profiles has been
     *      removed from the loader.
     */
    public function offsetUnset($offset)
    {
        unset($this->_profiles[$offset]);
        $this->_profiles = array_values($this->_profiles);
    }
}

