<?php

ErebotUtils::incl('configProxy.php');
ErebotUtils::incl('networkConfig.php');
ErebotUtils::incl('../logging/src/logging.php');
ErebotUtils::incl('../logging/src/config/XML.php');
ErebotUtils::incl('../streams/xglob.php');
ErebotUtils::incl('../ifaces/mainConfig.php');

/**
 * \brief
 *      Contains the main (general) configuration for Erebot.
 *
 * This class deals with settings which affect the whole bot such as its
 * version string.
 * It also contains references to instances of the ErebotNetworkConfig class.
 */
class       ErebotMainConfig
extends     ErebotConfigProxy
implements  iErebotMainConfig
{
    /// The (relative or absolute) path to the configuration, if available.
    protected $configFile;

    /// A list of ErebotNetworkConfig objects.
    protected $networks;

    /// The bot's version string.
    protected $version;

    /// The bot's current timezone.
    protected $timezone;

    /// The prefix used to recognize commands.
    protected $commands_prefix;

    // Documented in the interface.
    public function __construct($configData, $source)
    {
        $this->load($configData, $source);
    }

    /**
     * Destructs ErebotMainConfig instances.
     */
    public function __destruct()
    {
        parent::__destruct();
        unset($this->modules, $this->networks);
    }

    // Documented in the interface.
    public function __clone()
    {
        throw new Exception('Cloning is forbidden');
    }

    private function __stripXGlobWrappers(&$domxml)
    {
        $xpath = new DOMXPath($domxml);
        $xpath->registerNamespace('xglob', ErebotWrapperXGlob::XMLNS);
        $wrappers = $xpath->query('//xglob:'.ErebotWrapperXGlob::TAG);
        foreach ($wrappers as $wrapper) {
            for ($i = $wrapper->childNodes->length; $i > 0; $i--) {
                $wrapper->parentNode->insertBefore(
                    $wrapper->childNodes->item($i - 1),
                    $wrapper->nextSibling);
            }
            $wrapper->parentNode->removeChild($wrapper);
        }
    }

    // Documented in the interface.
    public function load($configData, $source)
    {
        $possible_sources =     array(
                                    ErebotMainConfig::LOAD_FROM_FILE,
                                    ErebotMainConfig::LOAD_FROM_STRING,
                                );
        if (!in_array($source, $possible_sources, TRUE))
            throw new EErebotInvalidValue('Invalid $source');

        if (is_string($configData) && $configData != '') {
            if ($source == ErebotMainConfig::LOAD_FROM_FILE)
                $file = $configData[0] == '/' ? $configData :
                        dirname(dirname(__FILE__)).'/'.$configData;
            else
                $file = NULL;
        }
        else
            throw new EErebotInvalidValue('Invalid configuration file');

        $schema = dirname(__FILE__).'/config.rng';
        $ue     = libxml_use_internal_errors(TRUE);
        $domxml = new DomDocument;
        if ($source == ErebotMainConfig::LOAD_FROM_FILE)
            $domxml->load($file);
        else
            $domxml->loadXML($configData);

        $domxml->xinclude(LIBXML_NOBASEFIX);
        $this->__stripXGlobWrappers($domxml);
        $domxml->relaxNGValidate($schema);

        $errors = libxml_get_errors();
        if (count($errors)) {
            # Some unpredicted error occurred,
            # show some (hopefully) useful information.
            $errmsg = print_r($errors, TRUE);
            fprintf(STDERR, '%s', $errmsg);
            throw new EErebotInvalidValue(
                'Error while validating the configuration file');
        }

        $xml            = simplexml_import_dom($domxml);
        parent::__construct($this, $xml);

        if (!isset($xml['version']))
            throw new EErebotInvalidValue('No version defined');
        $this->version  = (string) $xml['version'];

        if (!version_compare($this->version, Erebot::VERSION, 'eq'))
            throw new EErebotInvalidValue(sprintf(
                'Invalid version (expected %s, got %s)',
                Erebot::VERSION, $this->version
            ));

        if (!isset($xml['timezone']))
            throw new EErebotInvalidValue('No timezone defined');
        $this->timezone = (string) $xml['timezone'];

        // Set timezone information.
        // This is needed to configure the logging subsystem.
        if (function_exists('date_default_timezone_set')) {
            if (!date_default_timezone_set($this->timezone))
                throw EErebotInvalidValue(sprintf(
                    'Invalid timezone: "%s"', $this->timezone));
        }

        if (!isset($xml['commands-prefix']))
            $this->commands_prefix = '!';
        else {
            $this->commands_prefix = (string) $xml['commands-prefix'];
            if (strcspn($this->commands_prefix, " \r\n\t") !=
                strlen($this->commands_prefix))
                throw new EErebotInvalidValue('Invalid command prefix');
        }

        $logging =& Plop::getInstance();
        if (isset($xml->children(PlopConfigXML::XMLNS)->logging[0]))
            $logging->fileConfig($xml->children(
                PlopConfigXML::XMLNS)->logging[0],
                array(), 'XML');

        $logger = $logging->getLogger(__FILE__);
        $logger->debug('Loaded configuration data');

        $this->networks = array();
        foreach ($xml->networks->network as $netCfg) {
            $newConfig  =   new ErebotNetworkConfig($this, $netCfg);
            $this->networks[$newConfig->getName()]  =& $newConfig;
            unset($newConfig);
        }

        libxml_clear_errors();
        libxml_use_internal_errors($ue);
        unset($domxml);

        if ($source == ErebotMainConfig::LOAD_FROM_FILE)
            $this->configFile   = $configData;
        else
            $this->configFile   = NULL;
    }

    // Documented in the interface.
    public function & getNetworkCfg($network)
    {
        if (!isset($this->networks[$network]))
            throw new EErebotNotFound('No such network');
        return $this->networks[$network];
    }

    // Documented in the interface.
    public function getNetworks()
    {
        return $this->networks;
    }

    // Documented in the interface.
    public function getVersion()
    {
        return $this->version;
    }

    // Documented in the interface.
    public function getTimezone()
    {
        return $this->timezone;
    }

    public function getCommandsPrefix()
    {
        return $this->commands_prefix;
    }
}

if (!defined('LIBXML_NOBASEFIX'))
    define('LIBXML_NOBASEFIX', 1 << 18);

?>
