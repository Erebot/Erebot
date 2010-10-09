<?php

/**
 * \brief
 *      Interface for a module's configuration.
 *
 * This interface provides the necessary methods
 * to represent the configuration associated with
 * a module.
 */
interface iErebotModuleConfig
{
    /**
     * Creates a new instance of the ErebotModuleConfig class.
     *
     * \param $xml
     *      A SimpleXMLElement node containing the configuration
     *      settings for a module.
     */
    public function __construct(SimpleXMLElement &$xml);

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
    public function isActive($active = NULL);

    /**
     * Returns the name of this module.
     *
     * \return
     *      The name of the module represented by this module configuration.
     */
    public function getName();

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
    public function getParam($param);

    /**
     * Returns the names of the parameters for this module.
     *
     * \return
     *      An array with the names of the parameters configured
     *      for this module.
     */    
    public function getParamsNames();
}

?>
