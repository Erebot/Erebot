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

#$oldErrorReporting = error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
#require('File/Gettext.php');
#error_reporting($oldErrorReporting);
#unset($oldErrorReporting);

/**
 * \brief
 *      A class which provides translations for
 *      messages used by the core and modules.
 */
class       Erebot_I18n
implements  Erebot_Interface_I18n
{
    static protected $_cache = array();

    /// The locale for which messages are translated.
    protected $_locale;

    /// The component to get translations from (a module name or "Erebot").
    protected $_component;

    protected $_parser;

    // Documented in the interface.
    public function __construct($locale, $component)
    {
        $this->_locale = str_replace('-', '_', $locale);
        $this->_component = $component;
    }

    // Documented in the interface.
    public function getLocale()
    {
        return $this->_locale;
    }

    protected function real_gettext($message, $component)
    {
        if (basename(dirname(dirname(__DIR__))) == 'trunk') {
            if ($component == 'Erebot') {
                $base = '../../data/i18n';
            }
            else if (!strncasecmp($component, 'Erebot_Module', 13)) {
                $base = '../../../../modules/' .
                    substr($component, 13) .
                    '/trunk/data/i18n';
            }
        }
        else
            $base = '../../../data/pear.erebot.net/' . $component . '/i18n';
        $base = str_replace('/', DIRECTORY_SEPARATOR, trim($base, '/'));
        $prefix = __DIR__ . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR;

        $translationFile = $prefix . $this->_locale . DIRECTORY_SEPARATOR .
            'LC_MESSAGES' . DIRECTORY_SEPARATOR . $component . '.mo';

        if (version_compare(PHP_VERSION, '5.3.0', '>='))
            clearstatcache(FALSE, $translationFile);
        else
            clearstatcache();

        /**
         * FIXME: filemtime() raises a warning if the given file
         * could not be stat'd (such as when is does not exist).
         * An error_reporting level of E_ALL & ~E_DEPRECATED
         * would otherwise be fine for File_Gettext.
         */
        $oldErrorReporting = error_reporting(0);
        $mtime = filemtime($translationFile);

        if (!isset(self::$_cache[$translationFile]) ||
            $mtime !== self::$_cache[$translationFile]['mtime']) {
            $parser =& File_Gettext::factory('MO', $translationFile);
            $parser->load();
            self::$_cache[$translationFile] = array(
                'mtime'     => $mtime,
                'strings'   => $parser->strings,
            );
        }
        error_reporting($oldErrorReporting);

        if (isset(self::$_cache[$translationFile]['strings'][$message]))
            return self::$_cache[$translationFile]['strings'][$message];
        return $message;
    }

    // Documented in the interface.
    public function gettext($message)
    {
        return $this->real_gettext($message, $this->_component);
    }

    // Documented in the interface.
    public function formatDuration($duration)
    {
        /**
         * @HACK: We need to translate the rule using $this->gettext
         * while specifying "Erebot" as the application. xgettext would
         * extract "Erebot" as the message to translate without this hack.
         */
        $gettext = create_function('$a', 'return $a;');
        $rule = $gettext("
%with-words:
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
    2: =#0= weeks;
");

        $fmt = new NumberFormatter($this->_locale,
                    NumberFormatter::PATTERN_RULEBASED,
                    $this->real_gettext($rule, "Erebot"));
        return $fmt->format($duration);
    }
}

