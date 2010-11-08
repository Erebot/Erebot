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

include_once('src/dependency.php');

/**
 * \brief
 *      An abstract class which serves as the base
 *      to build additional modules for Erebot.
 */
abstract class ErebotModuleBase
{
    protected   $_moduleName;
    protected   $_connection;
    protected   $_channel;
    protected   $_translator;
    static protected $_metadata = array();

    const RELOAD_INIT       = 0x01;
    const RELOAD_TESTING    = 0x02;
    const RELOAD_MEMBERS    = 0x10;
    const RELOAD_HANDLERS   = 0x20;
    const RELOAD_ALL        = 0xF0;

    /**
     * An abstract method which is called whenever the module
     * is (re)loaded. You should perform whatever operations
     * you need to do, depending of the given $flags.
     *
     * \param int $flags
     *      A bitwise OR of the ErebotModuleBase::RELOAD_*
     *      constants. Your method should take proper actions
     *      depending on the value of those flags.
     *
     * \note
     *      See the documentation on individual RELOAD_*
     *      constants for a list of possible values.
     */
    abstract public function reload($flags);

    final public function __construct(iErebotConnection &$connection, $channel)
    {
        $this->_connection  =&  $connection;
        $bot                =&  $connection->getBot();
        $this->_moduleName  =   $bot->moduleClassToName($this);
        unset($bot);

        $config             =&  $this->_connection->getConfig(NULL);
        $this->_mainCfg     =&  $config->getMainCfg();
        $this->_translator  =   $this->_mainCfg->getTranslator(
            $this->_moduleName
        );
        unset($config);

        $this->_channel     =   $channel;
    }

    final public function __destruct()
    {
        unset(
            $this->_connection,
            $this->_translator,
            $this->_channel,
            $this->_moduleName,
            $this->_mainCfg
        );
    }

    static public function getMetadata($className)
    {
        if (!class_exists($className))
            throw new EErebotInvalidValue('Invalid class name');
        for ($obj = $className; $obj; $obj = get_parent_class($obj)) {
            $refl = new ReflectionClass($obj);
            try {
                $reflProp = $refl->getProperty('_metadata');
                return $reflProp->getValue();
            }
            catch (ReflectionException $e) {
            }
        }
        return array();
    }

    public function install()
    {
        // By default, we do nothing.
    }

    public function uninstall()
    {
        // By default, we do nothing.
    }

    protected function sendMessage($targets, $message)
    {
        if (is_array($targets))
            $targets = implode(',', $targets);
        else if (!is_string($targets))
            throw new Exception('Not a valid target');

        $parts      = array_map('trim', explode("\n", trim($message)));
        $message    = implode(' ', $parts);
        $prefix = 'PRIVMSG '.$targets.' :';
        /* The RFC says a message (command+parameters) may take up
         * 510 characters, but some IRC servers truncate messages
         * before that. The 450 character limit is a rough estimation
         * of what "ought to be enough for anybody". */
        $messages = explode(
            "\n", wordwrap($message, 450 - $prefix - 2, "\n", TRUE)
        );
        foreach ($messages as $msg)
            $this->_connection->pushLine($prefix.$msg);
    }

    protected function sendCommand($command)
    {
        $this->_connection->pushLine($command);
    }

    protected function addTimer(iErebotTimer &$timer)
    {
        $bot =& $this->_connection->getBot();
        return $bot->addTimer($timer);
    }

    protected function removeTimer(iErebotTimer &$timer)
    {
        $bot =& $this->_connection->getBot();
        return $bot->removeTimer($timer);
    }

    private function parseSomething($something, $param, $default)
    {
        $function   =   'parse'.$something;
        $bot        =&  $this->_connection->getBot();
        if ($this->_channel !== NULL) {
            try {
                $config     =&  $this->_connection->getConfig($this->_channel);
                return $config->$function($this->_moduleName, $param);
            }
            catch (EErebot $e) {
                unset($config);
            }
        }
        $config     =&  $this->_connection->getConfig(NULL);
        return $config->$function($this->_moduleName, $param, $default);
    }

