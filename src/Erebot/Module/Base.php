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

/**
 * \brief
 *      An abstract class which serves as the base
 *      to build additional modules for Erebot.
 */
abstract class Erebot_Module_Base
{
    /// The connection associated with this instance.
    protected   $_connection;

    /// The channel associated with this instance, if any.
    protected   $_channel;

    /// The translator to use for messages coming from this instance.
    protected   $_translator;

    /// The module's metadata.
    static protected $_metadata = array();


    /// Passed when the module is loaded (instead of reloaded).
    const RELOAD_INIT       = 0x01;

    /// Passed during unittests (currently unused...).
    const RELOAD_TESTING    = 0x02;

    /// The module should (re)load its members.
    const RELOAD_MEMBERS    = 0x10;

    /// The module should (re)load its handlers.
    const RELOAD_HANDLERS   = 0x20;

    /// The module should (re)load all of its contents.
    const RELOAD_ALL        = 0xF0;


    /// A regular message.
    const MSG_TYPE_PRIVMSG      = 'PRIVMSG';

    /// A notice.
    const MSG_TYPE_NOTICE       = 'NOTICE';

    /// A CTCP request.
    const MSG_TYPE_CTCP         = 'CTCP';

    /// A reply to a CTCP request.
    const MSG_TYPE_CTCPREPLY    = 'CTCPREPLY';

    /// An action.
    const MSG_TYPE_ACTION       = 'ACTION';

    /**
     * An abstract method which is called whenever the module
     * is (re)loaded. You should perform whatever operations
     * you need to do, depending on the given $flags.
     *
     * \param int $flags
     *      A bitwise OR of the Erebot_Module_Base::RELOAD_*
     *      constants. Your method should take proper actions
     *      depending on the value of those flags.
     *
     * \note
     *      See the documentation on individual RELOAD_*
     *      constants for a list of possible values.
     */
    abstract protected function _reload($flags);

    abstract protected function _unload();

    /**
     * Constructor for modules.
     *
     * \param string|NULL $channel
     *      (optional) The channel this instance applies to.
     *      This will be NULL for modules loaded at the server
     *      level or higher in the configuration hierarchy.
     */
    final public function __construct($channel)
    {
        $this->_connection  =
        $this->_translator  =
        $this->_mainCfg     = NULL;
        $this->_channel     = $channel;
    }

    /** Destructor. */
    final public function __destruct()
    {
        unset(
            $this->_connection,
            $this->_translator,
            $this->_channel,
            $this->_mainCfg
        );
    }

    /**
     * Public method to (re)load a module.
     * This eventually reconfigures the bot.
     *
     * \param Erebot_Interface_Connection $connection
     *      IRC connection associated with this instance.
     *
     * \param int $flags
     *      A bitwise OR of the Erebot_Module_Base::RELOAD_*
     *      constants. Your method should take proper actions
     *      depending on the value of those flags.
     *
     * \note
     *      See the documentation on individual RELOAD_*
     *      constants for a list of possible values.
     */
    final public function reload(
        Erebot_Interface_Connection $connection,
                                    $flags
    )
    {
        if ($this->_connection === NULL)
            $flags |= self::RELOAD_INIT;
        else
            $flags &= ~self::RELOAD_INIT;

        $this->_connection  = $connection;
        $serverCfg          = $this->_connection->getConfig(NULL);
        $this->_mainCfg     = $serverCfg->getMainCfg();
        $this->_translator  = $this->_mainCfg->getTranslator(get_class($this));
        $this->_reload($flags);
    }

    final public function unload()
    {
        return $this->_unload();
    }

