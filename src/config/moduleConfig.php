<?php

ErebotUtils::incl('../ifaces/moduleConfig.php');

/**
 * \brief
 *      This class stores configuration data about modules.
 *
 * For each module at any configuration level, an instance of
 * ErebotModuleConfig will be created.
 */
class       ErebotModuleConfig
implements  iErebotModuleConfig
{
    /// A dictionary mapping parameter names to their textual value.
    protected $params;

    /// A boolean indicating whether the module is active or not.
    protected $active;

    /// The name of the module.
    protected $name;

    /**
     * Creates a new instance of the ErebotModuleConfig class.
     *
     * \param $xml
     *      A SimpleXMLElement node containing the configuration
     *      settings for a module.
     */
    public function __construct(SimpleXMLElement &$xml)
    {
        $this->name     = (string) $xml['name'];
        $this->params   = array();
        $active         = strtoupper(
                            isset($xml['active']) ?
                            (string) $xml['active'] :
                            'TRUE');
        $this->active   = in_array($active, array('1', 'TRUE', 'ON', 'YES'));

        foreach ($xml->param as $param) {
            $prm    = (string) $param['name'];
            $val    = (string) $param['value'];
            $this->params[$prm] = $val;
        }
    }

    /**
     * Gets/sets the active flag on this module.
     *
     * \param $active
     *      An optional parameter which can be used to change the
     *      value of the active flag for this module.
     *
     * \return
     *      Returns the value of the active flag as it was before
     *      this method was called.
     *
     * \throw EErebotInvalidValue
     *      The new value for the active flag is not a valid boolean
     *      value.
     */
    public function isActive($active = NULL)
    {
        $res = $this->active;
        if ($active !== NULL) {
            if (!is_bool($active))
                throw new EErebotInvalidValue('Invalid activation value');
            $this->active = $active;
        }
        return $res;
    }

    /**
     * Returns the name of this module.
     *
     * \return
     *      The name of the module represented by this module configuration.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the value for the given parameter.
     *
     * \param $param
     *      The name of the parameter we are interested in.
     *
     * \return
     *      The value of the parameter, as a string.
     *
     * \throw EErebotInvalidValue
     *      The $param argument was not a valid parameter name.
     *
     * \throw EErebotNotFound
     *      There was no parameter with this name defined in the
     *      configuration file.
     */
    public function getParam($param)
    {
        if (!is_string($param))
            throw new EErebotInvalidValue('Bad parameter name');
        if (!isset($this->params[$param]))
            throw new EErebotNotFound('No such parameter');
        return $this->params[$param];
    }
}

?>
