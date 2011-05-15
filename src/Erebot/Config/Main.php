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

include_once(
    dirname(dirname(__FILE__)) .
    DIRECTORY_SEPARATOR .
    'XglobStream.php'
);

/**
 * \brief
 *      Contains the main (general) configuration for Erebot.
 *
 * This class deals with settings which affect the whole bot such as its
 * version string.
 * It also contains references to instances of the Erebot_Config_Network class.
 */
class       Erebot_Config_Main
extends     Erebot_Config_Proxy
implements  Erebot_Interface_Config_Main
{
    /// The (relative or absolute) path to the configuration, if available.
    protected $_configFile;

    /// A list of Erebot_Config_Network objects.
    protected $_networks;

    /// The configuration file's version string.
    protected $_version;

    /// The bot's current timezone.
    protected $_timezone;

    /// The prefix used to recognize commands.
    protected $_commandsPrefix;

    /// Whether to daemonize the bot or not.
    protected $_daemonize;

    /// User identity to switch to.
    protected $_userIdentity;

    /// Group identity to switch to.
    protected $_groupIdentity;

    /// File where the bot's PID will be written. 
    protected $_pidfile;

    // Documented in the interface.
    public function __construct($configData, $source)
    {
        $this->_proxified   = NULL;
        $this->_modules     = array();

        $this->_configFile  = NULL;
        $this->load($configData, $source);
    }

    /**
     * Destructs Erebot_Config_Main instances.
     */
    public function __destruct()
    {
        parent::__destruct();
        unset(
            $this->_networks
        );
    }

    // Documented in the interface.
    public function __clone()
    {
        throw new Exception('Cloning is forbidden');
    }

    private function _stripXGlobWrappers(&$domxml)
    {
        $xpath = new DOMXPath($domxml);
        $xpath->registerNamespace('xglob', Erebot_XGlobStream::XMLNS);
        $wrappers = $xpath->query('//xglob:'.Erebot_XGlobStream::TAG);
        foreach ($wrappers as $wrapper) {
            for ($i = $wrapper->childNodes->length; $i > 0; $i--) {
                $wrapper->parentNode->insertBefore(
                    $wrapper->childNodes->item($i - 1),
                    $wrapper->nextSibling
                );
            }
            $wrapper->parentNode->removeChild($wrapper);
        }
    }

    // Documented in the interface.
    public function load($configData, $source)
    {
        $possibleSources =     array(
                                    self::LOAD_FROM_FILE,
                                    self::LOAD_FROM_STRING,
                                );
        if (!in_array($source, $possibleSources, TRUE))
            throw new Erebot_InvalidValueException('Invalid $source');

        if (is_string($configData) && $configData != '') {
            if ($source == self::LOAD_FROM_FILE)
                $file = $configData[0] == '/' ? $configData :
                        dirname(dirname(__FILE__)).'/'.$configData;
            else
                $file = NULL;
        }
        else
            throw new Erebot_InvalidValueException('Invalid configuration file');

        if (basename(dirname(dirname(dirname(dirname(__FILE__))))) == 'trunk')
            $schemaDir = '../../../data';
        else
            $schemaDir = '../../../data/pear.erebot.net/Erebot';

        $schemaDir = str_replace('/', DIRECTORY_SEPARATOR, $schemaDir);
        $schema = dirname(__FILE__) .
            DIRECTORY_SEPARATOR . $schemaDir .
            DIRECTORY_SEPARATOR . 'config.rng';

        $ue     = libxml_use_internal_errors(TRUE);
        $domxml = new Erebot_DOM();
        if ($source == self::LOAD_FROM_FILE)
            $domxml->load($file);
        else
            $domxml->loadXML($configData);

        $domxml->xinclude(LIBXML_NOBASEFIX);
        $this->_stripXGlobWrappers($domxml);
        $ok     = $domxml->relaxNGValidate($schema);
        $errors = $domxml->getErrors();
        libxml_use_internal_errors($ue);

        if (!$ok || count($errors)) {
            # Some unpredicted error occurred,
            # show some (hopefully) useful information.
            $errmsg = print_r($errors, TRUE);
            fprintf(STDERR, '%s', $errmsg);
            throw new Erebot_InvalidValueException(
                'Error while validating the configuration file');
        }

        $xml = simplexml_import_dom($domxml);
        parent::__construct($this, $xml);

        if (!isset($xml['version']))
            throw new Erebot_InvalidValueException('No version defined');
        $this->_version  = (string) $xml['version'];

        if (!isset($xml['timezone']))
            throw new Erebot_InvalidValueException('No timezone defined');
        $this->_timezone = (string) $xml['timezone'];

        // Set timezone information.
        // This is needed to configure the logging subsystem.
        if (function_exists('date_default_timezone_set')) {
            if (!date_default_timezone_set($this->_timezone))
                throw Erebot_InvalidValueException(
                    sprintf(
                        'Invalid timezone: "%s"',
                        $this->_timezone
                    )
                );
        }

        $daemonize      = isset($xml['daemon'])
                        ? $this->_parseBool((string) $xml['daemon'])
                        : FALSE;
        $userIdentity   = isset($xml['uid']) ? ((string) $xml['uid']) : NULL;
        $groupIdentity  = isset($xml['gid']) ? ((string) $xml['gid']) : NULL;
        $pidfile        = isset($xml['pidfile'])
                        ? ((string) $xml['pidfile'])
                        : NULL;

        if ($daemonize === NULL)
            throw new Erebot_InvalidValueException('Invalid "daemon" value');

        if (!isset($xml['commands-prefix']))
            $this->_commandsPrefix = '!';
        else {
            $this->_commandsPrefix = (string) $xml['commands-prefix'];
            if (strcspn($this->_commandsPrefix, " \r\n\t") !=
                strlen($this->_commandsPrefix))
                throw new Erebot_InvalidValueException('Invalid command prefix');
        }

        $logging = Plop::getInstance();
        if (isset($xml->children(Plop_Config_Format_XML::XMLNS)->logging[0]))
            $logging->fileConfig(
                $xml->children(Plop_Config_Format_XML::XMLNS)->logging[0],
                NULL,
                'Plop_Config_Format_XML'
            );
        else
            $logging->basicConfig();

        $logger = $logging->getLogger(__FILE__);

        if (!version_compare(
            $this->_version,
            Erebot_Interface_Core::VERSION,
            'eq'
        ))
            $logger->warning(
                'This configuration file is meant for '.
                'Erebot %(config_version)s, while you are '.
                'running version %(code_version)s. '.
                'Things may not work as expected.',
                array(
                    'config_version'   => $this->_version,
                    'code_version'     => Erebot_Interface_Core::VERSION,
                )
            );

        $logger->debug('Loaded configuration data');

        $this->_networks = array();
        foreach ($xml->networks->network as $netCfg) {
            /// @TODO use dependency injection instead.
            $newConfig  = new Erebot_Config_Network($this, $netCfg);
            $this->_networks[$newConfig->getName()] = $newConfig;
            unset($newConfig);
        }

        if ($source == self::LOAD_FROM_FILE)
            $this->_configFile   = $configData;
        else
            $this->_configFile   = NULL;

        // Default values.
        $this->_daemonize       = $daemonize;
        $this->_userIdentity    = $userIdentity;
        $this->_groupIdentity   = $groupIdentity;
        $this->_pidfile         = $pidfile;
    }

    // Documented in the interface.
    public function getNetworkCfg($network)
    {
        if (!isset($this->_networks[$network]))
            throw new Erebot_NotFoundException('No such network');
        return $this->_networks[$network];
    }

    // Documented in the interface.
    public function getNetworks()
    {
        return $this->_networks;
    }

    // Documented in the interface.
    public function getVersion()
    {
        return $this->_version;
    }

    // Documented in the interface.
    public function getTimezone()
    {
        return $this->_timezone;
    }

    // Documented in the interface.
    public function getCommandsPrefix()
    {
        return $this->_commandsPrefix;
    }

    // Documented in the interface.
    public function getConfigFile()
    {
        return $this->_configFile;
    }

    // Documented in the interface.
    public function mustDaemonize()
    {
        return $this->_daemonize;
    }

    // Documented in the interface.
    public function getGroupIdentity()
    {
        return $this->_groupIdentity;
    }

    // Documented in the interface.
    public function getUserIdentity()
    {
        return $this->_userIdentity;
    }

    // Documented in the interface.
    public function getPidfile()
    {
        return $this->_pidfile;
    }
}

/* Needed to prevent libxml from trying to magically "fix" URLs
 * included with XInclude as this breaks a lot of things.
 * This requires libxml >= 2.6.20 (which was released in 2005). */
if (!defined('LIBXML_NOBASEFIX'))
    define('LIBXML_NOBASEFIX', 1 << 18);

