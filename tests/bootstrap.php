<?php

// Avoid a harmless warning in PHPUnit
// when it generates coverage report.
date_default_timezone_set('UTC');
set_include_path(
    dirname(dirname(__FILE__)) .
    PATH_SEPARATOR .
    get_include_path()
);

include_once('src/logging/src/logging.php');

$logging =& Plop::getInstance();
$logging->basicConfig();
unset($logging);

