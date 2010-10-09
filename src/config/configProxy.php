<?php

ErebotUtils::incl('../exceptions/NotFound.php');
ErebotUtils::incl('../exceptions/InvalidValue.php');
ErebotUtils::incl('moduleConfig.php');
ErebotUtils::incl('../i18n.php');

/**
 * \brief
 *      A configuration proxy which cascades settings.
 *
 * This class is used to build a hierarchy of configurations.
 * Each level in this hierarchy may override settings applied at
 * upper levels. In no override has been made on some setting,
 * this setting retains the value it had at the preceding level.
 * This allows settings to be cascaded in the hierarchy of
 * configuration levels.
 *
 * The root of the hierarchy acts as a proxy for itself and is
 * always an instance of ErebotMainConfig.
 */
class ErebotConfigProxy
{
    /// Reference to an ErebotI18n object for translations.
    protected $translator;

    /// Reference to a proxified object.
    protected $proxified;

    /// Array of modules loaded at this particular configuration level.
    protected $modules;

    /**
     * Creates new instance of a ErebotConfigProxy object.
     *
     * \param $proxified
     *      An ErebotConfigProxy object which should be proxied through
     *      this instance. This allows settings to be cascaded.
     *
     * \param $xml
     *      A SimpleXMLElement representing the XML node which should
     *      be used as the basis for configuration.
     */
    protected function __construct(ErebotConfigProxy &$proxified, SimpleXMLElement &$xml)
    {
        $this->proxified    =&  $proxified;
        $this->modules      =   array();

        if (isset($xml['language']))
            $this->translator = new ErebotI18n((string) $xml['language']);
        else
            $this->translator = NULL;

        if (!isset($xml->modules->module))
            return;

        foreach ($xml->modules->module as $module) {
            $instance = new ErebotModuleConfig($module);
            $this->modules[$instance->getName()] = $instance;
        }
    }

    /**
     * Destructor for ErebotConfigProxy instances.
     * Takes care of breaking possible circular references.
     */
    public function __destruct()
    {
        unset($proxified);
    }

    /**
     * Returns the appropriate ErebotI18n object to apply for translations
     * at this configuration level.
     *
     * \param $component
     *      Name of the component we're interested in.
     *      This should be set to the name of a module or "Erebot"
     *      for the core translators.
     *
     * \return
     *      An appropriate ErebotI18n object.
     *
     * \throw EErebotNotFound
     *      No appropriate translator exists for this configuration level.
     */
    public function getTranslator($component)
    {
        if (isset($this->translator))
            return new ErebotI18nWrapper($this->translator, $component);
        if ($this->proxified === $this)
            throw new EErebotNotFound('No translator associated');
        return $this->proxified->getTranslator($component);
    }

    /**
     * Returns the ErebotMainConfig object associated with this hierarchy
     * of configurations.
     *
     * \return
     *      The ErebotMainConfig for this hierarchy.
     */
    public function & getMainCfg()
    {
        if ($this->proxified === $this)
            return $this;
        return $this->proxified->getMainCfg();
    }

    /**
     * \note Currently, does not do anything.
     * \XXX Add configuration export capabilities to the bot.
     */
    public function export($indent = 0)
    {
    }

    /**
     * Returns a list with the names of all currently active modules.
     *
     * \param $recursive
     *      If set to TRUE, the list is retrieved recursively by merging
     *      the lists obtained at this level and all levels above it.
     *      If set to FALSE, only the modules activated at this particular
     *      level are returned.
     *
     * \return
     *      A list with the names of currently active modules.
     */
    public function getModules($recursive)
    {
        if (!is_bool($recursive))
            throw new EErebotInvalidValue('Invalid value for recursion');

        if ($recursive && $this->proxified !== $this)
            $inherited = $this->proxified->getModules(TRUE);
        else
            $inherited = array();

        $added      = array();
        $removed    = array();
        foreach ($this->modules as $name => &$module) {
            if ($module->isActive())
                $added[]    = $name;
            else
                $removed[]  = $name;
        }
        unset($module);

        $inherited = array_diff($inherited, $removed);
        return array_merge($added, $inherited);
    }

    /**
     * Parses a text and tries to extract a boolean value.
     *
     * \param $value
     *      The text from which a boolean should be extracted.
     *
     * \return
     *      If a boolean could be extracted from the $value provided,
     *      it is returned as the corresponding PHP boolean value
     *      (either TRUE or FALSE). If no boolean could be extracted,
     *      NULL is returned instead.
     */
    protected function _parseBool($value)
    {
        if (!strcasecmp($value, 'true') || $value == '1' || !strcasecmp($value, 'on'))
            return TRUE;
        if (!strcasecmp($value, 'false') || $value == '0' || !strcasecmp($value, 'off'))
            return FALSE;
        return NULL;
    }

    /**
     * Returns the boolean value for a setting in some module.
     *
     * \param $module
     *      The name of the module from which to retrieved the setting.
     *
     * \param $param
     *      The name of the parameter we are interested in.
     *
     * \param $default
     *      An optional default value in case no value has been set
     *      at the configuration level.
     *
     * \return
     *      The boolean value for that particular module and parameter.
     *
     * \throw EErebotNotFound
     *      The given $module or $param name could not be found.
     *
     * \throw EErebotInvalidValue
     *      A value could be retrieved, but its type is not the one we
     *      expected (it was NOT a boolean). Either the configuration file
     *      contains an invalid value, or no value has been defined in the
     *      configuration file and the $default value is invalid.
     *
     * \note
     *      This method tries to retrieve the value recursively by traversing
     *      the configuration hierarchy.
     */
    public function parseBool($module, $param, $default = NULL)
    {
        try {
            if (!isset($this->modules[$module]))
                throw new EErebotNotFound('No such module');
            $value = $this->modules[$module]->getParam($param);
            $value = $this->_parseBool($value);
            if ($value !== NULL)
                return $value;
            throw new EErebotInvalidValue('Bad value in configuration');
        }
        catch (EErebotNotFound $e) {
            if ($this->proxified !== $this)
                return $this->proxified->parseBool($module, $param, $default);

            if ($default === NULL)
                throw new EErebotNotFound('No such parameter');

            $value = $this->_parseBool($default);
            if ($value !== NULL)
                return $value;
            throw new EErebotInvalidValue('Bad default value');
        }
    }

