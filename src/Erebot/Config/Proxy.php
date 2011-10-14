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
 *      A configuration proxy which cascades settings.
 *
 * This class is used to build a hierarchy of configurations.
 * Each level in this hierarchy may override settings applied at
 * upper levels. If no override has been made on some setting,
 * that setting retains the value it had on the preceding level.
 * This allows settings to be cascaded in the hierarchy of
 * configuration levels.
 *
 * The root of the hierarchy acts as a proxy for itself and is
 * always an instance implementing Erebot_Interface_Config_Main.
 */
class Erebot_Config_Proxy
{
    /// The current locale.
    protected $_locale;

    /// Reference to a proxified object.
    protected $_proxified;

    /// Array of modules loaded at this particular configuration level.
    protected $_modules;

    /**
     * Creates a new Erebot_Proxy_Config object.
     *
     * \param Erebot_Interface_Config_Proxy $proxified
     *      A configuration object which should be proxied through
     *      this instance. This allows settings to be cascaded.
     *
     * \param SimpleXMLElement $xml
     *      An XML node which should be used as the basis for configuration.
     */
    protected function __construct(
        Erebot_Interface_Config_Proxy   $proxified,
        SimpleXMLElement                $xml
    )
    {
        $this->_proxified   = $proxified;
        $this->_modules     = array();

        if (isset($xml['language']))
            $this->_locale = (string) $xml['language'];
        else
            $this->_locale = NULL;

        if (!isset($xml->modules->module))
            return;

        foreach ($xml->modules->module as $module) {
            /// @TODO use dependency injection instead.
            $instance = new Erebot_Config_Module($module);
            $this->_modules[$instance->getName()] = $instance;
        }
    }

    /**
     * Destructor.
     * Takes care of breaking possible circular references.
     */
    public function __destruct()
    {
        unset(
            $this->_modules,
            $this->_proxified
        );
    }

    /**
     * Copy constructor.
     */
    public function __clone()
    {
        throw new Exception("Cloning forbidden!");
    }

    /// \copydoc Erebot_Interface_Config_Proxy::getTranslator()
    public function getTranslator($component)
    {
        if (isset($this->_locale)) {
            $translator = new Erebot_I18n($component);
            $translator->setLocale(
                Erebot_Interface_I18n::LC_MESSAGES,
                $this->_locale
            );
            return $translator;
        }
        if ($this->_proxified === $this)
            throw new Erebot_NotFoundException('No translator associated');
        return $this->_proxified->getTranslator($component);
    }

    /// \copydoc Erebot_Interface_Config_Proxy::getMainCfg()
    public function getMainCfg()
    {
        if ($this->_proxified === $this)
            return $this;
        return $this->_proxified->getMainCfg();
    }

    /// \copydoc Erebot_Interface_Config_Proxy::getModules()
    public function getModules($recursive)
    {
        if (!is_bool($recursive)) {
            throw new Erebot_InvalidValueException(
                'Invalid value for recursion'
            );
        }

        if ($recursive && $this->_proxified !== $this)
            $inherited = $this->_proxified->getModules(TRUE);
        else
            $inherited = array();

        $added      = array();
        $removed    = array();
        foreach ($this->_modules as $name => $module) {
            if ($module->isActive())
                $added[]    = $name;
            else
                $removed[]  = $name;
        }

        $inherited = array_diff($inherited, $removed);
        return array_merge($added, $inherited);
    }

    /// \copydoc Erebot_Interface_Config_Proxy::getModule()
    public function getModule($moduleName)
    {
        if (!isset($this->_modules[$moduleName])) {
            if ($this->_proxified !== $this)
                return $this->_proxified->getModule($moduleName);
            throw new Erebot_NotFoundException('No such module');
        }
        return $this->_modules[$moduleName];
    }

    /**
     * Parses a text and tries to extract a boolean value.
     *
     * \param string $value
     *      The text from which a boolean should be extracted.
     *
     * \retval bool
     *      If a boolean could be extracted from the $value provided,
     *      it is returned as the corresponding PHP boolean value
     *      (either TRUE or FALSE).
     *
     * \retval NULL
     *      No boolean could be extracted.
     *
     * \note
     *      Currently, the following texts are recognized as TRUE:
     *      "true", "1", "on" & "yes", while the values
     *      "false", "0", "off" & "no" are recognized as FALSE.
     *      The comparison is case-insensitive (ie. "true" == "TrUe").
     */
    static public function _parseBool($value)
    {
        $value = strtolower($value);
        if (in_array($value, array('true', '1', 'on', 'yes'), TRUE))
            return TRUE;
        if (in_array($value, array('false', '0', 'off', 'no'), TRUE))
            return FALSE;
        return NULL;
    }

