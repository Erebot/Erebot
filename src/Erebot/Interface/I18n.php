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

/**
 * \brief
 *      Interface to provide internationalization.
 *
 * This interface provides the necessary methods
 * to provide internationalization of the messages
 * the bot emits through logging or exceptions.
 */
interface Erebot_Interface_I18n
{
    /**
     * Creates a new translator for messages.
     *
     * \param string $locale
     *      The target locale for messages translated
     *      by this instance.
     *
     * \param string $component
     *      The name of the component to use for translations.
     *      This should be set to the name of the module
     *      or "Erebot" for the core.
     */
    public function __construct($locale, $component);

    /**
     * Returns the target locale of this translator
     * in canonical form.
     *
     * \retval string
     *      The canonical form of the target locale
     *      for this translator. 
     */
    public function getLocale();

    /**
     * Translates the given message using the translations
     * for the current component.
     *
     * \param string $message
     *      The original message to translate, in US English.
     *
     * \retval string
     *      The message, translated into the selected locale.
     */
    public function gettext($message);

    /**
     * Formats a duration according to the rules
     * of the current locale.
     *
     * \param int $duration
     *      The duration to format, given in seconds.
     *
     * \retval string
     *      A representation of the duration according to
     *      the rules of the current locale.
     */
    public function formatDuration($duration);
}