    /**
     * \internal
     * Returns metadata associated with this module.
     *
     * \param string $className
     *      The class whose metadata should be returned.
     *
     * \retval array
     *      The module's metadata.
     */
    static public function getMetadata($className)
    {
        if (!class_exists($className))
            throw new Erebot_InvalidValueException('Invalid class name');
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

    /**
     * Send a message to a set of IRC targets (nicks or channels).
     *
     * \param string|list $targets
     *      Either a single nick or channel to which the message
     *      must be sent or an array of nicks/channels.
     *
     * \param string $message
     *      The message to send.
     *
     * \param opaque $type
     *      (optional) The type of message to send. The default is
     *      to send a regular message (using the PRIVMSG command).
     *      Use the MSG_TYPE_* constants to specify a different type.
     *
     * \throw Exception
     *      An invalid value was used for the $type or $targets
     *      parameter.
     */
    protected function sendMessage(
        $targets,
        $message,
        $type = self::MSG_TYPE_PRIVMSG
    )
    {
        $types  = array('PRIVMSG', 'NOTICE', 'CTCP', 'CTCPREPLY', 'ACTION');
        $type   = strtoupper($type);
        if (!in_array($type, $types))
            throw new Exception('Not a valid type');

        if (is_array($targets))
            $targets = implode(',', $targets);
        else if ($targets instanceof Erebot_Identity)
            $targets = (string) $targets;
        else if (!is_string($targets))
            throw new Exception('Not a valid target');

        $parts      = array_map('trim', explode("\n", trim($message)));
        $message    = implode(' ', $parts);
        $marker     = '';
        $ctcpType   = '';

        if ($type == 'ACTION') {
            $type       = 'PRIVMSG';
            $marker     = "\001";
            $ctcpType   = 'ACTION';
        }

        if ($type == 'CTCP' || $type == 'CTCPREPLY') {
                $type       = ($type == 'CTCP' ? 'PRIVMSG' : 'NOTICE');
                $marker     = "\001";
                $parts      = explode(' ', $message);
                $ctcpType   = array_shift($parts);
                $message    = implode(' ', $parts);
        }

        if ($ctcpType != "" && $message != "")
            $ctcpType .= " ";

        $prefix = $type.' '.$targets.' :'.$marker.$ctcpType;
        $messages = explode(
            "\n",
            wordwrap(
                $message,
                450 - $prefix - 2,
                "\n",
                TRUE
            )
        );
        foreach ($messages as $msg)
            $this->_connection->pushLine($prefix.$msg.$marker);
    }

    /**
     * Send a raw command to the IRC server.
     *
     * \param string $command
     *      The command to send.
     */
    protected function sendCommand($command)
    {
        $this->_connection->pushLine($command);
    }

    /**
     * Register a timer.
     *
     * \param Erebot_Interface_Timer $timer
     *      The timer to register.
     *
     * \note
     *      This method is only a shortcut for
     *      Erebot_Interface_Core::addTimer().
     */
    protected function addTimer(Erebot_Interface_Timer $timer)
    {
        $bot = $this->_connection->getBot();
        return $bot->addTimer($timer);
    }

    /**
     * Unregister a timer.
     *
     * \param Erebot_Interface_Timer $timer
     *      The timer to unregister.
     *
     * \note
     *      This method is only a shortcut for
     *      Erebot_Interface_Core::removeTimer().
     */
    protected function removeTimer(Erebot_Interface_Timer $timer)
    {
        $bot = $this->_connection->getBot();
        return $bot->removeTimer($timer);
    }

    /**
     * \internal
     * Retrieves a parameter from the module's configuration
     * by recursively traversing the configuration hierarchy
     * and parses it using the appropriate function.
     *
     * \param string $something
     *      The type of parsing to apply to the parameter.
     *      This is used to determine the correct parsing
     *      method to call.
     *
     * \param string $param
     *      The name of the parameter to retrieve.
     *
     * \param mixed $default
     *      The default value if the parameter is absent.
     *      It's actual type depends on the type of parsing
     *      applied by the $something argument.
     *
     * \warning
     *      This method may throw several exceptions for
     *      different reasons (such as a missing parameter,
     *      an invalid value or an invalid default value).
     */
    private function parseSomething($something, $param, $default)
    {
        $function   = 'parse'.$something;
        $bot        = $this->_connection->getBot();
        if ($this->_channel !== NULL) {
            try {
                $config = $this->_connection->getConfig($this->_channel);
                return $config->$function(get_class($this), $param);
            }
            catch (Erebot_Exception $e) {
                unset($config);
            }
        }
        $config = $this->_connection->getConfig(NULL);
        return $config->$function(get_class($this), $param, $default);
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
     * \throw Erebot_InvalidValueException
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
     * \throw Erebot_InvalidValueException
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
     * \throw Erebot_InvalidValueException
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
     * \throw Erebot_InvalidValueException
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
                'Erebot_Module_Helper',
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
            $config = $this->_connection->getConfig($chan);
            try {
                return $config->getTranslator(get_class($this));
            }
            catch (Erebot_Exception $e) {
            // The channel lacked a specific config. Use the cascade.
            }
            unset($config);
        }

        $config = $this->_connection->getConfig($this->_channel);
        try {
            return $config->getTranslator(get_class($this));
        }
        catch (Erebot_Exception $e) {
            // The channel lacked a specific config. Use the cascade.
        }
        unset($config);

        $config = $this->_connection->getConfig(NULL);
        return $config->getTranslator(get_class());
    }
}

