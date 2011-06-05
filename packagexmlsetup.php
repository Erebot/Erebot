<?php

/**
 * Extra package.xml settings such as dependencies.
 * More information: http://pear.php.net/manual/en/pyrus.commands.make.php#pyrus.commands.make.packagexmlsetup
 */

$deps = array(
    'required' => array(
        'pear.php.net/Console_CommandLine',
        'pear.php.net/File_Gettext',
        'pear.erebot.net/Erebot_Module_IrcConnector',
        'pear.erebot.net/Erebot_Module_AutoConnect',
        'pear.erebot.net/Erebot_Module_PingReply',
        'pear.erebot.net/Plop',
    ),
);

foreach (array($package, $compatible) as $obj) {
    $obj->dependencies['required']->php = '5.2.0';
    $obj->license['name'] = 'GPL';
    $obj->license['uri'] = 'http://www.gnu.org/licenses/gpl-3.0.txt';
    // Pyrus <= 2.0.0a3 has a bug with this, see:
    // https://github.com/saltybeagle/PEAR2_Pyrus/issues/12
#    $obj->license['path'] = 'LICENSE';

    /* Add dependencies.
     * For packages provided by Erebot's pear channel,
     * make sure the dependencies' stability follows
     * that of Erebot itself.
     */
    foreach ($deps as $req => $data)
        foreach ($data as $dep)
            $obj->dependencies[$req]->package[$dep]->save();
}

?>
