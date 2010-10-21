<?php

class       ErebotStubbedI18n
implements  iErebotI18n
{
    public function __construct($locale = NULL, $component = NULL)
    {
    }

    public function getLocale()
    {
        return 'en_US';
    }

    public function gettext($message)
    {
        return $message;
    }

    public function formatDuration($duration)
    {
        return NULL;
    }
}


