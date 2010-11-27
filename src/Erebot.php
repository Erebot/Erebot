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

// Define the __DIR__ magic-constant for pre-5.3.0.
// Many thanks to the anonymous person who posted this trick on:
// http://php.net/manual/en/language.constants.predefined.php#99278
if (!defined('__DIR__')) {
  class __FILE_CLASS__ {
    function  __toString() {
      $X = debug_backtrace();
      return dirname($X[1]['file']);
    }
  }
  define('__DIR__', new __FILE_CLASS__);
} 

/*
/// @TODO re-think integration of Doctrine a little...
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
 * \link Erebot_Connection connections\endlink, handle
 * \link Erebot_Timer timers\endlink, apply multiplexing, etc.
 *
 * The main method you will be interested in is Erebot::start() which
 * starts the bot.
 */
class       Erebot
implements  Erebot_Interface_Core
{
    /// List of \link Erebot_Interface_Connection connections\endlink to handle.
    protected $_connections;

    /// List of \link Erebot_Interface_Timer timers\endlink to trigger, eventually.
    protected $_timers;

    /// Main configuration for the bot.
    protected $_mainCfg;

    /// Indicates whether the bot is currently running or not.
    protected $_running;

    // Documented in the interface.
    public function __construct(Erebot_Interface_Config_Main $config)
    {
        $this->_connections     =
        $this->_timers          = array();
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
    public function start($connectionCls = 'Erebot_Connection')
    {
        $logging    =&  Plop::getInstance();
        $logger     =   $logging->getLogger(__FILE__);
        $logger->info($this->gettext('Erebot is starting'));

        if (!is_string($connectionCls) || !class_exists($connectionCls))
            throw new Erebot_InvalidValueException('Not a valid class name '.
                $connectionCls);
        else {
            $reflect = new ReflectionClass($connectionCls);
            if (!$reflect->implementsInterface('Erebot_Interface_Connection'))
                throw new Erebot_InvalidValueException($connectionCls.' does not '.
                    'implement the Erebot_Interface_Connection interface');
        }

        // Let's establish some contacts.
        $networks = $this->_mainCfg->getNetworks();
        foreach ($networks as $network) {
            $servers = $network->getServers();

            if (!in_array('Erebot_Module_AutoConnect', $network->getModules(TRUE)))
                continue;

            foreach ($servers as $server) {
                try {
                    $connection = new $connectionCls($this, $server);
                    $connection->connect();
                    $this->_connections[] = $connection;
                    break;
                }
                catch (Erebot_ConnectionFailureException $e) {
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
                // Throws a warning under PHP 5.2 when a signal is received.
                $nb = @stream_select($read, $write, $except, NULL);
            }
            catch (Erebot_ErrorReportingException $e) {
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
                        catch (Erebot_ConnectionFailureException $e) {
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
                if ($index !== FALSE && isset($this->_connections[$index]) &&
                    $this->_connections[$index] instanceof $connectionCls)
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
            $event = new Erebot_Event_Exit($connection);
            $connection->dispatchEvent($event);
            $connection->disconnect();
        }
        unset($connection);

        $logger->info($this->gettext('Erebot has stopped'));
        unset(
            $this->_timers,
            $this->_connections,
            $this->_mainCfg
        );

        $this->_running         = FALSE;
        $this->_connections     =
        $this->_timers          =
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
            $logger->info($this->gettext('Memory usage:'));

            $stats = array(
                $this->gettext("Allocated:")    => memory_get_peak_usage(TRUE)."B",
                $this->gettext("Used:")         => memory_get_peak_usage(FALSE)."B",
                $this->gettext("Limit:")        => ini_get('memory_limit'),
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
    public function addTimer(Erebot_Interface_Timer &$timer)
    {
        $key = array_search($timer, $this->_timers);
        if ($key !== FALSE)
            throw new Erebot_InvalidValueException('Timer already registered');

        $timer->reset();
        $this->_timers[] =&  $timer;
    }

    // Documented in the interface.
    public function removeTimer(Erebot_Interface_Timer &$timer)
    {
        $key = array_search($timer, $this->_timers);
        if ($key === FALSE)
            throw new Erebot_NotFoundException('Timer not found');

        unset($this->_timers[$key]);
    }

    // Documented in the interface.
    public static function getVersion()
    {
        return 'Erebot v'.self::VERSION;
    }

    // Documented in the interface.
    public function addConnection(Erebot_Interface_Connection &$connection)
    {
        $key = array_search($connection, $this->_connections);
        if ($key !== FALSE)
            throw new Erebot_InvalidValueException('Already handling this connection');

        $this->_connections[] =& $connection;
    }

    // Documented in the interface.
    public function removeConnection(Erebot_Interface_Connection &$connection)
    {
        /* $this->_connections is unset during destructor call,
         * but the destructing code depends on this method.
         * we silently ignore the problem. */
        if (!isset($this->_connections))
            return;

        $key = array_search($connection, $this->_connections);
        if ($key === FALSE)
            throw new Erebot_NotFoundException('No such connection');

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

// For older versions of PHP that support signals
// but don't support pcntl_signal_dispatch (5.2.x).
if (!function_exists('pcntl_signal_dispatch'))
    declare(ticks=1);

