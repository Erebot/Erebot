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
 *      A class that provides some patches
 *      for PHP.
 */
class Erebot_Patches
{
    /**
     * Apply a few patches to PHP.
     *
     * Currently the following changes are made:
     * - ctype_digit() is emulated (in case it was
     *   disabled on the configure line).
     *
     * \return
     *      This method does not return anything.
     */
    static public function patch()
    {
        // Quick replacement for ctype_digit in case
        // PHP was compiled with --disable-ctype.
        if (!function_exists('ctype_digit')) {
            function ctype_digit($s)
            {
                if (!is_string($s))
                    return FALSE;
                $len = strlen($s);
                if (!$len)
                    return FALSE;
                return strspn($s, '1234567890') == $len;
            }
        }

        set_error_handler(
            function ($errno, $errstr, $errfile, $errline) {
                if (($errno & error_reporting()) !== $errno) {
                    return FALSE;
                }

                throw new \Erebot\ErrorReportingException(
                   $errstr,
                   $errno,
                   $errfile,
                   $errline
                );
            },
            E_ALL
        );

        \Erebot\CallableWrapper\Init::initialize();
    }
}
