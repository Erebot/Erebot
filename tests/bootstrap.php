<?php

// Avoid a harmless warning in PHPUnit
// when it generates coverage report.
date_default_timezone_set('UTC');

if (!defined('__DIR__')) {
  class __FILE_CLASS__ {
    function  __toString() {
      $X = debug_backtrace();
      return dirname($X[1]['file']);
    }
  }
  define('__DIR__', new __FILE_CLASS__);
} 

set_include_path(
    __DIR__ . DIRECTORY_SEPARATOR . '..' .
    PATH_SEPARATOR .
    get_include_path()
);

set_include_path(
    __DIR__ . implode(
        DIRECTORY_SEPARATOR, array(
            'src',
            'logging',
            'src',
        )
    ) .
    PATH_SEPARATOR .
    get_include_path()
);

include_once('src/logging/src/Plop/Plop.php');
include_once('src/logging/tests/bootstrap.php');

$logging =& Plop_Plop::getInstance();
$logging->basicConfig();
unset($logging);

