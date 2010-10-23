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
 *      An exception thrown whenever PHP raises a notice, warning, etc.
 *
 * This is an implementation of a custom exception which turns
 * all PHP messages (errors, warnings, notices, etc.) into exceptions.
 * We use this exception instead of PHP's ErrorException because the
 * latter is buggy under PHP 5.2, which might be a target version for
 * Erebot at some time.
 * Original implementation proposed by luke at cywh dot com:
 * http://php.net/manual/en/class.errorexception.php#89132
 */
class   EErebotErrorReporting
extends EErebot
{
    static protected $_map = NULL;

    public function __construct($message, $code, $filename, $lineno)
    {
        if (self::$_map === NULL) {
            $constants = get_defined_constants(TRUE);
            $core = array();
            if (isset($constants['Core']))
                $core = $constants['Core'];
            else if (isset($constants['internal']))
                $core = $constants['internal'];
            else if (isset($constants['mhash']))
                $core = $constants['mhash'];

            self::$_map = array();
            foreach ($core as $name => $value) {
                if (substr($name, 0, 2) == 'E_')
                    self::$_map[$value] = $name;
            }
        }

        parent::__construct($message, $code);
        $this->file = $filename;
        $this->line = $lineno;
    }

    public function __toString()
    {
        if (isset(self::$_map[$this->code]))
            $code = self::$_map[$this->code];
        else
            $code = '???';

        return "[$code] - {$this->message}";
    }
}

set_error_handler(
    create_function(
        '$errno, $errstr, $errfile, $errline',
        'if (($errno & error_reporting()) != $errno) return FALSE;'.
        'throw new EErebotErrorReporting($errstr, $errno, $errfile, $errline);'
    ),
    E_ALL
);