    /**
     * Returns the string value for a setting in some module.
     *
     * \param $module
     *      The name of the module from which to retrieved the setting.
     *
     * \param $param
     *      The name of the parameter we are interested in.
     *
     * \param $default
     *      An optional default value in case no value has been set
     *      at the configuration level.
     *
     * \return
     *      The string value for that particular module and parameter.
     *
     * \throw EErebotNotFound
     *      The given $module or $param name could not be found.
     *
     * \throw EErebotInvalidValue
     *      A value could be retrieved, but its type is not the one we
     *      expected (it was NOT a string). Either the configuration file
     *      contains an invalid value, or no value has been defined in the
     *      configuration file and the $default value is invalid.
     *
     * \note
     *      This method tries to retrieve the value recursively by traversing
     *      the configuration hierarchy.
     */
    public function parseString($module, $param, $default = NULL)
    {
        try {
            if (!isset($this->modules[$module]))
                throw new EErebotNotFound('No such module');
            return $this->modules[$module]->getParam($param);
        }
        catch (EErebotNotFound $e) {
            if ($this->proxified !== $this)
                return $this->proxified->parseString($module, $param, $default);

            if ($default === NULL)
                throw new EErebotNotFound('No such parameter');

            if (is_string($default))
                return $default;
            throw new EErebotInvalidValue('Bad default value');
        }
    }

    /**
     * Parses a text and tries to extract an integer value.
     *
     * \param $value
     *      The text from which an integer should be extracted.
     *
     * \return
     *      If an integer could be extracted from the $value provided,
     *      it is returned as the corresponding PHP (signed) integer value.
     *      If no integer could be extracted, NULL is returned instead.
     */
    protected function _parseInt($value)
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
     * Returns the integer value for a setting in some module.
     *
     * \param $module
     *      The name of the module from which to retrieved the setting.
     *
     * \param $param
     *      The name of the parameter we are interested in.
     *
     * \param $default
     *      An optional default value in case no value has been set
     *      at the configuration level.
     *
     * \return
     *      The boolean value for that particular module and parameter.
     *
     * \throw EErebotNotFound
     *      The given $module or $param name could not be found.
     *
     * \throw EErebotInvalidValue
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
     *              throw new EErebotInvalidValue(
     *                  "Incoming port value is out of range (1-65535)");
     *          // Else, use the $port value to create a listening socket, etc.
     *      \endcode
     *
     * \note
     *      This method tries to retrieve the value recursively by traversing
     *      the configuration hierarchy.
     */
    public function parseInt($module, $param, $default = NULL)
    {
        try {
            if (!isset($this->modules[$module]))
                throw new EErebotNotFound('No such module');
            $value = $this->modules[$module]->getParam($param);
            $value = $this->_parseInt($value);
            if ($value !== NULL)
                return $value;
            throw new EErebotInvalidValue('Bad value in configuration');
        }
        catch (EErebotNotFound $e) {
            if ($this->proxified !== $this)
                return $this->proxified->parseInt($module, $param, $default);

            if ($default === NULL)
                throw new EErebotNotFound('No such parameter');

            $value = $this->_parseInt($default);
            if ($value !== NULL)
                return $value;
            throw new EErebotInvalidValue('Bad default value');
        }
    }

    /**
     * Parses a text and tries to extract a real.
     *
     * \param $value
     *      The text from which a real should be extracted.
     *
     * \return
     *      If a real could be extracted from the $value provided,
     *      it is returned as the corresponding PHP double value.
     *      If no real could be extracted, NULL is returned instead.
     */
    protected function _parseReal($value)
    {
        if (!is_numeric($value))
            return NULL;

        return (double) $value;
    }

    /**
     * Returns the real number for a setting in some module.
     *
     * \param $module
     *      The name of the module from which to retrieved the setting.
     *
     * \param $param
     *      The name of the parameter we are interested in.
     *
     * \param $default
     *      An optional default value in case no value has been set
     *      at the configuration level.
     *
     * \return
     *      The real number for that particular module and parameter.
     *
     * \throw EErebotNotFound
     *      The given $module or $param name could not be found.
     *
     * \throw EErebotInvalidValue
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
     *              throw EErebotInvalidValue(
     *                  "Invalid price: we're loosing money here!");
     *      \endcode
     *
     * \note
     *      This method tries to retrieve the value recursively by traversing
     *      the configuration hierarchy.
     */
    public function parseReal($module, $param, $default = NULL)
    {
        try {
            if (!isset($this->modules[$module]))
                throw new EErebotNotFound('No such module');
            $value = $this->modules[$module]->getParam($param);
            $value = $this->_parseReal($value);
            if ($value !== NULL)
                return $value;
            throw new EErebotInvalidValue('Bad value in configuration');
        }
        catch (EErebotNotFound $e) {
            if ($this->proxified !== $this)
                return $this->proxified->parseReal($module, $param, $default);

            if ($default === NULL)
                throw new EErebotNotFound('No such parameter');

            $value = $this->_parseReal($default);
            if ($value !== NULL)
                return $value;
            throw new EErebotInvalidValue('Bad default value');
        }
    }
}

?>
