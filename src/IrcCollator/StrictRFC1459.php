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

namespace Erebot\IrcCollator;

/**
 * \brief
 *      IRC collator for the "strict-rfc1459" subset.
 *
 * \note
 *      The name of this collator comes from the
 *      label associated with it by IRC servers.
 *
 * \note
 *      This collator uses the rules defined in RFC 1459,
 *      which is missing a few characters. This is fixed
 *      by the Erebot::IrcCollator::RFC1459 collator.
 *
 * \see
 *      Erebot::IrcCollator::RFC1459 for a collator
 *      that implements saner collation rules.
 */
class StrictRFC1459 extends \Erebot\IrcCollator
{
    protected function realNormalizeNick($nick)
    {
        return strtr(
            $nick,
            array_combine(
                // a..}
                range('a', chr(125)),
                // A..]
                range('A', chr(93))
            )
        );
    }
}
