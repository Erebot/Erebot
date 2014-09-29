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

namespace Erebot;

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
 * \link Erebot::Interfaces::Connection connections\endlink, handle
 * \link Erebot::TimerInterface timers\endlink, apply multiplexing,
 * etc.
 *
 * The main method you will be interested in is Erebot::start() which
 * starts the bot.
 */
class Core implements \Erebot\Interfaces\Core
{
    /// \link Erebot::Interfaces::Connection Connections\endlink to handle.
    protected $connections;

    /// \link Erebot::TimerInterface Timers\endlink to trigger.
    protected $timers;

    /// Main configuration for the bot.
    protected $mainCfg;

    /// Indicates whether the bot is currently running or not.
    protected $running;

    /// Translator object for messages the bot may display.
    protected $translator;

    /**
     * Creates a new Erebot instance.
     *
     * \param Erebot::Interfaces::Config::MainInterface $config
     *      The (main) configuration to use.
     *
     * \param Erebot::IntlInterface $translator
     *      A translator object that will provide translations
     *      for messages emitted by the core class.
     */
    public function __construct(
        \Erebot\Interfaces\Config\Main $config,
        \Erebot\IntlInterface $translator
    ) {
        $this->connections  =
        $this->timers       = array();
        $this->running      = false;
        $this->mainCfg      = $config;
        $this->translator   = $translator;

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

            foreach ($signals as $signal) {
                pcntl_signal($signal, array($this, 'handleSignal'), true);
            }

            pcntl_signal(SIGHUP, array($this, 'handleSIGHUP'), true);
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
        throw new \Exception("Cloning forbidden!");
    }

    /// \copydoc Erebot::Interfaces::Core::getConnections()
    public function getConnections()
    {
        return $this->connections;
    }

    /**
     * Really starts the bot.
     *
     * \param Erebot::Interfaces::ConnectionFactory $factory
     *      Factory to use to create new connections.
     *
     * \attention
     *      This method does not return until the bot drops its connections.
     *      Therefore, this MUST be the last method you call in your script.
     *
     * \note
     *      This method is called by Erebot::start() and does
     *      the actual workload of running the bot.
     */
    protected function realStart(\Erebot\Interfaces\ConnectionFactory $factory)
    {
        $logger = \Plop\Plop::getInstance();
        $logger->info($this->gettext('Erebot is starting'));

        // This is changed by handleSignal()
        // when the bot should stop.
        $this->running = time();

        $this->createConnections($factory, $this->mainCfg);

        // Main loop
        while ($this->running) {
            pcntl_signal_dispatch();

            $read = $write = $except = array();
            $actives = array('connections' => array(), 'timers' => array());

            if ($this->connections === null) {
                break;
            }

            // Find out connections in need of some handling.
            foreach ($this->connections as $index => $connection) {
                $socket = $connection->getSocket();

                if ($connection instanceof \Erebot\Interfaces\SendingConnection &&
                    $connection->getIO()->inWriteQueue()) {
                    $write[]    = $socket;
                }

                if ($connection instanceof \Erebot\Interfaces\ReceivingConnection) {
                    $read[] = $socket;
                }

                $except[]                       = $socket;
                $actives['connections'][$index] = $socket;
            }

            // Find out timed out timers.
            foreach ($this->timers as $index => $timer) {
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
                $nb = @stream_select($read, $write, $except, null);
            } catch (\Erebot\ErrorReportingException $e) {
                if ($this->running) {
                    $logger->exception($this->gettext('Got exception'), $e);
                } else {
                    /* If the bot is not running anymore,
                     * this probably means we received a signal.
                     * We continue to the next iteration,
                     * which will make the bot exit properly. */
                    continue;
                }
            }

            // Handle exceptional (out-of-band) data.
            // It seems that PHP will mark signal interruptions with OOB data.
            // We simply do a new iteration, because the signal dispatcher
            // will be called right away if needed.
            // For older versions, we use declare(ticks) (see Patches.php).
            if (count($except)) {
                continue;
            }

            // Handle read-ready "sockets"
            foreach ($read as $socket) {
                do {
                    // Is it a connection?
                    $index = array_search(
                        $socket,
                        $actives['connections'],
                        true
                    );
                    if ($index !== false) {
                        // Read as much data from the connection as possible.
                        try {
                            $this->connections[$index]->read();
                        } catch (\Erebot\ConnectionFailureException $e) {
                            $logger->info(
                                $this->gettext(
                                    'Connection failed, removing it '.
                                    'from the pool'
                                )
                            );
                            $this->removeConnection(
                                $this->connections[$index]
                            );
                        }
                        break;
                    }

                    // Is it a timer?
                    $index = array_search($socket, $actives['timers'], true);
                    if ($index !== false) {
                        $timer = $this->timers[$index];
                        // During shutdown, weird things happen to timers,
                        // including magical disappearance.
                        if (!is_object($timer)) {
                            unset($this->timers[$index]);
                            break;
                        }
                        $restart    = $timer->activate();

                        // Maybe the callback function
                        // removed the timer already.
                        if (!isset($this->timers[$index])) {
                            break;
                        }

                        // Otherwise, restart or remove it as necessary.
                        if ($restart === true || $timer->getRepetition()) {
                            $timer->reset();
                        } else {
                            unset($this->timers[$index]);
                        }

                        break;
                    }

                    // No, it's superman!! Let's do nothing then...
                } while (0);
            }

            // Take care of incoming data waiting for processing.
            if (is_array($this->connections)) {
                foreach ($this->connections as $connection) {
                    if ($connection instanceof \Erebot\Interfaces\ReceivingConnection) {
                        $connection->process();
                    }
                }
            }

            // Handle write-ready sockets (flush outgoing data).
            foreach ($write as $socket) {
                $index = array_search($socket, $actives['connections']);
                if ($index !== false && isset($this->connections[$index]) &&
                    $this->connections[$index] instanceof \Erebot\Interfaces\SendingConnection) {
                    $this->connections[$index]->write();
                }
            }
        }
    }

