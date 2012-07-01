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


    public function __construct($eol, $socket = NULL)
    {
        $this->setSocket($socket);
        $this->setEOL($eol);
    }

    public function setSocket($socket)
    {
        $this->_socket          = $socket;
        $this->_incomingData    = "";
        $this->_rcvQueue        = array();
        $this->_sndQueue        = array();
    }

    public function getSocket()
    {
        return $this->_socket;
    }

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

        $logging    = Plop::getInstance();
        $logger     = $logging->getLogger(
            __FILE__ . DIRECTORY_SEPARATOR . 'input'
        );
        $logger->debug("%s", addcslashes($line, "\000..\037"));
        return TRUE;
    }

    public function read()
    {
        if ($this->_socket === NULL)
            return FALSE;

        $received   = fread($this->_socket, 4096);
        if ($received === FALSE || feof($this->_socket))
            return FALSE;

        $this->_incomingData .= $received;
        // Read all messages currently in the input buffer.
        while ($this->_getLine());
        return TRUE;
    }

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

    public function write()
    {
        if (!count($this->_sndQueue))
            return FALSE;

        $line       = array_shift($this->_sndQueue);
        $logging    = Plop::getInstance();
        $logger     = $logging->getLogger(
            __FILE__ . DIRECTORY_SEPARATOR . 'output'
        );

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
        $logger->debug("%s", addcslashes($line, "\000..\037"));
        return $written;
    }

    public function inReadQueue()
    {
        return count($this->_rcvQueue);
    }

    public function inWriteQueue()
    {
        return count($this->_sndQueue);
    }
}

