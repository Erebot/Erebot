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

namespace Erebot\Console;

/**
 * \brief
 *      Custom option that can be used in parallel with regular options.
 *
 * The Console_CommandLine package usually prevents
 * options from acting on the same variable. This
 * specific type of option can be used with the
 * Erebot::Console::StoreProxyAction to work around this.
 */
class ParallelOption extends \Console_CommandLine_Option
{
    /**
     * Overrides the parent method so that
     * this option never expects an argument.
     *
     * \retval bool
     *      Always returns \b false.
     */
    public function expectsArgument()
    {
        return false;
    }
}
