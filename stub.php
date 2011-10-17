#!/usr/bin/env php
<?php

if (realpath($_SERVER['PATH_TRANSLATED']) == realpath(__FILE__))
    ini_set('include_path', '.');

if (version_compare(phpversion(), '5.3.1', '<')) {
    if (substr(phpversion(), 0, 5) != '5.3.1') {
        // this small hack is because of running RCs of 5.3.1
        echo    basename(__FILE__, '.phar') .
                " requires PHP 5.3.1 or newer." . PHP_EOL;
        exit(1);
    }
}

$exts = array(
    'ctype',
    'dom',
    'intl',
    'libxml',
    'pcre',
    'Reflection',
    'SimpleXML',
    'sockets',
    'phar',
    'SPL',
    'xml',
);
foreach ($exts as $ext) {
    if (!extension_loaded($ext)) {
        echo 'Extension ' . $ext . " is required" . PHP_EOL;
        exit(1);
    }
}

try {
    Phar::mapPhar();
} catch (Exception $e) {
    echo "Cannot process " . basename(__FILE__, '.phar') . ":" . PHP_EOL;
    echo "\t" . $e->getMessage() . PHP_EOL;
    exit(1);
}

Phar::interceptFileFuncs();
$phar = new Phar(__FILE__);
$sig = $phar->getSignature();

define('Erebot_SIG', $sig['hash']);
define('Erebot_SIGTYPE', $sig['hash_type']);
define('Erebot_PHAR', __FILE__);

include(
    "phar://" . __FILE__ .
    DIRECTORY_SEPARATOR . "Erebot-@PACKAGE_VERSION@" .
    DIRECTORY_SEPARATOR . "php" .
    DIRECTORY_SEPARATOR . "Erebot" .
    DIRECTORY_SEPARATOR . "Autoload.php"
);

$baseDir =
     "phar://" . __FILE__ .
    DIRECTORY_SEPARATOR . "Erebot-@PACKAGE_VERSION@" .
    DIRECTORY_SEPARATOR . "php";

set_include_path(get_include_path() . PATH_SEPARATOR . $baseDir);
Erebot_Autoload::initialize($baseDir);
Erebot_Autoload::initialize(
    $baseDir .
    DIRECTORY_SEPARATOR . 'SymfonyComponents' .
    DIRECTORY_SEPARATOR . 'DependencyInjection'
);

$logging    = Plop::getInstance();
$logger     = $logging->getLogger(__FILE__);
$translator = new Erebot_I18n('Erebot');

$modulesDir = __DIR__ . DIRECTORY_SEPARATOR . 'modules';
echo sprintf(
    $translator->gettext('Pre-loading modules from "%s".'),
    $modulesDir
) . PHP_EOL;

try {
    $iter = new DirectoryIterator($modulesDir);
    $dots = array('.', '..');
    foreach ($iter as $moduleInfo) {
        if (in_array($moduleInfo->getFilename(), $dots))
            continue;

        try {
            $module = new Phar($moduleInfo->getPathName());
            foreach ($module as $entry) {
                if ($entry->isDir() && !in_array($entry->getFilename(), $dots))
                    Erebot_Autoload::initialize(
                        "phar://" . $moduleInfo->getPathName() .
                        DIRECTORY_SEPARATOR . $entry->getFilename() .
                        DIRECTORY_SEPARATOR . "php"
                    );
            }
            echo sprintf(
                $translator->gettext('Successfully pre-loaded "%s".'),
                $moduleInfo->getFilename()
            ) . PHP_EOL;
        }
        catch (Exception $e) {
            echo sprintf(
                $translator->gettext(
                    'Could not pre-load module "%s", ' .
                    "it won't be available."
                ),
                $moduleInfo->getFilename()
            ) . PHP_EOL;
            continue;
        }
    }
}
catch (Exception $e) {
}

echo "Loaded paths:" . PHP_EOL;
foreach (Erebot_Autoload::getPaths() as $path)
    echo "\t" . $path . PHP_EOL;

// Quick replacement for ctype_digit in case
// PHP was compiled with --disable-ctype.
if (!function_exists('ctype_digit')) {
    function ctype_digit($s) {
        if (!is_string($s))
            return FALSE;
        $len = strlen($s);
        if (!$len)
            return FALSE;
        return strspn($s, '1234567890') == $len;
    }
}

// For older versions of PHP that support signals
// but don't support pcntl_signal_dispatch (5.2.x).
if (!function_exists('pcntl_signal_dispatch'))
    declare(ticks=1);
Erebot_CLI::run();

__HALT_COMPILER();
