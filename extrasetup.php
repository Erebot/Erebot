<?php
/**
 * This file is used to provide extra files/packages outside package.xml
 * More information: http://pear.php.net/manual/en/pyrus.commands.package.php#pyrus.commands.package.extrasetup
 */
$extrafiles = array();

/**
 * for example:
if (basename(__DIR__) == 'trunk') {
    $extrafiles = array(
        new \PEAR2\Pyrus\Package(__DIR__ . '/../../HTTP_Request/trunk/package.xml'),
        new \PEAR2\Pyrus\Package(__DIR__ . '/../../sandbox/Console_CommandLine/trunk/package.xml'),
        new \PEAR2\Pyrus\Package(__DIR__ . '/../../MultiErrors/trunk/package.xml'),
        new \PEAR2\Pyrus\Package(__DIR__ . '/../../Exception/trunk/package.xml'),
    );
} else {
    $extrafiles = array(
        new \PEAR2\Pyrus\Package(__DIR__ . '/../HTTP_Request/package.xml'),
        new \PEAR2\Pyrus\Package(__DIR__ . '/../sandbox/Console_CommandLine/package.xml'),
        new \PEAR2\Pyrus\Package(__DIR__ . '/../MultiErrors/package.xml'),
        new \PEAR2\Pyrus\Package(__DIR__ . '/../Exception/package.xml'),
    );
}
*/
