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
 *      IRC collator for the "rfc1459" subset.
 *
 * \note
 *      The name of this collator comes from the
 *      label associated with it by IRC servers.
 *
 * \note
 *      Despite its name, this collator uses the rules
 *      defined in RFC 2812 rather than the ones in
 *      RFC 1459.
 *      This is intended to fix a mistake introduced
 *      in RFC 1459 were some characters were missing
 *      from the definition.
 *
 * \see
 *      Erebot::IrcCollator::StrictRFC1459 for a collator
 *      that strictly follows the collation rules defined
 *      in RFC 1459.
 */
class RFC1459 extends \Erebot\IrcCollator
{
    protected function realNormalizeNick($nick)
    {
        return strtr(
            $nick,
            array_combine(
                // a..~
                range('a', chr(126)),
                // A..^
                range('A', chr(94))
            )
        );
    }
}
