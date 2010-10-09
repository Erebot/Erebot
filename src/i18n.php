<?php

// php-gettext isn't all that clean...
// ignore E_STRICT errors when importing it.
$err = error_reporting(E_ALL & ~E_STRICT);
$php_gettext = (isset($_ENV['PHP_GETTEXT_PATH']) ? $_ENV['PHP_GETTEXT_PATH'] : NULL);
if ($php_gettext === NULL ||
    !file_exists($php_gettext.'/gettext.inc') ||
    !is_readable($php_gettext.'/gettext.inc'))
    $php_gettext = '/usr/share/php/php-gettext/';
require($php_gettext.'/gettext.inc');
define('PHP_GETTEXT_PATH', $php_gettext);
error_reporting($err);
unset($php_gettext, $err);

ErebotUtils::incl('ifaces/i18n.php');

/**
 * \brief
 *      A class which provides translations for
 *      messages used by the core and modules.
 */
class ErebotI18n
implements iErebotI18n
{
    /// The locale for which messages are translated.
    protected $locale;

    /// The component to get translations from (a module name or "Erebot").
    protected $component;

    // Documented in the interface.
    public function __construct($locale, $component)
    {
        $this->locale = str_replace('-', '_', $locale);
        $this->component = $component;
    }

    // Documented in the interface.
    public function getLocale()
    {
        return $this->locale;
    }

    protected function real_gettext($message, $component)
    {
        if ($component == 'Erebot')
            $localesDir = dirname(dirname(__FILE__)).'/i18n/';
        else
            $localesDir = dirname(dirname(__FILE__)).
                '/modules/'.$component.'/i18n/';

        // HACK: php-gettext doesn't reset the translations
        // correctly when set T_setlocale is called.
        // We have to use T_textdomain separately here.
        T_textdomain($component);
        T_setlocale(LC_MESSAGES, $this->locale);
        // php-gettext isn't quite clean, we try to bear with it.
        $err = error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE);
        T_bindtextdomain($component, $localesDir);
        T_bind_textdomain_codeset($component, 'UTF-8');

        // Do the actual translation here.
        $msg = T_gettext($message);
        error_reporting($err);

##        $cmd =  "LANG=".$this->locale.".UTF-8 ".
##                "TEXTDOMAINDIR=".escapeshellarg($localesDir)." ".
##                "gettext -d ".$component." ".escapeshellarg($message);
##        
##        $msg = shell_exec($cmd);
        return $msg;
    }

    // Documented in the interface.
    public function gettext($message)
    {
        return $this->real_gettext($message, $this->component);
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

        $fmt = new NumberFormatter($this->locale,
                    NumberFormatter::PATTERN_RULEBASED,
                    $this->real_gettext($rule, "Erebot"));
        return $fmt->format($duration);
    }
}

?>
