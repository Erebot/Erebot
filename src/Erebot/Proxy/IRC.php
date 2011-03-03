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

class       Erebot_Proxy_IRC
implements  Erebot_Interface_Proxy
{
    static public function getDefaultPort()
    {
        // As assigned by IANA.
        return 194;
    }

    static public function requiresSSL()
    {
        return FALSE;
    }

    static public function proxify(Erebot_URI $proxyURI, Erebot_URI $nextURI, $socket)
    {
    }
}
