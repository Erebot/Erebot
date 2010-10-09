<?php

if (!empty($_SERVER['DOCUMENT_ROOT']))
    die("This script isn't meant to be run from the Internet!\n");

// For older versions of PHP (>= 4.3.0)
// which don't support pcntl_signal_dispatch,
// and yet support signals (< 5.3.0).
if (!function_exists('pcntl_signal_dispatch'))
    declare(ticks=1);

// We want maximum verbosity! (or just a clean code :)
error_reporting(E_ALL | E_STRICT);
// The bot may run indefinitely, avoid the default 30 seconds time limit.
set_time_limit(0);

require_once(dirname(__FILE__).'/utils.php');
ErebotUtils::incl('logging/src/logging.php');
ErebotUtils::incl('events/raws.php');
ErebotUtils::incl('events/events.php');
ErebotUtils::incl('moduleBase.php');
ErebotUtils::incl('connection.php');
ErebotUtils::incl('config/mainConfig.php');
ErebotUtils::incl('timer.php');
ErebotUtils::incl('streams/irc.php');
ErebotUtils::incl('exceptions/NotImplemented.php');
ErebotUtils::incl('exceptions/ErrorReporting.php');
ErebotUtils::incl('version.php');
ErebotUtils::incl('ifaces/core.php');

/**
 * \brief
 *      Provides core functionalities for Erebot.
 *
 * This class is responsible for controlling the bot, from its start
 * to its shutdown. This is the class that will create new
 * \link ErebotConnection ErebotConnections\endlink, handle
 * \link ErebotTimer ErebotTimers\endlink, apply multiplexing, etc.
 *
 * The main method you will be interested in is Erebot::start() which starts
 * parsing the configuration and runs the bot.
 */
