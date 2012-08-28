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
unset($composerDir);

// Include the dependency checker.
include(
    "phar://" . __FILE__ .
    DIRECTORY_SEPARATOR . "@PACKAGE_NAME@-@PACKAGE_VERSION@" .
    DIRECTORY_SEPARATOR . "data" .
    DIRECTORY_SEPARATOR . "pear.erebot.net" .
    DIRECTORY_SEPARATOR . "@PACKAGE_NAME@" .
    DIRECTORY_SEPARATOR . "checker.php"
);

// Prepare a new dependency checker.
$checker = new Erebot_Package_Dependencies_Checker("", '@PACKAGE_VERSION@');

// Return metadata about this package.
$packageName = '@PACKAGE_NAME@';
$packageVersion = '@PACKAGE_VERSION@';
$metadata = array(
    'pear.erebot.net/@PACKAGE_NAME@' => array(
        'version' => '@PACKAGE_VERSION@',
        'path' =>
            "phar://" . __FILE__ .
            DIRECTORY_SEPARATOR . "@PACKAGE_NAME@-@PACKAGE_VERSION@" .
            DIRECTORY_SEPARATOR . "php",
    )
);

// Use closures to avoid variables pollution.
$inc = function ($modulePath) {
    return require("phar://" . $modulePath);
};
$phars      = array();
$handleMetadata = function ($checker, $metadata, &$phars, $pharPath) {
    if (!is_array($metadata))
        return;
    $checker->handleMetadata($metadata);

    // Try to figure out the "main package" from the .phar.
    $main = NULL;
    foreach (array_keys($metadata) as $pkgName) {
        if (!strncasecmp($pkgName, 'pear.erebot.net/', 16)) {
            $shortName  = (string) substr($pkgName, 16);
            $modulePath = 'phar://' . $pharPath .
                            DIRECTORY_SEPARATOR .
                            $shortName . '-' .
                            $metadata[$pkgName]['version'] .
                            DIRECTORY_SEPARATOR;
            $phars[$shortName] = array();
            if (file_exists($modulePath)) {
                $main = $modulePath;
                break;
            }
        }
    }
    if ($main === NULL)
        continue;

    foreach (array_keys($metadata) as $pkgName) {
        if (!strncasecmp($pkgName, 'pear.erebot.net/', 16)) {
            $phars[(string) substr($pkgName, 16)][] = $main;
        }
    }
};

$pharPath = 
require(
    "phar://" . __FILE__ .
    DIRECTORY_SEPARATOR . "@PACKAGE_NAME@-@PACKAGE_VERSION@" .
    DIRECTORY_SEPARATOR . "data" .
    DIRECTORY_SEPARATOR . "pear.erebot.net" .
    DIRECTORY_SEPARATOR . "@PACKAGE_NAME@" .
    DIRECTORY_SEPARATOR . "package.php"
);
$handleMetadata($checker, $metadata, $phars, __FILE__);

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
            // Take the module's metadata into account.
            $pharPath = $moduleInfo->getPathName();
            $metadata = $inc($pharPath);
            $handleMetadata($checker, $metadata, $phars, $pharPath);
        }
        catch (Exception $e) {
        }
    }
}
catch (Exception $e) {
}

// Define constants with the paths to each module's phars (+ core).
define('Erebot_PHARS', serialize($phars));

// Global scope cleanup.
unset(
    $metadata, $modulesDir, $iter, $dots,
    $moduleInfo, $inc, $pkgName, $phars,
    $pharPath
);

// Check the dependencies.
$error = NULL;
if (!$checker->check($error)) {
    echo $error;
    exit(1);
}
unset($error, $checker);

// Necessary for PEAR 1 packages that require() stuff.
set_include_path(implode(PATH_SEPARATOR, Erebot_Autoload::getPaths()));

Erebot_CLI::run();
__HALT_COMPILER();