    public function start(\Erebot\Interfaces\ConnectionFactory $factory)
    {
        try {
            return $this->realStart($factory);
        } catch (\Erebot\StopException $e) {
            // This exception is raised by Erebot::handleSignal()
            // whenever one of SIGINT, SIGQUIT, SIGALRM, or SIGTERM
            // is received and indicates the bot is stopping.
        }
    }

    public function stop()
    {
        $logger = \Plop\Plop::getInstance();

        if (!$this->running) {
            return;
        }

        foreach ($this->connections as $connection) {
            if ($connection instanceof \Erebot\Interfaces\EventDispatcher) {
                $eventsProducer = $connection->getEventsProducer();
                $connection->dispatch($eventsProducer->makeEvent('!ExitEvent'));
            }
        }

        $logger->info($this->gettext('Erebot has stopped'));
        unset(
            $this->timers,
            $this->connections,
            $this->mainCfg
        );

        $this->running         = false;
        $this->connections     =
        $this->timers          =
        $this->mainCfg         = null;
        throw new \Erebot\StopException();
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
        $consts     = get_defined_constants(true);
        $signame    = '???';
        foreach ($consts['pcntl'] as $name => $value) {
            if (!strncmp($name, 'SIG', 3) &&
                strncmp($name, 'SIG_', 4) &&
                $signum == $value) {
                $signame = $name;
                break;
            }
        }

        $logger = \Plop\Plop::getInstance();
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
            $limit  = \Erebot\Utils::parseHumanSize($limit."B");
            $stats  = array(
                $this->gettext("Allocated:") =>
                    \Erebot\Utils::humanSize(memory_get_peak_usage(true)),
                $this->gettext("Used:") =>
                    \Erebot\Utils::humanSize(memory_get_peak_usage(false)),
                $this->gettext("Limit:") =>
                    \Erebot\Utils::humanSize($limit),
            );

            foreach ($stats as $key => $value) {
                $logger->debug(
                    '%(key)-16s%(value)10s',
                    array(
                        'key'   => $key,
                        'value' => $value,
                    )
                );
            }
        }