class       Erebot
implements  iErebot
{
    /**
     * Version information regarding this release of the bot.
     * This information is also available as a global constant called
     * EREBOT_VERSION.
     */
    const VERSION                   = EREBOT_VERSION;

    const DEFAULT_CONNECTION_CLS    = 'ErebotConnection';

    /// List of \link ErebotConnection ErebotConnections\endlink to handle.
    protected $connections;

    /// List of \link ErebotTimer ErebotTimers\endlink to trigger, eventually.
    protected $timers;

    /// Dictionary with mappings between modules and their classes.
    protected $modulesMapping;

    /// Main configuration for the bot.
    protected $mainCfg;

    /// Indicates whether the bot is currently running or not.
    protected $running;

    /**
     * Creates a new Erebot instance.
     *
     * \param $config
     *      A configuration to use, as an ErebotMainConfig object.
     *
     * \note
     *      If no argument is given, this method looks for a file called
     *      "Erebot.xml" and loads its configuration from that file.
     */
    public function __construct(iErebotMainConfig $config = NULL)
    {
        $this->connections      =
        $this->timers           =
        $this->modulesMapping   = array();
        $this->running          = FALSE;

        // Attach configuration.
        if ($config === NULL)
            $config = new ErebotMainConfig('../Erebot.xml', ErebotMainConfig::LOAD_FROM_FILE);
        $this->mainCfg  = $config;

        // If pcntl_signal is not supported,
        // the bot won't be able to stop!
        // See start() for reasons why (hint: infinite loop).
        if (function_exists('pcntl_signal')) {
            /* These ought to be the most common signals
             * we expect to receive, eventually. */
            $signals =  array(
                            SIGINT,
                            SIGQUIT,
                            SIGALRM,
                            SIGTERM,
                        );

            foreach ($signals as $signal)
                pcntl_signal($signal, array($this, 'quitGracefully'), TRUE);
        }
    }

    /**
     * Destruct an Erebot instance.
     */
    public function __destruct()
    {
        $this->stop();
    }

    /**
     * Retrieve a list of all connections handled by the bot.
     *
     * \return
     *      A list of all \link ErebotConnection ErebotConnections\endlink
     *      handled by this instance.
     *
     * \note
     *      There is not much use for this method actually. The only
     *      case where you might need it is when you're willing to
     *      broadcast a message/command to all connections (such as
     *      to signal the bot shutting down).
     */
    public function getConnections()
    {
        return $this->connections;
    }

    /**
     * Starts the bot.
     *
     * \param $connectionCls
     *      The name of the class to use to create new connections.
     *      This class must implement the iErebotConnection interface.
     *
     * \note
     *      If no connection class is given or the given class name
     *      is invalid (inexistent class, bad interface, etc.),
     *      it defaults to ErebotConnection.
     *
     * \attention
     *      This method never returns. Therefore, this MUST be the last
     *      method you call in your script.
     */
    public function start($connectionCls = NULL)
    {
        $logging    =&  ErebotLogging::getInstance();
        $logger     =   $logging->getLogger(__FILE__);
        $logger->info($this->gettext('Erebot is starting'));

        /// @TODO: check that it implements the correct interface.
        if (!is_string($connectionCls) || !class_exists($connectionCls))
            $connectionCls = self::DEFAULT_CONNECTION_CLS;
        else {
            $reflect = new ReflectionClass($connectionCls);
            if (!$reflect->implementsInterface('iErebotConnection'))
                $connectionCls = self::DEFAULT_CONNECTION_CLS;
        }

        // Let's establish some contacts.
        $networks       =   $this->mainCfg->getNetworks();
        foreach ($networks as $network) {
            $servers    = $network->getServers();

            if (!in_array('AutoConnect', $network->getModules(TRUE)))
                continue;

            foreach ($servers as $server) {
                try {
                    $connection = new $connectionCls($this, $server);
                    $connection->connect();
                    $this->connections[] = $connection;
                    break;
                }
                catch (EErebotConnectionFailure $e) {
                    // Nothing to do... We simply
                    // try the next server on the
                    // list until we successfully
                    // connect or cycle the list.
                    $logger->warning($this->gettext(
                        'Could not connect to "%s"'),
                        $server->getConnectionURL());
                }
            }
        }

        // This flag is changed by quitGracefully()
        // when the bot should stop.
        $this->running = TRUE;

        // Main loop
        while ($this->running) {
            $logger->debug($this->gettext('Main event loop'));

            // This is the way PHP 5.3 passes signals to their handlers.
            if (function_exists('pcntl_signal_dispatch'))
                pcntl_signal_dispatch();

            $read = $write = $except = array();
            $actives = array('connections' => array(), 'timers' => array());

            if ($this->connections === NULL)
                break;

            // Find out connections in need of some handling.
            foreach ($this->connections as $index => &$connection) {
                $socket = $connection->getSocket();

                if (!$connection->emptySendQueue())
                    $write[]    = $socket;

                $except[]                       = $socket;
                $read[]                         = $socket;
                $actives['connections'][$index] = $socket;
            }

            // Find out timed out timers.
            foreach ($this->timers as $index => &$timer) {
                $stream = $timer->getStream();

                $read[]                     = $stream;
                $actives['timers'][$index]  = $stream;
            }

            unset($connection, $timer);

            // No activity.
            if (count($read) + count($write) + count($except) == 0) {
                $logger->debug($this->gettext(
                    'No more connections to handle, leaving...'));
                return $this->stop();
            }

            try {
                // Block until there is activity. Since timers
                // are treated as streams too, we will also wake
                // up whenever a timer fires.
                $nb = stream_select($read, $write, $except, NULL);            
            }
            catch (EErebotErrorReporting $e) {
                if ($this->running)
                    $logger->exception($this->gettext('Got exception'), $e);
                else
                    /* If the bot is not running anymore,
                     * this probably means we received a signal.
                     * We continue to the next iteration,
                     * which will make the bot exit properly. */
                    continue;
            }

            // Handle exception (OOB) data.
            if (count($except)) {
                $logger->error($this->gettext('Received out-of-band data'));
                return $this->stop();
            }

            // Handle read-ready "sockets"
            foreach ($read as $socket) {
                do {
                    // Is it a connection?
                    $index = array_search($socket, $actives['connections'], TRUE);
                    if ($index !== FALSE) {
                        // Read as much data from the connection as possible.
                        try {
                            $this->connections[$index]->processIncomingData();
                        }
                        catch (EErebotConnectionFailure $e) {
                            $logger->info($this->gettext(
                                'Connection failed, removing it from the pool'));
                            $this->removeConnection($this->connections[$index]);
                        }
                        break;
                    }

                    // Is it a timer?
                    $index = array_search($socket, $actives['timers'], TRUE);
                    if ($index !== FALSE) {
                        $timer      =&  $this->timers[$index];
                        $restart    =   $timer->activate();

                        // Maybe the callback function
                        // removed the timer already.
                        if (!isset($this->timers[$index]))
                            break;

                        // Otherwise, restart or remove it as necessary.
                        if ($restart === TRUE || $timer->isRepeated())
                            $timer->reset();
                        else
                            unset($this->timers[$index]);

                        break;
                    }

                    // No, it's superman!! Let's do nothing then...
                } while (0);
            }

            // Take care of incoming data waiting for processing.
            foreach ($this->connections as &$connection) {
                $connection->processQueuedData();
            }
            unset($connection);

            // Handle write-ready sockets (flush outgoing data).
            foreach ($write as $socket) {
                $index = array_search($socket, $actives['connections']);
                if ($index !== FALSE &&
                    is_a($this->connections[$index], $connectionCls))
                    $this->connections[$index]->processOutgoingData();
            }
        }
    }

    /**
     * Stops the bot.
     */
    public function stop()
    {
        $logging    =&  ErebotLogging::getInstance();
        $logger     =   $logging->getLogger(__FILE__);

        if (!$this->running)
            return;

        $error          = error_get_last();
        $quitMessage    = NULL;
        if ($error !== NULL) {
            $quitMessage = $error['message'].' ['.
                            $error['file'].':'.
                            $error['line'].']';
            $logger->critical('%(message)s in %(file)s on line %(line)d',
                $error);
        }

        foreach ($this->connections as &$connection) {
            $event = new ErebotEventExit($connection);
            $connection->dispatchEvent($event);
            $connection->disconnect($quitMessage);
        }
        unset($connection);

        $logger->info($this->gettext('Erebot has stopped'));
        unset(
            $this->timers,
            $this->connections,
            $this->modulesMapping,
            $this->mainCfg
        );

        $this->running = FALSE;
        $this->connections      =
        $this->timers           =
        $this->modulesMapping   =
        $this->mainCfg          = NULL;
    }

    /**
     * Handles request for a graceful shutdown of the bot.
     * Such request are received as signals.
     *
     * \param $signum
     *      The number of the received signal.
     */
    public function quitGracefully($signum)
    {
        $consts     = get_defined_constants(TRUE);
        $signame    = '???';
        foreach ($consts['pcntl'] as $name => $value) {
            if (!strncmp($name, 'SIG', 3) &&
                strncmp($name, 'SIG_', 4) &&
                $signum == $value) {
                $signame = $name;
                break;
            }
        }

        $logging    =&  ErebotLogging::getInstance();
        $logger     =   $logging->getLogger(__FILE__);
        $logger->info($this->gettext(
            'Received signal #%(signum)d (%(signame)s)'), array(
                'signum'    => $signum,
                'signame'   => $signame,
            ));

        // Print some statistics.
        if (function_exists('memory_get_peak_usage')) {
            $logger->info($this->gettext('Max. Allocated memory:'));

            $stats = array(
                $this->gettext("System:")   => memory_get_peak_usage(TRUE)."B",
                $this->gettext("Internal:") => memory_get_peak_usage(FALSE)."B",
                $this->gettext("Limit:")    => ini_get('memory_limit'),
            );

            foreach ($stats as $key => $value)
                $logger->info('%(key)-16s%(value)10s', array(
                    'key'   => $key,
                    'value' => $value,
                ));
        }

        $this->stop();
    }

    /**
     * Retrieve a list of all \link ErebotTimer ErebotTimers\endlink registered.
     *
     * \return
     *      Returns a list of all \link ErebotTimer ErebotTimers\endlink
     *      registered for this instance.
     */
    public function getTimers()         { return $this->timers; }

    /**
     * Registers a timer for this instance.
     *
     * \param $timer
     *      An ErebotTimer instance which conveys information about the timer
     *      to register.
     */
    public function addTimer(iErebotTimer &$timer)
    {
        $key = array_search($timer, $this->timers);
        if ($key !== FALSE)
            throw new EErebotInvalidValue('Timer already registered');

        $timer->reset();
        $this->timers[] =&  $timer;
    }

    /**
     * Unregisters a timer.
     *
     * \param $timer
     *      An ErebotTimer instance which must be removed from the list of
     *      registered timers for this instance.
     */
    public function removeTimer(iErebotTimer &$timer)
    {
        $key = array_search($timer, $this->timers);
        if ($key === FALSE)
            throw new EErebotNotFound('Timer not found');

        unset($this->timers[$key]);
    }

    /**
     * Retrieve the bot's version information.
     *
     * \return
     *      Returns a string containing formatted version information
     *      about the bot.
     *
     * \see
     *      The constant Erebot::VERSION contains the raw version information.
     */
    public static function getVersion()
    {
        return 'Erebot v'.self::VERSION;
    }

    static private function __subclasses_EMB($a)
    {
        return (is_subclass_of($a, "ErebotModuleBase"));
    }

    static private function __inherits_EMB($a, $b)
    {
        if (is_subclass_of($a, get_class($b)))
            return -1;
        if (is_subclass_of($b, get_class($a)))
            return +1;
        throw new EErebotInvalidValue("Bad module! There must be exactly ".
            "1 subclass of ErebotModuleBase in it.");
    }

    /**
     * Loads a module.
     *
     * \param $module
     *      The name of the module to load.
     *
     * \return
     *      Returns the name of the class to use to create instances of this
     *      module.
     *
     * \throw EErebotInvalidValue
     *      The given \a $module name does not define a valid module.
     *
     * \note
     *      There should be a folder going by the name of the \a $module
     *      itself containing a file called \a $module.php in the
     *      "modules/" folder. The file must contain a single class derived
     *      from ErebotModuleBase for it to be considered a valid module.
     *      You're free to give that class whatever name you like.
     */
    public function loadModule($module)
    {
        if (isset($this->modulesMapping[$module]))
            return $this->modulesMapping[$module];

        $first = substr($module, 0, 1);
        if (strtoupper($first) != $first)
            throw new EErebotInvalidValue('Module names must start with '.
                                            'an uppercase letter');

        $moduleName = ErebotUtils::resolveRelative('', $module);
        $path = ErebotUtils::resolveRelative(__FILE__, '../..').
                '/modules/'.$module.'/'.$module.'.php';

        if (!is_readable($path))
            throw new EErebotInvalidValue('Not a valid module');

        $classes = get_declared_classes();
        $ok = ErebotUtils::incl($path);
        if ($ok === FALSE)
            throw new EErebotInvalidValue('Error while loading module');

        $classes = array_diff(get_declared_classes(), $classes);
        $classes = array_filter($classes, array($this, '__subclasses_EMB'));

        if (!count($classes))
            throw new EErebotInvalidValue("Bad module! No subclass of ".
                                            "ErebotModuleBase found.");

        // __inherits_EMB throws an exception if there are more than
        // two hierarchies of classes inheriting from ErebotModuleBase.
        usort($classes, array($this, '__inherits_EMB'));
        $class                          = reset($classes);
        $this->modulesMapping[$module]  = $class;
        return $class;
    }

    /**
     * Get the class associated with a given module.
     *
     * \param $modName
     *      Name of the (loaded) module whose class we're interested in.
     *
     * \return
     *      Upon success, the name of the class to use to create
     *      new instances of this module is returned.
     *
     * \throw EErebotNotFound
     *      No module with the given name has been loaded.
     *
     * \see
     *      Erebot::moduleClassToName does the opposite conversion.
     */
    public function moduleNameToClass($modName)
    {
        if (!isset($this->modulesMapping[$modName]))
            throw new EErebotNotFound('No such module');
        return $this->modulesMapping[$module];
    }

    /**
     * Get the name of the module associated with a given class.
     *
     * \param $className
     *      Name of the class whose module we're interested in.
     *
     * \return
     *      Upon success, the name of the module that the given class
     *      is used to create is returned.
     *
     * \throw EErebotNotFound
     *      No module using the given class name has been loaded.
     *
     * \see
     *      Erebot::moduleNameToClass does the opposite conversion.
     */
    public function moduleClassToName($className)
    {
        if (is_object($className))
            $className = get_class($className);

        $modName = array_search($className, $this->modulesMapping);
        if ($modName === FALSE)
            throw new EErebotNotFound('No such module');
        return $modName;
    }

    /**
     * Add a (new) connection to the bot.
     *
     * Once a new connection has been created, use this method to add
     * it to the pool of connections the bot must process.
     * This enables the connection to send and receive messages.
     *
     * \param $connection
     *      An ErebotConnection instance.
     *
     * \throw EErebotInvalidValue
     *      This connection is already part of the connection pool
     *      handled by this instance of the bot.
     */
    public function addConnection(iErebotConnection &$connection)
    {
        $key = array_search($connection, $this->connections);
        if ($key !== FALSE)
            throw new EErebotInvalidValue('Already handling this connection');

        $this->connections[]    =& $connection;
    }

    /**
     * Remove a connection from the bot.
     *
     * Use this method to remove a connection from the pool of connections
     * the bot must process, such as when the connection is lost with the
     * remote IRC server.
     *
     * \param $connection
     *      An ErebotConnection instance.
     *
     * \throw EErebotNotFound
     *      The given connection is not part of the connection pool
     *      handled by this instance of the bot.
     */
    public function removeConnection(iErebotConnection &$connection)
    {
        /* $this->connections is unset during destructor call,
         * but the destructing code depends on this method.
         * we silently ignore the problem. */
        if (!isset($this->connections))
            return;

        $key = array_search($connection, $this->connections);
        if ($key === FALSE)
            throw new EErebotNotFound('No such connection');

        unset($this->connections[$key]);
    }

    /**
     * Returns the translation of a message in the primary language.
     *
     * Use this method to get a translated message in the primary language.
     * That is, using the language defined in the "language" attribute of
     * the "configuration" tag in your XML configuration file.
     *
     * \param $message
     *      The original message to translate, in english.
     *
     * \return
     *      Returns the translation for this message or the original (english)
     *      message if no translation is available.
     */
    public function gettext($message)
    {
        $translator = $this->mainCfg->getTranslator('Erebot');
        $function   = 'gettext';
        return $translator->$function($message);
    }
}

?>
