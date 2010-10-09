<?php

define('EREBOT_VERSION',    '0.2.1');

function getErebotVersion()
{
    $included = get_included_files();
    if ($included[0] == __FILE__)
        echo EREBOT_VERSION;
}
getErebotVersion();


?>
