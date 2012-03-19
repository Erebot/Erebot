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

    /// List of \link Erebot_Interface_Timer timers\endlink to trigger.
    protected $_timers;

    /// Main configuration for the bot.
    protected $_mainCfg;

    /// Indicates whether the bot is currently running or not.
    protected $_running;

    /// Translator object for messages the bot may display.
    protected $_translator;

    /**
     * Creates a new Erebot instance.
     *
     * \param Erebot_Interface_Config_MainInterface $config
     *      The (main) configuration to use.
     */
    public function __construct(
        Erebot_Interface_Config_Main    $config,
        Erebot_Interface_I18n           $translator
    )
    {
        $this->_connections     =
        $this->_timers          = array();
        $this->_running         = FALSE;
        $this->_mainCfg         = $config;
        $this->_translator      = $translator;

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
                pcntl_signal($signal, array($this, 'handleSignal'), TRUE);

            pcntl_signal(SIGHUP, array($this, 'handleSIGHUP'), TRUE);
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
     * Copy-constructor.
     */
    public function __clone()
    {
        throw new Exception("Cloning forbidden!");
    }

    /// \copydoc Erebot_Interface_Core::getConnections()
    public function getConnections()
    {
        return $this->_connections;
    }

    /// \copydoc Erebot_Interface_Core::start()
    public function start(Erebot_Interface_ConnectionFactory $factory)
    {
        $logging    = Plop::getInstance();
        $logger     = $logging->getLogger(__FILE__);
        $logger->info($this->gettext('Erebot is starting'));

        // This is changed by handleSignal()
        // when the bot should stop.
        $this->_running = time();

        $this->_createConnections($factory, $this->_mainCfg);

        // PHP 5.3 way of handling signals.
        $hasSignalDispatch = function_exists('pcntl_signal_dispatch');

        // Main loop
        while ($this->_running) {
            if ($hasSignalDispatch)
                pcntl_signal_dispatch();

            $read = $write = $except = array();
            $actives = array('connections' => array(), 'timers' => array());

            if ($this->_connections === NULL)
                break;

            // Find out connections in need of some handling.
            foreach ($this->_connections as $index => $connection) {
                $socket = $connection->getSocket();

                if ($connection instanceof Erebot_Interface_SendingConnection &&
                    !$connection->emptySendQueue())
                    $write[]    = $socket;

                if ($connection instanceof Erebot_Interface_ReceivingConnection)
                    $read[] = $socket;

                $except[]                       = $socket;
                $actives['connections'][$index] = $socket;
            }

            // Find out timed out timers.
            foreach ($this->_timers as $index => $timer) {
                $stream = $timer->getStream();

                $read[]                     = $stream;
                $actives['timers'][$index]  = $stream;
            }

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

            // Handle exceptional (out-of-band) data.
            // It seems that PHP will mark signal interruptions with OOB data.
            // We simply do a new iteration, because the signal dispatcher
            // will be called right away if needed.
            // For older versions, see declare(ticks) at the end.
            if (count($except))
                continue;

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
                        $timer      = $this->_timers[$index];
                        // During shutdown, weird things happen to timers,
                        // including magical disappearance.
                        if (!is_object($timer)) {
                            unset($this->_timers[$index]);
                            break;
                        }
                        $restart    = $timer->activate();

                        // Maybe the callback function
                        // removed the timer already.
                        if (!isset($this->_timers[$index]))
                            break;

                        // Otherwise, restart or remove it as necessary.
                        if ($restart === TRUE || $timer->getRepetition())
                            $timer->reset();
                        else
                            unset($this->_timers[$index]);

                        break;
                    }

                    // No, it's superman!! Let's do nothing then...
                } while (0);
            }

            // Take care of incoming data waiting for processing.
            if (is_array($this->_connections)) {
                foreach ($this->_connections as $connection) {
                    if ($connection instanceof
                        Erebot_Interface_ReceivingConnection)
                        $connection->processQueuedData();
                }
            }

            // Handle write-ready sockets (flush outgoing data).
            foreach ($write as $socket) {
                $index = array_search($socket, $actives['connections']);
                if ($index !== FALSE && isset($this->_connections[$index]) &&
                    $this->_connections[$index] instanceof
                    Erebot_Interface_SendingConnection)
                    $this->_connections[$index]->processOutgoingData();
            }
        }
    }

    /// \copydoc Erebot_Interface_Core::stop()
    public function stop()
    {
        $logging    = Plop::getInstance();
        $logger     = $logging->getLogger(__FILE__);

        if (!$this->_running)
            return;

        foreach ($this->_connections as $connection) {
            if ($connection instanceof Erebot_Interface_EventDispatcher) {
                $eventsProducer = $connection->getEventsProducer();
                $connection->dispatch($$eventsProducer->makeEvent('!Exit'));
            }
        }

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
     * Such requests are received as signals.
     *
     * \param int $signum
     *      The number of the signal.
     */
    public function handleSignal($signum)
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

        $logging    = Plop::getInstance();
        $logger     = $logging->getLogger(__FILE__);
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
            $logger->debug($this->gettext('Memory usage:'));

            $limit  = trim(ini_get('memory_limit'));
            $limit  = Erebot_Utils::parseHumanSize($limit."B");
            $stats  = array(
                $this->gettext("Allocated:") =>
                    Erebot_Utils::humanSize(memory_get_peak_usage(TRUE)),
                $this->gettext("Used:") =>
                    Erebot_Utils::humanSize(memory_get_peak_usage(FALSE)),
                $this->gettext("Limit:") =>
                    Erebot_Utils::humanSize($limit),
            );

            foreach ($stats as $key => $value)
                $logger->debug(
                    '%(key)-16s%(value)10s',
                    array(
                        'key'   => $key,
                        'value' => $value,
                    )
                );
        }

        $this->stop();
    }

    /// \copydoc Erebot_Interface_Core::getTimers()
    public function getTimers()
    {
        return $this->_timers;
    }

    /// \copydoc Erebot_Interface_Core::addTimer()
    public function addTimer(Erebot_Interface_Timer $timer)
    {
        $key = array_search($timer, $this->_timers);
        if ($key !== FALSE)
            throw new Erebot_InvalidValueException('Timer already registered');

        $timer->reset();
        $this->_timers[] =  $timer;
    }

    /// \copydoc Erebot_Interface_Core::removeTimer()
    public function removeTimer(Erebot_Interface_Timer $timer)
    {
        $key = array_search($timer, $this->_timers);
        if ($key === FALSE)
            throw new Erebot_NotFoundException('Timer not found');

        unset($this->_timers[$key]);
    }

    /// \copydoc Erebot_Interface_Core::getVersion()
    public static function getVersion()
    {
        return 'Erebot v'.self::VERSION;
    }

    /// \copydoc Erebot_Interface_Core::addConnection()
    public function addConnection(Erebot_Interface_Connection $connection)
    {
        $key = array_search($connection, $this->_connections);
        if ($key !== FALSE)
            throw new Erebot_InvalidValueException(
                'Already handling this connection'
            );

        $this->_connections[] = $connection;
    }

    /// \copydoc Erebot_Interface_Core::removeConnection()
    public function removeConnection(Erebot_Interface_Connection $connection)
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

    /// \copydoc Erebot_Interface_Core::gettext()
    public function gettext($message)
    {
        return $this->_translator->gettext($message);
    }

    /// \copydoc Erebot_Interface_Core::getRunningTime()
    public function getRunningTime()
    {
        if (!$this->_running)
            return FALSE;
        return time() - $this->_running;
    }

    /**
     * Handles requests to reload the configuration.
     * Such requests are received as signals.
     *
     * \param int $signum
     *      The number of the signal.
     */
    public function handleSIGHUP($signum)
    {
        return $this->reload();
    }

    public function reload(Erebot_Interface_Config_Main $config = NULL)
    {
        $logging    = Plop::getInstance();
        $logger     = $logging->getLogger(__FILE__);

        $msg = $this->gettext('Reloading the configuration');
        $logger->info($msg);

        if (!count($this->_connections)) {
            $logger->info($this->gettext('No active connections... Aborting.'));
            return;
        }

        if ($config === NULL) {
            $configFile = $this->_mainCfg->getConfigFile();
            if ($configFile === NULL) {
                $msg = $this->gettext('No configuration file to reload');
                $logger->info($msg);
                return;
            }

            /// @TODO: dependency injection
            $config = new Erebot_Config_Main(
                $configFile,
                Erebot_Interface_Config_Main::LOAD_FROM_FILE
            );
        }

        $connectionCls = get_class($this->_connections[0]);
        $this->_createConnections($connectionCls, $config);
        $msg = $this->gettext('Successfully reloaded the configuration');
        $logger->info($msg);
    }

    protected function _createConnections(
        Erebot_Interface_ConnectionFactory  $factory,
        Erebot_Interface_Config_Main        $config
    )
    {
        $logging    = Plop::getInstance();
        $logger     = $logging->getLogger(__FILE__);

        // List existing connections so they
        // can eventually be reused.
        $newConnections     =
        $currentConnections = array();
        foreach ($this->_connections as $connection) {
            $connCfg = $connection->getConfig(NULL);
            if ($connCfg) {
                $netCfg = $connCfg->getNetworkCfg();
                $currentConnections[$netCfg->getName()] = $connection;
            }
            else
                $newConnections[] = $connection;
        }

        // Let's establish some contacts.
        $networks = $config->getNetworks();
        foreach ($networks as $network) {
            $netName = $network->getName();
            if (isset($currentConnections[$netName])) {
                try {
                    $uris   = $currentConnections[$netName]
                                ->getConfig(NULL)
                                ->getConnectionURI();
                    $uri    = new Erebot_URI($uris[count($uris) - 1]);
                    $serverCfg = $network->getServerCfg((string) $uri);

                    $logger->info(
                        $this->gettext(
                            'Reusing existing connection for network "%s"'
                        ),
                        $netName
                    );
                    // Move it from existing connections to new connections,
                    // marking it as still being in use.
                    $copy = clone $currentConnections[$netName];
                    $copy->reload($serverCfg);
                    $newConnections[] = $copy;
                    unset($currentConnections[$netName]);
                    continue;
                }
                catch (Erebot_NotFoundException $e) {
                    // Nothing to do.
                }
            }

            if (!in_array(
                'Erebot_Module_AutoConnect',
                $network->getModules(TRUE)
            ))
                continue;

            $servers = $network->getServers();
            foreach ($servers as $server) {
                $uris       = $server->getConnectionURI();
                $serverUri  = new Erebot_URI($uris[count($uris) - 1]);
                try {
                    $connection = $factory->newConnection($this, $server);

                    // Drop connection to a (now-)unconfigured
                    // server on that network.
                    if (isset($currentConnections[$netName])) {
                        $currentConnections[$netName]->disconnect();
                        unset($currentConnections[$netName]);
                    }

                    $logger->info(
                        $this->gettext('Trying to connect to "%s"...'),
                        $serverUri
                    );
                    $connection->connect();
                    $newConnections[] = $connection;

                    $logger->info(
                        $this->gettext('Successfully connected to "%s"...'),
                        $serverUri
                    );

                    break;
                }
                catch (Erebot_ConnectionFailureException $e) {
                    // Nothing to do... We simply
                    // try the next server on the
                    // list until we successfully
                    // connect or cycle the list.
                    $logger->exception(
                        $this->gettext('Could not connect to "%s"'),
                        $e, $serverUri
                    );
                }
            }
        }

        // Gracefully quit leftover connections.
        foreach ($currentConnections as $connection) {
            $connection->disconnect();
        }

        $this->_connections = $newConnections;
        $this->_mainCfg     = $config;
    }
}

if (!empty($_SERVER['DOCUMENT_ROOT']))
    die("This script isn't meant to be run from the Internet!\n");

// For older versions of PHP that support signals
// but don't support pcntl_signal_dispatch (5.2.x).
if (!function_exists('pcntl_signal_dispatch'))
    declare(ticks=1);

