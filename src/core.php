<?php

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
 * The main method you will be interested in is Erebot::start() which
 * starts the bot.
 */
class       Erebot
implements  iErebot
{
    /**
     * The default class to use to create new connections if no specific class
     * is passed to the start() method.
     */
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

    // Documented in the interface.
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

    // Documented in the interface.
    public function getConnections()
    {
        return $this->connections;
    }

    // Documented in the interface.
    public function start($connectionCls = NULL)
    {
        $logging    =&  Plop::getInstance();
        $logger     =   $logging->getLogger(__FILE__);
        $logger->info($this->gettext('Erebot is starting'));

        if ($connectionCls === NULL)
            $connectionCls = self::DEFAULT_CONNECTION_CLS;
        else if (!is_string($connectionCls) || !class_exists($connectionCls))
            throw new EErebotInvalidValue('Not a valid class name '.
                $connectionCls);
        else {
            $reflect = new ReflectionClass($connectionCls);
            if (!$reflect->implementsInterface('iErebotConnection'))
                throw new EErebotInvalidValue($connectionCls.' does not '.
                    'implement the iErebotConnection interface');
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

    // Documented in the interface.
    public function stop()
    {
        $logging    =&  Plop::getInstance();
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

    // Documented in the interface.
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

        $logging    =&  Plop::getInstance();
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

    // Documented in the interface.
    public function getTimers()         { return $this->timers; }

    // Documented in the interface.
    public function addTimer(iErebotTimer &$timer)
    {
        $key = array_search($timer, $this->timers);
        if ($key !== FALSE)
            throw new EErebotInvalidValue('Timer already registered');

        $timer->reset();
        $this->timers[] =&  $timer;
    }

    // Documented in the interface.
    public function removeTimer(iErebotTimer &$timer)
    {
        $key = array_search($timer, $this->timers);
        if ($key === FALSE)
            throw new EErebotNotFound('Timer not found');

        unset($this->timers[$key]);
    }

    // Documented in the interface.
    public static function getVersion()
    {
        return 'Erebot v'.self::VERSION;
    }

    /**
     * Determines whether a given class subclasses ErebotModuleBase.
     * This method is used to create an inheritance tree for a newly
     * loaded module and determine whether that module respects a
     * set of constraints.
     *
     * \return
     *      A boolean indicating whether the given class subclasses
     *      ErebotModuleBase (\b{TRUE}) or not (\b{FALSE}).
     */
    static private function __subclasses_EMB($a)
    {
        return (is_subclass_of($a, "ErebotModuleBase"));
    }

    /**
     * Determines how two classes inherit from one another.
     * This method is used to create an inheritance tree for a newly
     * loaded module and determine whether that module respects a
     * set of constraints.
     *
     * \param $a
     *      First class.
     *
     * \param $b
     *      Second class.
     *
     * \return
     *      Returns :
     *      \li -1 if $a subclasses $b.
     *      \li +1 if $b subclasses $a.
     *
     * \note
     *      This method assumes both classes are subclasses
     *      of ErebotModuleBase.
     *
     * \throw EErebotInvalidValue
     *      The two classes are not related (ie: none of the two
     *      classes subclasses the other one).
     */
    static private function __inherits_EMB($a, $b)
    {
        if (is_subclass_of($a, get_class($b)))
            return -1;
        if (is_subclass_of($b, get_class($a)))
            return +1;
        throw new EErebotInvalidValue("Bad module! There must be exactly ".
            "1 subclass of ErebotModuleBase in it.");
    }

    // Documented in the interface.
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

    // Documented in the interface.
    public function moduleNameToClass($modName)
    {
        if (!isset($this->modulesMapping[$modName]))
            throw new EErebotNotFound('No such module');
        return $this->modulesMapping[$module];
    }

    // Documented in the interface.
    public function moduleClassToName($className)
    {
        if (is_object($className))
            $className = get_class($className);

        $modName = array_search($className, $this->modulesMapping);
        if ($modName === FALSE)
            throw new EErebotNotFound('No such module');
        return $modName;
    }

    // Documented in the interface.
    public function addConnection(iErebotConnection &$connection)
    {
        $key = array_search($connection, $this->connections);
        if ($key !== FALSE)
            throw new EErebotInvalidValue('Already handling this connection');

        $this->connections[]    =& $connection;
    }

    // Documented in the interface.
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

    // Documented in the interface.
    public function gettext($message)
    {
        $translator = $this->mainCfg->getTranslator('Erebot');
        $function   = 'gettext';
        return $translator->$function($message);
    }
}

if (!empty($_SERVER['DOCUMENT_ROOT']))
    die("This script isn't meant to be run from the Internet!\n");

// For older versions of PHP (>= 4.3.0)
// which don't support pcntl_signal_dispatch,
// and yet support signals (< 5.3.0).
if (!function_exists('pcntl_signal_dispatch'))
    declare(ticks=1);

?>
