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

namespace Erebot\Config;

/**
 * \brief
 *      This class stores configuration data about modules.
 *
 * For each module at any configuration level, an instance of
 * Erebot::Config::Module will be created.
 */
class Module implements \Erebot\Interfaces\Config\Module
{
    /// A dictionary mapping parameter names to their textual value.
    protected $_params;

    /// A boolean indicating whether the module is active or not.
    protected $_active;

    /// The name of the module.
    protected $_name;

    /**
     * Creates a new configuration object for a module.
     *
     * \param SimpleXMLElement $xml
     *      An XML node containing the configuration
     *      settings for the module.
     */
    public function __construct(SimpleXMLElement $xml)
    {
        $this->_name    = (string) $xml['name'];
        $this->_params  = array();
        $active         = strtoupper(
            isset($xml['active']) ?
            (string) $xml['active'] :
            'TRUE'
        );
        $this->_active  = in_array($active, array('1', 'TRUE', 'ON', 'YES'));

        foreach ($xml->param as $param) {
            $prm    = (string) $param['name'];
            $val    = (string) $param['value'];
            $this->_params[$prm] = $val;
        }
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
    }

    /// \copydoc Erebot::Interfaces::Config::Module::isActive()
    public function isActive($active = NULL)
    {
        $res = $this->_active;
        if ($active !== NULL) {
            if (!is_bool($active)) {
                throw new \Erebot\InvalidValueException(
                    'Invalid activation value'
                );
            }
            $this->_active = $active;
        }
        return $res;
    }

    /// \copydoc Erebot::Interfaces::Config::Module::getName()
    public function getName()
    {
        return $this->_name;
    }

    /// \copydoc Erebot::Interfaces::Config::Module::getParam()
    public function getParam($param)
    {
        if (!is_string($param))
            throw new \Erebot\InvalidValueException('Bad parameter name');
        if (!isset($this->_params[$param]))
            throw new \Erebot\NotFoundException('No such parameter');
        return $this->_params[$param];
    }

    /// \copydoc Erebot::Interfaces::Config::Module::getParamsNames()
    public function getParamsNames()
    {
        return array_keys($this->_params);
    }
}

