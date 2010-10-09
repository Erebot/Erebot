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

    // Documented in the interface.
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

    // Documented in the interface.
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

    // Documented in the interface.
    public function getName()
    {
        return $this->name;
    }

    // Documented in the interface.
    public function getParam($param)
    {
        if (!is_string($param))
            throw new EErebotInvalidValue('Bad parameter name');
        if (!isset($this->params[$param]))
            throw new EErebotNotFound('No such parameter');
        return $this->params[$param];
    }

    // Documented in the interface.
    public function getParamsNames()
    {
        return array_keys($this->params);
    }
}

?>
