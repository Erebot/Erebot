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
        return $cls->newInstanceArgs($args);
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
        $parts  = explode(' ', $msg);
        $source = array_shift($parts);

        // Ping message from the server.
        if (!strcasecmp($source, 'PING')) {
            $msg = implode(' ', $parts);
            if (substr($msg, 0, 1) == ':')
                $msg = substr($msg, 1);
            return $this->_connection->dispatch(
                $this->makeEvent('!Ping', $msg)
            );
        }

        $type   = array_shift($parts);
        if ($source == '' || !count($parts))
            return;

        if ($source[0] == ':')
            $source = substr($source, 1);

        $type = strtoupper($type);

        // ERROR usually happens when disconnecting from the server,
        // when using QUIT or getting KILLed, etc.
        if ($type == 'ERROR') {
            if (substr($parts[0], 0, 1) == ':')
                $parts[0] = substr($parts[0], 1);

            $msg = implode(' ', $parts);
            return $this->_connection->dispatch(
                $this->makeEvent('!Error', $source, $msg)
            );
        }

        $target = array_shift($parts);

        if (count($parts) && substr($parts[0], 0, 1) == ':')
            $parts[0] = substr($parts[0], 1);
        $msg = implode(' ', $parts);

        if (substr($target, 0, 1) == ':')
            $target = substr($target, 1);

        $method = '_handle'.$type;
        $exists = method_exists($this, $method);
        if ($exists)
            $res = $this->$method($source, $target, $msg, $parts);

        if (ctype_digit($type))
            return $this->_connection->dispatch(
                $this->makeEvent(
                    '!Raw',
                    intval($type, 10),
                    $source,
                    $target,
                    $msg
                )
            );

        if ($exists)
            return $res;

        /// @TODO: logging
        return FALSE;
    }

    protected function _handleINVITE($source, $target, $msg, $parts) {
        // :nick1!ident@host INVITE nick2 :#chan
        return $this->_connection->dispatch(
            $this->makeEvent('!Invite', $msg, $source, $target)
        );
    }

    protected function _handleJOIN($source, $target, $msg, $parts) {
        // :nick1!ident@host JOIN :#chan
        return $this->_connection->dispatch(
            $this->makeEvent('!Join', $target, $source)
        );
    }

    protected function _handleKICK($source, $target, $msg, $parts) {
        // :nick1!ident@host KICK #chan nick2 :Reason
        $pos    = strcspn($msg, " ");
        $nick   = substr($msg, 0, $pos);
        $msg    = substr($msg, $pos + 1);
        if (strlen($msg) && $msg[0] == ':')
            $msg = substr($msg, 1);

        return $this->_connection->dispatch(
            $this->makeEvent('!Kick', $target, $source, $nick, $msg)
        );
    }

    protected function _handleKILL($source, $target, $msg, $parts) {
        // :nick1!ident@host KILL nick2 :Reason
        return $this->_connection->dispatch(
            $this->makeEvent('!Kill', $target, $source, $msg)
        );
    }

    protected function _handleMODE($source, $target, $msg, $parts) {
        // :nick1!ident@host MODE <nick2/#chan> modes
        if (!$this->_connection->isChannel($target)) {
            return $this->_connection->dispatch(
                $this->makeEvent('!UserMode', $source, $target, $msg)
            );
        }

        $event = $this->makeEvent('!RawMode', $target, $source, $msg);
        $this->_connection->dispatch($event);
        if ($event->preventDefault(TRUE))
            return;

        $wrappedMessage = new Erebot_TextWrapper($msg);
        $modes  = $wrappedMessage[0];
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
                    $tnick  = $wrappedMessage[$k++];
                    $cls    = $priv[$mode][$modes[$i]];
                    $this->_connection->dispatch(
                        $this->makeEvent($cls, $target, $source, $tnick)
                    );
                    break;

                default:
/// @TODO fix this
/*
                    for ($j = 3; $j > 0; $j--) {
                        $pos = strpos(
                            $this->chanModes[$j-1],
                            $modes[$i]
                        );
                        if ($pos !== FALSE)
                            break;
                    }
                    switch ($j) {
                        case 3:
                            if ($mode == self::MODE_REMOVE)
                                break;
                        case 1:
                        case 2:
                            $remains[] = $wrappedMessage[$k++]
                            break;
                    }
*/
            }
        } // for each mode in $modes

        $remainingModes = str_replace(
            array('o', 'h', 'v', 'b', '+', '-'),
            '',
            $modes
        );
        if ($remainingModes != '') {
            if (count($remains))
                $remains = ' '.implode(' ', $remains);
            else $remains = '';

            for ($i = strlen($modes) - 1; $i >= 0; $i--) {

            }

# @TODO
#                    $event = new ErebotEvent($this, $source, $target,
#                        ErebotEvent::ON_MODE, $remainingModes.$remains);
#                    $this->dispatchEvent($event);
        }
    }

    protected function _handleNICK($source, $target, $msg, $parts) {
        // :oldnick!ident@host NICK newnick
        return $this->_connection->dispatch(
            $this->makeEvent('!Nick', $source, $target)
        );
    }

    protected function _handleNOTICE($source, $target, $msg, $parts) {
        // :nick1!ident@host NOTICE <nick2/#chan> :Message
        if (($len = strlen($msg)) > 1 &&
            $msg[$len-1] == "\001" &&
            $msg[0] == "\001") {

            // Remove the markers.
            $msg    = (string) substr($msg, 1, -1);
            // Unquote the message.
            $msg    = self::_ctcpUnquote($msg);
            // Extract the tag from the rest of the message.
            $pos    = strcspn($msg, " ");
            $ctcp   = substr($msg, 0, $pos);
            $msg    = (string) substr($msg, $pos + 1);

            if ($this->_connection->isChannel($target))
                return $this->_connection->dispatch(
                    $this->makeEvent(
                        '!ChanCtcpReply',
                        $target, $source, $ctcp, $msg
                    )
                );

            return $this->_connection->dispatch(
                $this->makeEvent(
                    '!PrivateCtcpReply',
                    $source, $ctcp, $msg
                )
            );
        }

        if ($this->_connection->isChannel($target))
            return $this->_connection->dispatch(
                $this->makeEvent('!ChanNotice', $target, $source, $msg)
            );

        return $this->_connection->dispatch(
                $this->makeEvent('!PrivateNotice', $source, $msg)
            );
    }

    protected function _handlePART($source, $target, $msg, $parts) {
        // :nick1!ident@host PART #chan :Reason
        return $this->_connection->dispatch(
            $this->makeEvent('!Part', $target, $source, $msg)
        );
    }

    protected function _handlePONG($source, $target, $msg, $parts) {
        // :origin PONG origin target
        return $this->_connection->dispatch(
            $this->makeEvent('!Pong', $source, $msg)
        );
    }

    protected function _handlePRIVMSG($source, $target, $msg, $parts) {
        // :nick1!ident@host PRIVMSG <nick2/#chan> :Msg
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
                if ($this->_connection->isChannel($target))
                    return $this->_connection->dispatch(
                        $this->makeEvent(
                            '!ChanAction',
                            $target, $source, $msg
                        )
                    );

                return $this->_connection->dispatch(
                    $this->makeEvent(
                        '!PrivateAction',
                        $source, $msg
                    )
                );
            }

            if ($this->_connection->isChannel($target))
                return $this->_connection->dispatch(
                    $this->makeEvent(
                        '!ChanCtcp',
                        $target, $source, $ctcp, $msg
                    )
                );

            return $this->_connection->dispatch(
                $this->makeEvent(
                    '!PrivateCtcp',
                    $source, $ctcp, $msg
                )
            );
        }

        if ($this->_connection->isChannel($target))
            return $this->_connection->dispatch(
                $this->makeEvent('!ChanText', $target, $source, $msg)
            );

        return $this->_connection->dispatch(
            $this->makeEvent('!PrivateText', $source, $msg)
        );
    }

    protected function _handleTOPIC($source, $target, $msg, $parts) {
        // :nick1!ident@host TOPIC #chan :New topic
        return $this->_connection->dispatch(
            $this->makeEvent('!Topic', $target, $source, $msg)
        );
    }

    protected function _handle255($source, $target, $msg, $parts)
    {
        // Erebot_Interface_RawProfile_RFC1459::RPL_LUSERME
        /* We can't rely on RPL_WELCOME because we may need
         * to detect the server's capabilities first.
         * So, we delay detection of the connection for as
         * long as we can (while keeping portability). */
        if (!$this->_connection->isConnected())
            return $this->_connection->dispatch($this->makeEvent('!Connect'));
    }

    protected function _handle600($source, $target, $msg, $parts)
    {
        // Erebot_Interface_RawProfile_WATCH::RPL_LOGON
        return $this->_watchList('!Notify', $parts);
    }

    protected function _handle601($source, $target, $msg, $parts)
    {
        // Erebot_Interface_RawProfile_WATCH::RPL_LOGOFF
        return $this->_watchList('!UnNotify', $parts);
    }

    protected function _handle604($source, $target, $msg, $parts)
    {
        // Erebot_Interface_RawProfile_WATCH::RPL_NOWON
        return $this->_watchList('!Notify', $parts);
    }

    protected function _handle605($source, $target, $msg, $parts)
    {
        // Erebot_Interface_RawProfile_WATCH::RPL_NOWOFF
        return $this->_watchList('!UnNotify', $parts);
    }

    protected function _watchList($event, $parts)
    {
        $nick       = array_shift($parts);
        $ident      = array_shift($parts);
        $host       = array_shift($parts);
        $timestamp  = intval(array_shift($parts), 10);
        $timestamp  = new DateTime('@'.$timestamp);
        $text       = implode(' ', $parts);
        if (substr($text, 0, 1) == ':')
            $text = substr($text, 1);

        return $this->_connection->dispatch(
            $this->makeEvent(
                $event, $nick,
                ($ident == '*' ? NULL : $ident),
                ($host == '*' ? NULL : $host),
                $timestamp, $text
            )
        );
    }
}

