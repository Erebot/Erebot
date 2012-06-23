#!/usr/bin/env php
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

if (!empty($_SERVER['DOCUMENT_ROOT']))
    die("This script isn't meant to be run from the Internet!\n");

if (realpath($_SERVER['PATH_TRANSLATED']) == realpath(__FILE__))
    // Don't use any external file
    // (eg. from a PEAR repository).
    ini_set('include_path', PATH_SEPARATOR);

if (version_compare(phpversion(), '5.3.1', '<')) {
    if (substr(phpversion(), 0, 5) != '5.3.1') {
        // this small hack is because of running RCs of 5.3.1
        echo "@PACKAGE_NAME@ requires PHP 5.3.1 or newer." . PHP_EOL;
        exit(1);
    }
}

// Check dependencies needed to parse the phar.
$exts = array('phar', 'spl', 'pcre', 'simplexml');
foreach ($exts as $ext) {
    if (!extension_loaded($ext)) {
        echo "Extension $ext is required." . PHP_EOL;
        exit(1);
    }
}
unset($exts, $ext);


// Parse the current phar.
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

define('@PACKAGE_NAME@ SIG', $sig['hash']);
define('@PACKAGE_NAME@ SIGTYPE', $sig['hash_type']);
define('@PACKAGE_NAME@ PHAR', __FILE__);
define('@PACKAGE_NAME@ VERSION', '@PACKAGE_VERSION@');
unset($phar, $sig);

// Load the autoloader.
include(
    "phar://" . __FILE__ .
    DIRECTORY_SEPARATOR . "@PACKAGE_NAME@-@PACKAGE_VERSION@" .
    DIRECTORY_SEPARATOR . "php" .
    DIRECTORY_SEPARATOR . "@PACKAGE_NAME@" .
    DIRECTORY_SEPARATOR . "Autoload.php"
);

// Register composer into the autoloader.
$composerDir =
     "phar://" . __FILE__ .
    DIRECTORY_SEPARATOR . "@PACKAGE_NAME@-@PACKAGE_VERSION@" .
    DIRECTORY_SEPARATOR . "php";
Erebot_Autoload::initialize($composerDir);

// Include the dependency checker.
include(
    "phar://" . __FILE__ .
    DIRECTORY_SEPARATOR . "@PACKAGE_NAME@-@PACKAGE_VERSION@" .
    DIRECTORY_SEPARATOR . "data" .
    DIRECTORY_SEPARATOR . "pear.erebot.net" .
    DIRECTORY_SEPARATOR . "@PACKAGE_NAME@" .
    DIRECTORY_SEPARATOR . "dependencies.php"
);

// Prepare a new dependency checker.
$checker = new Erebot_Phar_DependencyChecker("", '@PACKAGE_VERSION@');

// Retrieve this package's metadata.
$metadata = include(
    "phar://" . __FILE__ .
    DIRECTORY_SEPARATOR . "@PACKAGE_NAME@-@PACKAGE_VERSION@" .
    DIRECTORY_SEPARATOR . "data" .
    DIRECTORY_SEPARATOR . "pear.erebot.net" .
    DIRECTORY_SEPARATOR . "@PACKAGE_NAME@" .
    DIRECTORY_SEPARATOR . "package.php"
);
if (is_array($metadata))
    $checker->handleMetadata($metadata);

// Load phar modules.
$modulesDir = __DIR__ . DIRECTORY_SEPARATOR . 'modules';
try {
    $iter = new DirectoryIterator($modulesDir);
    $dots = array('.', '..');
    foreach ($iter as $moduleInfo) {
        if (in_array($moduleInfo->getFilename(), $dots))
            continue;

        if (substr($moduleInfo->getFilename(), -5) != '.phar')
            continue;

        try {
            // Use a closure to avoid variables pollution.
            $inc = function ($modulePath) {
                return include("phar://" . $modulePath);
            };

            // Take the module's metadata into account.
            $metadata = $inc($moduleInfo->getPathName());
            if (is_array($metadata))
                $checker->handleMetadata($metadata);
        }
        catch (Exception $e) {
        }
    }
}
catch (Exception $e) {
}
unset($iter, $dots, $moduleInfo, $inc, $translator, $modulesDir);

// Check the dependencies.
$error = NULL;
if (!$checker->check($error)) {
    echo $error . PHP_EOL;
    exit(1);
}

// Necessary for PEAR 1 packages that require() stuff.
set_include_path(implode(PATH_SEPARATOR, Erebot_Autoload::getPaths()));

Erebot_CLI::run();

__HALT_COMPILER();
