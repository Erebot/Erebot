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

class       Erebot_IrcParser
implements  Erebot_Interface_IrcParser
{
    /// Mappings from (lowercase) interface names to actual classes.
    protected $_eventsMapping;

    protected $_connection;

    public function __construct(Erebot_Interface_Connection $connection)
    {
        $this->_connection = $connection;
        $this->_eventsMapping = array();
    }

    /// \copydoc Erebot_Interface_EventFactory::makeEvent()
    public function makeEvent($iface /* , ... */)
    {
        $args = func_get_args();

        // Shortcuts.
        $iface = str_replace('!', 'Erebot_Interface_Event_', $iface);
        $iface = strtolower($iface);

        if (!isset($this->_eventsMapping[$iface]))
            throw new Erebot_NotFoundException('No such declared interface');

        // Replace the first argument (interface) with a reference
        // to the connection, since all events require it anyway.
        // This simplifies calls to this method a bit.
        $args[0]    = $this->_connection;
        $cls        = new ReflectionClass($this->_eventsMapping[$iface]);
        $instance   = $cls->newInstanceArgs($args);
        return $instance;
    }

    static protected function _ctcpUnquote($msg)
    {
        // See http://www.irchelp.org/irchelp/rfc/ctcpspec.html
        // CTCP-level unquoting
        $quoting = array(
            "\\a"   => "\001",
            "\\\\"  => "\\",
            "\\"    => "",  // Ignore quoting character
                            // for invalid sequences.
        );
        $msg = strtr($msg, $quoting);

        // Low-level unquoting
        $quoting = array(
            "\0200"     => "\000",
            "\020n"     => "\n",
            "\020r"     => "\r",
            "\020\020"  => "\020",
            "\020"      => "",  // Ignore quoting character
                                // for invalid sequences.
        );
        $msg = strtr($msg, $quoting);
        return $msg;
    }

    /**
     * \copydoc
     *      Erebot_Interface_EventFactory::getEventClass($iface)
     *
     * \note
     *      As a special shortcut, you may use an exclamation
     *      point ("!") in the interface name, which will be
     *      replaced by the text "Erebot_Interface_Event_".
     *      Hence, to retrieve the class used to create events
     *      with the "Erebot_Interface_Event_Op" interface,
     *      it is enough to simply pass "!Op" as the value
     *      for $iface.
     */
    public function getEventClass($iface)
    {
        // Shortcuts.
        $iface = str_replace('!', 'Erebot_Interface_Event_', $iface);
        $iface = strtolower($iface);

        return isset($this->_eventsMapping[$iface])
            ? $this->_eventsMapping[$iface]
            : NULL;
    }

    public function getEventClasses()
    {
        return $this->_eventsMapping;
    }

    public function setEventClasses($events)
    {
        foreach ($events as $iface => $cls) {
            $this->setEventClass($iface, $cls);
        }
    }

    /**
     * \copydoc
     *      Erebot_Interface_EventFactory::setEventClass($iface, $cls)
     *
     * \note
     *      As a special shortcut, you may use an exclamation
     *      point ("!") in the interface name, which will be
     *      replaced by the text "Erebot_Interface_Event_".
     *      Hence, to change the class used to create events
     *      with the "Erebot_Interface_Event_Op" interface,
     *      it is enough to simply pass "!Op" as the value
     *      for $iface. The $cls is always left unaffected.
     */
    public function setEventClass($iface, $cls)
    {
        // Shortcuts.
        $iface = str_replace('!', 'Erebot_Interface_Event_', $iface);
        if (!interface_exists($iface)) {
            throw new Erebot_InvalidValueException(
                'The given interface ('.$iface.') does not exist'
            );
        }

        $iface = strtolower($iface);
        $reflector = new ReflectionClass($cls);
        if (!$reflector->implementsInterface($iface)) {
            throw new Erebot_InvalidValueException(
                'The given class does not implement that interface'
            );
        }
        $this->_eventsMapping[$iface] = $cls;
    }

    /**
     * Handles a single IRC message.
     *
     * \param string $msg
     *      The message to process.
     *
     * \note
     *      Events/raws are dispatched as necessary
     *      by this method.
     */
    public function parseLine($msg)
    {
        if (!strncmp($msg, ':', 1)) {
            $pos    = strcspn($msg, ' ');
            $source = (string) substr($msg, 1, $pos - 1);
            $msg    = new Erebot_IrcTextWrapper((string) substr($msg, $pos + 1));
        }
        else {
            /// @FIXME the RFCs say we should assume origin = server instead.
            $source = '';
            $msg    = new Erebot_IrcTextWrapper($msg);
        }

        $type   = $msg[0];
        $type   = strtoupper($type);
        unset($msg[0]);

        $method = '_handle'.$type;
        $exists = method_exists($this, $method);
        // We need a backup for numeric events
        // as the regular method may modify the message.
        $backup = clone $msg;

        if ($exists)
            $res = $this->$method($source, $msg);

        if (ctype_digit($type)) {
            // For raw events, the first token is always the target.
            $target = $backup[0];
            unset($backup[0]);
            return $this->_connection->dispatch(
                $this->makeEvent(
                    '!Raw',
                    intval($type, 10),
                    $source,
                    $target,
                    $backup
                )
            );
        }

        if ($exists)
            return $res;

        /// @TODO: logging
        return FALSE;
    }

    protected function _noticeOrPrivmsg($source, $msg, $mapping)
    {
        // :nick1!ident@host NOTICE <nick2/#chan> :Message
        // :nick1!ident@host PRIVMSG <nick2/#chan> :Message
        $target = $msg[0];
        $msg    = $msg[1];
        $isChan = (int) $this->_connection->isChannel($target);
        if (($len = strlen($msg)) > 1 &&
            $msg[$len-1] == "\x01" &&
            $msg[0] == "\x01") {

            // Remove the markers.
            $msg    = (string) substr($msg, 1, -1);
            // Unquote the message.
            $msg    = self::_ctcpUnquote($msg);
            // Extract the tag from the rest of the message.
            $pos    = strcspn($msg, " ");
            $ctcp   = substr($msg, 0, $pos);
            $msg    = (string) substr($msg, $pos + 1);

            if ($ctcp == "ACTION") {
                if ($isChan)
                    return $this->_connection->dispatch(
                        $this->makeEvent(
                            $mapping['action'][$isChan],
                            $target, $source, $msg
                        )
                    );

                return $this->_connection->dispatch(
                    $this->makeEvent(
                        $mapping['action'][$isChan],
                        $source, $msg
                    )
                );
            }

            if ($isChan)
                return $this->_connection->dispatch(
                    $this->makeEvent(
                        $mapping['ctcp'][$isChan],
                        $target, $source, $ctcp, $msg
                    )
                );

            return $this->_connection->dispatch(
                $this->makeEvent(
                    $mapping['ctcp'][$isChan],
                    $source, $ctcp, $msg
                )
            );
        }

        if ($isChan)
            return $this->_connection->dispatch(
                $this->makeEvent(
                    $mapping['normal'][$isChan],
                    $target,
                    $source,
                    $msg
                )
            );

        return $this->_connection->dispatch(
            $this->makeEvent($mapping['normal'][$isChan], $source, $msg)
        );
    }

    protected function _handleINVITE($source, $msg) {
        // :nick1!ident@host INVITE nick2 :#chan
        return $this->_connection->dispatch(
            $this->makeEvent('!Invite', $msg[1], $source, $msg[0])
        );
    }

    protected function _handleJOIN($source, $msg) {
        // :nick1!ident@host JOIN :#chan
        return $this->_connection->dispatch(
            $this->makeEvent('!Join', $msg[0], $source)
        );
    }

    protected function _handleKICK($source, $msg) {
        // :nick1!ident@host KICK #chan nick2 :Reason
        return $this->_connection->dispatch(
            $this->makeEvent('!Kick', $msg[0], $source, $msg[1], $msg[2])
        );
    }

    protected function _handleMODE($source, $msg) {
        // :nick1!ident@host MODE <nick2/#chan> <modes> [args]
        $target = $msg[0];
        unset($msg[0]);
        if (!$this->_connection->isChannel($target)) {
            return $this->_connection->dispatch(
                $this->makeEvent('!UserMode', $source, $target, $msg)
            );
        }

        $event = $this->makeEvent('!RawMode', $target, $source, $msg);
        $this->_connection->dispatch($event);
        if ($event->preventDefault(TRUE))
            return;

        $modes  = $msg[0];
        unset($msg[0]);
        $len    = strlen($modes);
        $mode   = 'add';
        $k      = 1;

        $priv     = array(
            'add' =>
                array(
                    'o' => '!Op',
                    'h' => '!Halfop',
                    'v' => '!Voice',
                    'a' => '!Protect',
                    'q' => '!Owner',
                    'b' => '!Ban',
                    'e' => '!Except',
                ),
            'remove' =>
                array(
                    'o' => '!DeOp',
                    'h' => '!DeHalfop',
                    'v' => '!DeVoice',
                    'a' => '!DeProtect',
                    'q' => '!DeOwner',
                    'b' => '!UnBan',
                    'e' => '!UnExcept',
                ),
        );

        $remains = array();
        for ($i = 0; $i < $len; $i++) {
            switch ($modes[$i]) {
                case '+':
                    $mode = 'add';
                    break;
                case '-':
                    $mode = 'remove';
                    break;

                case 'o':
                case 'v':
                case 'h':
                case 'a':
                case 'q':
                case 'b':
                case 'e':
                    $tnick  = $msg[$k++];
                    $cls    = $priv[$mode][$modes[$i]];
                    $this->_connection->dispatch(
                        $this->makeEvent($cls, $target, $source, $tnick)
                    );
                    break;

                default:
            }
        } // for each mode in $modes
        /// @TODO: handle remaining modes as well
    }

    protected function _handleNICK($source, $msg) {
        // :oldnick!ident@host NICK newnick
        return $this->_connection->dispatch(
            $this->makeEvent('!Nick', $source, $msg[0])
        );
    }

    protected function _handleNOTICE($source, $msg) {
        // :nick1!ident@host NOTICE <nick2/#chan> :Message
        $mapping = array(
            'action'    => array('!PrivateCtcpReply', '!ChanCtcpReply'),
            'ctcp'      => array('!PrivateCtcpReply', '!ChanCtcpReply'),
            'normal'    => array('!PrivateNotice', '!ChanNotice'),
        );
        return $this->_noticeOrPrivmsg($source, $msg, $mapping);
    }

    protected function _handlePART($source, $msg) {
        // :nick1!ident@host PART #chan :Reason
        return $this->_connection->dispatch(
            $this->makeEvent('!Part', $msg[0], $source, $msg[1])
        );
    }

    protected function _handlePING($source, $msg) {
        // PING origin
        return $this->_connection->dispatch(
            $this->makeEvent('!Ping', $msg)
        );
    }

    protected function _handlePONG($source, $msg) {
        // :origin PONG origin target
        return $this->_connection->dispatch(
            $this->makeEvent('!Pong', $source, $msg[1])
        );
    }

    protected function _handlePRIVMSG($source, $msg) {
        // :nick1!ident@host PRIVMSG <nick2/#chan> :Message
        $mapping = array(
            'action'    => array('!PrivateAction', '!ChanAction'),
            'ctcp'      => array('!PrivateCtcp', '!ChanCtcp'),
            'normal'    => array('!PrivateText', '!ChanText'),
        );
        return $this->_noticeOrPrivmsg($source, $msg, $mapping);
    }

    protected function _handleQUIT($source, $msg) {
        // :nick1!ident@host QUIT :Reason
        return $this->_connection->dispatch(
            $this->makeEvent('!Quit', $source, $msg[0])
        );
    }

    protected function _handleTOPIC($source, $msg) {
        // :nick1!ident@host TOPIC #chan :New topic
        return $this->_connection->dispatch(
            $this->makeEvent('!Topic', $msg[0], $source, $msg[1])
        );
    }

    protected function _handle255($source, $msg)
    {
        // Erebot_Interface_RawProfile_RFC1459::RPL_LUSERME
        /* We can't rely on RPL_WELCOME because we may need
         * to detect the server's capabilities first.
         * So, we delay detection of the connection for as
         * long as we can (while retaining portability). */
        if (!$this->_connection->isConnected())
            return $this->_connection->dispatch($this->makeEvent('!Connect'));
    }

    protected function _handle600($source, $msg)
    {
        // Erebot_Interface_RawProfile_WATCH::RPL_LOGON
        return $this->_watchList('!Notify', $msg);
    }

    protected function _handle601($source, $msg)
    {
        // Erebot_Interface_RawProfile_WATCH::RPL_LOGOFF
        return $this->_watchList('!UnNotify', $msg);
    }

    protected function _handle604($source, $msg)
    {
        // Erebot_Interface_RawProfile_WATCH::RPL_NOWON
        return $this->_watchList('!Notify', $msg);
    }

    protected function _handle605($source, $msg)
    {
        // Erebot_Interface_RawProfile_WATCH::RPL_NOWOFF
        return $this->_watchList('!UnNotify', $msg);
    }

    protected function _watchList($event, $msg)
    {
        // <bot> <nick> <ident> <host> <timestamp> :<msg>
        unset($msg[0]);
        $nick       = $msg[0];
        $ident      = $msg[1];
        $host       = $msg[2];
        $timestamp  = intval($msg[3], 10);
        $timestamp  = new DateTime('@'.$timestamp);
        $text       = $msg[4];

        return $this->_connection->dispatch(
            $this->makeEvent(
                $event,
                $nick,
                ($ident == '*' ? NULL : $ident),
                ($host == '*' ? NULL : $host),
                $timestamp,
                $text
            )
        );
    }
}

