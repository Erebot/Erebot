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
implements  Erebot_Interface_RawProfileLoader
{
    protected $_profile;

    public function __construct($profile)
    {
        $this->setProfile($profile);
    }

    public function setProfile($profile)
    {
        if (!interface_exists($profile))
            throw new Erebot_InvalidValueException('Not an interface');

        $reflector = new ReflectionClass($profile);
        if (!$reflector->implementsInterface('Erebot_Interface_RawProfile'))
            throw new Erebot_InvalidValueException(
                '$profile must inherit from "Erebot_Interface_RawProfile"'
            );

        $raws               = array();
        $ifaces             = $reflector->getInterfaces();
        $ifaces[$profile]   = $reflector;

        foreach ($ifaces as $iface_name => $iface) {
            /* There is no direct way to get the constants defined in
             * the interface without also retrieving inherited constants.
             * So we have to take care of the filtering ourselves. */
            $constants = $iface->getConstants();
            foreach ($iface->getInterfaces() as $parent_iface)
                $constants = array_diff($constants, $parent_iface->getConstants());

            foreach (array_keys($constants) as $const_name) {
                // We use the reflector to get the value to support overloading.
                $const_value = $reflector->getConstant($const_name);

                if (!is_int($const_value) || $const_value < 0 || $const_value > 999)
                    continue;

                /* We look for two constants defined with the same value.
                 * To allow aliases, two constants with the same value defined
                 * in the same interface are treated as not conflicting. */
                if (isset($raws[$const_value]) &&
                    !isset($raws[$const_value][$iface_name]) &&
                    count($raws[$const_value]) > 1) {
                    reset($raws[$const_value]);
                    $remote_iface = key($raws[$const_value]);
                    $remote_const = $raws[$const_value][$remote_iface][0];
                    throw new Erebot_InvalidValueException(
                        "Definition of $iface::$const_name conflicts ".
                        "with that of $remote_iface::$remote_const"
                    );
                }
                $raws[$const_value][$iface_name][] = $const_name;
            }
        }

        $this->_profile = $reflector;
    }

    public function getProfile()
    {
        return $this->_profile->getName();
    }

    public function getRawByName($rawName)
    {
        if (!is_string($rawName))
            throw new Erebot_InvalidValueException('Not a valid name');

        if (!$this->_profile->hasConstant($rawName))
            return NULL;

        return $this->_profile->getConstant($rawName);
    }
}

