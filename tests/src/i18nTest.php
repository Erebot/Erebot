<?php

include_once('src/utils.php');
include_once('src/i18n.php');

class   I18nTest
extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->translators = array();
        $locales = array(
            'en_US',
            'fr_FR',
        );
        foreach ($locales as $locale)
            $this->translators[$locale] = new ErebotI18n($locale, "Erebot");
    }

    public function testGetLocale()
    {
        foreach ($this->translators as $locale => &$translator)
            $this->assertEquals($locale, $translator->getLocale());
        unset($translator);
    }

    public function testDurationFormatting()
    {
        // English
        $this->assertEquals(
            "0 seconds",
            $this->translators['en_US']->formatDuration(0));
        $this->assertEquals(
            "1 second",
            $this->translators['en_US']->formatDuration(1));
        $this->assertEquals(
            "2 seconds",
            $this->translators['en_US']->formatDuration(2));
        $this->assertEquals(
            "1 week, 1 day, 1 hour, 1 minute, 1 second",
            $this->translators['en_US']->formatDuration(694861));
        $this->assertEquals(
            "2 weeks, 2 days, 2 hours, 2 minutes, 2 seconds",
            $this->translators['en_US']->formatDuration(1389722));

        // French
        $this->assertEquals(
            "0 seconde",
            $this->translators['fr_FR']->formatDuration(0));
        $this->assertEquals(
            "1 seconde",
            $this->translators['fr_FR']->formatDuration(1));
        $this->assertEquals(
            "2 secondes",
            $this->translators['fr_FR']->formatDuration(2));
        $this->assertEquals(
            "1 semaine, 1 jour, 1 heure, 1 minute, 1 seconde",
            $this->translators['fr_FR']->formatDuration(694861));
        $this->assertEquals(
            "2 semaines, 2 jours, 2 heures, 2 minutes, 2 secondes",
            $this->translators['fr_FR']->formatDuration(1389722));
    }

    public function testCoreTranslation()
    {
        $message = "Erebot is starting";
        $translations = array(
            'en_US' =>  $message,
            'fr_FR' =>  "Erebot démarre",
        );
        foreach ($translations as $locale => $translation)
            $this->assertEquals($translation,
                $this->translators[$locale]->gettext($message));
    }
}

?>
