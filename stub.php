#!/usr/bin/env php
<?php
/**
 * If your package does special stuff in phar format, use this file.  Remove if
 * no phar format is ever generated
 * More information: http://pear.php.net/manual/en/pyrus.commands.package.php#pyrus.commands.package.stub
 */
if (version_compare(phpversion(), '5.3.1', '<')) {
    if (substr(phpversion(), 0, 5) != '5.3.1') {
        // this small hack is because of running RCs of 5.3.1
        echo "Erebot requires PHP 5.3.1 or newer.
";
        exit -1;
    }
}
foreach (array('phar', 'spl', 'pcre', 'simplexml') as $ext) {
    if (!extension_loaded($ext)) {
        echo 'Extension ', $ext, " is required
";
        exit -1;
    }
}
try {
    Phar::mapPhar();
} catch (Exception $e) {
    echo "Cannot process Erebot phar:
";
    echo $e->getMessage(), "
";
    exit -1;
}
function Erebot_autoload($class)
{
    $class = str_replace(array('_', '\\'), '/', $class);
    if (file_exists('phar://' . __FILE__ . '/Erebot-0.3.2/php/' . $class . '.php')) {
        include 'phar://' . __FILE__ . '/Erebot-0.3.2/php/' . $class . '.php';
    }
}
spl_autoload_register("Erebot_autoload");
$phar = new Phar(__FILE__);
$sig = $phar->getSignature();
define('Erebot_SIG', $sig['hash']);
define('Erebot_SIGTYPE', $sig['hash_type']);

// your package-specific stuff here, for instance, here is what Pyrus does:

function main()
{
    $parser = new Console_CommandLine(array(
        'name'                  => 'Erebot',
        'description'           => 'A modular IRC bot written in PHP',
        'version'               => Erebot::VERSION,
        'add_help_option'       => TRUE,
        'add_version_option'    => TRUE,
        'force_posix'           => FALSE,
    ));

    $defaultConfigFile = getcwd() . DIRECTORY_SEPARATOR . 'Erebot.xml';
    $parser->addOption('config', array(
        'short_name'    => '-c',
        'long_name'     => '--config',
        'description'   =>  'Path to the configuration file to use instead '.
                            'of "Erebot.xml" in the current directory.',
        'action'        => 'StoreString',
        'default'       => $defaultConfigFile,
    ));

    try {
        $res = $parser->parse();
    }
    catch (Exception $exc) {
        $parser->displayError($exc->getMessage());
        exit(1);
    }
    $config = new Erebot_Config_Main(
        $res->options['config'],
        Erebot_Config_Main::LOAD_FROM_FILE
    );
    $bot = new Erebot($config);
    $bot->start();
    exit(0);
}

main();

/**
 * $frontend = new \PEAR2\Pyrus\ScriptFrontend\Commands;
 * @array_shift($_SERVER['argv']);
 * $frontend->run($_SERVER['argv']);
 */
__HALT_COMPILER();
