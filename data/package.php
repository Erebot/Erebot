<?php

$baseDir =
    "phar://" .
    constant('Erebot PHAR') .
    DIRECTORY_SEPARATOR .
    "Erebot-" . constant('Erebot VERSION') .
    DIRECTORY_SEPARATOR;

return array(
    'pear.erebot.net/Erebot' => array(
        'version' => constant('Erebot VERSION'),
        'path' => $baseDir . 'php',
        'requires' => array(
            'php >= 5.2.0',
            'virt-Erebot_API = 0.2.*',
            'virt-log',
            'pear.erebot.net/DependencyInjection',
            'pear.php.net/Console_CommandLine',
            'pear.php.net/File_Gettext',
            'pecl.php.net/ctype',
            'pecl.php.net/dom',
            'pecl.php.net/intl',
            'pecl.php.net/libxml',
            'pecl.php.net/pcre',
            'pecl.php.net/Reflection',
            'pecl.php.net/SimpleXML',
            'pecl.php.net/sockets',
            'pecl.php.net/SPL',
            'pecl.php.net/XML',
            'pear.erebot.net/Erebot_Module_AutoConnect',
            'pear.erebot.net/Erebot_Module_IrcConnector',
            'pear.erebot.net/Erebot_Module_PingReply',
        ),
        'suggests' => array(
            'pecl.php.net/openssl',
            'pecl.php.net/pcntl',
            'pecl.php.net/posix',
        ),
    ),

    'pear.erebot.net/Erebot_API' => array(
        'version' => 'master-dev',
        'requires' => array(
            'php >= 5.2.0',
        ),
        'provides' => array(
            'virt-Erebot_API = 0.2.0',
        ),
    ),

    'pear.erebot.net/Plop' => array(
        'version' => 'master-dev',
        'requires' => array(
            'php >= 5.2.0',
        ),
        'provides' => array(
            'virt-log',
        ),
    ),

    'pear.erebot.net/DependencyInjection' => array(
        'version' => 'master-dev',
        'requires' => array(
            'php >= 5.2.0',
        ),
    ),

    'pear.php.net/Console_CommandLine' => array(
        'version' => 'master-dev',
        'requires' => array(
            'php >= 5.2.0',
        ),
    ),

    'pear.php.net/File_Gettext' => array(
        'version' => 'master-dev',
        'requires' => array(
            'php >= 5.2.0',
        ),
    ),
);

