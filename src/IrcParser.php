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
 *      A class that can parse IRC messages
 *      and produce events to match the commands
 *      in those messages.
 */
class IrcParser implements \Erebot\Interfaces\IrcParser
{
    /// Do not strip anything from the text.
    const STRIP_NONE        = 0x00;
    /// Strip (mIRC/pIRCh) colors from the text.
    const STRIP_COLORS      = 0x01;
    /// Strip the bold attribute from the text.
    const STRIP_BOLD        = 0x02;
    /// Strip the underline attribute from the text.
    const STRIP_UNDERLINE   = 0x04;
    /// Strip the reverse attribute from the text.
    const STRIP_REVERSE     = 0x08;
    /// Strip the reset control character from the text.
    const STRIP_RESET       = 0x10;
    /// Strip extended colors from the text.
    const STRIP_EXT_COLORS  = 0x20;
    /// Strip all forms of styles from the text.
    const STRIP_ALL         = 0xFF;


    /// Mappings from (lowercase) interface names to actual classes.
    protected $_eventsMapping;

    /// IRC connection that will send us some messages to parse.
    protected $_connection;


    /**
     * Constructor.
     *
     * \param Erebot::Interfaces::Connection $connection
     *      The connection associated with this parser.
     *      Every event created by this parser will
     *      reference this connection.
     *
     * \warning
     *      Do not ask this parser to parse messages coming
     *      from a different connection that the one it was
     *      constructed with or the results will be
     *      unpredictable.
     */
    public function __construct(\Erebot\Interfaces\Connection $connection)
    {
        $this->_connection = $connection;
        $this->_eventsMapping = array();
    }

    /**
     * Strips IRC styles from a text.
     *
     * \param string $text
     *      The text from which styles must be stripped.
     *
     * \param int $strip
     *      A bitwise OR of the codes of the styles we want to strip.
     *      The default is to strip all forms of styles from the text.
     *      See also the Erebot::Utils::STRIP_* constants.
     *
     * \retval string
     *      The text with all the styles specified in $strip stripped.
     */
    static public function stripCodes($text, $strip = self::STRIP_ALL)
    {
        if (!is_int($strip))
            throw new \Erebot\InvalidValueException("Invalid stripping flags");

        if ($strip & self::STRIP_BOLD)
            $text = str_replace("\002", '', $text);

        if ($strip & self::STRIP_COLORS)
            $text = preg_replace(
                "/\003(?:[0-9]{1,2}(?:,[0-9]{1,2})?)?/",
                '', $text
            );

        /// @TODO strip extended colors.

        if ($strip & self::STRIP_RESET)
            $text = str_replace("\017", '', $text);

        if ($strip & self::STRIP_REVERSE)
            $text = str_replace("\026", '', $text);

        if ($strip & self::STRIP_UNDERLINE)
            $text = str_replace("\037", '', $text);

        return $text;
    }

    /**
     * \copydoc Erebot::Interfaces::IrcParser::makeEvent()
     *
     * \note
     *      This method can also use the same shortcuts as
     *      Erebot::IrcParser::getEventClass().
     *
     * \note
     *      The name of the interface to use is case-insensitive.
     */
    public function makeEvent($iface /* , ... */)
    {
        $args = func_get_args();

        // Shortcuts.
        $iface = str_replace('!', '\\Erebot\\Interfaces\\Event\\', $iface);
        $iface = strtolower($iface);

        if (!isset($this->_eventsMapping[$iface]))
            throw new \Erebot\NotFoundException('No such declared interface');

        // Replace the first argument (interface) with a reference
        // to the connection, since all events require it anyway.
        // This simplifies calls to this method a bit.
        $args[0]    = $this->_connection;
        $cls        = new ReflectionClass($this->_eventsMapping[$iface]);
        $instance   = $cls->newInstanceArgs($args);
        return $instance;
    }

