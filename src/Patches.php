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

namespace Erebot;

/**
 * \brief
 *      A class that provides some patches
 *      for PHP.
 */
class Patches
{
    /**
     * Apply a few patches to PHP.
     *
     * \return
     *      This method does not return anything.
     */
    public static function patch()
    {
        set_error_handler(
            function ($errno, $errstr, $errfile, $errline) {
                if (($errno & error_reporting()) !== $errno) {
                    return false;
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

        \Erebot\CallableWrapper::initialize();

        // The name "glob" is already used internally as of PHP 5.3.0.
        // Moreover, the wrapper returns an XML document, hence "xglob".
        if (!in_array("xglob", stream_get_wrappers())) {
            stream_wrapper_register('xglob', '\\Erebot\\XGlobStream', STREAM_IS_URL);
        }

        /* Needed to prevent libxml from trying to magically "fix" URLs
         * included with XInclude as this breaks a lot of things.
         * This requires libxml >= 2.6.20 (which was released in 2005). */
        if (!defined('LIBXML_NOBASEFIX')) {
            define('LIBXML_NOBASEFIX', 1 << 18);
        }
    }
}
