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

// We want maximum verbosity! (or just a clean code :)
error_reporting(E_ALL | E_STRICT);
// The bot may run indefinitely, avoid the default 30 seconds time limit.
set_time_limit(0);

if (!defined('__DIR__')) {
  class __FILE_CLASS__ {
    function  __toString() {
      $X = debug_backtrace();
      return dirname($X[1]['file']);
    }
  }
  define('__DIR__', new __FILE_CLASS__);
} 

include_once(__DIR__.'/logging/src/Plop/Plop.php');

include_once(__DIR__.'/utils.php');
// We need to include the styling API,
// so that modules do not need to do it themselves.
include_once(__DIR__.'/styling.php');
include_once(__DIR__.'/events/raws.php');
include_once(__DIR__.'/events/events.php');
include_once(__DIR__.'/moduleBase.php');
include_once(__DIR__.'/connection.php');
include_once(__DIR__.'/config/mainConfig.php');
include_once(__DIR__.'/timer.php');
include_once(__DIR__.'/exceptions/NotImplemented.php');
include_once(__DIR__.'/exceptions/ErrorReporting.php');
include_once(__DIR__.'/ifaces/core.php');

/*
/// @TODO: re-think integration of Doctrine a little...
// Especially, we don't want to depend on it too much.
include_once(__DIR__.'/orm/Doctrine.php');

// Initialize Doctrine.
spl_autoload_register(array('Doctrine', 'autoload'));
$manager = Doctrine_Manager::getInstance();
$manager->setAttribute(
    Doctrine_Core::ATTR_VALIDATE,
    Doctrine_Core::VALIDATE_ALL
);
$manager->setAttribute(
    Doctrine_Core::ATTR_EXPORT,
    Doctrine_Core::EXPORT_ALL
);
$manager->setAttribute(
    Doctrine_Core::ATTR_MODEL_LOADING,
    Doctrine_Core::MODEL_LOADING_CONSERVATIVE
);
unset($manager);
*/

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

    /// List of \link iErebotConnection iErebotConnections\endlink to handle.
    protected $_connections;

    /// List of \link iErebotTimer iErebotTimers\endlink to trigger, eventually.
    protected $_timers;

    /// Dictionary with mappings between modules and their classes.
    protected $_modulesMapping;

    /// Main configuration for the bot.
    protected $_mainCfg;

    /// Indicates whether the bot is currently running or not.
    protected $_running;

    // Documented in the interface.
    public function __construct(iErebotMainConfig $config)
    {
        $this->_connections     =
        $this->_timers          =
        $this->_modulesMapping  = array();
        $this->_running         = FALSE;
        $this->_mainCfg  = $config;

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
        return $this->_connections;
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
        $networks       =   $this->_mainCfg->getNetworks();
        foreach ($networks as $network) {
            $servers    = $network->getServers();

            if (!in_array('AutoConnect', $network->getModules(TRUE)))
                continue;

            foreach ($servers as $server) {
                try {
                    $connection = new $connectionCls($this, $server);
                    $connection->connect();
                    $this->_connections[] = $connection;
                    break;
                }
                catch (EErebotConnectionFailure $e) {
                    // Nothing to do... We simply
                    // try the next server on the
                    // list until we successfully
                    // connect or cycle the list.
                    $logger->warning(
                        $this->gettext('Could not connect to "%s"'),
                        $server->getConnectionURL()
                    );
                }
            }
        }

        // This flag is changed by quitGracefully()
        // when the bot should stop.
        $this->_running = TRUE;

        // Main loop
        while ($this->_running) {
            $logger->debug($this->gettext('Main event loop'));

            // This is the way PHP 5.3 passes signals to their handlers.
            if (function_exists('pcntl_signal_dispatch'))
                pcntl_signal_dispatch();

            $read = $write = $except = array();
            $actives = array('connections' => array(), 'timers' => array());

            if ($this->_connections === NULL)
                break;

            // Find out connections in need of some handling.
            foreach ($this->_connections as $index => &$connection) {
                $socket = $connection->getSocket();

                if (!$connection->emptySendQueue())
                    $write[]    = $socket;

                $except[]                       = $socket;
                $read[]                         = $socket;
                $actives['connections'][$index] = $socket;
            }

            // Find out timed out timers.
            foreach ($this->_timers as $index => &$timer) {
                $stream = $timer->getStream();

                $read[]                     = $stream;
                $actives['timers'][$index]  = $stream;
            }

            unset($connection, $timer);

            // No activity.
            if (count($read) + count($write) + count($except) == 0) {
                $logger->debug(
                    $this->gettext('No more connections to handle, leaving...')
                );
                return $this->stop();
            }

            try {
                // Block until there is activity. Since timers
                // are treated as streams too, we will also wake
                // up whenever a timer fires.
                $nb = stream_select($read, $write, $except, NULL);
            }
            catch (EErebotErrorReporting $e) {
                if ($this->_running)
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
                    $index = array_search(
                        $socket,
                        $actives['connections'],
                        TRUE
                    );
                    if ($index !== FALSE) {
                        // Read as much data from the connection as possible.
                        try {
                            $this->_connections[$index]->processIncomingData();
                        }
                        catch (EErebotConnectionFailure $e) {
                            $logger->info(
                                $this->gettext(
                                    'Connection failed, removing it '.
                                    'from the pool'
                                )
                            );
                            $this->removeConnection(
                                $this->_connections[$index]
                            );
                        }
                        break;
                    }

                    // Is it a timer?
                    $index = array_search($socket, $actives['timers'], TRUE);
                    if ($index !== FALSE) {
                        $timer      =&  $this->_timers[$index];
                        $restart    =   $timer->activate();

                        // Maybe the callback function
                        // removed the timer already.
                        if (!isset($this->_timers[$index]))
                            break;

                        // Otherwise, restart or remove it as necessary.
                        if ($restart === TRUE || $timer->isRepeated())
                            $timer->reset();
                        else
                            unset($this->_timers[$index]);

                        break;
                    }

                    // No, it's superman!! Let's do nothing then...
                } while (0);
            }

            // Take care of incoming data waiting for processing.
            foreach ($this->_connections as &$connection) {
                $connection->processQueuedData();
            }
            unset($connection);

            // Handle write-ready sockets (flush outgoing data).
            foreach ($write as $socket) {
                $index = array_search($socket, $actives['connections']);
                if ($index !== FALSE &&
                    is_a($this->_connections[$index], $connectionCls))
                    $this->_connections[$index]->processOutgoingData();
            }
        }
    }

    // Documented in the interface.
    public function stop()
    {
        $logging    =&  Plop::getInstance();
        $logger     =   $logging->getLogger(__FILE__);

        if (!$this->_running)
            return;

        foreach ($this->_connections as &$connection) {
            $event = new ErebotEventExit($connection);
            $connection->dispatchEvent($event);
            $connection->disconnect();
        }
        unset($connection);

        $logger->info($this->gettext('Erebot has stopped'));
        unset(
            $this->_timers,
            $this->_connections,
            $this->_modulesMapping,
            $this->_mainCfg
        );

        $this->_running         = FALSE;
        $this->_connections     =
        $this->_timers          =
        $this->_modulesMapping  =
        $this->_mainCfg         = NULL;
    }

    /**
     * Handles request for a graceful shutdown of the bot.
     * Such request are received as signals.
     *
     * \param int $signum
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

        $logging    =&  Plop::getInstance();
        $logger     =   $logging->getLogger(__FILE__);
        $logger->info(
            $this->gettext(
                'Received signal #%(signum)d (%(signame)s)'
            ),
            array(
                'signum'    => $signum,
                'signame'   => $signame,
            )
        );

        // Print some statistics.
        if (function_exists('memory_get_peak_usage')) {
            $logger->info($this->gettext('Max. Allocated memory:'));

            $stats = array(
                $this->gettext("System:")   => memory_get_peak_usage(TRUE)."B",
                $this->gettext("Internal:") => memory_get_peak_usage(FALSE)."B",
                $this->gettext("Limit:")    => ini_get('memory_limit'),
            );

            foreach ($stats as $key => $value)
                $logger->info(
                    '%(key)-16s%(value)10s',
                    array(
                        'key'   => $key,
                        'value' => $value,
                    )
                );
        }

        $this->stop();
    }

    // Documented in the interface.
    public function getTimers()
    {
        return $this->_timers;
    }

    // Documented in the interface.
    public function addTimer(iErebotTimer &$timer)
    {
        $key = array_search($timer, $this->_timers);
        if ($key !== FALSE)
            throw new EErebotInvalidValue('Timer already registered');

        $timer->reset();
        $this->_timers[] =&  $timer;
    }

    // Documented in the interface.
    public function removeTimer(iErebotTimer &$timer)
    {
        $key = array_search($timer, $this->_timers);
        if ($key === FALSE)
            throw new EErebotNotFound('Timer not found');

        unset($this->_timers[$key]);
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
     * \retval TRUE
     *      The given class subclasses ErebotModuleBase.
     *
     * \retval FALSE
     *      It does not subclasses ErebotModuleBase.
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
     * \param string $a
     *      First class.
     *
     * \param string $b
     *      Second class.
     *
     * \retval -1
     *      $a subclasses $b.
     *
     * \retval +1
     *      $b subclasses $a.
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
        if (isset($this->_modulesMapping[$module]))
            return $this->_modulesMapping[$module];

        $first = substr($module, 0, 1);
        if (strtoupper($first) != $first)
            throw new EErebotInvalidValue('Module names must start with '.
                                            'an uppercase letter');

        $classes = get_declared_classes();

        $pathParts      = array('modules', $module);
        $pathParts[]    = $module.'.php';

        $path = implode(DIRECTORY_SEPARATOR, $pathParts);
        if (!file_exists($path)) {
            // Try again with a "trunk" subpath (for dev environments).
            array_splice($pathParts, 2, 0, array('trunk'));
            $path = implode(DIRECTORY_SEPARATOR, $pathParts);
            if (!file_exists($path))
                throw new EErebotInvalidValue('No such module');
        }

        $ok = include_once($path);
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
        $this->_modulesMapping[$module] = $class;
        return $class;
    }

    // Documented in the interface.
    public function moduleNameToClass($modName)
    {
        if (!isset($this->_modulesMapping[$modName]))
            throw new EErebotNotFound('No such module');
        return $this->_modulesMapping[$module];
    }

    // Documented in the interface.
    public function moduleClassToName($className)
    {
        if (is_object($className))
            $className = get_class($className);

        $modName = array_search($className, $this->_modulesMapping);
        if ($modName === FALSE)
            throw new EErebotNotFound('No such module');
        return $modName;
    }

    // Documented in the interface.
    public function addConnection(iErebotConnection &$connection)
    {
        $key = array_search($connection, $this->_connections);
        if ($key !== FALSE)
            throw new EErebotInvalidValue('Already handling this connection');

        $this->_connections[] =& $connection;
    }

    // Documented in the interface.
    public function removeConnection(iErebotConnection &$connection)
    {
        /* $this->_connections is unset during destructor call,
         * but the destructing code depends on this method.
         * we silently ignore the problem. */
        if (!isset($this->_connections))
            return;

        $key = array_search($connection, $this->_connections);
        if ($key === FALSE)
            throw new EErebotNotFound('No such connection');

        unset($this->_connections[$key]);
    }

    // Documented in the interface.
    public function gettext($message)
    {
        $translator = $this->_mainCfg->getTranslator('Erebot');
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

