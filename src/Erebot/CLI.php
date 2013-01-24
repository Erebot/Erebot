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

/**
 * \brief
 *      Custom action which acts as a proxy.
 *
 * This class can be used in conjunction with
 * Console_CommandLine_ParallelOption to have
 * multiple options that act on the same variable.
 */
class   StoreProxy_Action
extends Console_CommandLine_Action
{
    /**
     * Sets the result of this action.
     * In our case, this actually changes
     * the value of another option.
     *
     * \param mixed $result
     *      Result to assign to the other option.
     *
     * \param mixed $option
     *      (optional) Name of the option this proxy
     *      will operate on. The default is NULL.
     */
    public function setResult($result, $option = NULL)
    {
        $this->result->options[$option] = $result;
    }

    /**
     * Executes this action whenever the associated
     * option has been passed on the command-line.
     *
     * \param mixed $value
     *      (optional) Value given to this option.
     *      The default is FALSE.
     *
     * \param array $params
     *      (optional) Parameters associated with
     *      this action. The default is an empty
     *      set of parameters (empty array).
     */
    public function execute($value = FALSE, $params = array())
    {
        $this->setResult(FALSE, $params['option']);
    }
}

/**
 * \brief
 *      Custom message provider for Console_CommandLine.
 */
class   Erebot_Console_CommandLine_MessageProvider
extends Console_CommandLine_MessageProvider_Default
{
    /**
     * Overrides the PROG_VERSION_LINE message so that
     * a dot is not automatically appended at the end
     * of the text.
     */
    public function __construct()
    {
        $this->messages['PROG_VERSION_LINE'] = '{$progname} version {$version}';
    }
}

/**
 * \brief
 *      Custom option that can be used in parallel with regular options.
 *
 * The Console_CommandLine package usually prevents
 * options from acting on the same variable. This
 * specific type of option can be used with the
 * StoreProxy_Action to work around this.
 */
class   Console_CommandLine_ParallelOption
extends Console_CommandLine_Option
{
    /**
     * Overrides the parent method so that
     * this option never expects an argument.
     *
     * \retval bool
     *      Always returns FALSE.
     */
    public function expectsArgument()
    {
        return FALSE;
    }
}

/**
 * \brief
 *      Provides the entry-point for Erebot.
 *
 * Erebot_CLI::run() is called statically by the "Erebot" script.
 * This class then takes care of loading every configuration file
 * and module required and connects the bot to the different IRC
 * servers.
 */
class Erebot_CLI
{
    /**
     * Signal handler used during startup.
     *
     * \param int $signum
     *      Number of the signal received.
     *
     * \post
     *      The current process exits with an exit code
     *      of 0 or 1, depending on the signal received.
     *
     * \return
     *      This method does not return anything.
     */
    static public function _startup_sighandler($signum)
    {
        if (defined('SIGUSR1') && $signum == SIGUSR1)
            exit(0);
        exit(1);
    }

    /**
     * Called after the bot has finished its execution
     * to perform cleanup tasks.
     *
     * \param resource $handle
     *      Open file handle on the pidfile.
     *
     * \param string $pidfile
     *      Name of the pidfile.
     *
     * \return
     *      This method does not return anything.
     */
    static public function _cleanup_pidfile($handle, $pidfile)
    {
        flock($handle, LOCK_UN);
        @unlink($pidfile);
        $logger = Plop::getInstance();
        $logger->debug(
            'Removed lock on pidfile (%(pidfile)s)',
            array('pidfile' => $pidfile)
        );
    }