    /**
     * Unquotes a CTCP message.
     *
     * \param string $msg
     *      Some CTCP message to unquote.
     *
     * \retval string
     *      The message, with CTCP quoting removed.
     *
     * \see
     *      http://www.irchelp.org/irchelp/rfc/ctcpspec.html
     */
    static protected function ctcpUnquote($msg)
    {
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
     * \copydoc Erebot::Interfaces::IrcParser::getEventClass()
     *
     * \note
     *      As a special shortcut, you may use an exclamation
     *      point ("!") in the interface name, which will be
     *      replaced by the text "\\Erebot\\Interfaces\\Event\\".
     *      Hence, to retrieve the class used to create events
     *      with the "Erebot::Interface::Event::Op" interface,
     *      it is enough to simply pass "!Op" as the value
     *      for $iface.
     */
    public function getEventClass($iface)
    {
        // Shortcuts.
        $iface = str_replace('!', '\\Erebot\\Interfaces\\Event\\', $iface);
        $iface = strtolower($iface);

        return isset($this->_eventsMapping[$iface])
            ? $this->_eventsMapping[$iface]
            : NULL;
    }

    /**
     * \copydoc Erebot::Interfaces::IrcParser::getEventClasses()
     *
     * \note
     *      The interfaces' name will be returned in lowercase,
     *      while the classes' name is returned using the same
     *      spelling as when it was set.
     */
    public function getEventClasses()
    {
        return $this->_eventsMapping;
    }

    /**
     * \copydoc Erebot::Interface::IrcParser::setEventClasses()
     *
     * \note
     *      As a special shortcut, you may use an exclamation
     *      point ("!") in any interface name, which will be
     *      replaced by the text "\\Erebot\\Interfaces\\Event\\".
     */
    public function setEventClasses($events)
    {
        foreach ($events as $iface => $cls) {
            $this->setEventClass($iface, $cls);
        }
    }

    /**
     * \copydoc Erebot::Interfaces::IrcParser::setEventClass()
     *
     * \note
     *      As a special shortcut, you may use an exclamation
     *      point ("!") in the interface name, which will be
     *      replaced by the text "\\Erebot\\Interfaces\\Event\\".
     *      Hence, to change the class used to create events
     *      with the "Erebot::Interfaces::Event::Op" interface,
     *      it is enough to simply pass "!Op" as the value
     *      for $iface. The $cls is always left unaffected.
     */
    public function setEventClass($iface, $cls)
    {
        // Shortcuts.
        $iface = str_replace('!', '\\Erebot\\Interfaces\\Event\\', $iface);
        if (!interface_exists($iface)) {
            throw new \Erebot\InvalidValueException(
                'The given interface ('.$iface.') does not exist'
            );
        }

        $iface = strtolower($iface);
        $reflector = new ReflectionClass($cls);
        if (!$reflector->implementsInterface($iface)) {
            throw new \Erebot\InvalidValueException(
                'The given class does not implement that interface'
            );
        }
        $this->_eventsMapping[$iface] = $cls;
    }

    /**
     * \copydoc Erebot::Interfaces::IrcParser::parseLine()
     *
     * \warning
     *      Do not ask this parser to parse messages coming
     *      from a different connection that the one it was
     *      constructed with or the results will be
     *      unpredictable.
     */
    public function parseLine($msg)
    {
        if (!strncmp($msg, ':', 1)) {
            $pos    = strcspn($msg, ' ');
            $origin = (string) substr($msg, 1, $pos - 1);
            $msg    = new \Erebot\IrcTextWrapper((string) substr($msg, $pos + 1));
        }
        else {
            /// @FIXME the RFCs say we should assume origin = server instead.
            $origin = '';
            $msg    = new \Erebot\IrcTextWrapper($msg);
        }

        $type   = $msg[0];
        $type   = strtoupper($type);
        unset($msg[0]);

        $method = '_handle'.$type;
        $exists = method_exists($this, $method);
        // We need a backup for numeric events
        // as the method may alter the message.
        $backup = clone $msg;

        if ($exists)
            $res = $this->$method($origin, $msg);

        if (ctype_digit($type)) {
            // For numeric events, the first token is always the target.
            $target = $backup[0];
            unset($backup[0]);
            return $this->_connection->dispatch(
                $this->makeEvent(
                    '!Numeric',
                    intval($type, 10),
                    $origin,
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

    /**
     * Process a NOTICE or PRIVMSG message.
     *
     * \param string $origin
     *      Origin of the message to process.
     *
     * \param Erebot::Interfaces::IrcTextWrapper $msg
     *      The message to process, wrapped in
     *      a special object that makes it easier
     *      to analyze each token separately.
     *
     * \param array $mapping
     *      A mapping of types to a list of event names.
     *      Each list should contain two items. The first
     *      one if the name of the event to use when the
     *      message is targeted at an IRC user. The second
     *      one is used for messages targeted at IRC channels.
     *      The following (case-sensitive) types must appear
     *      in the mapping: 'ctcp' (for CTCP messages),
     *      'action' (for the special ACTION CTCP message)
     *      and 'normal' for regular messages.
     */
    protected function _noticeOrPrivmsg($origin, $msg, $mapping)
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
            $msg    = self::ctcpUnquote($msg);
            // Extract the tag from the rest of the message.
            $pos    = strcspn($msg, " ");
            $ctcp   = substr($msg, 0, $pos);
            $msg    = (string) substr($msg, $pos + 1);

            if ($ctcp == "ACTION") {
                if ($isChan)
                    return $this->_connection->dispatch(
                        $this->makeEvent(
                            $mapping['action'][$isChan],
                            $target, $origin, $msg
                        )
                    );

                return $this->_connection->dispatch(
                    $this->makeEvent(
                        $mapping['action'][$isChan],
                        $origin, $msg
                    )
                );
            }

            if ($isChan)
                return $this->_connection->dispatch(
                    $this->makeEvent(
                        $mapping['ctcp'][$isChan],
                        $target, $origin, $ctcp, $msg
                    )
                );

            return $this->_connection->dispatch(
                $this->makeEvent(
                    $mapping['ctcp'][$isChan],
                    $origin, $ctcp, $msg
                )
            );
        }

        if ($isChan)
            return $this->_connection->dispatch(
                $this->makeEvent(
                    $mapping['normal'][$isChan],
                    $target,
                    $origin,
                    $msg
                )
            );

        return $this->_connection->dispatch(
            $this->makeEvent($mapping['normal'][$isChan], $origin, $msg)
        );
    }

    /**
     * Processes a message of type INVITE.
     *
     * \param string $origin
     *      Origin of the message to process.
     *
     * \param Erebot::Interfaces::IrcTextWrapper $msg
     *      The message to process, wrapped in
     *      a special object that makes it easier
     *      to analyze each token separately.
     */
    protected function _handleINVITE($origin, $msg) {
        // :nick1!ident@host INVITE nick2 :#chan
        return $this->_connection->dispatch(
            $this->makeEvent('!Invite', $msg[1], $origin, $msg[0])
        );
    }

    /**
     * Processes a message of type JOIN.
     *
     * \param string $origin
     *      Origin of the message to process.
     *
     * \param Erebot::Interfaces::IrcTextWrapper $msg
     *      The message to process, wrapped in
     *      a special object that makes it easier
     *      to analyze each token separately.
     */
    protected function _handleJOIN($origin, $msg) {
        // :nick1!ident@host JOIN :#chan
        return $this->_connection->dispatch(
            $this->makeEvent('!Join', $msg[0], $origin)
        );
    }

    /**
     * Processes a message of type KICK.
     *
     * \param string $origin
     *      Origin of the message to process.
     *
     * \param Erebot::Interfaces::IrcTextWrapper $msg
     *      The message to process, wrapped in
     *      a special object that makes it easier
     *      to analyze each token separately.
     */
    protected function _handleKICK($origin, $msg) {
        // :nick1!ident@host KICK #chan nick2 :Reason
        return $this->_connection->dispatch(
            $this->makeEvent('!Kick', $msg[0], $origin, $msg[1], $msg[2])
        );
    }

    /**
     * Processes a (user or channel-related)
     * message of type MODE.
     *
     * \param string $origin
     *      Origin of the message to process.
     *
     * \param Erebot::Interfaces::IrcTextWrapper $msg
     *      The message to process, wrapped in
     *      a special object that makes it easier
     *      to analyze each token separately.
     */
    protected function _handleMODE($origin, $msg) {
        // :nick1!ident@host MODE <nick2/#chan> <modes> [args]
        $target = $msg[0];
        unset($msg[0]);
        if (!$this->_connection->isChannel($target)) {
            return $this->_connection->dispatch(
                $this->makeEvent('!UserMode', $origin, $target, $msg)
            );
        }

        $event = $this->makeEvent('!RawMode', $target, $origin, $msg);
        $this->_connection->dispatch($event);
        if ($event->preventDefault(TRUE))
            return;

        $modes  = $msg[0];
        unset($msg[0]);
        $len    = strlen($modes);
        $mode   = 'add';
        $k      = 0;

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
                        $this->makeEvent($cls, $target, $origin, $tnick)
                    );
                    break;

                default:
            }
        } // for each mode in $modes
        /// @TODO: handle remaining modes as well
    }

