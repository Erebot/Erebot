<?php

class       Erebot_Console
implements  Erebot_Interface_ReceivingConnection
{
    protected $_bot;
    protected $_socket;
    protected $_rcvQueue;
    protected $_incomingData;

    public function __construct(
        Erebot_Interface_Core   $bot,
                                $connector  = '/tmp/Erebot.sock',
                                $group      = NULL,
                                $perms      = 0777
    )
    {
        $this->_bot         = $bot;
        $this->_socket      = stream_socket_server("udg://".$connector, $errno, $errstr, STREAM_SERVER_BIND);
        if (!$this->_socket)
            throw new Exception("Could not create console (".$errstr.")");
        $this->_rcvQueue        = array();
        $this->_incomingData    = '';

        $logging    = Plop::getInstance();
        $logger     = $logging->getLogger(__FILE__);
        $logger->info($bot->gettext('Console started in "%s"'), $connector);

        register_shutdown_function(
            array(__CLASS__, '_cleanup_socket'),
            $connector
        );
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    public function connect()
    {
        $this->_bot->addConnection($this);
    }

    public function disconnect($quitMessage = NULL)
    {
        $this->_bot->removeConnection($this);
        if ($this->_socket !== NULL)
            stream_socket_shutdown($this->_socket, STREAM_SHUT_RDWR);
        $this->_socket = NULL;
    }

    public function isConnected() { return TRUE; }
    public function getSocket() { return $this->_socket; }

    public function emptyReadQueue() {
        return (count($this->_rcvQueue) == 0);
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
    protected function _getSingleLine()
    {
        $pos = strpos($this->_incomingData, "\r\n");
        if ($pos === FALSE)
            return FALSE;

        $line = Erebot_Utils::toUTF8(substr($this->_incomingData, 0, $pos));
        $this->_incomingData    = substr($this->_incomingData, $pos + 2);
        $this->_rcvQueue[]      = $line;

        $logging    = Plop::getInstance();
        $logger     = $logging->getLogger(
            __FILE__ . DIRECTORY_SEPARATOR . 'input'
        );
        $logger->debug("%s", addcslashes($line, "\000..\037"));
        return TRUE;
    }

    /// \copydoc Erebot_Interface_ReceivingConnection::processIncomingData()
    public function processIncomingData()
    {
        $received   = fread($this->_socket, 4096);
        if ($received === FALSE || feof($this->_socket)) {
            throw new Erebot_ConnectionFailureException('Disconnected');
        }

        $this->_incomingData .= $received;
        while ($this->_getSingleLine())
            ;   // Read messages.
    }

    public function processQueuedData()
    {
        if (!count($this->_rcvQueue))
            return;

        while (count($this->_rcvQueue))
            $this->_handleMessage(array_shift($this->_rcvQueue));
    }

    protected function _handleMessage($line)
    {
        foreach ($this->_bot->getConnections() as $connection)
            if ($connection instanceof Erebot_Interface_SendingConnection &&
                $connection != $this)
                $connection->pushLine($line);
    }

    public function getBot() { return $this->_bot; }
    public function getConfig($chan) { return NULL; }

    static public function _cleanup_socket($socket)
    {
        @unlink($socket);
    }
}

