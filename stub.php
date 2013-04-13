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

// Use closures to avoid variables pollution.
$inc = function ($modulePath) {
    $res = @include("phar://" . $modulePath);
    if (!is_array($res)) {
        fprintf(
            STDERR,
            "An error occurred while processing %s, ".
            "the module has been ignored.%s",
            $modulePath,
            PHP_EOL
        );
    }
    return $res;
};
$phars      = array();
$handleMetadata = function ($checker, $metadata, &$phars, $pharPath) {
    $checker->handleMetadata($metadata);

    list($vendor, $pkgName) = explode('/', $metadata['name'], 2);
    if ($vendor == 'erebot' || $vendor == 'pear.erebot.net') {
        if (!isset($metadata['extra']['phar']['path']) ||
            !isset($metadata['extra']['PEAR']['name'])) {
            return;
        }
        $path       = $metadata['extra']['phar']['path'];
        $pearName   = $metadata['extra']['PEAR']['name'];
        $phars[$pearName]['version'] = $metadata['version'];
        if (file_exists($path)) {
            $phars[$pearName]['paths'][] = $path;
        }
    }
};

$metadata = json_decode(
    file_get_contents(
        "phar://" . __FILE__ .
        DIRECTORY_SEPARATOR . "@PACKAGE_NAME@-@PACKAGE_VERSION@" .
        DIRECTORY_SEPARATOR . "data" .
        DIRECTORY_SEPARATOR . "pear.erebot.net" .
        DIRECTORY_SEPARATOR . "@PACKAGE_NAME@" .
        DIRECTORY_SEPARATOR . "composer.json"
    ),
    TRUE
);
// Add the extra info the normal stub would usually add.
$metadata['version'] = '@PACKAGE_VERSION@';
$metadata['extra']['PEAR']['name'] = '@PACKAGE_NAME@';
$metadata['extra']['phar']['path'] =
    "phar://" . __FILE__ .
    DIRECTORY_SEPARATOR . "@PACKAGE_NAME@-@PACKAGE_VERSION@" .
    DIRECTORY_SEPARATOR . "php";
// Erebot's main .phar embeds the code of several dependencies.
$metadata['provide']['erebot/erebot-api'] = '*';
$metadata['provide']['erebot/plop'] = '*';
$metadata['provide']['erebot/dependencyinjection'] = '*';
$metadata['provide']['pear-pear.php.net/console_commandline'] = '*';
$metadata['provide']['pear-pear.php.net/file_gettext'] = '*';
$handleMetadata($checker, $metadata, $phars, __FILE__);

// Load phar modules.
$modulesDir = getcwd() . DIRECTORY_SEPARATOR . 'modules';
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
