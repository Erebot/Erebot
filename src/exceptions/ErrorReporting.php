<?php

/**
 * \brief
 *      An exception thrown whenever PHP raises a notice, warning, etc.
 *
 * This is an implementation of a custom exception which turns
 * all PHP messages (errors, warnings, notices, etc.) into exceptions.
 * We use this exception instead of PHP's ErrorException because it
 * is buggy under PHP 5.2, which might be a target version for Erebot
 * at some time.
 * Original implementation proposed by luke at cywh dot com:
 * http://php.net/manual/en/class.errorexception.php#89132
 */
class   EErebotErrorReporting
extends EErebot
{
    static protected $map = NULL;

    public function __construct($message, $code, $filename, $lineno)
    {
        if (self::$map === NULL) {
            $constants = get_defined_constants(TRUE);
            $core = array();
            if (isset($constants['Core']))
                $core = $constants['Core'];
            else if (isset($constants['internal']))
                $core = $constants['internal'];
            else if (isset($constants['mhash']))
                $core = $constants['mhash'];

            self::$map = array();
            foreach ($core as $name => $value) {
                if (substr($name, 0, 2) == 'E_')
                    self::$map[$value] = $name;
            }
        }

        parent::__construct($message, $code);
        $this->file = $filename;
        $this->line = $lineno;
    }

    public function __toString()
    {
        if (isset(self::$map[$this->code]))
            $code = self::$map[$this->code];
        else
            $code = '???';

        return "[$code] - {$this->message}";
    }
}

set_error_handler(create_function(
        '$errno, $errstr, $errfile, $errline',
        'throw new EErebotErrorReporting($errstr, $errno, $errfile, $errline);'
    ), E_ALL);

?>