    /**
     * Returns the boolean value for a setting in this module's configuration.
     *
     * \param string $param
     *      The name of the parameter we are interested in.
     *
     * \param bool $default
     *      (optional) A default value in case no value has been set
     *      at the configuration level.
     *
     * \retval bool
     *      The value for that parameter.
     *
     * \throw EErebotInvalidValue
     *      The given $default value does not have the right type.
     */
    protected function parseBool($param, $default = NULL)
    {
        return $this->parseSomething('Bool', $param, $default);
    }

    /**
     * Returns the string value for a setting in this module's configuration.
     *
     * \param string $param
     *      The name of the parameter we are interested in.
     *
     * \param string $default
     *      An optional default value in case no value has been set
     *      at the configuration level.
     *
     * \retval string
     *      The value for that parameter.
     *
     * \throw EErebotInvalidValue
     *      The given $default value does not have the right type.
     */
    protected function parseString($param, $default = NULL)
    {
        return $this->parseSomething('String', $param, $default);
    }

    /**
     * Returns the integer value for a setting in this module's configuration.
     *
     * \param int $param
     *      The name of the parameter we are interested in.
     *
     * \param int $default
     *      (optional) A default value in case no value has been set
     *      at the configuration level.
     *
     * \retval int
     *      The value for that parameter.
     *
     * \throw EErebotInvalidValue
     *      The given $default value does not have the right type.
     */
    protected function parseInt($param, $default = NULL)
    {
        return $this->parseSomething('Int', $param, $default);
    }

    /**
     * Returns the real value for a setting in this module's configuration.
     *
     * \param string $param
     *      The name of the parameter we are interested in.
     *
     * \param float $default
     *      (optional) A default value in case no value has been set
     *      at the configuration level.
     *
     * \retval float
     *      The value for that parameter.
     *
     * \throw EErebotInvalidValue
     *      The given $default value does not have the right type.
     */
    protected function parseReal($param, $default = NULL)
    {
        return $this->parseSomething('Real', $param, $default);
    }

    /**
     * Registers the given callback as the help method for this module.
     * All help requests directed to this module will be passed to this
     * method which may choose to handle it (eg. by sending back help
     * messages to the person requesting help).
     * This method may also choose to ignore a given request, which will
     * result in a default "No help available" response.
     *
     * \param callback $callback
     *      The callback to register as the help method
     *      for this module.
     *
     * \retval TRUE
     *      The callback could be registered.
     *
     * \retval FALSE
     *      The callback could not be registered.
     *
     * \note
     *      In case multiple calls to this method are done by
     *      the same module, only the last registered callback
     *      will effectively be called to handle help requests.
     */
    protected function registerHelpMethod($callback)
    {
        try {
            $helper = $this->_connection->getModule(
                'Helper',
                ErebotConnection::MODULE_BY_NAME,
                $this->_channel
            );
            return $helper->realRegisterHelpMethod($this, $callback);
        }
        catch (EException $e) {
            return FALSE;
        }
    }

    /**
     * Returns the appropriate translator for the given channel.
     *
     * \param NULL|FALSE|string $chan
     *      The channel for which a translator must be returned.
     *      If $chan is NULL, the hierarchy of configurations is
     *      traversed to find the most appropriate translator.
     *      If $chan is FALSE, a translator using the bot's main
     *      language is returned (this is the same as using
     *      <tt>$this->_translator</tt>).
     */
    protected function getTranslator($chan)
    {
        if ($chan === FALSE)
            return $this->_translator;

        else if ($chan !== NULL) {
            $config     =&  $this->_connection->getConfig($chan);
            try {
                return $config->getTranslator($this->_moduleName);
            }
            catch (EErebot $e) {
            // The channel lacked a specific config. Use the cascade.
            }
            unset($config);
        }

        $config     =&  $this->_connection->getConfig($this->_channel);
        try {
            return $config->getTranslator($this->_moduleName);
        }
        catch (EErebot $e) {
            // The channel lacked a specific config. Use the cascade.
        }
        unset($config);

        $config     =&  $this->_connection->getConfig(NULL);
        return $config->getTranslator($this->_moduleName);
    }
}

