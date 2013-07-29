<?php
/*
    This file is part of Erebot, a modular IRC bot written in PHP.

    Copyright © 2010 François Poirotte

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
 *      A class which provides translations for
 *      messages used by the core and modules.
 */
class       Erebot_I18n
implements  Erebot_Interface_I18n
{
    /// Expiration time of entries in the cache (in seconds).
    const EXPIRE_CACHE = 60;

    /// A cache for translation catalogs, with some additional metadata.
    static protected $_cache = array();

    /// The actual locales used for i18n.
    protected $_locales;

    /// The component to get translations from (a module name or "Erebot").
    protected $_component;

    /**
     * Creates a new translator for messages.
     *
     * \param string $component
     *      The name of the component to use for translations.
     *      This should be set to the name of the module
     *      or "Erebot" for the core.
     */
    public function __construct($component)
    {
        $this->_locales = array();
        $categories = array(
            self::LC_CTYPE,
            self::LC_NUMERIC,
            self::LC_TIME,
            self::LC_COLLATE,
            self::LC_MONETARY,
            self::LC_MESSAGES,
            self::LC_PAPER,
            self::LC_NAME,
            self::LC_ADDRESS,
            self::LC_TELEPHONE,
            self::LC_MEASUREMENT,
            self::LC_IDENTIFICATION,
        );
        foreach ($categories as $category)
            $this->_locales[$category] = "en_US";
        $this->_component = $component;
    }

    /// \copydoc Erebot_Interface_I18n::nameToCategory()
    static public function nameToCategory($name)
    {
        $categories = array_flip(
            array(
                self::LC_CTYPE          => 'LC_CTYPE',
                self::LC_NUMERIC        => 'LC_NUMERIC',
                self::LC_TIME           => 'LC_TIME',
                self::LC_COLLATE        => 'LC_COLLATE',
                self::LC_MONETARY       => 'LC_MONETARY',
                self::LC_MESSAGES       => 'LC_MESSAGES',
                self::LC_PAPER          => 'LC_PAPER',
                self::LC_NAME           => 'LC_NAME',
                self::LC_ADDRESS        => 'LC_ADDRESS',
                self::LC_TELEPHONE      => 'LC_TELEPHONE',
                self::LC_MEASUREMENT    => 'LC_MEASUREMENT',
                self::LC_IDENTIFICATION => 'LC_IDENTIFICATION',
            )
        );
        if (!isset($categories[$name]))
            throw new Erebot_InvalidValueException('Invalid category name');
        return $categories[$name];
    }

    /// \copydoc Erebot_Interface_I18n::categoryToName()
    static public function categoryToName($category)
    {
        $categories = array(
            self::LC_CTYPE          => 'LC_CTYPE',
            self::LC_NUMERIC        => 'LC_NUMERIC',
            self::LC_TIME           => 'LC_TIME',
            self::LC_COLLATE        => 'LC_COLLATE',
            self::LC_MONETARY       => 'LC_MONETARY',
            self::LC_MESSAGES       => 'LC_MESSAGES',
            self::LC_PAPER          => 'LC_PAPER',
            self::LC_NAME           => 'LC_NAME',
            self::LC_ADDRESS        => 'LC_ADDRESS',
            self::LC_TELEPHONE      => 'LC_TELEPHONE',
            self::LC_MEASUREMENT    => 'LC_MEASUREMENT',
            self::LC_IDENTIFICATION => 'LC_IDENTIFICATION',
        );
        if (!isset($categories[$category]))
            throw new Erebot_InvalidValueException('Invalid category');
        return $categories[$category];
    }

    /// \copydoc Erebot_Interface_I18n::getLocale()
    public function getLocale($category)
    {
        if (!isset($this->_locales[$category]))
            throw new Erebot_InvalidValueException('Invalid category');
        return $this->_locales[$category];
    }

    /// \copydoc Erebot_Interface_I18n::setLocale()
    public function setLocale($category, $candidates)
    {
        $categoryName = self::categoryToName($category);
        if (!is_array($candidates))
            $candidates = array($candidates);
        if (!count($candidates))
            throw new Erebot_InvalidValueException('Invalid locale');

        $newLocale = NULL;
        foreach ($candidates as $candidate) {
            if (!is_string($candidate))
                throw new Erebot_InvalidValueException('Invalid locale');

            $locale = Locale::parseLocale($candidate);
            if (!is_array($locale) || !isset($locale['language']))
                throw new Erebot_InvalidValueException('Invalid locale');

            // For anything else than LC_MESSAGES,
            // we take the first candidate as is.
            if ($categoryName != 'LC_MESSAGES')
                $newLocale = $candidate;

            if ($newLocale !== NULL)
                continue;

            if (isset($locale['region'])) {
                $normLocale = $locale['language'] . '_' . $locale['region'];
                try {
                    $file = Erebot_Utils::getResourcePath(
                        $this->_component,
                        'i18n' .
                        DIRECTORY_SEPARATOR . $normLocale .
                        DIRECTORY_SEPARATOR . $categoryName .
                        DIRECTORY_SEPARATOR . $this->_component . '.mo'
                    );
                    $newLocale = $normLocale;
                    continue;
                }
                catch (Exception $e) {
                }
            }

            try {
                $file = Erebot_Utils::getResourcePath(
                    $this->_component,
                    'i18n' .
                    DIRECTORY_SEPARATOR . $locale['language'] .
                    DIRECTORY_SEPARATOR . $categoryName .
                    DIRECTORY_SEPARATOR . $this->_component . '.mo'
                );
                $newLocale = $locale['language'];
                continue;
            }
            catch (Exception $e) {
            }
        }

        if ($newLocale === NULL)
            $newLocale = 'en_US';
        $this->_locales[$category] = $newLocale;
        return $newLocale;
    }

    /**
     * Returns the translation for the given message,
     * as contained in some translation catalog (MO file).
     *
     * \param string $file
     *      Path to the translation catalog to use.
     *
     * \param string $message
     *      The message to translate.
     *
     * \param string $mode
     *      Either "MO" or "PO", indicating whether
     *      the given file refers to a MO or PO catalog.
     *
     * \retval string
     *      The translation matching the given message.
     *
     * \retval NULL
     *      The message could not be found in the translation
     *      catalog.
     *
     * \note
     *      This method implements a caching strategy
     *      so that the translation catalog is not read
     *      again every time this method is called
     *      but only when the catalog actually changed.
     */
    protected function _get_translation($file, $message, $mode)
    {
        $time = time();
        if (!isset(self::$_cache[$file]) ||
            $time > (self::$_cache[$file]['added'] + self::EXPIRE_CACHE)) {

            /**
             * FIXME: filemtime() raises a warning if the given file
             * could not be stat'd (such as when is does not exist).
             * An error_reporting level of E_ALL & ~E_DEPRECATED
             * would otherwise be fine for File_Gettext.
             */
            $oldErrorReporting = error_reporting(E_ERROR);

            if (version_compare(PHP_VERSION, '5.3.0', '>='))
                clearstatcache(FALSE, $file);
            else
                clearstatcache();

            $mtime = filemtime($file);
            if ($mtime === FALSE) {
                // We also cache failures to avoid
                // harassing the CPU too much.
                self::$_cache[$file] = array(
                    'mtime'     => $time,
                    'string'    => array(),
                    'added'     => $time,
                );
            }
            else if (!isset(self::$_cache[$file]) ||
                $mtime !== self::$_cache[$file]['mtime']) {
                $parser = File_Gettext::factory($mode, $file);
                $parser->load();
                self::$_cache[$file] = array(
                    'mtime'     => $mtime,
                    'strings'   => $parser->strings,
                    'added'     => $time,
                );
            }
            error_reporting($oldErrorReporting);
        }

        if (isset(self::$_cache[$file]['strings'][$message]))
            return self::$_cache[$file]['strings'][$message];
        return NULL;
    }

    /**
     * Low-level translation method.
     *
     * \param string $message
     *      The message to translate.
     *
     * \param string $component
     *      The name of the component this translation
     *      belongs to, such as "Erebot" for core messages
     *      or "Erebot_Module_ABC" for messages belonging
     *      to the module named "Erebot_Module_ABC".
     *
     * \retval string
     *      Either the translation for the given message
     *      is returned, or the original message if none
     *      could be found.
     */
    protected function _real_gettext($message, $component)
    {
        try {
            $mode = 'MO';
            $translationFile = Erebot_Utils::getResourcePath(
                $component,
                'i18n' .
                DIRECTORY_SEPARATOR . $this->_locales[self::LC_MESSAGES] .
                DIRECTORY_SEPARATOR . 'LC_MESSAGES' .
                DIRECTORY_SEPARATOR . $component . '.mo'
            );
        }
        catch (Exception $e) {
            $mode = 'PO';
            $translationFile = Erebot_Utils::getResourcePath(
                $component,
                'i18n' .
                DIRECTORY_SEPARATOR . $this->_locales[self::LC_MESSAGES] .
                DIRECTORY_SEPARATOR . 'LC_MESSAGES' .
                DIRECTORY_SEPARATOR . $component . '.po'
            );
        }

        $translation = $this->_get_translation($translationFile, $message, $mode);
        return ($translation === NULL) ? $message : $translation;
    }

    /// \copydoc Erebot_Interface_I18n::gettext()
    public function gettext($message)
    {
        return $this->_real_gettext($message, $this->_component);
    }

    /// \copydoc Erebot_Interface_I18n::_()
    public function _($message)
    {
        return $this->_real_gettext($message, $this->_component);
    }

    /**
     * Clears the cache used for translation catalogs.
     *
     * \retval
     *      This method does not return any value.
     */
    static public function clearCache()
    {
        self::$_cache = array();
    }
}
