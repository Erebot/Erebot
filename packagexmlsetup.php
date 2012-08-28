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

foreach (array($package, $compatible) as $obj) {
    // FIXME: $package needs the original filenames,
    // while $compatible wants the logical filenames.
    if ($obj === $compatible) {
        $scriptDir  = 'script';
        $srcDir     = 'php';
        $docDir     = 'doc';
    }
    else {
        $scriptDir  = 'scripts';
        $srcDir     = 'src';
        $docDir     = 'docs';
    }

    // Don't include the doc (uses too much space).
    unset($obj->files[$docDir]);

    // Apply replacement tasks to the proper files.
    $obj->files["$scriptDir/Erebot"] = array_merge_recursive(
        $obj->files["$scriptDir/Erebot"]->getArrayCopy(),
        $php_dir
    );

    $obj->files["$srcDir/Erebot/Timer.php"] = array_merge_recursive(
        $obj->files["$srcDir/Erebot/Timer.php"]->getArrayCopy(),
        $php_bin
    );

    // Don't include the API override if it is present.
    if (isset($obj->files["$scriptDir/Erebot_API"])) {
        unset($obj->files["$scriptDir/Erebot_API"]);
    }
}

