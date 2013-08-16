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

class   I18nTest
extends Erebot_TestEnv_TestCase
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

