<?php

// Avoid a harmless warning in PHPUnit
// when it generates coverage report.
date_default_timezone_set('UTC');

PHPUnit_Util_Filter::addFileToFilter(__FILE__);
PHPUnit_Util_Filter::addDirectoryToFilter('/usr/share/php/php-gettext/');
PHPUnit_Util_Filter::addDirectoryToFilter('/usr/share/php/php-gettext/', '.inc');

#require('PHPUnit/Framework.php');
require(dirname(dirname(__FILE__)).'/src/utils.php');

#ErebotUtils::incl('classProxy.php');
ErebotUtils::incl('connectionStub.php');
ErebotUtils::incl('configStub.php');

ErebotUtils::incl('../src/moduleBase.php');
ErebotUtils::incl('../src/events/events.php');

ErebotUtils::incl('../src/exceptions/IllegalAction.php');
ErebotUtils::incl('../src/exceptions/InvalidValue.php');

ErebotUtils::incl('../src/logging/src/logging.php');

$logging =& Plop::getInstance();
$logging->basicConfig();
unset($logging);

?>
