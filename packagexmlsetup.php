<?php

/**
 * Extra package.xml settings such as dependencies.
 * More information: http://pear.php.net/manual/en/pyrus.commands.make.php#pyrus.commands.make.packagexmlsetup
 */

$package->license = 'GPL';
$compatible->license = 'GPL';

$deps = array(
    'pear.php.net/Console_CommandLine',
    'pear.php.net/File_Gettext',
    'pear.erebot.net/Erebot_Module_IrcConnector',
    'pear.erebot.net/Erebot_Module_AutoJoin',
    'pear.erebot.net/Erebot_Module_AutoConnect',
    'pear.erebot.net/Plop',
);
$compat_deps = $deps;

foreach ($deps as $dep)
    $package->dependencies['required']->package[$dep]->save();
foreach ($compat_deps as $dep)
    $compatible->dependencies['required']->package[$dep]->save();

?>
