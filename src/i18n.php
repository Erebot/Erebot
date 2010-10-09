<?php

// php-gettext isn't all that clean...
// ignore E_STRICT errors when importing it.
$err = error_reporting(E_ALL & ~E_STRICT);
require('/usr/share/php/php-gettext/gettext.inc');
error_reporting($err);

ErebotUtils::incl('ifaces/i18n.php');

/**
 * A wrapper around an ErebotI18n object.
 * This class simply indicates to the wrapper ErebotI18n object
 * what component to use for translations.
 */
class ErebotI18nWrapper
{
    protected $wrappedTranslator;
    protected $component;

    /**
     * Create a wrapper around an ErebotI18n object.
     *
     * \param $wrappedTranslator
     *      The ErebotI18n object to wrap.
     *
     * \param $component
     *      The name of the component to use for translations.
     *      This should be set to the name of the module
     *      or "Erebot" for the core.
     */
    public function __construct(iErebotI18n &$wrappedTranslator, $component)
    {
        $this->wrappedTranslator    = $wrappedTranslator;
        $this->component            = $component;
    }

    /**
     * Wraps a call to the translator.
     * This method captures all calls to methods which are
     * undefined in this class and passes the call to the
     * wrapped ErebotI18n object.
     */
    public function __call($name, $args)
    {
        return call_user_func_array(array($this->wrappedTranslator, $name), $args);
    }

    /**
     * Wraps a static call to the translator.
     * This method captures all calls to static methods which
     * are undefined in this class and passes the call to the
     * wrapped ErebotI18n object.
     */
    static public function __callStatic($name, $args)
    {
        return forward_static_call_array(array($this->wrappedTranslator, $name), $args);
    }

    /**
     * Returns the translated version of a message.
     *
     * \param $message
     *      The original message to translate.
     *
     * \return
     *      A translation of the original message.
     */
    public function gettext($message)
    {
        return $this->wrappedTranslator->gettext($this->component, $message);
    }
}

/**
 * A class which provides translations for
 * messages used by the core and modules.
 */
class ErebotI18n
implements iErebotI18n
{
    protected $locale;

    /**
     * Creates a new translator for messages.
     *
     * \param $locale
     *      The target locale for messages translated
     *      by this instance.
     */
    public function __construct($locale)
    {
        $this->locale = str_replace('-', '_', $locale);
    }

    /**
     * Returns the target locale of this translator
     * in canonical form.
     *
     * \return
     *      Returns the canonical form of the target
     *      locale for this translator. 
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Translates the given message using the translations
     * provided by a particular application (component).
     *
     * \param $application
     *      The application (component) whose translations
     *      are to be used for the message.
     *
     * \param $message
     *      The original message to translate, in US English.
     *
     * \return
     *      Returns the message translated into the selected
     *      locale.
     */
    public function gettext($application, $message)
    {
        // Prepare the (php-)gettext library for translation.
        T_setlocale(LC_MESSAGES, $this->locale);
        if ($application == 'Erebot')
            $localesDir = dirname(dirname(__FILE__)).'/i18n/';
        else
            $localesDir = dirname(dirname(__FILE__)).
                '/modules/'.$application.'/i18n/';

        // php-gettext isn't quite clean, we try to bear with it.
        $err = error_reporting(E_ALL & ~E_STRICT);
        T_bindtextdomain($application, $localesDir);
        error_reporting($err);
        T_bind_textdomain_codeset($application, 'UTF-8');

        // Do the actual translation here.
        $msg = T_dgettext($application, $message);
        return $msg;
    }

    /**
     * Formats a duration according to the rules
     * of the current locale.
     *
     * \param $duration
     *      The duration to format, given in seconds.
     *
     * \return
     *      Returns a string expressing the duration
     *      according to the rules of the current
     *      locale.
     */
    public function formatDuration($duration)
    {
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

        $func = 'gettext';
        $fmt = new NumberFormatter($this->locale,
                    NumberFormatter::PATTERN_RULEBASED,
                    $this->$func('Erebot', $rule));
        return $fmt->format($duration);
    }
}

?>