    /**
     * Processes a message of type NICK.
     *
     * \param string $origin
     *      Origin of the message to process.
     *
     * \param Erebot::Interfaces::IrcTextWrapper $msg
     *      The message to process, wrapped in
     *      a special object that makes it easier
     *      to analyze each token separately.
     */
    protected function _handleNICK($origin, $msg) {
        // :oldnick!ident@host NICK newnick
        return $this->_connection->dispatch(
            $this->makeEvent('!Nick', $origin, $msg[0])
        );
    }

    /**
     * Processes a (user or channel-related) message
     * of type NOTICE.
     *
     * \param string $origin
     *      Origin of the message to process.
     *
     * \param Erebot::Interfaces::IrcTextWrapper $msg
     *      The message to process, wrapped in
     *      a special object that makes it easier
     *      to analyze each token separately.
     */
    protected function _handleNOTICE($origin, $msg) {
        // :nick1!ident@host NOTICE <nick2/#chan> :Message
        $mapping = array(
            'action'    => array('!PrivateCtcpReply', '!ChanCtcpReply'),
            'ctcp'      => array('!PrivateCtcpReply', '!ChanCtcpReply'),
            'normal'    => array('!PrivateNotice', '!ChanNotice'),
        );
        return $this->_noticeOrPrivmsg($origin, $msg, $mapping);
    }

