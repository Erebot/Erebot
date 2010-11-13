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

require_once(
    dirname(__FILE__) .
    str_replace(
        '/', DIRECTORY_SEPARATOR,
        '/../../../autoloader.php'
    )
);

require_once(
    dirname(__FILE__) .
    DIRECTORY_SEPARATOR . 'testenv' .
    DIRECTORY_SEPARATOR . 'bootstrap.php'
);

$logging =& Plop::getInstance();
$logging->basicConfig();
unset($logging);

