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
 *      A configuration proxy which cascades settings.
 *
 * This class is used to build a hierarchy of configurations.
 * Each level in this hierarchy may override settings applied at
 * upper levels. If no override has been made on some setting,
 * that setting retains the value it had on the preceding level.
 * This allows settings to be cascaded in the hierarchy of
 * configuration levels.
 *
 * The root of the hierarchy acts as a proxy for itself and
 * always implements Erebot::Interfaces::Config::Main.
 */
class Proxy
{
    /// The current locale.
    protected $locale;

    /// Reference to a proxified object.
    protected $proxified;

    /// Array of modules loaded at this particular configuration level.
    protected $modules;

    /**
     * Creates a new Erebot::Proxy::Config object.
     *
     * \param Erebot::Interfaces::Config::Proxy $proxified
     *      A configuration object which should be proxied through
     *      this instance. This allows settings to be cascaded.
     *
     * \param SimpleXMLElement $xml
     *      An XML node which should be used as the basis for configuration.
     */
    protected function __construct(
        \Erebot\Interfaces\Config\Proxy   $proxified,
        \SimpleXMLElement $xml
    ) {
        $this->proxified    = $proxified;
        $this->modules      = array();

        if (isset($xml['language'])) {
            $this->locale = (string) $xml['language'];
        } else {
            $this->locale = null;
        }

        if (!isset($xml->modules->module)) {
            return;
        }

        foreach ($xml->modules->module as $module) {
            /// @TODO use dependency injection instead.
            $instance = new \Erebot\Config\Module($module);
            $this->modules[$instance->getName()] = $instance;
        }
    }

    /**
     * Destructor.
     * Takes care of breaking possible circular references.
     */
    public function __destruct()
    {
        unset(
            $this->modules,
            $this->proxified
        );
    }

    /**
     * Copy constructor.
     */
    public function __clone()
    {
        throw new \Exception("Cloning forbidden!");
    }

    protected static function getBaseDir($component)
    {
        $reflector = new \ReflectionClass($component);
        $parts = explode(DIRECTORY_SEPARATOR, $reflector->getFileName());
        do {
            $last = array_pop($parts);
        } while ($last !== 'src' && count($parts));
        $parts[] = 'data';
        $parts[] = 'i18n';
        $base = implode(DIRECTORY_SEPARATOR, $parts);
        return $base;
    }

    /// \copydoc Erebot::Interfaces::Config::Proxy::getTranslator()
    public function getTranslator($component)
    {
        if (isset($this->locale)) {
            $domain = str_replace('\\', '_', ltrim($component, '\\'));
            $localedir = static::getBaseDir($component);
            return \Erebot\Intl\GettextFactory::translation($domain, $localedir, array($this->locale));
        }

        if ($this->proxified === $this) {
            throw new \Erebot\NotFoundException('No translator associated');
        }

        return $this->proxified->getTranslator($component);
    }

    /// \copydoc Erebot::Interfaces::Config::Proxy::getMainCfg()
    public function getMainCfg()
    {
        if ($this->proxified === $this) {
            return $this;
        }
        return $this->proxified->getMainCfg();
    }

    /// \copydoc Erebot::Interfaces::Config::Proxy::getModules()
    public function getModules($recursive)
    {
        if (!is_bool($recursive)) {
            throw new \Erebot\InvalidValueException(
                'Invalid value for recursion'
            );
        }

        if ($recursive && $this->proxified !== $this) {
            $inherited = $this->proxified->getModules(true);
        } else {
            $inherited = array();
        }

        $added      = array();
        $removed    = array();
        foreach ($this->modules as $name => $module) {
            if ($module->isActive()) {
                $added[]    = $name;
            } else {
                $removed[]  = $name;
            }
        }

        $inherited = array_diff($inherited, $removed);
        return array_merge($added, $inherited);
    }