    /**
     * Processes a message of type PART.
     *
     * \param string $origin
     *      Origin of the message to process.
     *
     * \param Erebot::Interface::IrcTextWrapper $msg
     *      The message to process, wrapped in
     *      a special object that makes it easier
     *      to analyze each token separately.
     */
    protected function _handlePART($origin, $msg) {
        // :nick1!ident@host PART #chan [reason]
        return $this->_connection->dispatch(
            $this->makeEvent(
                '!Part',
                $msg[0],
                $origin,
                isset($msg[1]) ? $msg[1] : ''
            )
        );
    }

    /**
     * Processes a message of type PING.
     *
     * \param string $origin
     *      Origin of the message to process.
     *
     * \param Erebot::Interfaces::IrcTextWrapper $msg
     *      The message to process, wrapped in
     *      a special object that makes it easier
     *      to analyze each token separately.
     */
    protected function _handlePING($origin, $msg) {
        // PING origin
        return $this->_connection->dispatch(
            $this->makeEvent('!Ping', $msg)
        );
    }

    /**
     * Processes a message of type PONG.
     *
     * \param string $origin
     *      Origin of the message to process.
     *
     * \param Erebot::Interfaces::IrcTextWrapper $msg
     *      The message to process, wrapped in
     *      a special object that makes it easier
     *      to analyze each token separately.
     */
    protected function _handlePONG($origin, $msg) {
        // :origin PONG origin target
        return $this->_connection->dispatch(
            $this->makeEvent('!Pong', $origin, $msg[1])
        );
    }

    /**
     * Processes a (user or channel-related) message
     * of type PRIVMSG.
     *
     * \param string $origin
     *      Origin of the message to process.
     *
     * \param Erebot::Interfaces::IrcTextWrapper $msg
     *      The message to process, wrapped in
     *      a special object that makes it easier
     *      to analyze each token separately.
     */
    protected function _handlePRIVMSG($origin, $msg) {
        // :nick1!ident@host PRIVMSG <nick2/#chan> :Message
        $mapping = array(
            'action'    => array('!PrivateAction', '!ChanAction'),
            'ctcp'      => array('!PrivateCtcp', '!ChanCtcp'),
            'normal'    => array('!PrivateText', '!ChanText'),
        );
        return $this->_noticeOrPrivmsg($origin, $msg, $mapping);
    }

    /**
     * Processes a message of type QUIT.
     *
     * \param string $origin
     *      Origin of the message to process.
     *
     * \param Erebot::Interfaces::IrcTextWrapper $msg
     *      The message to process, wrapped in
     *      a special object that makes it easier
     *      to analyze each token separately.
     */
    protected function _handleQUIT($origin, $msg) {
        // :nick1!ident@host QUIT :Reason
        return $this->_connection->dispatch(
            $this->makeEvent('!Quit', $origin, $msg[0])
        );
    }

