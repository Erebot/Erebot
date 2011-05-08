#!/usr/bin/env php
<?php
/**
 * This program displays information about versioning:
 * - for a RELEASE
 * - for the API
 */

function usage($script)
{
    echo "Usage: $script <options> <API|RELEASE> [directory]\n";
    echo "where options can be:\n";
    echo "-f\tDisplay latest file\n";
    echo "or\n";
    echo "-v\tDisplay matching version\n";
    exit(1);
}

function main()
{
    $args = $_SERVER['argv'];
    $script = array_shift($args);
    if (!count($args))
        usage($script);

    $options = ltrim(array_shift($args), '-');
    if (!in_array($options, array('f', 'v')))
        usage($script);

    if (!count($args))
        usage($script);

    $type = strtoupper(array_shift($args));
    if (!in_array($type, array('API', 'RELEASE')))
        usage($script);

    $dir = getcwd();
    if (count($args))
        $dir = array_shift($args);

    $files = array();
    foreach (new RegexIterator(
            new DirectoryIterator($dir),
           '/^'.$type.'\-(.+)$/',
           RegexIterator::GET_MATCH
        ) as $file) {
        $files[$file[1]] = $file;
    }

    if (!count($files))
        exit(2);

    uksort($files, 'version_compare');
    list($file, $version) = array_pop($files);
    if ($options == "f")
        echo "$file\n";
    else
        echo "$version\n";
    exit(0);
}

main();

