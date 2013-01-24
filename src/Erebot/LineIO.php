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

/**
 * \brief
 *      A class that provides a line-by-line reader.
 *
 * Using this class, it is possible to read messages
 * one line at a time simply by specifying the
 * End-Of-Line (EOL) style in use by the protocol.
 */
class   Erebot_LineIO
{
    /// Windows line-ending.
    const EOL_WIN       = "\r\n";

    /// Old MacOS line-ending (MacOS <= 9).
    const EOL_OLD_MAC   = "\r";

    /// Unix line-ending.
    const EOL_UNIX      = "\n";

    /// New MacOS line-ending (MacOS > 9).
    const EOL_NEW_MAC   = "\n";

    /// Universal line-ending mode (compatible with Windows, Mac and *nix).
    const EOL_ANY       = NULL;

    /// Line-ending mode in use.
    protected $_eol;

    /// The underlying socket, represented as a stream.
    protected $_socket;

    /// A FIFO queue for outgoing messages.
    protected $_sndQueue;

    /// A FIFO queue for incoming messages.
    protected $_rcvQueue;

    /// A raw buffer for incoming data.
    protected $_incomingData;

    /**
     * Constructs a new line-by-line reader.
     *
     * \param opaque $eol
     *      The type of line-endings to accept.
     *      This may be one of Erebot_LineIO::EOL_WIN,
     *      Erebot_LineIO::EOL_OLD_MAC,
     *      Erebot_LineIO::EOL_NEW_MAC,
     *      Erebot_LineIO::EOL_UNIX or
     *      Erebot_LineIO::EOL_ANY.
     *
     * \param resource $socket
     *      (optional) The socket to operate on.
     *
     * \warning
     *      Even though the \a $socket argument is
     *      optional here, you must still provide one
     *      (using the setSocket() method) before
     *      calling any of this object's methods.
     *      Failure to do so may result in unpredictable
     *      results.
     */
    public function __construct($eol, $socket = NULL)
    {
        $this->setSocket($socket);
        $this->setEOL($eol);
    }

    /**
     * Sets the socket this line reader operates on.
     *
     * \param resource $socket
     *      The socket this reader will use from now on.
     */
    public function setSocket($socket)
    {
        $this->_socket          = $socket;
        $this->_incomingData    = "";
        $this->_rcvQueue        = array();
        $this->_sndQueue        = array();
    }

    /**
     * Returns the socket associated with this reader.
     *
     * \retval resource
     *      Socket associated with this reader,
     *      as set during this object's creation
     *      or the latest call to setSocket().
     */
    public function getSocket()
    {
        return $this->_socket;
    }

    /**
     * Sets the End-Of-Line (EOL) style
     * used to process lines.
     *
     * \param opaque $eol
     *      The type of line-endings to accept.
     *      This may be one of Erebot_LineIO::EOL_WIN,
     *      Erebot_LineIO::EOL_OLD_MAC,
     *      Erebot_LineIO::EOL_NEW_MAC,
     *      Erebot_LineIO::EOL_UNIX or
     *      Erebot_LineIO::EOL_ANY.
     */
    public function setEOL($eol)
    {
        $eols = array(
            self::EOL_WIN,
            self::EOL_OLD_MAC,
            self::EOL_UNIX,
            self::EOL_ANY,
        );
        if (!in_array($eol, $eols))
            throw new Erebot_InvalidValueException('Invalid EOL mode');
        if ($eol === self::EOL_ANY)
            $eol = array("\r\n", "\r", "\n");
        else
            $eol = array($eol);

        $this->_eol     = $eol;
    }

    /**
     * Returns the End-Of-Line (EOL) style
     * currently used.
     *
     * \retval opaque
     *      EOL style currently in use.
     */
    public function getEOL()
    {
        return $this->_eol;
    }

    /**
     * Retrieves a single line of text from the incoming buffer
     * and puts it in the incoming FIFO.
     *
     * \retval TRUE
     *      Whether a line could be fetched from the buffer.
     *
     * \retval FALSE
     *      ... or not.
     *
     * \note
     *      Lines fetched by this method are always UTF-8 encoded.
     */
    protected function _getLine()
    {
        $pos = FALSE;
        foreach ($this->_eol as $eol) {
            $pos = strpos($this->_incomingData, $eol);
            if ($pos !== FALSE)
                break;
        }
        if ($pos === FALSE)
            return FALSE;

        $len    = strlen($eol);
        $line   = Erebot_Utils::toUTF8(substr($this->_incomingData, 0, $pos));
        $this->_incomingData    = substr($this->_incomingData, $pos + $len);
        $this->_rcvQueue[]      = $line;

        $logger = Plop::getInstance();
        $logger->debug(
            '%(line)s',
            array('line' => addcslashes($line, "\000..\037"))
        );
        return TRUE;
    }

