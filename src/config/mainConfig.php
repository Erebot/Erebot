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

include_once('src/config/configProxy.php');
include_once('src/config/networkConfig.php');
include_once('src/logging/src/logging.php');
include_once('src/logging/src/config/XML.php');
include_once('src/streams/xglob.php');
include_once('src/ifaces/mainConfig.php');

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
    protected $_configFile;

    /// A list of ErebotNetworkConfig objects.
    protected $_networks;

    /// The bot's version string.
    protected $_version;

    /// The bot's current timezone.
    protected $_timezone;

    /// The prefix used to recognize commands.
    protected $_commandsPrefix;

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
        unset(
            $this->_modules,
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
        $xpath->registerNamespace('xglob', ErebotWrapperXGlob::XMLNS);
        $wrappers = $xpath->query('//xglob:'.ErebotWrapperXGlob::TAG);
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
                                    ErebotMainConfig::LOAD_FROM_FILE,
                                    ErebotMainConfig::LOAD_FROM_STRING,
                                );
        if (!in_array($source, $possibleSources, TRUE))
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
        $this->_stripXGlobWrappers($domxml);
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
        $this->_version  = (string) $xml['version'];

        if (!version_compare($this->_version, iErebot::VERSION, 'eq'))
            throw new EErebotInvalidValue(sprintf(
                'Invalid version (expected %s, got %s)',
                Erebot::VERSION, $this->_version
            ));

        if (!isset($xml['timezone']))
            throw new EErebotInvalidValue('No timezone defined');
        $this->_timezone = (string) $xml['timezone'];

        // Set timezone information.
        // This is needed to configure the logging subsystem.
        if (function_exists('date_default_timezone_set')) {
            if (!date_default_timezone_set($this->_timezone))
                throw EErebotInvalidValue(
                    sprintf(
                        'Invalid timezone: "%s"',
                        $this->_timezone
                    )
                );
        }

        if (!isset($xml['commands-prefix']))
            $this->_commandsPrefix = '!';
        else {
            $this->_commandsPrefix = (string) $xml['commands-prefix'];
            if (strcspn($this->_commandsPrefix, " \r\n\t") !=
                strlen($this->_commandsPrefix))
                throw new EErebotInvalidValue('Invalid command prefix');
        }

        $logging =& Plop::getInstance();
        if (isset($xml->children(PlopConfigXML::XMLNS)->logging[0]))
            $logging->fileConfig(
                $xml->children(PlopConfigXML::XMLNS)->logging[0],
                array(),
                'XML'
            );

        $logger = $logging->getLogger(__FILE__);
        $logger->debug('Loaded configuration data');

        $this->_networks = array();
        foreach ($xml->networks->network as $netCfg) {
            $newConfig  =   new ErebotNetworkConfig($this, $netCfg);
            $this->_networks[$newConfig->getName()]  =& $newConfig;
            unset($newConfig);
        }

        libxml_clear_errors();
        libxml_use_internal_errors($ue);
        unset($domxml);

        if ($source == ErebotMainConfig::LOAD_FROM_FILE)
            $this->_configFile   = $configData;
        else
            $this->_configFile   = NULL;
    }

    // Documented in the interface.
    public function & getNetworkCfg($network)
    {
        if (!isset($this->_networks[$network]))
            throw new EErebotNotFound('No such network');
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

    public function getCommandsPrefix()
    {
        return $this->_commandsPrefix;
    }
}

if (!defined('LIBXML_NOBASEFIX'))
    define('LIBXML_NOBASEFIX', 1 << 18);

