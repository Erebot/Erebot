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

include_once('src/ifaces/serverConfig.php');

/**
 * \brief
 *      This class contains the configuration for an IRC server.
 *
 * This class stores settings which are specific to an IRC server,
 * such as a connection URL and a default quit message.
 */
class       ErebotServerConfig
extends     ErebotConfigProxy
implements  iErebotServerConfig
{
    /// A URL used to connect to this IRC(S) server.
    protected $_connectionURL;

    // Documented in the interface.
    public function __construct(
        iErebotNetworkConfig    &$netCfg,
        SimpleXMLElement        &$xml
    )
    {
        parent::__construct($netCfg, $xml);
        $this->_connectionURL = (string) $xml['url'];
    }

    /**
     * Destructs ErebotServerConfig instances.
     */
    public function __destruct()
    {
    }

    // Documented in the interface.
    public function getConnectionURL()
    {
        return $this->_connectionURL;
    }

    // Documented in the interface.
    public function & getNetworkCfg()
    {
        return $this->_proxified;
    }
}

