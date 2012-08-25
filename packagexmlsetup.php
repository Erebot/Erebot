<?php

/**
 * Extra package.xml settings such as dependencies.
 * More information: http://pear.php.net/manual/en/pyrus.commands.make.php#pyrus.commands.make.packagexmlsetup
 */

$deps = array(
    'required' => array(
        'pear.php.net/Console_CommandLine',
        'pear.php.net/File_Gettext',
        'pear.erebot.net/DependencyInjection',
        'pear.erebot.net/Erebot_Module_IrcConnector',
        'pear.erebot.net/Erebot_Module_AutoConnect',
        'pear.erebot.net/Erebot_Module_PingReply',
        'pear.erebot.net/Plop',
    ),
);

$exts = array(
    'required' => array(
        'ctype',
        'dom',
        'intl',
        'libxml',
        'pcre',
        'Reflection',
        'SimpleXML',
        'sockets',
        'SPL',
        'xml',
    ),
    'optional' => array(
        'openssl',
        'pcntl',
        'posix',
    ),
);

// This only applies to Pyrus (PEAR2).
$package->dependencies['required']->pearinstaller->min = '2.0.0a3';

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
// And last but certainly not least, "data_dir".
$data_dir = array(
    'tasks:replace' => array(
        'attribs' => array(
            'from'  => '@data_dir@',
            'to'    => 'data_dir',
            'type'  => 'pear-config'
        )
    )
);

foreach (array($package, $compatible) as $obj) {
    $obj->dependencies['required']->php->min = '5.2.2';
    $obj->license['name'] = 'GPL';
    $obj->license['uri'] = 'http://www.gnu.org/licenses/gpl-3.0.txt';

    // Add dependencies...
    // ...on packages.
    foreach ($deps as $req => $data)
        foreach ($data as $dep)
            $obj->dependencies[$req]->package[$dep]->save();

    // ...on extensions.
    foreach ($exts as $req => $data)
        foreach ($data as $ext)
            $obj->dependencies[$req]->extension[$ext]->save();

    // Don't include the docs (it uses a lot of space).
    unset($obj->files['docs']);
    unset($obj->files['doc']);

    // Apply replacement tasks to the proper files.
    // FIXME: $package needs the original filenames,
    // while $compatible wants the logical filenames.
    if ($obj === $compatible) {
        $scriptDir  = 'script';
        $srcDir     = 'php';
    }
    else {
        $scriptDir  = 'scripts';
        $srcDir     = 'src';
    }

    $obj->files["$scriptDir/Erebot"] = array_merge_recursive(
        $obj->files["$scriptDir/Erebot"]->getArrayCopy(),
        $php_dir
    );

    $obj->files["$srcDir/Erebot/Timer.php"] = array_merge_recursive(
        $obj->files["$srcDir/Erebot/Timer.php"]->getArrayCopy(),
        $php_bin
    );
    $dataFileRefs = array(
        "$srcDir/Erebot/I18n.php",
        "$srcDir/Erebot/Config/Main.php",
        "$srcDir/Erebot/DOM.php",
        "$srcDir/Erebot/Styling.php",
        "$srcDir/Erebot/CLI.php",
    );
    foreach ($dataFileRefs as $dataFileRef) {
        $obj->files[$dataFileRef] = array_merge_recursive(
            $obj->files[$dataFileRef]->getArrayCopy(),
            $data_dir
        );
    }

    // Don't include the API override if it is present.
    if (isset($obj->files["$scriptDir/Erebot_API"])) {
        unset($obj->files["$scriptDir/Erebot_API"]);
    }
}

