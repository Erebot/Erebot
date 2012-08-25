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

    // Now, apply those tasks to the proper files.
    $obj->files['scripts/Erebot'] = array_merge_recursive(
        $obj->files['scripts/Erebot']->getArrayCopy(),
        $php_dir
    );
    $obj->files['src/Erebot/Timer.php'] = array_merge_recursive(
        $obj->files['src/Erebot/Timer.php']->getArrayCopy(),
        $php_bin
    );
    $dataFileRefs = array(
        'src/Erebot/I18n.php',
        'src/Erebot/Config/Main.php',
        'src/Erebot/DOM.php',
        'src/Erebot/Styling.php',
        'src/Erebot/CLI.php',
    );
    foreach ($dataFileRefs as $dataFileRef) {
        $obj->files[$dataFileRef] = array_merge_recursive(
            $obj->files[$dataFileRef]->getArrayCopy(),
            $php_bin
        );
    }
}

