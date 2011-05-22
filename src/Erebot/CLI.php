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

class   Erebot_CLI
{
    static public function _startup_sighandler($signum)
    {
        if (defined('SIGUSR1') && $signum == SIGUSR1)
            exit(0);
        exit(1);
    }

    static public function _cleanup_pidfile($handle, $pidfile)
    {
        $logging    =&  Plop::getInstance();
        $logger     =   $logging->getLogger(__FILE__);

        flock($handle, LOCK_UN);
        @unlink($pidfile);
        $logger->debug('Removed lock on pidfile (%s)', $pidfile);
    }

    static public function run()
    {
        // Determine availability of PHP extensions
        // needed by some of the command-line options.
        $hasPosix = in_array('posix', get_loaded_extensions());
        $hasPcntl = in_array('pcntl', get_loaded_extensions());

        $locales = Locale::getDefault();
        $locales = empty($locales) ? array() : array($locales);
        $localeSources = array(
            'LANGUAGE'      => TRUE,
            'LC_ALL'        => FALSE,
            'LC_MESSAGES'   => FALSE,
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
        $translator = new Erebot_I18n("Erebot");
        $translator->setLocale(Erebot_Interface_I18n::LC_MESSAGES, $locales);

        Console_CommandLine::registerAction('StoreProxy', 'StoreProxy_Action');
        $parser = new Console_CommandLine(array(
            'name'                  => 'Erebot',
            'description'           => 'A modular IRC bot written in PHP',
            'version'               => Erebot::VERSION,
            'add_help_option'       => TRUE,
            'add_version_option'    => TRUE,
            'force_posix'           => FALSE,
        ));
        $parser->renderer->options_on_different_lines = TRUE;

        $defaultConfigFile = getcwd() . DIRECTORY_SEPARATOR . 'Erebot.xml';
        $parser->addOption('config', array(
            'short_name'    => '-c',
            'long_name'     => '--config',
            'description'   =>  'Path to the configuration file to use instead '.
                                'of "Erebot.xml", relative to the current '.
                                'directory.',
            'help_name'     => 'FILE',
            'action'        => 'StoreString',
            'default'       => $defaultConfigFile,
        ));

        $parser->addOption('daemon', array(
            'short_name'        => '-d',
            'long_name'         => '--daemon',
            'description'       =>  'Run the bot in the background (daemon).',
            'action'            => 'StoreTrue',
        ));

        $noDaemon = new Console_CommandLine_MyOption('no_daemon', array(
            'short_name'    => '-n',
            'long_name'     => '--no-daemon',
            'description'   =>  'Do not run the bot in the background. '.
                                'This is the default, unless the -d option '.
                                'is used or the bot is configured otherwise',
            'action'        => 'StoreProxy',
            'action_params' => array('option' => 'daemon'),
        ));
        $parser->addOption($noDaemon);

        $parser->addOption('pidfile', array(
            'short_name'    => '-p',
            'long_name'     => '--pidfile',
            'description'   =>  'Store the bot\'s PID in this file.',
            'help_name'     => 'FILE',
            'action'        => 'StoreString',
            'default'       => NULL,
        ));

        $parser->addOption('group', array(
            'short_name'    => '-g',
            'long_name'     => '--group',
            'description'   =>  'Set group identity to this GID/group during '.
                                'startup. The default is to NOT change group '.
                                'identity, unless configured otherwise.',
            'help_name'     => 'GROUP/GID',
            'action'        => 'StoreString',
            'default'       => NULL,
        ));

        $parser->addOption('user', array(
            'short_name'    => '-u',
            'long_name'     => '--user',
            'description'   =>  'Set user identity to this UID/username during '.
                                'startup. The default is to NOT change user '.
                                'identity, unless configured otherwise.',
            'help_name'     => 'USER/UID',
            'action'        => 'StoreString',
            'default'       => NULL,
        ));

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

        $logging    =&  Plop::getInstance();
        $logger     =   $logging->getLogger(__FILE__);

        /* Handle daemonization.
         * See also:
         * - http://www.itp.uzh.ch/~dpotter/howto/daemonize
         * - http://andytson.com/blog/2010/05/daemonising-a-php-cli-script
         */
        if ($parsed->options['daemon']) {
            if (!$hasPosix) {
                $logger->error(
                    'The posix extension is required in order '.
                    'to start the bot in the background'
                );
                exit(1);
            }

            if (!$hasPcntl) {
                $logger->error(
                    'The pcntl extension is required in order '.
                    'to start the bot in the background'
                );
                exit(1);
            }

            foreach (array('SIGCHLD', 'SIGUSR1', 'SIGALRM') as $signal)
                if (defined($signal))
                    pcntl_signal(
                        constant($signal),
                        array(__CLASS__, '_startup_sighandler')
                    );

            $logger->info('Starting the bot in the background...');
            $pid = pcntl_fork();
            if ($pid < 0) {
                $logger->error('Could not start in the background (unable to fork)');
                exit(1);
            }
            if ($pid > 0) {
                pcntl_alarm(2);
                pcntl_wait($dummy, WUNTRACED);
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
                $logger->warning('Could not change umask');

            if (posix_setsid() == -1) {
                $logger->error('Could not start in the background (unable to setsid)');
                exit(1);
            }

            // Prevent the child from ever acquiring a controlling terminal.
            // Not required under Linux, but required by at least System V.
            $pid = pcntl_fork();
            if ($pid < 0) {
                $logger->error('Could not start in the background (unable to fork)');
                exit(1);
            }
            if ($pid > 0)
                exit(0);

            // Avoid locking up the current directory.
            if (!chdir(DIRECTORY_SEPARATOR))
                $logger->error('Could not chdir to "%s"', DIRECTORY_SEPARATOR);

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
            $stderr = fopen('php://stdout', 'w');

            if (defined('SIGUSR1'))
                posix_kill($parent, SIGUSR1);
            $logger->info('Successfully started in the background');
        }

        // Change group identity if necessary.
        if ($parsed->options['group'] !== NULL &&
            $parsed->options['group'] != '') {
            if (!$hasPosix) {
                $logger->warning(
                    'The posix extension is needed in order '.
                    'to change group identity.'
                );
            }
            else if (posix_getuid() !== 0) {
                $logger->warning(
                    'Only root can change group identity! '.
                    'Your current UID is %d',
                    posix_getuid()
                );
            }
            else {
                if (ctype_digit($parsed->options['group']))
                    $info = posix_getgrgid((int) $parsed->options['group']);
                else
                    $info = posix_getgrnam($parsed->options['group']);

                if ($info === FALSE) {
                    $logger->error('No such group "%s"', $parsed->options['group']);
                    exit(1);
                }

                if (!posix_setgid($info['gid'])) {
                    $logger->error(
                        'Could not set group identity to "%(name)s" (%(id)d)',
                        array(
                            'name'  => $info['name'],
                            'id'    => $info['gid'],
                        )
                    );
                    exit(1);
                }

                $logger->debug(
                    'Successfully changed group identity to "%(name)s" (%(id)d)',
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
                    'The posix extension is needed in order '.
                    'to change user identity.'
                );
            }
            else if (posix_getuid() !== 0) {
                $logger->warning(
                    'Only root can change user identity! '.
                    'Your current UID is %d',
                    posix_getuid()
                );
            }
            else {
                if (ctype_digit($parsed->options['user']))
                    $info = posix_getpwuid((int) $parsed->options['user']);
                else
                    $info = posix_getpwnam($parsed->options['user']);

                if ($info === FALSE) {
                    $logger->error('No such user "%s"', $parsed->options['user']);
                    exit(1);
                }

                if (!posix_setuid($info['uid'])) {
                    $logger->error(
                        'Could not set user identity to "%(name)s" (%(id)d)',
                        array(
                            'name'  => $info['name'],
                            'id'    => $info['uid'],
                        )
                    );
                    exit(1);
                }
                $logger->debug(
                    'Successfully changed user identity to "%(name)s" (%(id)d)',
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
                        'The pidfile (%s) contained garbage. Exiting',
                        $parsed->options['pidfile']
                    );
                    exit(1);
                }
                else {
                    posix_kill($pid, 0);
                    $res = posix_errno();
                    switch ($res) {
                        case 0: // No error.
                            $logger->error(
                                'Erebot is already running with PID %d',
                                $pid
                            );
                            exit(1);

                        case 3: // ESRCH.
                            $logger->warning(
                                'Found stalled PID %(pid)d in pidfile '.
                                '"%(pidfile)s". Removing it',
                                array(
                                    'pidfile'   => $parsed->options['pidfile'],
                                    'pid'       => $pid,
                                )
                            );
                            @unlink($parsed->options['pidfile']);
                            break;

                        case 1: // EPERM.
                            $logger->error(
                                'Found another program\'s PID %(pid)d in '.
                                'pidfile "%(pidfile)s". Exiting',
                                array(
                                    'pidfile'   => $parsed->options['pidfile'],
                                    'pid'       => $pid,
                                )
                            );
                            exit(1);

                        default:
                            $logger->error(
                                'Unknown error while checking for the existence '.
                                'of another running instance of Erebot (%s)',
                                posix_get_last_error()
                            );
                            exit(1);
                    }
                }
            }

            $pidfile = fopen($parsed->options['pidfile'], 'wt');
            flock($pidfile, LOCK_EX | LOCK_NB, $wouldBlock);
            if ($wouldBlock) {
                $logger->error(
                    'Could not lock pidfile (%s). '.
                    'Is the bot already running?',
                    $parsed->options['pidfile']
                );
                exit(1);
            }

            $pid = sprintf("%u\n", getmypid());
            $res = fwrite($pidfile, $pid);
            if ($res != strlen($pid)) {
                $logger->error(
                    'Unable to write PID to pidfile (%s)',
                    $parsed->options['pidfile']
                );
                exit(1);
            }

            $logger->debug(
                'Wrote our PID (%(pid)d) into the pidfile (%(pidfile)s)',
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
        if (getmyuid() === 0)
            $logger->warning('You SHOULD NOT run Erebot as root !');

        $bot = new Erebot($config, $translator);

        // This doesn't return until we purposely
        // make the bot drop all active connections.
        $bot->start();
        exit(0);
    }
}
