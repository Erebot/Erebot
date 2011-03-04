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
 * Base class for a proxy client.
 */
abstract class Erebot_Proxy_Base
{
    /// A socket on which to operate.
    protected $_socket;

    /// A logger for this proxy client.
    protected $_logger;

    /**
     * Create the base for a proxy client.
     *
     * \param stream $socket
     *      A socket which will be used to tunnel data.
     */
    public function __construct($socket)
    {
        if (!is_resource($socket))
            throw new Erebot_InvalidValueException('Not a socket');

        $this->_socket  = $socket;
        $logging        = Plop::getInstance();
        $this->_logger  = $logging->getLogger(__FILE__ . DIRECTORY_SEPARATOR);
    }

    /**
     * Main method which will do all the work
     * of making the proxy tunnel.
     *
     * \param Erebot_URI $proxyURI
     *      URI used to designate the proxy.
     *
     * \param Erebot_URI $nextURI
     *      URI used to designate the next element
     *      in the proxy chain. The next URI may
     *      point to either another proxy (chaining),
     *      or the final end point of the chain.
     */
    abstract public function proxify(Erebot_URI $proxyURI, Erebot_URI $nextURI);
}

