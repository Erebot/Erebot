<?php
/*
    This file is part of Erebot.

    Erebot is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Erebot is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Erebot.  If not, see <http://www.gnu.org/licenses/>.
*/

if (!defined('TESTENV_DIR'))
    define(
        'TESTENV_DIR',
        dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'testenv'
    );
require_once(TESTENV_DIR . DIRECTORY_SEPARATOR . 'bootstrap.php');

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
        foreach ($locales as $locale) {
            $this->translators[$locale] = new Erebot_I18n("Erebot");
            $this->translators[$locale]->setLocale(
                Erebot_Interface_I18n::LC_MESSAGES,
                $locale
            );
        }
    }

    /**
     * @covers Erebot_I18n
     */
    public function testGetLocale()
    {
        foreach ($this->translators as $locale => $translator)
            $this->assertEquals(
                $locale,
                $translator->getLocale(Erebot_Interface_I18n::LC_MESSAGES)
            );
    }

    /**
     * @covers Erebot_I18n
     */
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

    /**
     * @covers Erebot_I18n
     */
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

