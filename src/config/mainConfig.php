<?php

ErebotUtils::incl('configProxy.php');
ErebotUtils::incl('networkConfig.php');
ErebotUtils::incl('../logging/src/logging.php');
ErebotUtils::incl('../streams/xglob.php');
ErebotUtils::incl('../ifaces/mainConfig.php');

if (!defined('LIBXML_NOBASEFIX'))
    define('LIBXML_NOBASEFIX', 1 << 18);

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
    /// Indicates that the configuration must be loaded from a file.
    const LOAD_FROM_FILE = 1;

    /// Indicates that the configuration must be loaded from a string.
    const LOAD_FROM_STRING = 2;

    /// The (relative or absolute) path to the configuration, if available.
    protected $configFile;

    /// A list of ErebotNetworkConfig objects.
    protected $networks;

    /// The bot's version string.
    protected $version;

    /// The bot's current timezone.
    protected $timezone;

    /**
     * Creates a new instance of the ErebotMainConfig class.
     *
     * Do not call this class' constructor directly. Instead, use
     * ErebotMainConfig::getInstance() to retrieve an instance of
     * this class.
     */
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

    /**
     * Prevents cloning of this class to avoid escape from the
     * singleton design pattern.
     */
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

    /**
     * (Re)loads a configuration file.
     *
     * \param $configData
     *      Either a (relative or absolute) path to the configuration file
     *      to load or a string representation of the configuration, 
     *      depending on the value of the $source parameter.
     *
     * \param $source
     *      Whether $configData contains a filename or the string
     *      representation of the configuration. data
     *
     * \throw EErebotInvalidValue
     *      The configuration file did not exist or contained invalid values.
     *      This exception is also thrown when the $source parameter contains
     *      an invalid value.
     *
     * \note
     *      Each time this method is called, all previous settings will be
     *      discarded.
     */
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
            foreach ($errors as $error) {
                switch ($error->code) {
                    case 1546:  // Failed to load external entity, ignored
                                // because for schemas, #1757 is more explicit.
                        continue;

                    case 1757:
                        fprintf(STDERR, '%s', "No schema found to validate ".
                            "the configuration file. Did you run 'make' ?");
                        throw new EErebotInvalidValue('No schema found');
                }
            }

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
            throw new EErebotInvalidValue('Invalid version');

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

        $logging =& ErebotLogging::getInstance();
        if (isset($xml->children(EREBOT_LOG_XMLNS)->logging[0]))
            $logging->fileConfig($xml->children(
                EREBOT_LOG_XMLNS)->logging[0],
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

    /**
     * \XXX Not yet implemented.
     */
    public function save($config = NULL)
    {
        if ($config === NULL)
            $file = $this->configFile;
        else if ($config === '-')
            $file = 'php://stdout';
        else if (is_string($config) && $config != '')
            $file = $config[0] == '/' ? $config :
                    dirname(dirname(__FILE__)).'/'.$config;
        else
            throw new EErebotInvalidValue('Invalid configuration file');

        file_put_contents($file, $this->export(0), LOCK_EX);
    }
/*
    public function export($indent = 0)
    {
        if (!count($this->modules))
            $modulesXML = '';
        else {
            $modulesXML = "    <modules>\n";
            foreach ($this->modules as &$module)
                $modulesXML .= $module->export($indent + 2);
            $modulesXML .= '    </modules>';
        }

        if (!count($this->networks))
            $networksXML = '';
        else {
            $networksXML =  "    <networks>\n";
            foreach ($this->networks as &$network)
                $networksXML .= $network->export($indent + 2)."\n";
            $networksXML =  substr($networksXML, 0, -1).
                            '    </networks>';
        }

        $version    = Erebot::VERSION;
        $dbDSN      = htmlspecialchars($this->dbDSN);
        $dbUser     = htmlspecialchars($this->dbUser);
        $dbPass     = htmlspecialchars($this->dbPass);
        $XML =<<<XML
<configuration xmlns="http://localhost/Erebot/" version="$version">
    <database
        dsn="$dbDSN"
        username="$dbUser"
        password="$dbPass"
    />

$modulesXML

$networksXML
</configuration>
XML;

        return $XML;
    }
*/

    /**
     * Returns the configuration object for a particular IRC network.
     *
     * \param $network
     *      The name of the IRC network whose configuration we're interested in.
     *
     * \return
     *      The ErebotNetworkConfig object for that network.
     *
     * \throw EErebotNotFound
     *      No such network has been configured on the bot.
     */
    public function & getNetworkCfg($network)
    {
        if (!isset($this->networks[$network]))
            throw new EErebotNotFound('No such network');
        return $this->networks[$network];
    }

    /**
     * Returns all IRC network configurations.
     *
     * \return
     *       A list of ErebotNetworkConfig instances.
     */
    public function getNetworks()
    {
        return $this->networks;
    }

    /**
     * Returns the bot's version string.
     *
     * \return
     *      A string representing the bot's version,
     *      such as '0.20-pre'.
     *
     * \note
     *      This version string is compatible with PHP's versioning scheme.
     *      Therefore, you may use PHP's version_compare() function to compare
     *      the version strings for different releases of the bot.
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Returns the bot's timezone.
     *
     * \return
     *      A string describing the bot's current timezone,
     *      such as 'Europe/Paris'.
     */
    public function getTimezone()
    {
        return $this->timezone;
    }
}

?>
