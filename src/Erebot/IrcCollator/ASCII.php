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
 *      IRC collator for the "ascii" subset.
 *
 * \note
 *      The name of this collator comes from the
 *      label associated with it by IRC servers.
 *
 * This collator accepts all latin letters
 * and normalizes nicknames by uppercasing them.
 */
class   Erebot_IrcCollator_ASCII
extends Erebot_IrcCollator
{
    /// \copydoc Erebot_IrcCollator::_normalizeNick()
    protected function _normalizeNick($nick)
    {
        // We don't use strtoupper() as it's locale-dependent
        // and causes issues when using a Turkish locale.
        return strtr(
            $nick,
            array_combine(
                range('a', 'z'),
                range('A', 'Z')
            )
        );
    }
}

