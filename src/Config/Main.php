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
 *      Contains the main (general) configuration for Erebot.
 *
 * This class deals with settings which affect the whole bot such as its
 * version string.
 * It also contains references to instances of Erebot::Config::Network.
 */
class Main extends \Erebot\Config\Proxy implements \Erebot\Interfaces\Config\Main
{
    /// The (relative or absolute) path to the configuration, if available.
    protected $configFile;

    /// A list of Erebot::Config::Network objects.
    protected $networks;

    /// The configuration file's version string.
    protected $version;

    /// The bot's current timezone.
    protected $timezone;

    /// The prefix used to recognize commands.
    protected $commandsPrefix;

    /// Whether to daemonize the bot or not.
    protected $daemonize;

    /// User identity to switch to.
    protected $userIdentity;

    /// Group identity to switch to.
    protected $groupIdentity;

    /// File where the bot's PID will be written.
    protected $pidfile;

    /// Translator used by core files.
    protected $coreTranslator;

    /**
     * Creates a new instance of the Erebot::Config::Main class.
     *
     * \param string $configData
     *      Either a (relative or absolute) path to the configuration file
     *      to load or a string representation of the configuration,
     *      depending on the value of the $source parameter.
     *
     * \param opaque $source
     *      Erebot::Interfaces::Config::Main::LOAD_FROM_FILE or
     *      Erebot::Interfaces::Config::Main::LOAD_FROM_STRING,
     *      depending on whether $configData contains a filename
     *      or the string representation of the configuration data,
     *      respectively.
     *
     * \param Erebot::IntlInterface $translator
     *      Translator to use for messages coming from core files.
     *
     * \throw Erebot::InvalidValueException
     *      The configuration file did not exist or contained invalid values.
     *      This exception is also thrown when the $source parameter contains
     *      an invalid value.
     */
    public function __construct(
        $configData,
        $source,
        \Erebot\IntlInterface $translator
    ) {
        $this->proxified        = null;
        $this->modules          = array();
        $this->configFile       = null;
        $this->coreTranslator   = $translator;
        $this->load($configData, $source);
    }

    /**
     * Destructs Erebot::Config::Main instances.
     */
    public function __destruct()
    {
        parent::__destruct();
        unset(
            $this->networks
        );
    }

    /// \copydoc Erebot::Interface::Config::Main::__clone()
    public function __clone()
    {
        throw new \Exception('Cloning is forbidden');
    }

