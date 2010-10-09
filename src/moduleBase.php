<?php

ErebotUtils::incl('./dependency.php');

/**
 * \brief
 *      An abstract class which serves as the base
 *      to build additional modules for Erebot.
 */
abstract class ErebotModuleBase
{
    protected   $moduleName;
    protected   $connection;
    protected   $channel;
    protected   $translator;
    private     $metadata;

    const RELOAD_INIT       = 0x01;
    const RELOAD_TESTING    = 0x02;
    const RELOAD_MEMBERS    = 0x10;
    const RELOAD_HANDLERS   = 0x20;
    const RELOAD_METADATA   = 0x40;
    const RELOAD_ALL        = 0xF0;

    const META_CONTACT      = 0;
    const META_AUTHOR       = 0;    // Alias for META_CONTACT.
    const META_DESCRIPTION  = 1;
    const META_HOMEPAGE     = 2;
    const META_DEPENDS      = 3;
    const META_REQUIRES     = 3;    // Alias for META_DEPENDS.
    const META_RECOMMENDS   = 4;
    const META_CATEGORY     = 5;
    const META_VERSION      = 6;

    /**
     * An abstract method which is called whenever the module
     * is (re)loaded. You should perform whatever operations
     * you need to do, depending of the given $flags.
     *
     * \param $flags
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
        $this->connection   =&  $connection;
        $bot                =&  $connection->getBot();
        $this->moduleName   =   $bot->moduleClassToName($this);
        unset($bot);

        $config             =&  $this->connection->getConfig(NULL);
        $this->mainCfg      =&  $config->getMainCfg();
        $this->translator   =   $this->mainCfg->getTranslator($this->moduleName);
        unset($config);

        $this->channel      =   $channel;
        $this->resetMetadata();
    }

    final public function __destruct()
    {
        unset(
            $this->connection,
            $this->translator,
            $this->channel,
            $this->metadata,
            $this->moduleName
        );
    }

    /**
     * Removes any metadata associated to this module.
     * \internal You should never call this method directly.
     */
    public function resetMetadata()
    {
        $this->metadata     =   array(
                                    self::META_AUTHOR       => array(),
                                    self::META_DESCRIPTION  => NULL,
                                    self::META_HOMEPAGE     => NULL,
                                    self::META_DEPENDS      => array(),
                                    self::META_RECOMMENDS   => array(),
                                    self::META_CATEGORY     => array(),
                                    self::META_VERSION      => NULL,
                                );
    }

    /**
     * Associates metadata with the current module.
     *
     * \param $type
     *      One of the ErebotModuleBase::META_* constants which indicates
     *      the type of the metadata to add.
     *
     * \param $value
     *      The actual metadata to add.
     *
     * \note
     *      Some types of metadata accept multiple values, namely
     *      META_AUTHOR, META_DEPENDS, META_RECOMMENDS, META_CATEGORY
     *      and their aliases. Other types will throw an exception
     *      if you try to give them more than one value.
     */
    protected function addMetadata($type, $value)
    {
        if (!isset($this->metadata[$type]))
            throw new Exception('Invalid metadata type');

        if (!is_string($value))
            throw new Exception('Metadata value should be a string');

        if (!is_array($this->metadata[$type])) {
            if ($this->metadata[$type] !== NULL)
                throw new Exception('There is already a value for that metadata');
            $this->metadata[$type] = $value;
        }
        else {
            if ($type === self::META_DEPENDS ||
                $type === self::META_RECOMMENDS)
                $this->metadata[$type][] = new ErebotDependency($value);
            else
                $this->metadata[$type][] = $value;
        }
    }

    /**
     * Returns the metadata on this module for the given type.
     *
     * \param $type
     *      An optional parameter which indicates the type of
     *      metadata to return. If $type is NULL (the default),
     *      all metadata associated to this module is returned,
     *      as an array whose keys are the META_* constants.
     *
     * \return
     *      The metadata for the given $type or all metadata
     *      if $type is NULL (the default).
     */
    public function getMetadata($type = NULL)
    {
        if ($type === NULL)
            return $this->metadata;

        if (!isset($this->metadata[$type]))
            throw new Exception('Unknown metadata type');
        return $this->metadata[$type];
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
        $messages = explode("\n", wordwrap($message,
                    450 - $prefix - 2, "\n", TRUE));
        foreach ($messages as $msg)
            $this->connection->pushLine($prefix.$msg);
    }