    /// \copydoc Erebot::Interfaces::Config::Proxy::getModule()
    public function getModule($moduleName)
    {
        $moduleName = '\\' . ltrim($moduleName, '\\');
        if (!isset($this->modules[$moduleName])) {
            if ($this->proxified !== $this) {
                return $this->proxified->getModule($moduleName);
            }
            throw new \Erebot\NotFoundException('No such module "' .
                                                $moduleName . '"');
        }
        return $this->modules[$moduleName];
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
     *      (either \b true or \b false).
     *
     * \retval null
     *      No boolean could be extracted.
     *
     * \note
     *      Currently, the following texts are recognized as \b true:
     *      "true", "1", "on" & "yes", while the values
     *      "false", "0", "off" & "no" are recognized as \b false.
     *      The comparison is case-insensitive (ie. "true" == "TrUe").
     */
    public static function parseBoolHelper($value)
    {
        $value = strtolower($value);
        if (in_array($value, array('true', '1', 'on', 'yes'), true)) {
            return true;
        }
        if (in_array($value, array('false', '0', 'off', 'no'), true)) {
            return false;
        }
        return null;
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
     * \retval null
     *      If no integer could be extracted.
     */
    public static function parseIntHelper($value)
    {
        if ($value == '') {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (ctype_digit($value)) {
            return (int) $value;
        }

        if (strpos('+-', $value[0]) !== false && ctype_digit(substr($value, 1))) {
            return (int) $value;
        }

        return null;
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
     * \retval null
     *      If no real could be extracted.
     */
    public static function parseRealHelper($value)
    {
        if (!is_numeric($value)) {
            return null;
        }

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
     *      or \b null if there is no default value.
     *
     * \param callable $parser
     *      Object that will be used to parse the parameter.
     *      It will receive the value of that parameter as a
     *      string and should convert it to the proper type.
     *
     * \param string $origin
     *      Name of the method the request to parse
     *      the parameter originated from.
     *
     * \param callable $checker
     *      Object that will be passed the parsed value
     *      and should return \b true if it respects the
     *      type constraints defined by this checker,
     *      or \b false if it does not.
     *
     * \retval mixed
     *      Value as parsed from the module's settings,
     *      or the default value if no value existed in
     *      the settings and it passed the type check.
     *
     * \throw Erebot::InvalidValueException
     *      The value parsed or the default value did not
     *      pass the type check.
     *
     * \throw Erebot::NotFoundException
     *
     */
    protected function parseSomething(
        $module,
        $param,
        $default,
        callable $parser,
        $origin,
        callable $checker
    ) {
        try {
            if (!isset($this->modules[$module])) {
                throw new \Erebot\NotFoundException('No such module');
            }
            $value  = $this->modules[$module]->getParam($param);
            $value  = $parser($value);
            if ($value !== null) {
                return $value;
            }
            throw new \Erebot\InvalidValueException(
                'Bad value in configuration'
            );
        } catch (\Erebot\NotFoundException $e) {
            if ($this->proxified !== $this) {
                return $this->proxified->$origin($module, $param, $default);
            }

            if ($default === null) {
                throw new \Erebot\NotFoundException('No such parameter "' .
                                                    $param . '" for module "' .
                                                    $module . '"');
            }

            if ($checker($default)) {
                return $default;
            }
            throw new \Erebot\InvalidValueException('Bad default value');
        }
    }

    /// \copydoc Erebot::Interfaces::Config::Proxy::parseBool()
    public function parseBool($module, $param, $default = null)
    {
        return $this->parseSomething(
            $module,
            $param,
            $default,
            array($this, 'parseBoolHelper'),
            __FUNCTION__,
            'is_bool'
        );
    }

    /// \copydoc Erebot::Interfaces::Config::Proxy::parseString()
    public function parseString($module, $param, $default = null)
    {
        return $this->parseSomething(
            $module,
            $param,
            $default,
            'strval',
            __FUNCTION__,
            'is_string'
        );
    }

    /// \copydoc Erebot::Interfaces::Config::Proxy::parseInt()
    public function parseInt($module, $param, $default = null)
    {
        return $this->parseSomething(
            $module,
            $param,
            $default,
            array($this, 'parseIntHelper'),
            __FUNCTION__,
            'is_int'
        );
    }

    /// \copydoc Erebot::Interfaces::Config::Proxy::parseReal()
    public function parseReal($module, $param, $default = null)
    {
        return $this->parseSomething(
            $module,
            $param,
            $default,
            array($this, 'parseRealHelper'),
            __FUNCTION__,
            'is_real'
        );
    }
}