        $this->stop();
    }

    public function getTimers()
    {
        return $this->timers;
    }

    public function addTimer(\Erebot\TimerInterface $timer)
    {
        $key = array_search($timer, $this->timers);
        if ($key !== false) {
            throw new \Erebot\InvalidValueException('Timer already registered');
        }

        $timer->reset();
        $this->timers[] =  $timer;
    }

    public function removeTimer(\Erebot\TimerInterface $timer)
    {
        $key = array_search($timer, $this->timers);
        if ($key === false) {
            throw new \Erebot\NotFoundException('Timer not found');
        }

        unset($this->timers[$key]);
    }

    public function addConnection(\Erebot\Interfaces\Connection $connection)
    {
        $key = array_search($connection, $this->connections);
        if ($key !== false) {
            throw new \Erebot\InvalidValueException(
                'Already handling this connection'
            );
        }

        $this->connections[] = $connection;
    }

    public function removeConnection(\Erebot\Interfaces\Connection $connection)
    {
        /* $this->connections is unset during destructor call,
         * but the destructing code depends on this method.
         * we silently ignore the problem. */
        if (!isset($this->connections)) {
            return;
        }

        $key = array_search($connection, $this->connections);
        if ($key === false) {
            throw new \Erebot\NotFoundException('No such connection');
        }

        unset($this->connections[$key]);
    }

    public function gettext($message)
    {
        return $this->translator->gettext($message);
    }

    public function getRunningTime()
    {
        if (!$this->running) {
            return false;
        }
        return time() - $this->running;
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

    /**
     * Reload this instance of the bot.
     *
     * This method makes the bot reload most of the data
     * it currently relies on, such as configuration files.
     *
     * \param Erebot::Interfaces::Config::MainInterface $config
     *      (optional) The new configuration to use.
     *      If omitted, the configuration file currently in use
     *      is reloaded.
     */
    public function reload(\Erebot\Interfaces\Config\Main $config = null)
    {
        $logger = \Plop\Plop::getInstance();

        $msg = $this->gettext('Reloading the configuration');
        $logger->info($msg);

        if (!count($this->connections)) {
            $logger->info($this->gettext('No active connections... Aborting.'));
            return;
        }

        if ($config === null) {
            $configFile = $this->mainCfg->getConfigFile();
            if ($configFile === null) {
                $msg = $this->gettext('No configuration file to reload');
                $logger->info($msg);
                return;
            }

            /// @TODO: dependency injection
            $config = new \Erebot\Config\Main(
                $configFile,
                \Erebot\Interfaces\Config\Main::LOAD_FROM_FILE
            );
        }

        $connectionCls = get_class($this->connections[0]);
        $this->createConnections($connectionCls, $config);
        $msg = $this->gettext('Successfully reloaded the configuration');
        $logger->info($msg);
    }

    /**
     * Creates the connections for this instance.
     *
     * \param Erebot::Interfaces::ConnectionFactory $factory
     *      The factory to use to create the connections.
     *
     * \param Erebot::Interfaces::Config::Main $config
     *      The main configuration for the bot.
     */
    protected function createConnections(
        \Erebot\Interfaces\ConnectionFactory $factory,
        \Erebot\Interfaces\Config\Main $config
    ) {
        $logger = \Plop\Plop::getInstance();

        // List existing connections so they
        // can eventually be reused.
        $newConnections     =
        $currentConnections = array();
        foreach ($this->connections as $connection) {
            $connCfg = $connection->getConfig(null);
            if ($connCfg) {
                $netCfg = $connCfg->getNetworkCfg();
                $currentConnections[$netCfg->getName()] = $connection;
            } else {
                $newConnections[] = $connection;
            }
        }

        // Let's establish some contacts.
        $networks = $config->getNetworks();
        foreach ($networks as $network) {
            $netName = $network->getName();
            if (isset($currentConnections[$netName])) {
                try {
                    $uris   = $currentConnections[$netName]
                                ->getConfig(null)
                                ->getConnectionURI();
                    $uri    = new \Erebot\URI($uris[count($uris) - 1]);
                    $serverCfg = $network->getServerCfg((string) $uri);

                    $logger->info(
                        $this->gettext(
                            'Reusing existing connection ' .
                            'for network "%(network)s"'
                        ),
                        array('network' => $netName)
                    );
                    // Move it from existing connections to new connections,
                    // marking it as still being in use.
                    $copy = clone $currentConnections[$netName];
                    $copy->reload($serverCfg);
                    $newConnections[] = $copy;
                    unset($currentConnections[$netName]);
                    continue;
                } catch (\Erebot\NotFoundException $e) {
                    // Nothing to do.
                }
            }

            if (!in_array('\\Erebot\\Module\\AutoConnect', $network->getModules(true))) {
                continue;
            }

            $servers = $network->getServers();
            foreach ($servers as $server) {
                $uris       = $server->getConnectionURI();
                $serverUri  = new \Erebot\URI($uris[count($uris) - 1]);
                try {
                    $connection = $factory->newConnection($this, $server);

                    // Drop connection to a (now-)unconfigured
                    // server on that network.
                    if (isset($currentConnections[$netName])) {
                        $currentConnections[$netName]->disconnect();
                        unset($currentConnections[$netName]);
                    }

                    $logger->info(
                        $this->gettext('Trying to connect to "%(uri)s"...'),
                        array('uri' => $serverUri)
                    );
                    $connection->connect();
                    $newConnections[] = $connection;

                    $logger->info(
                        $this->gettext('Successfully connected to "%(uri)s"...'),
                        array('uri' => $serverUri)
                    );

                    break;
                } catch (\Erebot\ConnectionFailureException $e) {
                    // Nothing to do... We simply
                    // try the next server on the
                    // list until we successfully
                    // connect or cycle the list.
                    $logger->exception(
                        $this->gettext('Could not connect to "%(uri)s"'),
                        $e,
                        array('uri' => $serverUri)
                    );
                }
            }
        }

        // Gracefully quit leftover connections.
        foreach ($currentConnections as $connection) {
            $connection->disconnect();
        }

        $this->connections = $newConnections;
        $this->mainCfg     = $config;
    }
}
