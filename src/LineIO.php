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

namespace Erebot;

/**
 * \brief
 *      A class that provides a line-by-line reader.
 *
 * Using this class, it is possible to read messages
 * one line at a time simply by specifying the
 * End-Of-Line (EOL) style in use by the protocol.
 */
class LineIO
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
    const EOL_ANY       = null;

    /// Line-ending mode in use.
    protected $eol;

    /// The underlying socket, represented as a stream.
    protected $socket;

    /// A FIFO queue for outgoing messages.
    protected $sndQueue;

    /// A FIFO queue for incoming messages.
    protected $rcvQueue;

    /// A raw buffer for incoming data.
    protected $incomingData;

    /**
     * Constructs a new line-by-line reader.
     *
     * \param opaque $eol
     *      The type of line-endings to accept.
     *      This may be one of Erebot::LineIO::EOL_WIN,
     *      Erebot::LineIO::EOL_OLD_MAC,
     *      Erebot::LineIO::EOL_NEW_MAC,
     *      Erebot::LineIO::EOL_UNIX or
     *      Erebot::LineIO::EOL_ANY.
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
    public function __construct($eol, $socket = null)
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
        $this->socket          = $socket;
        $this->incomingData    = "";
        $this->rcvQueue        = array();
        $this->sndQueue        = array();
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
        return $this->socket;
    }

    /**
     * Sets the End-Of-Line (EOL) style
     * used to process lines.
     *
     * \param opaque $eol
     *      The type of line-endings to accept.
     *      This may be one of Erebot::LineIO::EOL_WIN,
     *      Erebot::LineIO::EOL_OLD_MAC,
     *      Erebot::LineIO::EOL_NEW_MAC,
     *      Erebot::LineIO::EOL_UNIX or
     *      Erebot::LineIO::EOL_ANY.
     */
    public function setEOL($eol)
    {
        $eols = array(
            self::EOL_WIN,
            self::EOL_OLD_MAC,
            self::EOL_UNIX,
            self::EOL_ANY,
        );
        if (!in_array($eol, $eols)) {
            throw new \Erebot\InvalidValueException('Invalid EOL mode');
        }
        if ($eol === self::EOL_ANY) {
            $eol = array("\r\n", "\r", "\n");
        } else {
            $eol = array($eol);
        }

        $this->eol     = $eol;
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
        return $this->eol;
    }

    /**
     * Retrieves a single line of text from the incoming buffer
     * and puts it in the incoming FIFO.
     *
     * \retval true
     *      Whether a line could be fetched from the buffer.
     *
     * \retval false
     *      ... or not.
     *
     * \note
     *      Lines fetched by this method are always UTF-8 encoded.
     */
    protected function getLine()
    {
        $pos = false;
        foreach ($this->eol as $eol) {
            $pos = strpos($this->incomingData, $eol);
            if ($pos !== false) {
                break;
            }
        }
        if ($pos === false) {
            return false;
        }

        $len    = strlen($eol);
        $line   = \Erebot\Utils::toUTF8(substr($this->incomingData, 0, $pos));
        $this->incomingData    = substr($this->incomingData, $pos + $len);
        $this->rcvQueue[]      = $line;

        $logger = \Plop::getInstance();
        $logger->debug(
            '%(line)s',
            array('line' => addcslashes($line, "\000..\037"))
        );
        return true;
    }

    /**
     * Reads as many lines from the socket
     * as possible.
     *
     * \retval bool
     *      \b true if lines were successfully read,
     *      \b false is returned whenever EOF is reached
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
        if ($this->socket === null) {
            return false;
        }

        if (feof($this->socket)) {
            return false;
        }

        $received = fread($this->socket, 4096);
        if ($received === false) {
            return false;
        }
        $this->incomingData .= $received;

        // Workaround for issue #8.
        $metadata = stream_get_meta_data($this->socket);
        if ($metadata['stream_type'] == 'tcp_socket/ssl' && !feof($this->socket)) {
            $blocking = (int) $metadata['blocked'];
            stream_set_blocking($this->socket, 0);
            $received = fread($this->socket, 4096);
            stream_set_blocking($this->socket, $blocking);

            if ($received !== false) {
                $this->incomingData .= $received;
            }
        }

        // Read all messages currently in the input buffer.
        while ($this->getLine()) {
            ; // Nothing more to do.
        }
        return true;
    }

    /**
     * Returns a single line from the input buffer.
     *
     * \retval string
     *      A single line from the input buffer.
     *
     * \retval null
     *      The input buffer did not contain
     *      any line.
     */
    public function pop()
    {
        if (count($this->rcvQueue)) {
            return array_shift($this->rcvQueue);
        }
        return null;
    }

    /**
     * Adds a given line to the outgoing FIFO.
     *
     * \param string $line
     *      The line of text to send.
     *
     * \throw Erebot::InvalidValueException
     *      The $line contains invalid characters.
     */
    public function push($line)
    {
        if ($this->socket === null) {
            throw new \Erebot\IllegalActionException('Uninitialized socket');
        }

        if (strcspn($line, "\r\n") != strlen($line)) {
            throw new \Erebot\InvalidValueException(
                'Line contains forbidden characters'
            );
        }
        $this->sndQueue[] = $line;
    }

    /**
     * Writes a single line from the output buffer
     * to the socket.
     *
     * \retval int
     *      The number of bytes successfully
     *      written on the socket.
     *
     * \retval false
     *      The connection was lost while trying
     *      to send the line or the output buffer
     *      was empty.
     */
    public function write()
    {
        if (!count($this->sndQueue)) {
            return false;
        }

        $line   = array_shift($this->sndQueue);
        $logger = \Plop::getInstance();

        // Make sure we send the whole line,
        // with a trailing CR LF sequence.
        $eol    = $this->eol[count($this->eol) - 1];
        $line  .= $eol;
        $len    = strlen($line);
        for ($written = 0; $written < $len; $written += $fwrite) {
            $fwrite = @fwrite($this->socket, substr($line, $written));
            if ($fwrite === false) {
                return false;
            }
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
     *      (\b true) or not (\b false).
     */
    public function inReadQueue()
    {
        return count($this->rcvQueue);
    }

    /**
     * Indicates whether there are lines
     * in the output buffer waiting to be
     * written.
     *
     * \retval bool
     *      Whether there are lines in the
     *      output buffer awaiting writing
     *      (\b true) or not (\b false).
     */
    public function inWriteQueue()
    {
        return count($this->sndQueue);
    }
}