    /**
     * Parses a text and tries to extract an integer value.
     *
     * \param string $value
     *      The text from which an integer should be extracted.
     *
     * \retval int
     *      If an integer could be extracted from the $value provided,
     *      it is returned as the corresponding PHP (signed) integer value.
     *
     * \retval NULL
     *      If no integer could be extracted.
     */
    static public function _parseInt($value)
    {
        if ($value == '')
            return NULL;

        if (is_int($value))
            return $value;

        if (ctype_digit($value))
            return (int) $value;

        if (strpos('+-', $value[0]) !== FALSE &&
            ctype_digit(substr($value, 1)))
            return (int) $value;

        return NULL;
    }

    /**
     * Parses a text and tries to extract a real.
     *
     * \param string $value
     *      The text from which a real should be extracted.
     *
     * \retval float
     *      If a real could be extracted from the $value provided,
     *      it is returned as the corresponding PHP float value.
     *
     * \retval NULL
     *      If no real could be extracted.
     */
    static public function _parseReal($value)
    {
        if (!is_numeric($value))
            return NULL;

        return (double) $value;
    }

    /**
     * Returns the typed value for a module's parameter.
     *
     * \param string $module
     *      The name of the module.
     *
     * \param string $param
     *      The name of the parameter to fetch
     *      from the module's settings.
     *
     * \param mixed $default
     *      Default value if no value has been
     *      defined in the module's settings,
     *      or NULL if there is no default value.
     *
     * \param Erebot_Interface_Callable $parser
     *      Object that will be used to parse the parameter.
     *      It will receive the value of that parameter as a
     *      string and should convert it to the proper type.
     *
     * \param string $origin
     *      Name of the method the request to parse
     *      the parameter originated from.
     *
     * \param Erebot_Interface_Callable $checker
     *      Object that will be passed the parsed value
     *      and should return TRUE if it respects the
     *      type constraints defined by this checker,
     *      or FALSE if it does not.
     *
     * \retval mixed
     *      Value as parsed from the module's settings,
     *      or the default value if no value existed in
     *      the settings and it passed the type check.
     *
     * \throw Erebot_InvalidValueException
     *      The value parsed or the default value did not
     *      pass the type check.
     *
     * \throw Erebot_NotFoundException
     *
     */
    protected function _parseSomething(
                                    $module,
                                    $param,
                                    $default,
        Erebot_Interface_Callable   $parser,
                                    $origin,
        Erebot_Interface_Callable   $checker
    )
    {
        try {
            if (!isset($this->_modules[$module]))
                throw new Erebot_NotFoundException('No such module');
            $value  = $this->_modules[$module]->getParam($param);
            $value  = $parser->invoke($value);
            if ($value !== NULL)
                return $value;
            throw new Erebot_InvalidValueException(
                'Bad value in configuration'
            );
        }
        catch (Erebot_NotFoundException $e) {
            if ($this->_proxified !== $this)
                return $this->_proxified->$origin($module, $param, $default);

            if ($default === NULL)
                throw new Erebot_NotFoundException('No such parameter');

            if ($checker->invoke($default))
                return $default;
            throw new Erebot_InvalidValueException('Bad default value');
        }
    }

    /// \copydoc Erebot_Interface_Config_Proxy::parseBool()
    public function parseBool($module, $param, $default = NULL)
    {
        return $this->_parseSomething(
            $module,
            $param,
            $default,
            new Erebot_Callable(array($this, '_parseBool')),
            __FUNCTION__,
            new Erebot_Callable('is_bool')
        );
    }

    /// \copydoc Erebot_Interface_Config_Proxy::parseString()
    public function parseString($module, $param, $default = NULL)
    {
        return $this->_parseSomething(
            $module,
            $param,
            $default,
            new Erebot_Callable('strval'),
            __FUNCTION__,
            new Erebot_Callable('is_string')
        );
    }

    /// \copydoc Erebot_Interface_Config_Proxy::parseInt()
    public function parseInt($module, $param, $default = NULL)
    {
        return $this->_parseSomething(
            $module,
            $param,
            $default,
            new Erebot_Callable(array($this, '_parseInt')),
            __FUNCTION__,
            new Erebot_Callable('is_int')
        );
    }

    /// \copydoc Erebot_Interface_Config_Proxy::parseReal()
    public function parseReal($module, $param, $default = NULL)
    {
        return $this->_parseSomething(
            $module,
            $param,
            $default,
            new Erebot_Callable(array($this, '_parseReal')),
            __FUNCTION__,
            new Erebot_Callable('is_real')
        );
    }
}

