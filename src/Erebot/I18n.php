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

    /// \copydoc Erebot_Interface_I18n::__construct()
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
        return $categories[$category];
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

            if ($newLocale !== NULL)
                continue;

            if (isset($locale['region'])) {
                $normLocale = $locale['language'] . '_' . $locale['region'];
                $file = self::_build_path(
                    $normLocale,
                    $categoryName,
                    $this->_component
                );
                if (file_exists($file)) {
                    $newLocale = $normLocale;
                    continue;
                }
            }

            $file = self::_build_path(
                $locale['language'],
                $categoryName,
                $this->_component
            );
            if (file_exists($file)) {
                $newLocale = $locale['language'];
                continue;
            }
        }

        if ($newLocale === NULL)
            $newLocale = 'en_US';
        $this->_locales[$category] = $newLocale;
        return $newLocale;
    }

    static protected function _build_path($locale, $category, $domain)
    {
        if (basename(dirname(dirname(dirname(__FILE__)))) == 'trunk') {
            if ($domain == 'Erebot') {
                $base = '../../data/i18n';
            }
            else if (!strncasecmp($domain, 'Erebot_Module_', 14)) {
                $base = '../../../../modules/' .
                    substr($domain, 14) .
                    '/trunk/data/i18n';
            }
        }
        else
            $base = '../../data/pear.erebot.net/' . $domain . '/i18n';
        $base = str_replace('/', DIRECTORY_SEPARATOR, trim($base, '/'));
        $prefix = dirname(__FILE__) . DIRECTORY_SEPARATOR .
            $base . DIRECTORY_SEPARATOR;

        return $prefix . $locale . DIRECTORY_SEPARATOR .
            $category . DIRECTORY_SEPARATOR . $domain . '.mo';
    }

    protected function _get_translation($file, $message)
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
            $oldErrorReporting = error_reporting(0);

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
                $parser =& File_Gettext::factory('MO', $file);
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

    protected function _real_gettext($message, $component)
    {
        $translationFile    = self::_build_path(
            $this->_locales[self::LC_MESSAGES],
            'LC_MESSAGES',
            $component
        );

        $translation = $this->_get_translation($translationFile, $message);
        return ($translation === NULL) ? $message : $translation;
    }

    /// \copydoc Erebot_Interface_I18n::gettext()
    public function gettext($message)
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

    /// \copydoc Erebot_Interface_I18n::formatDuration()
    public function formatDuration($duration)
    {
        /**
         * @HACK: We need to translate the rule using $this->gettext
         * while specifying "Erebot" as the application. xgettext would
         * extract "Erebot" as the message to translate without this hack.
         */
        $gettext = create_function('$a', 'return $a;');
        $rule = $gettext("%with-words:
    0: 0 seconds;
    1: 1 second;
    2: =#0= seconds;
    60/60: <%%min<;
    61/60: <%%min<, >%with-words>;
    3600/60: <%%hr<;
    3601/60: <%%hr<, >%with-words>;
    86400/86400: <%%day<;
    86401/86400: <%%day<, >%with-words>;
    604800/604800: <%%week<;
    604801/604800: <%%week<, >%with-words>;
%%min:
    1: 1 minute;
    2: =#0= minutes;
%%hr:
    1: 1 hour;
    2: =#0= hours;
%%day:
    1: 1 day;
    2: =#0= days;
%%week:
    1: 1 week;
    2: =#0= weeks;");

        $fmt = new NumberFormatter(
            $this->_locales[self::LC_MESSAGES],
            NumberFormatter::PATTERN_RULEBASED,
            $this->_real_gettext($rule, "Erebot")
        );
        return $fmt->format($duration);
    }
}
