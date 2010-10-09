<?php

interface iErebotI18n
{
    public function __construct($locale);
    public function getLocale();
    public function gettext($application, $message);
    public function formatDuration($duration);
}

?>
