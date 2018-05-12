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

// Workaround for https://bugs.php.net/bug.php?id=18556
// and other related bugs in older PHP versions.
setlocale(LC_CTYPE, 'C');

if (realpath($_SERVER['PATH_TRANSLATED']) == realpath(__FILE__))
    // Don't use any external file (eg. don't use
    // the system's PEAR repository).
    ini_set('include_path', PATH_SEPARATOR);

if (version_compare(phpversion(), '7.0.0', '<')) {
    echo basename(__FILE__) . " requires PHP 7.0.0 or newer." . PHP_EOL;
    exit(1);
}

// Check dependencies needed to parse the phar.
$exts = array('phar', 'json');
foreach ($exts as $ext) {
    if (!extension_loaded($ext)) {
        echo "Extension $ext is required." . PHP_EOL;
        exit(1);
    }
}

// Parse the current phar.
try {
    Phar::mapPhar();
} catch (Exception $e) {
    echo "Cannot process " . basename(__FILE__, '.phar') . ":" . PHP_EOL;
    echo "\t" . $e->getMessage() . PHP_EOL;
    exit(1);
}

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

// Load the autoloader.
include(
    "phar://" . __FILE__ .
    DIRECTORY_SEPARATOR . "vendor" .
    DIRECTORY_SEPARATOR . "erebot" .
    DIRECTORY_SEPARATOR . "api" .
    DIRECTORY_SEPARATOR . "src" .
    DIRECTORY_SEPARATOR . "Erebot" .
    DIRECTORY_SEPARATOR . "Autoload.php"
);

// Register the path to Erebot's core files into the autoloader.
Erebot_Autoload::initialize(
    "phar://" . __FILE__ .
    DIRECTORY_SEPARATOR . "src"
);

// Now register dependencies too.
$metadata = json_decode(
    file_get_contents('phar://' . __FILE__ . '/composer.lock'),
    TRUE
);
$packages = array_merge($metadata['packages'], $metadata['packages-dev']);

// First pass : register autoload paths.
foreach ($packages as $package) {
    if (!file_exists('phar://' . __FILE__ . "/vendor/${package['name']}"))
        continue;

    if (!isset($package['autoload']['psr-0']))
        continue;

    foreach ($package['autoload']['psr-0'] as $autoload) {
        Erebot_Autoload::initialize(
            "phar://" . __FILE__ . "/vendor/${package['name']}/$autoload"
        );
    }
}

$repository = new \Composer\Repository\InstalledArrayRepository();
$loader = new \Composer\Package\Loader\ArrayLoader();

// Second pass : register bundled packages.
foreach ($packages as $package) {
    if (!file_exists('phar://' . __FILE__ . "/vendor/${package['name']}"))
        continue;
    $repository->addPackage($loader->load($package));
}

$metadata = json_decode(
    file_get_contents(
        "phar://" . __FILE__ .
        DIRECTORY_SEPARATOR . "composer.json"
    ),
    TRUE
);
$phar = new Phar(__FILE__);
$md = $phar->getMetadata();
$metadata['version'] = $md['version'];
$repository->addPackage($loader->load($metadata));

// Load phar modules.
$modulesDir = getcwd() . DIRECTORY_SEPARATOR . 'modules';
try {
    $iter = new DirectoryIterator($modulesDir);
    foreach ($iter as $moduleInfo) {
        if (in_array($moduleInfo->getFilename(), array('.', '..')))
            continue;

        if (substr($moduleInfo->getFilename(), -5) !== '.phar')
            continue;

        // Take the module's metadata into account.
        $pharPath = $moduleInfo->getPathName();

        $e = NULL;
        try {
            ob_start();
            $metadata = $inc($pharPath);
            flush();
        }
        catch (Exception $e) {
            flush();
        }
        $buffer = ob_get_clean();

        $cut = strpos($buffer, "\r");
        if ($cut === FALSE)
            $cut = strpos($buffer, "\n");
        else if (strpos($buffer, "\n") !== FALSE)
            $cut = min($cut, strpos($buffer, "\n"));

        $shebang = (string) substr($buffer, 0, $cut);
        $buffer = (string) substr($buffer, $cut);

        if (substr($shebang, 0, 2) === '#!') {
            if (substr($buffer, 0, 1) === "\r")
                $buffer = (string) substr($buffer, 1);
            if (substr($buffer, 0, 1) === "\n")
                $buffer = (string) substr($buffer, 1);
        }
        echo $buffer;

        if ($e !== NULL) {
            echo "Cannot process " . $pharPath . ":" . PHP_EOL;
            echo "\t" . $e->getMessage() . PHP_EOL;
            continue;
        }

        if (!isset($metadata['packages'])) {
            $metadata['packages'] = array();
        }
        if (!isset($metadata['packages-dev'])) {
            $metadata['packages-dev'] = array();
        }

        $packages = array_merge(
            $metadata['packages'],
            $metadata['packages-dev']
        );
        foreach ($packages as $package) {
            $repository->addPackage($loader->load($package));

            if (!isset($package['time'])) {
                foreach ($package['autoload']['psr-0'] as $autoload) {
                    Erebot_Autoload::initialize(
                        "phar://" . $pharPath . "/$autoload"
                    );
                }
            }

            if (!file_exists('phar://' . $pharPath .
                             "/vendor/${package['name']}")) {
                continue;
            }

            if (!isset($package['autoload']['psr-0'])) {
                continue;
            }

            foreach ($package['autoload']['psr-0'] as $autoload) {
                Erebot_Autoload::initialize(
                    "phar://" . $pharPath .
                    "/vendor/${package['name']}/$autoload"
                );
            }
        }
    }
}
catch (Exception $e) {
}

// Create installed repo, this contains all local packages
// and platform packages (php, extensions & system libs).
$platformRepository = new \Composer\Repository\PlatformRepository();
$installedRepository = new \Composer\Repository\CompositeRepository(array(
    $platformRepository,
    $repository,
));

$pool = new \Composer\DependencyResolver\Pool;
$pool->addRepository($platformRepository);
$pool->addRepository($repository);

$request = new \Composer\DependencyResolver\Request($pool);
$phar = new Phar(__FILE__);
$md = $phar->getMetadata();

$request->install("erebot/erebot");
$policy = new \Composer\DependencyResolver\DefaultPolicy();
$solver = new \Composer\DependencyResolver\Solver(
    $policy,
    $pool,
    $repository
);

try {
    $solver->solve($request);
} catch (\Composer\DependencyResolver\SolverProblemsException $e) {
    echo $e->getMessage() . PHP_EOL;
    exit(1);
}

// Necessary for PEAR 1 packages that require() stuff.
set_include_path(implode(PATH_SEPARATOR, Erebot_Autoload::getPaths()));

Erebot_CLI::run();
__HALT_COMPILER();
