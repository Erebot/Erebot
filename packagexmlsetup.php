<?php

// Re-use the default packagexmlsetup.php from the build environment.
require(
    dirname(__FILE__) .
    DIRECTORY_SEPARATOR . 'buildenv' .
    DIRECTORY_SEPARATOR . 'packagexmlsetup.php'
);

// Replacement tasks.
// First comes "php_dir".
$php_dir = array(
    'tasks:replace' => array(
        'attribs' => array(
            'from'  => '@php_dir@',
            'to'    => 'php_dir',
            'type'  => 'pear-config'
        )
    )
);
// Then "php_bin".
$php_bin = array(
    'tasks:replace' => array(
        'attribs' => array(
            'from'  => '@php_bin@',
            'to'    => 'php_bin',
            'type'  => 'pear-config'
        )
    )
);

// Don't include those parts of the doc (uses too much disk space).
unset($package->files["docs/coverage"]);
unset($package->files["docs/api"]);
unset($package->files["docs/enduser"]);

// Apply replacement tasks to the proper files.
$package->files["scripts/Erebot"] = array_merge_recursive(
    $package->files["scripts/Erebot"]->getArrayCopy(),
    $php_dir
);

$package->files["src/Erebot/Timer.php"] = array_merge_recursive(
    $package->files["src/Erebot/Timer.php"]->getArrayCopy(),
    $php_bin
);

// Don't include the API override if it is present.
if (isset($package->files["scripts/Erebot_API"])) {
    unset($package->files["scripts/Erebot_API"]);
}