    protected function sendCommand($command)
    {
        $this->connection->pushLine($command);
    }

    protected function addTimer(iErebotTimer &$timer)
    {
        $bot =& $this->connection->getBot();
        return $bot->addTimer($timer);
    }

    protected function removeTimer(iErebotTimer &$timer)
    {
        $bot =& $this->connection->getBot();
        return $bot->removeTimer($timer);
    }

    private function parseSomething($something, $param, $default)
    {
        $function   =   'parse'.$something;
        $bot        =&  $this->connection->getBot();
        if ($this->channel !== NULL) {
            try {
                $config     =&  $this->connection->getConfig($this->channel);
                return $config->$function($this->moduleName, $param);
            }
            catch (EErebot $e) { unset($config); }
        }
        $config     =&  $this->connection->getConfig(NULL);
        return $config->$function($this->moduleName, $param, $default);
    }

    /**
     * Returns the boolean value for a setting in this module's configuration.
     *
     * \param $param
     *      The name of the parameter we are interested in.
     *
     * \param $default
     *      An optional default value in case no value has been set
     *      at the configuration level.
     *
     * \return
     *      The boolean value for that parameter.
     */
    protected function parseBool($param, $default = NULL)
    {
        return $this->parseSomething('Bool', $param, $default);
    }

    /**
     * Returns the string value for a setting in this module's configuration.
     *
     * \param $param
     *      The name of the parameter we are interested in.
     *
     * \param $default
     *      An optional default value in case no value has been set
     *      at the configuration level.
     *
     * \return
     *      The string value for that parameter.
     */
    protected function parseString($param, $default = NULL)
    {
        return $this->parseSomething('String', $param, $default);
    }

    /**
     * Returns the integer value for a setting in this module's configuration.
     *
     * \param $param
     *      The name of the parameter we are interested in.
     *
     * \param $default
     *      An optional default value in case no value has been set
     *      at the configuration level.
     *
     * \return
     *      The integer value for that parameter.
     */
    protected function parseInt($param, $default = NULL)
    {
        return $this->parseSomething('Int', $param, $default);
    }

    /**
     * Returns the real value for a setting in this module's configuration.
     *
     * \param $param
     *      The name of the parameter we are interested in.
     *
     * \param $default
     *      An optional default value in case no value has been set
     *      at the configuration level.
     *
     * \return
     *      The real value for that parameter.
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
     * \param $callback
     *      The callback to register as the help method
     *      for this module.
     *
     * \return
     *      Returns TRUE if the callback could be registered,
     *      FALSE otherwise.
     *
     * \note
     *      In case multiple calls to this method are done by
     *      the same module, only the last registered callback
     *      will effectively be called to handle help requests.
     */
    protected function registerHelpMethod($callback)
    {
        try {
            $helper = $this->connection->getModule('Helper',
                ErebotConnection::MODULE_BY_NAME, $this->channel);
            return $helper->realRegisterHelpMethod($this, $callback);
        }
        catch (EException $e) { return FALSE; }
    }

    /**
     * Returns the appropriate translator for the given channel.
     *
     * \param $chan
     *      The channel for which a translator must be returned.
     *      If $chan is NULL, the hierarchy of configurations is
     *      traversed to find the most appropriate translator.
     *      If $chan is FALSE, a translator using the bot's main
     *      language is returned (this is the same as using
     *      $this->translator).
     */
    protected function getTranslator($chan)
    {
        if ($chan === FALSE)
            return $this->translator;

        else if ($chan !== NULL) {
            $config     =&  $this->connection->getConfig($chan);
            try { return $config->getTranslator($this->moduleName); }
            catch (EErebot $e) { }
            unset($config);
        }

        $config     =&  $this->connection->getConfig($this->channel);
        try { return $config->getTranslator($this->moduleName); }
        catch (EErebot $e) { }
        unset($config);

        $config     =&  $this->connection->getConfig(NULL);
        return $config->getTranslator($this->moduleName);
    }
}

?>