    /**
     * Processes a message of type TOPIC.
     *
     * \param string $origin
     *      Origin of the message to process.
     *
     * \param Erebot::Interfaces::IrcTextWrapper $msg
     *      The message to process, wrapped in
     *      a special object that makes it easier
     *      to analyze each token separately.
     */
    protected function _handleTOPIC($origin, $msg) {
        // :nick1!ident@host TOPIC #chan :New topic
        return $this->_connection->dispatch(
            $this->makeEvent('!Topic', $msg[0], $origin, $msg[1])
        );
    }

    /**
     * Processes a message with numeric 255.
     *
     * \param string $origin
     *      Origin of the message to process.
     *
     * \param Erebot::Interfaces::IrcTextWrapper $msg
     *      The message to process, wrapped in
     *      a special object that makes it easier
     *      to analyze each token separately.
     *
     * \TODO
     *      Use a NumericEventHandler instead, even though it is less effective.
     */
    protected function _handle255($origin, $msg)
    {
        // \Erebot\Interfaces\Numerics::RPL_LUSERME
        /* We can't rely on RPL_WELCOME because we may need
         * to detect the server's capabilities first.
         * So, we delay detection of the connection for as
         * long as we can (while retaining portability). */
        if (!$this->_connection->isConnected())
            return $this->_connection->dispatch($this->makeEvent('!Connect'));
    }

    /**
     * Processes a message with numeric 600.
     *
     * \param string $origin
     *      Origin of the message to process.
     *
     * \param Erebot::Interfaces::IrcTextWrapper $msg
     *      The message to process, wrapped in
     *      a special object that makes it easier
     *      to analyze each token separately.
     *
     * \TODO
     *      Use a NumericEventHandler instead, even though it is less effective.
     */
    protected function _handle600($origin, $msg)
    {
        // \Erebot\Interfaces\Numerics::RPL_LOGON
        return $this->_watchList('!Notify', $msg);
    }

    /**
     * Processes a message with numeric 601.
     *
     * \param string $origin
     *      Origin of the message to process.
     *
     * \param Erebot::Interfaces::IrcTextWrapper $msg
     *      The message to process, wrapped in
     *      a special object that makes it easier
     *      to analyze each token separately.
     *
     * \TODO
     *      Use a NumericEventHandler instead, even though it is less effective.
     */
    protected function _handle601($origin, $msg)
    {
        // \Erebot\Interfaces\Numerics::RPL_LOGOFF
        return $this->_watchList('!UnNotify', $msg);
    }

    /**
     * Processes a message with numeric 604.
     *
     * \param string $origin
     *      Origin of the message to process.
     *
     * \param Erebot::Interfaces::IrcTextWrapper $msg
     *      The message to process, wrapped in
     *      a special object that makes it easier
     *      to analyze each token separately.
     *
     * \TODO
     *      Use a NumericEventHandler instead, even though it is less effective.
     */
    protected function _handle604($origin, $msg)
    {
        // \Erebot\Interfaces\Numerics::RPL_NOWON
        return $this->_watchList('!Notify', $msg);
    }

    /**
     * Processes a message with numeric 605.
     *
     * \param string $origin
     *      Origin of the message to process.
     *
     * \param Erebot::Interfaces::IrcTextWrapper $msg
     *      The message to process, wrapped in
     *      a special object that makes it easier
     *      to analyze each token separately.
     *
     * \TODO
     *      Use a NumericEventHandler instead, even though it is less effective.
     */
    protected function _handle605($origin, $msg)
    {
        // \Erebot\Interfaces\Numerics::RPL_NOWOFF
        return $this->_watchList('!UnNotify', $msg);
    }

    /**
     * Processes a message related to the WATCH list.
     *
     * \param string $event
     *      Interface name for the event to produce.
     *
     * \param Erebot::Interfaces::IrcTextWrapper $msg
     *      The message to process, wrapped in
     *      a special object that makes it easier
     *      to analyze each token separately.
     */
    protected function _watchList($event, $msg)
    {
        // <bot> <nick> <ident> <host> <timestamp> :<msg>
        unset($msg[0]);
        $nick       = $msg[0];
        $ident      = $msg[1];
        $host       = $msg[2];
        $timestamp  = intval($msg[3], 10);
        $timestamp  = new \DateTime('@'.$timestamp);
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

