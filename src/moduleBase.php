<?php

/**
 * 
 */
abstract class ErebotModuleBase
{
    protected   $moduleName;
    protected   $connection;
    protected   $channel;

    const RELOAD_INIT       = 0x01;
    const RELOAD_TESTING    = 0x02;
    const RELOAD_MEMBERS    = 0x10;
    const RELOAD_HANDLERS   = 0x20;
    const RELOAD_ALL        = 0xF0;

    final public function __construct(
        ErebotConnection &$connection,
        $channel,
        $flags = NULL)
    {
        if ($flags === NULL)
            $flags = self::RELOAD_ALL;

        $flags             |=   self::RELOAD_INIT;
        $this->connection   =&  $connection;
        $bot                =&  $connection->getBot();
        $this->moduleName   =   $bot->moduleClassToName($this);
        unset($bot);

        $this->channel      =   $channel;
        $this->reload($flags);
    }

    abstract public function reload($flags);

    protected function sendMessage($targets, $message)
    {
        if (is_array($targets))
            $targets = implode(',', $targets);
        else if (!is_string($targets))
            throw new Exception('Not a valid target.');

        $parts = array_map('trim', explode("\n", trim($message)));
        $this->connection->pushLine(
            'PRIVMSG '.$targets.' :'.
            implode(' ', $parts));
    }

    protected function sendCommand($command)
    {
        $this->connection->pushLine($command);
    }

    protected function addTimer(ErebotTimer &$timer)
    {
        $bot =& $this->connection->getBot();
        return $bot->addTimer($timer);
    }

    protected function removeTimer(ErebotTimer &$timer)
    {
        $bot =& $this->connection->getBot();
        return $bot->removeTimer($timer);
    }

    private function parseSomething($something, $param, $default)
    {
        $function   =   'parse'.$something;
        $bot        =&  $this->connection->getBot();
        $modName    =   $bot->moduleClassToName(get_class($this));
        if ($this->channel !== NULL) {
            try {
                $config     =&  $this->connection->getConfig($this->channel);
                return $config->$function($modName, $param);
            }
            catch (EErebot $e) { unset($config); }
        }
        $config     =&  $this->connection->getConfig(NULL);
        return $config->$function($modName, $param, $default);
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

    protected function parseTrigger($param, $default)
    {
        $trigger = $this->parseSomething('String', $param, $default);
        $this->addHelpTopic($trigger, $default);
        return $trigger;
    }

    protected function addHelpTopic($topic, $baseName)
    {
        try {
            $helper = $this->connection->getModule('Helper',
                ErebotConnection::MODULE_BY_NAME, $this->channel);
        }
        catch (EException $e) { return; }
        $helper->addSomeHelpTopic($this, $baseName, $topic);
    }

    protected function getTranslator($chan)
    {
        if ($chan === FALSE) {
            $config     =&  $this->connection->getConfig(NULL);
            $mainCfg    =&  $config->getMainCfg();
            return $mainCfg->getTranslator($this->moduleName);
        }

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
