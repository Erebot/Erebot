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

interface   Erebot_Interface_RawProfile_RULES
extends     Erebot_Interface_RawProfile
{
    const RPL_RULES                 = 232;

    const RPL_RULESTART             = 308;
    const RPL_RULESSTART            = 308;

    const RPL_ENDOFRULES            = 309;
    const RPL_RULESEND              = 309;

    const ERR_NORULES               = 434;
}
