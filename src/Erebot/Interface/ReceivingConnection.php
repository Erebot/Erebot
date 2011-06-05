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
 *      Interface for a connection that receives data.
 */
interface   Erebot_Interface_ReceivingConnection
extends     Erebot_Interface_Connection
{
    /**
     * Returns a boolean indicating whether the incoming FIFO
     * is empty or not.
     *
     * \retval TRUE
     *      The FIFO for incoming messages is empty.
     *
     * \retval FALSE
     *      The FIFO for incoming messages is NOT empty.
     */
    public function emptyReadQueue();

    /**
     * Processes data from the incoming buffer.
     *
     * Once this method has been called, all lines awaiting
     * processing in the incoming buffer have been transferred
     * to the incoming FIFO.
     * You must call Erebot_Connection::processQueuedData()
     * after that in order to process the lines in the FIFO.
     * This is done so that a throttling policy may be put
     * in place if needed (eg. for an anti-flood system).
     */
    public function processIncomingData();

    /**
     * Processes all lines in the incoming FIFO.
     * This method will dispatch the proper events/raws
     * for each line in the FIFO.
     */
    public function processQueuedData();
}