    /**
     * Reads as many lines from the socket
     * as possible.
     *
     * \retval bool
     *      TRUE if lines were successfully read,
     *      FALSE is returned whenever EOF is reached
     *      or if this method has been called while
     *      the socket was still uninitialized..
     *
     * \note
     *      This method blocks until lines have
     *      been read of EOF is reached (whichever
     *      comes first).
     */
    public function read()
    {
        if ($this->_socket === NULL)
            return FALSE;

        if (feof($this->_socket))
            return FALSE;

        $received = fread($this->_socket, 4096);
        if ($received === FALSE)
            return FALSE;
        $this->_incomingData .= $received;

        // Workaround for issue #8.
        $metadata = stream_get_meta_data($this->_socket);
        if ($metadata['stream_type'] == 'tcp_socket/ssl' &&
            !feof($this->_socket)) {
            $blocking = (int) $metadata['blocked'];
            stream_set_blocking($this->_socket, 0);
            $received = fread($this->_socket, 4096);
            stream_set_blocking($this->_socket, $blocking);

            if ($received !== FALSE)
                $this->_incomingData .= $received;
        }

        // Read all messages currently in the input buffer.
        while ($this->_getLine());
        return TRUE;
    }

    /**
     * Returns a single line from the input buffer.
     *
     * \retval string
     *      A single line from the input buffer.
     *
     * \retval NULL
     *      The input buffer did not contain
     *      any line.
     */
    public function pop()
    {
        if (count($this->_rcvQueue))
            return array_shift($this->_rcvQueue);
        return NULL;
    }

    /**
     * Adds a given line to the outgoing FIFO.
     *
     * \param string $line
     *      The line of text to send.
     *
     * \throw Erebot_InvalidValueException
     *      Thrown if the $line contains invalid characters.
     */
    public function push($line)
    {
        if ($this->_socket === NULL) {
            throw new Erebot_IllegalActionException('Uninitialized socket');
        }

        if (strcspn($line, "\r\n") != strlen($line)) {
            throw new Erebot_InvalidValueException(
                'Line contains forbidden characters'
            );
        }
        $this->_sndQueue[] = $line;
    }

    /**
     * Writes a single line from the output buffer
     * to the socket.
     *
     * \retval int
     *      The number of bytes successfully
     *      written on the socket.
     *
     * \retval FALSE
     *      The connection was lost while trying
     *      to send the line or the output buffer
     *      was empty.
     */
    public function write()
    {
        if (!count($this->_sndQueue))
            return FALSE;

        $line   = array_shift($this->_sndQueue);
        $logger = Plop::getInstance();

        // Make sure we send the whole line,
        // with a trailing CR LF sequence.
        $eol    = $this->_eol[count($this->_eol) - 1];
        $line  .= $eol;
        $len    = strlen($line);
        for ($written = 0; $written < $len; $written += $fwrite) {
            $fwrite = @fwrite($this->_socket, substr($line, $written));
            if ($fwrite === FALSE)
                return FALSE;
        }
        $line = substr($line, 0, -strlen($eol));
        $logger->debug(
            '%(line)s',
            array('line' => addcslashes($line, "\000..\037"))
        );
        return $written;
    }

    /**
     * Indicates whether there are lines
     * in the input buffer waiting to be
     * read.
     *
     * \retval bool
     *      Whether there are lines in the
     *      input buffer awaiting reading
     *      (TRUE) or not (FALSE).
     */
    public function inReadQueue()
    {
        return count($this->_rcvQueue);
    }

    /**
     * Indicates whether there are lines
     * in the output buffer waiting to be
     * written.
     *
     * \retval bool
     *      Whether there are lines in the
     *      output buffer awaiting writing
     *      (TRUE) or not (FALSE).
     */
    public function inWriteQueue()
    {
        return count($this->_sndQueue);
    }
}