    /**
     * \internal
     * Removes wrapping xglob tags that were automatically
     * added by the Erebot::XGlobStream wrapper.
     *
     * \param \DOMDocument $domxml
     *      A DOM node with the document resulting from
     *      the parsing and interpretation of the configuration
     *      file.
     */
    private function stripXGlobWrappers(&$domxml)
    {
        $xpath = new \DOMXPath($domxml);
        $xpath->registerNamespace('xglob', \Erebot\XGlobStream::XMLNS);
        $wrappers = $xpath->query('//xglob:' . \Erebot\XGlobStream::TAG);
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

    /// \copydoc Erebot::Interfaces::Config::Main::load()
    public function load($configData, $source)
    {
        $possibleSources =     array(
                                    self::LOAD_FROM_FILE,
                                    self::LOAD_FROM_STRING,
                                );
        if (!in_array($source, $possibleSources, true)) {
            throw new \Erebot\InvalidValueException('Invalid $source');
        }

        if ($source == self::LOAD_FROM_FILE) {
            if (is_string($configData) && $configData != "") {
                if (!strncasecmp(PHP_OS, "Win", 3)) {
                    if (!in_array($configData[0], array("/", "\\")) &&
                        strlen($configData) > 1 && $configData[1] != ":") {
                        $configData = getcwd() . DIRECTORY_SEPARATOR .
                                        $configData;
                    }
                } elseif ($configData[0] != DIRECTORY_SEPARATOR) {
                    $configData = getcwd() . DIRECTORY_SEPARATOR . $configData;
                }
                $file = \Erebot\URI::fromAbsPath($configData, false);
            } elseif (is_object($configData) &&
                $configData instanceof \Erebot\URIInterface) {
                $file = $configData;
            } else {
                throw new \Erebot\InvalidValueException(
                    "Invalid configuration file"
                );
            }
        } elseif (!is_string($configData)) {
            throw new \Erebot\InvalidValueException(
                "Invalid configuration file"
            );
        } else {
            $file = null;
        }

        $mainSchema = dirname(dirname(__DIR__)) .
                        DIRECTORY_SEPARATOR . 'data' .
                        DIRECTORY_SEPARATOR . 'config.rng';
        $mainSchema = file_get_contents($mainSchema);
        $ue         = libxml_use_internal_errors(true);
        $domxml     = new \Erebot\DOM();
        if ($source == self::LOAD_FROM_FILE) {
            $domxml->load((string) $file);
        } else {
            $domxml->loadXML($configData);
        }

        $domxml->xinclude(LIBXML_NOBASEFIX);
        $this->stripXGlobWrappers($domxml);

        $ok         = $domxml->relaxNGValidateSource($mainSchema);
        $errors     = $domxml->getErrors();
        libxml_use_internal_errors($ue);

        $logger = \Plop::getInstance();
        if (!$ok || count($errors)) {
            // Some unpredicted error occurred,
            // show some (hopefully) useful information.
            $logger->error(print_r($errors, true));
            throw new \Erebot\InvalidValueException(
                'Errors were found while validating the configuration file'
            );
        }

        $xml = simplexml_import_dom($domxml);
        parent::__construct($this, $xml);

        if (!isset($xml['version'])) {
            $this->version = null;
        } else {
            $this->version  = (string) $xml['version'];
        }

        if (!isset($xml['timezone'])) {
            throw new \Erebot\InvalidValueException('No timezone defined');
        }
        $this->timezone = (string) $xml['timezone'];

        // Set timezone information.
        // This is needed to configure the logging subsystem.
        if (function_exists('date_default_timezone_set')) {
            if (!date_default_timezone_set($this->timezone)) {
                throw \Erebot\InvalidValueException(
                    sprintf(
                        'Invalid timezone: "%s"',
                        $this->timezone
                    )
                );
            }
        }

        $daemonize      = isset($xml['daemon'])
                        ? $this->parseBool((string) $xml['daemon'])
                        : false;
        $userIdentity   = isset($xml['uid']) ? ((string) $xml['uid']) : null;
        $groupIdentity  = isset($xml['gid']) ? ((string) $xml['gid']) : null;
        $pidfile        = isset($xml['pidfile'])
                        ? ((string) $xml['pidfile'])
                        : null;

        if ($daemonize === null) {
            throw new \Erebot\InvalidValueException('Invalid "daemon" value');
        }

        if (!isset($xml['commands-prefix'])) {
            $this->commandsPrefix = '!';
        } else {
            $this->commandsPrefix = (string) $xml['commands-prefix'];
            if (strcspn($this->commandsPrefix, " \r\n\t") !=
                strlen($this->commandsPrefix)) {
                throw new \Erebot\InvalidValueException(
                    'Invalid command prefix'
                );
            }
        }

        $logger = \Plop::getInstance();
        $logger->debug(
            $this->coreTranslator->gettext(
                'Loaded configuration data'
            )
        );

        $this->networks = array();
        foreach ($xml->networks->network as $netCfg) {
            /// @TODO use dependency injection instead.
            $newConfig  = new \Erebot\Config\Network($this, $netCfg);
            $this->networks[$newConfig->getName()] = $newConfig;
            unset($newConfig);
        }

        if ($source == self::LOAD_FROM_FILE) {
            $this->configFile   = $configData;
        } else {
            $this->configFile   = null;
        }

        // Default values.
        $this->daemonize        = $daemonize;
        $this->userIdentity     = $userIdentity;
        $this->groupIdentity    = $groupIdentity;
        $this->pidfile          = $pidfile;
    }

    /// \copydoc Erebot::Interfaces::Config::Main::getNetworkCfg()
    public function getNetworkCfg($network)
    {
        if (!isset($this->networks[$network])) {
            throw new \Erebot\NotFoundException('No such network');
        }
        return $this->networks[$network];
    }

    /// \copydoc Erebot::Interfaces::Config::Main::getNetworks()
    public function getNetworks()
    {
        return $this->networks;
    }

    /// \copydoc Erebot::Interfaces::Config::Main::getVersion()
    public function getVersion()
    {
        return $this->version;
    }

    /// \copydoc Erebot::Interfaces::Config::Main::getTimezone()
    public function getTimezone()
    {
        return $this->timezone;
    }

    /// \copydoc Erebot::Interfaces::Config::Main::getCommandsPrefix()
    public function getCommandsPrefix()
    {
        return $this->commandsPrefix;
    }

    /// \copydoc Erebot::Interfaces::Config::Main::getConfigFile()
    public function getConfigFile()
    {
        return $this->configFile;
    }

    /// \copydoc Erebot::Interfaces::Config::Main::mustDaemonize()
    public function mustDaemonize()
    {
        return $this->daemonize;
    }

    /// \copydoc Erebot::Interfaces::Config::Main::getGroupIdentity()
    public function getGroupIdentity()
    {
        return $this->groupIdentity;
    }

    /// \copydoc Erebot::Interfaces::Config::Main::getUserIdentity()
    public function getUserIdentity()
    {
        return $this->userIdentity;
    }

    /// \copydoc Erebot::Interfaces::Config::Main::getPidfile()
    public function getPidfile()
    {
        return $this->pidfile;
    }

    /// \copydoc Erebot::Interfaces::Config::Main::getTranslator()
    public function getTranslator($component)
    {
        if (isset($this->locale)) {
            $translator = new \Erebot\Intl($component);
            $translator->setLocale(
                \Erebot\IntlInterface::LC_MESSAGES,
                $this->locale
            );
            $categories = array(
                \Erebot\IntlInterface::LC_MONETARY,
                \Erebot\IntlInterface::LC_NUMERIC,
                \Erebot\IntlInterface::LC_TIME,
            );
            foreach ($categories as $category) {
                $translator->setLocale(
                    $category,
                    $this->coreTranslator->getLocale($category)
                );
            }
            return $translator;
        }

        return $this->coreTranslator;
    }
}