    /**
     * Entry-point for Erebot.
     *
     * \return
     *      This method never returns.
     *      Instead, the program exits with an appropriate
     *      return code when Erebot is stopped.
     */
    static public function run()
    {
        // Don't require it : during development, we can't rely on PEAR.
        @include(
            'SymfonyComponents'.DIRECTORY_SEPARATOR.
            'DependencyInjection'.DIRECTORY_SEPARATOR.
            'sfServiceContainerAutoloader.php'
        );
        sfServiceContainerAutoloader::register();

        // Apply patches.
        Erebot_Patches::patch();

        // Load the configuration for the Dependency Injection Container.
        $baseDir    = dirname(dirname(dirname(__FILE__)));
        Erebot_Utils::getResourcePath(NULL, NULL, $baseDir);
        $dic        = new sfServiceContainerBuilder();
        $dic->setParameter('Erebot.src_dir', dirname(dirname(__FILE__)));
        $loader     = new sfServiceContainerLoaderFileXml($dic);
        $dicConfig = Erebot_Utils::getResourcePath(
            'Erebot',
            'defaults.xml'
        );
        if (!strncasecmp(__FILE__, 'phar://', 7)) {
            $dicConfigNew = getcwd() . DIRECTORY_SEPARATOR . 'defaults.xml';
            if (!file_exists($dicConfigNew)) {
                copy($dicConfig, $dicConfigNew);
            }
            $dicConfig = $dicConfigNew;
        }
        $loader->load($dicConfig);

        // Determine availability of PHP extensions
        // needed by some of the command-line options.
        $hasPosix = in_array('posix', get_loaded_extensions());
        $hasPcntl = in_array('pcntl', get_loaded_extensions());

        $logger             = $dic->logging;
        $localeGetter       = $dic['i18n.default_getter'];
        $coreTranslatorCls  = $dic['core.classes.i18n'];
        $translator         = new $coreTranslatorCls("Erebot");

        $categories = array(
            'LC_MESSAGES',
            'LC_MONETARY',
            'LC_TIME',
            'LC_NUMERIC',
        );
        foreach ($categories as $category) {
            $locales = call_user_func($localeGetter);
            $locales = empty($locales) ? array() : array($locales);
            $localeSources = array(
                'LANGUAGE'      => TRUE,
                'LC_ALL'        => FALSE,
                $category       => FALSE,
                'LANG'          => FALSE,
            );
            foreach ($localeSources as $source => $multiple) {
                if (!isset($_SERVER[$source]))
                    continue;
                if ($multiple)
                    $locales = explode(':', $_SERVER[$source]);
                else
                    $locales = array($_SERVER[$source]);
                break;
            }

            $translator->setLocale(
                $translator->nameToCategory($category),
                $locales
            );
        }

        // Also, include some information about the version
        // of currently loaded PHAR modules, if any.
        $version = Erebot::VERSION;
        if (defined('Erebot_PHARS')) {
            $phars = unserialize(Erebot_PHARS);
            ksort($phars);
            foreach ($phars as $module => $metadata) {
                if (strncasecmp($module, 'Erebot_Module_', 14))
                    continue;
                $version .= "\n  with $module version ${metadata['version']}";
            }
        }
        else {
            $version .= '.';
        }

        Console_CommandLine::registerAction('StoreProxy', 'StoreProxy_Action');
        $parser = new Console_CommandLine(
            array(
                'name'                  => 'Erebot',
                'description'           =>
                    $translator->gettext('A modular IRC bot written in PHP'),
                'version'               => $version,
                'add_help_option'       => TRUE,
                'add_version_option'    => TRUE,
                'force_posix'           => FALSE,
            )
        );
        $parser->accept(new Erebot_Console_CommandLine_MessageProvider());
        $parser->renderer->options_on_different_lines = TRUE;

        $defaultConfigFile = getcwd() . DIRECTORY_SEPARATOR . 'Erebot.xml';
        $parser->addOption(
            'config',
            array(
                'short_name'    => '-c',
                'long_name'     => '--config',
                'description'   => $translator->gettext(
                    'Path to the configuration file to use instead '.
                    'of "Erebot.xml", relative to the current '.
                    'directory.'
                ),
                'help_name'     => 'FILE',
                'action'        => 'StoreString',
                'default'       => $defaultConfigFile,
            )
        );

        $parser->addOption(
            'daemon',
            array(
                'short_name'        => '-d',
                'long_name'         => '--daemon',
                'description'       => $translator->gettext(
                    'Run the bot in the background (daemon).'.
                    ' [requires the POSIX and pcntl extensions]'
                ),
                'action'            => 'StoreTrue',
            )
        );

        $noDaemon = new Console_CommandLine_ParallelOption(
            'no_daemon',
            array(
                'short_name'    => '-n',
                'long_name'     => '--no-daemon',
                'description'   => $translator->gettext(
                    'Do not run the bot in the background. '.
                    'This is the default, unless the -d option '.
                    'is used or the bot is configured otherwise.'
                ),
                'action'        => 'StoreProxy',
                'action_params' => array('option' => 'daemon'),
            )
        );
        $parser->addOption($noDaemon);

        $parser->addOption(
            'pidfile',
            array(
                'short_name'    => '-p',
                'long_name'     => '--pidfile',
                'description'   => $translator->gettext(
                    "Store the bot's PID in this file."
                ),
                'help_name'     => 'FILE',
                'action'        => 'StoreString',
                'default'       => NULL,
            )
        );

        $parser->addOption(
            'group',
            array(
                'short_name'    => '-g',
                'long_name'     => '--group',
                'description'   => $translator->gettext(
                    'Set group identity to this GID/group during '.
                    'startup. The default is to NOT change group '.
                    'identity, unless configured otherwise.'.
                    ' [requires the POSIX extension]'
                ),
                'help_name'     => 'GROUP/GID',
                'action'        => 'StoreString',
                'default'       => NULL,
            )
        );

        $parser->addOption(
            'user',
            array(
                'short_name'    => '-u',
                'long_name'     => '--user',
                'description'   => $translator->gettext(
                    'Set user identity to this UID/username during '.
                    'startup. The default is to NOT change user '.
                    'identity, unless configured otherwise.'.
                    ' [requires the POSIX extension]'
                ),
                'help_name'     => 'USER/UID',
                'action'        => 'StoreString',
                'default'       => NULL,
            )
        );

        try {
            $parsed = $parser->parse();
        }
        catch (Exception $exc) {
            $parser->displayError($exc->getMessage());
            exit(1);
        }

        // Parse the configuration file.
        $config = new Erebot_Config_Main(
            $parsed->options['config'],
            Erebot_Config_Main::LOAD_FROM_FILE,
            $translator
        );

        $coreCls = $dic['core.classes.core'];
        $bot = new $coreCls($config, $translator);
        $dic->bot = $bot;

        // Use values from the XML configuration file
        // if there is no override from the command line.
        $overrides = array(
            'daemon'    => 'mustDaemonize',
            'group'     => 'getGroupIdentity',
            'user'      => 'getUserIdentity',
            'pidfile'   => 'getPidfile',
        );
        foreach ($overrides as $option => $func)
            if ($parsed->options[$option] === NULL)
                $parsed->options[$option] = $config->$func();

        /* Handle daemonization.
         * See also:
         * - http://www.itp.uzh.ch/~dpotter/howto/daemonize
         * - http://andytson.com/blog/2010/05/daemonising-a-php-cli-script
         */
        if ($parsed->options['daemon']) {
            if (!$hasPosix) {
                $logger->error(
                    $translator->gettext(
                        'The posix extension is required in order '.
                        'to start the bot in the background'
                    )
                );
                exit(1);
            }

            if (!$hasPcntl) {
                $logger->error(
                    $translator->gettext(
                        'The pcntl extension is required in order '.
                        'to start the bot in the background'
                    )
                );
                exit(1);
            }

            foreach (array('SIGCHLD', 'SIGUSR1', 'SIGALRM') as $signal)
                if (defined($signal))
                    pcntl_signal(
                        constant($signal),
                        array(__CLASS__, '_startup_sighandler')
                    );

            $logger->info(
                $translator->gettext('Starting the bot in the background...')
            );
            $pid = pcntl_fork();
            if ($pid < 0) {
                $logger->error(
                    $translator->gettext(
                        'Could not start in the background (unable to fork)'
                    )
                );
                exit(1);
            }
            if ($pid > 0) {
                pcntl_wait($dummy, WUNTRACED);
                pcntl_alarm(2);
                if (function_exists('pcntl_signal_dispatch'))
                    pcntl_signal_dispatch();
                exit(1);
            }
            $parent = posix_getppid();

            // Ignore some of the signals.
            foreach (array('SIGTSTP', 'SIGTOU', 'SIGTIN', 'SIGHUP') as $signal)
                if (defined($signal))
                    pcntl_signal(constant($signal), SIG_IGN);

            // Restore the signal handlers we messed with.
            foreach (array('SIGCHLD', 'SIGUSR1', 'SIGALRM') as $signal)
                if (defined($signal))
                    pcntl_signal(constant($signal), SIG_DFL);

            umask(0);
            if (umask() != 0)
                $logger->warning(
                    $translator->gettext('Could not change umask')
                );

            if (posix_setsid() == -1) {
                $logger->error(
                    $translator->gettext(
                        'Could not start in the background (unable to setsid)'
                    )
                );
                exit(1);
            }

            // Prevent the child from ever acquiring a controlling terminal.
            // Not required under Linux, but required by at least System V.
            $pid = pcntl_fork();
            if ($pid < 0) {
                $logger->error(
                    $translator->gettext(
                        'Could not start in the background (unable to fork)'
                    )
                );
                exit(1);
            }
            if ($pid > 0)
                exit(0);

            // Avoid locking up the current directory.
            if (!chdir(DIRECTORY_SEPARATOR))
                $logger->error(
                    $translator->gettext('Could not chdir to "%(path)s"'),
                    array('path' => DIRECTORY_SEPARATOR)
                );

            // Explicitly close the magic stream-constants (just in case).
            foreach (array('STDIN', 'STDOUT', 'STDERR') as $stream) {
                if (defined($stream))
                    fclose(constant($stream));
            }
            // Re-open them with the system's blackhole.
            /**
             * \todo
             *      should be made portable, but the requirement on the POSIX
             *      extension prevents this, so this is okay for now.
             */
            $stdin  = fopen('/dev/null', 'r');
            $stdout = fopen('/dev/null', 'w');
            $stderr = fopen('/dev/null', 'w');

            if (defined('SIGUSR1'))
                posix_kill($parent, SIGUSR1);
            $logger->info(
                $translator->gettext('Successfully started in the background')
            );
        }

        try {
            /// @TODO: Check the interface or something like that.
            $identd = $dic->identd;
        }
        catch (InvalidArgumentException $e) {
            $identd = NULL;
        }

        try {
            /// @TODO: Check the interface or something like that.
            $prompt = $dic->prompt;
        }
        catch (InvalidArgumentException $e) {
            $prompt = NULL;
        }

        // Change group identity if necessary.
        if ($parsed->options['group'] !== NULL &&
            $parsed->options['group'] != '') {
            if (!$hasPosix) {
                $logger->warning(
                    $translator->gettext(
                        'The posix extension is needed in order '.
                        'to change group identity.'
                    )
                );
            }
            else if (posix_getuid() !== 0) {
                $logger->warning(
                    $translator->gettext(
                        'Only root can change group identity! '.
                        'Your current UID is %(uid)d'
                    ),
                    array('uid' => posix_getuid())
                );
            }
            else {
                if (ctype_digit($parsed->options['group']))
                    $info = posix_getgrgid((int) $parsed->options['group']);
                else
                    $info = posix_getgrnam($parsed->options['group']);

                if ($info === FALSE) {
                    $logger->error(
                        $translator->gettext('No such group "%(group)s"'),
                        array('group' => $parsed->options['group'])
                    );
                    exit(1);
                }

                if (!posix_setgid($info['gid'])) {
                    $logger->error(
                        $translator->gettext(
                            'Could not set group identity '.
                            'to "%(name)s" (%(id)d)'
                        ),
                        array(
                            'id'    => $info['gid'],
                            'name'  => $info['name'],
                        )
                    );
                    exit(1);
                }

                $logger->debug(
                    $translator->gettext(
                        'Successfully changed group identity '.
                        'to "%(name)s" (%(id)d)'
                    ),
                    array(
                        'name'  => $info['name'],
                        'id'    => $info['gid'],
                    )
                );
            }
        }

        // Change user identity if necessary.
        if ($parsed->options['user'] !== NULL ||
            $parsed->options['user'] != '') {
            if (!$hasPosix) {
                $logger->warning(
                    $translator->gettext(
                        'The posix extension is needed in order '.
                        'to change user identity.'
                    )
                );
            }
            else if (posix_getuid() !== 0) {
                $logger->warning(
                    $translator->gettext(
                        'Only root can change user identity! '.
                        'Your current UID is %(uid)d'
                    ),
                    array('uid' => posix_getuid())
                );
            }
            else {
                if (ctype_digit($parsed->options['user']))
                    $info = posix_getpwuid((int) $parsed->options['user']);
                else
                    $info = posix_getpwnam($parsed->options['user']);

                if ($info === FALSE) {
                    $logger->error(
                        $translator->gettext('No such user "%(user)s"'),
                        array('user' => $parsed->options['user'])
                    );
                    exit(1);
                }

                if (!posix_setuid($info['uid'])) {
                    $logger->error(
                        $translator->gettext(
                            'Could not set user identity '.
                            'to "%(name)s" (%(id)d)'
                        ),
                        array(
                            'name'  => $info['name'],
                            'id'    => $info['uid'],
                        )
                    );
                    exit(1);
                }
                $logger->debug(
                    $translator->gettext(
                        'Successfully changed user identity '.
                        'to "%(name)s" (%(id)d)'
                    ),
                    array(
                        'name'  => $info['name'],
                        'id'    => $info['uid'],
                    )
                );
            }
        }

        // Write new pidfile.
        if ($parsed->options['pidfile'] !== NULL &&
            $parsed->options['pidfile'] != '') {
            $pid = @file_get_contents($parsed->options['pidfile']);
            // If the file already existed, the bot may already be started
            // or it may contain data not related to Erebot at all.
            if ($pid !== FALSE) {
                $pid = (int) rtrim($pid);
                if (!$pid) {
                    $logger->error(
                        $translator->gettext(
                            'The pidfile (%(pidfile)s) contained garbage. ' .
                            'Exiting'
                        ),
                        array('pidfile' => $parsed->options['pidfile'])
                    );
                    exit(1);
                }
                else {
                    posix_kill($pid, 0);
                    $res = posix_errno();
                    switch ($res) {
                        case 0: // No error.
                            $logger->error(
                                $translator->gettext(
                                    'Erebot is already running ' .
                                    'with PID %(pid)d'
                                ),
                                array('pid' => $pid)
                            );
                            exit(1);

                        case 3: // ESRCH.
                            $logger->warning(
                                $translator->gettext(
                                    'Found stalled PID %(pid)d in pidfile '.
                                    '"%(pidfile)s". Removing it'
                                ),
                                array(
                                    'pidfile'   => $parsed->options['pidfile'],
                                    'pid'       => $pid,
                                )
                            );
                            @unlink($parsed->options['pidfile']);
                            break;

                        case 1: // EPERM.
                            $logger->error(
                                $translator->gettext(
                                    'Found another program\'s PID %(pid)d in '.
                                    'pidfile "%(pidfile)s". Exiting'
                                ),
                                array(
                                    'pidfile'   => $parsed->options['pidfile'],
                                    'pid'       => $pid,
                                )
                            );
                            exit(1);

                        default:
                            $logger->error(
                                $translator->gettext(
                                    'Unknown error while checking for '.
                                    'the existence of another running '.
                                    'instance of Erebot (%(error)s)'
                                ),
                                array('error' => posix_get_last_error())
                            );
                            exit(1);
                    }
                }
            }

            $pidfile = fopen($parsed->options['pidfile'], 'wt');
            flock($pidfile, LOCK_EX | LOCK_NB, $wouldBlock);
            if ($wouldBlock) {
                $logger->error(
                    $translator->gettext(
                        'Could not lock pidfile (%(pidfile)s). '.
                        'Is the bot already running?'
                    ),
                    array('pidfile' => $parsed->options['pidfile'])
                );
                exit(1);
            }

            $pid = sprintf("%u\n", getmypid());
            $res = fwrite($pidfile, $pid);
            if ($res != strlen($pid)) {
                $logger->error(
                    $translator->gettext(
                        'Unable to write PID to pidfile (%(pidfile)s)'
                    ),
                    array('pidfile' => $parsed->options['pidfile'])
                );
                exit(1);
            }

            $logger->debug(
                $translator->gettext(
                    'PID (%(pid)d) written into %(pidfile)s'
                ),
                array(
                    'pidfile'   => $parsed->options['pidfile'],
                    'pid'       => getmypid(),
                )
            );
            // Register a callback to remove the pidfile upon exit.
            register_shutdown_function(
                array(__CLASS__, '_cleanup_pidfile'),
                $pidfile,
                $parsed->options['pidfile']
            );
        }

        // Display a desperate warning when run as user root.
        if ($hasPosix && posix_getuid() === 0) {
            $logger->warning(
                $translator->gettext('You SHOULD NOT run Erebot as root!')
            );
        }

        if ($identd !== NULL)
            $identd->connect();

        if ($prompt !== NULL)
            $prompt->connect();

        // This doesn't return until we purposely
        // make the bot drop all active connections.
        $bot->start($dic->getService('factory.connection'));
        exit(0);
    }
}
