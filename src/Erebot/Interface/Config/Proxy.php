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
 *      Interface for a generic cascading configuration.
 *
 * This interface provides methods which are common
 * to different configuration interfaces.
 */
interface Erebot_Interface_Config_Proxy
{
    /**
     * Returns the appropriate ErebotI18n object to apply for translations
     * at this configuration level.
     *
     * \param string $component
     *      Name of the component we're interested in.
     *      This should be set to the name of a module or "Erebot"
     *      for the core translator.
     *
     * \retval iErebotI18n
     *      A translator object.
     *
     * \throw Erebot_NotFoundException
     *      No appropriate translator exists for this configuration level.
     */
    public function getTranslator($component);

    /**
     * Returns the ErebotMainConfig object associated with this hierarchy
     * of configurations.
     *
     * \retval iErebotMainConfig
     *      The main configuration of the bot.
     */
    public function & getMainCfg();

    /**
     * Returns a list with the names of all currently active modules.
     *
     * \param bool $recursive
     *      If set to TRUE, the list is retrieved recursively by merging
     *      the lists obtained at this level and all levels above it.
     *      If set to FALSE, only the modules activated at this particular
     *      level are returned.
     *
     * \retval list(string)
     *      A list with the names of currently active modules.
     */
    public function getModules($recursive);

    /**
     * Returns the instance of the module configuration with the given name.
     *
     * \param string $moduleName
     *      Name of the module whose configuration we're insterested in.
     *
     * \retval iErebotModuleConfig
     *      Instance of the module's configuration.
     *
     * \throw Erebot_NotFoundException
     */
     public function & getModule($moduleName);

    /**
     * Returns the boolean value for a setting in some module.
     *
     * \param string $module
     *      The name of the module from which to retrieved the setting.
     *
     * \param string $param
     *      The name of the parameter we are interested in.
     *
     * \param bool $default
     *      An optional default value in case no value has been set
     *      at the configuration level.
     *
     * \retval bool
     *      The boolean value for that particular module and parameter.
     *
     * \throw Erebot_NotFoundException
     *      The given $module or $param name could not be found.
     *
     * \throw Erebot_InvalidValueException
     *      A value could be retrieved, but its type is not the one we
     *      expected (it was NOT a boolean). Either the configuration file
     *      contains an invalid value, or no value has been defined in the
     *      configuration file and the $default value is invalid.
     *
     * \note
     *      This method tries to retrieve the value recursively by traversing
     *      the configuration hierarchy.
     */
    public function parseBool($module, $param, $default = NULL);

    /**
     * Returns the string value for a setting in some module.
     *
     * \param string $module
     *      The name of the module from which to retrieved the setting.
     *
     * \param string $param
     *      The name of the parameter we are interested in.
     *
     * \param string $default
     *      An optional default value in case no value has been set
     *      at the configuration level.
     *
     * \retval string
     *      The string value for that particular module and parameter.
     *
     * \throw Erebot_NotFoundException
     *      The given $module or $param name could not be found.
     *
     * \throw Erebot_InvalidValueException
     *      A value could be retrieved, but its type is not the one we
     *      expected (it was NOT a string). Either the configuration file
     *      contains an invalid value, or no value has been defined in the
     *      configuration file and the $default value is invalid.
     *
     * \note
     *      This method tries to retrieve the value recursively by traversing
     *      the configuration hierarchy.
     */
    public function parseString($module, $param, $default = NULL);

    /**
     * Returns the integer value for a setting in some module.
     *
     * \param string $module
     *      The name of the module from which to retrieved the setting.
     *
     * \param string $param
     *      The name of the parameter we are interested in.
     *
     * \param int $default
     *      An optional default value in case no value has been set
     *      at the configuration level.
     *
     * \retval int
     *      The interger value for that particular module and parameter.
     *
     * \throw Erebot_NotFoundException
     *      The given $module or $param name could not be found.
     *
     * \throw Erebot_InvalidValueException
     *      A value could be retrieved, but its type is not the one we
     *      expected (it was NOT an integer). Either the configuration file
     *      contains an invalid value, or no value has been defined in the
     *      configuration file and the $default value is invalid.
     *
     * \note
     *      The returned value is always signed. There is currently no way to
     *      restrict the range of allowed values using this function. If you
     *      need to apply restrictions, do it in the calling function:
     *      \code
     *          $port = $config->parseInt('server', 'incomingPort');
     *          if ($port <= 0 || $port > 65535)
     *              throw new Erebot_InvalidValueException(
     *                  "Incoming port value is out of range (1-65535)");
     *          // Else, use the $port value to create a listening socket, etc.
     *      \endcode
     *
     * \note
     *      This method tries to retrieve the value recursively by traversing
     *      the configuration hierarchy.
     */
    public function parseInt($module, $param, $default = NULL);

    /**
     * Returns the real number for a setting in some module.
     *
     * \param string $module
     *      The name of the module from which to retrieved the setting.
     *
     * \param string $param
     *      The name of the parameter we are interested in.
     *
     * \param float $default
     *      An optional default value in case no value has been set
     *      at the configuration level.
     *
     * \retval float
     *      The real number for that particular module and parameter.
     *
     * \throw Erebot_NotFoundException
     *      The given $module or $param name could not be found.
     *
     * \throw Erebot_InvalidValueException
     *      A value could be retrieved, but its type is not the one we
     *      expected (it was NOT a real). Either the configuration file
     *      contains an invalid value, or no value has been defined in the
     *      configuration file and the $default value is invalid.
     *
     * \note
     *      The returned value is not restricted in nay way. If you need to
     *      apply restrictions, do it in the calling function:
     *      \code
     *          $price = $config->parseReal('prices', '$item);
     *          if ($price <= 0.1)
     *              throw Erebot_InvalidValueException(
     *                  "Invalid price: we're loosing money here!");
     *      \endcode
     *
     * \note
     *      This method tries to retrieve the value recursively by traversing
     *      the configuration hierarchy.
     */
    public function parseReal($module, $param, $default = NULL);
}

