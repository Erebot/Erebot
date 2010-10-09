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

class   ErebotModule_ServerCapabilities
extends ErebotModuleBase
{
    const LIST_BANS         = 0;
    const LIST_SILENCES     = 1;
    const LIST_EXCEPTS      = 2;
    const LIST_INVITES      = 3;
    const LIST_WATCHES      = 4;

    const TEXT_CHAN_NAME    = 0;
    const TEXT_NICKNAME     = 1;
    const TEXT_TOPIC        = 2;
    const TEXT_KICK         = 3;
    const TEXT_AWAY         = 4;

    const MODE_TYPE_A       = 0;
    const MODE_TYPE_B       = 1;
    const MODE_TYPE_C       = 2;
    const MODE_TYPE_D       = 3;

    const PATTERN_PREFIX    = '/^\\(([^\\)]+)\\)(.*)$/';

    static protected    $_caseMappings;
    protected           $_supported;

    public function reload($flags)
    {
        if ($flags & self::RELOAD_MEMBERS) {
            $this->_supported = array();

            self::$_caseMappings = array(
                    'ascii'             => array_combine(range('a', 'z'), range('A', 'Z')),
                    'strict-rfc1459'    => array_combine(range('a', chr(125)), range('A', chr(93))),
                    'rfc1459'           => array_combine(range('a', chr(126)), range('A', chr(94)))
            );
        }

        if ($flags & self::RELOAD_HANDLERS) {
            $handler = new ErebotRawHandler(array($this, 'handleRaw'), RPL_ISUPPORT);
            $this->_connection->addRawHandler($handler);

            $handler = new ErebotRawHandler(array($this, 'handleRaw'), RPL_MYINFO);
            $this->_connection->addRawHandler($handler);
        }
    }

    public function handleRaw(ErebotRaw &$raw)
    {
        if ($raw->getRaw() != RPL_ISUPPORT)
            return;

        $tokens = explode(' ', $raw->getText());
        foreach ($tokens as &$token) {
            if (substr($token, 0, 1) == ':')
                break;

            $supported          = $this->parseToken($token);
            if (isset($supported['NAMESX']))
                $this->sendCommand('PROTOCTL NAMESX');
            $this->_supported = array_merge($this->_supported, $supported);
        }
        unset($token);
    }

    protected function parseToken(&$token)
    {
        $pos = strpos($token, '=');
        if ($pos === FALSE)
            return array(strtoupper($token) => TRUE);

        $name   = strtoupper(substr($token, 0, $pos));
        $value  = substr($token, $pos + 1);

        if ($value == '')
            return array(strtoupper($name) => TRUE);

        $subs   = explode(',', $value);
        if (count($subs) == 1) {
            $subs   = explode(';', $value);
            if (count($subs) == 1)
                return array($name => $value);
        }

        $res = array();
        foreach ($subs as $sub) {
            if (strpos($sub, ':') === FALSE) {
                $res[$name][] = $sub;
                continue;
            }

            list($key, $val) = explode(':', $sub);
            $res[$name][$key] = $val;
        }

        return $res;
    }

    public function hasExtendedList()
    {
        return isset($this->_supported['ELIST']);
    }

    public function hasExtendedNames()
    {
        return isset($this->_supported['NAMESX']);
    }

    public function hasExtraPenalty()
    {
        return isset($this->_supported['PENALTY']);
    }

    public function hasForcedNickChange()
    {
        return isset($this->_supported['FNC']);
    }

    public function hasHybridConnectNotice()
    {
        return isset($this->_supported['HCN']);
    }

    public function hasCommand($cmdName)
    {
        if (!is_string($cmdName)) {
            $translator = $this->getTranslator(NULL);
            throw new EErebotInvalidValue($translator->gettext(
                'Not a valid command name'));
        }
        $cmdName = strtoupper($cmdName);

        if (isset($this->_supported[$cmdName]))
            return TRUE;

        if (isset($this->_supported['CMDS']) &&
            in_array($cmdName, $this->_supported['CMDS']))
            return TRUE;
        return FALSE;
    }

    public function hasSafeList()
    {
        return isset($this->_supported['SAFELIST']);
    }

    public function hasSecureList()
    {
        return isset($this->_supported['SECURELIST']);
    }

    public function hasStartTLS()
    {
        return isset($this->_supported['STARTTLS']);
    }

    public function hasStatusMsg($status)
    {
        if (!is_string($status) || strlen($status) != 1) {
            $translator = $this->getTranslator(NULL);
            throw new EErebotInvalidValue($translator->gettext(
                'Invalid status'));
        }

        if (isset($this->_supported['STATUSMSG']) &&
            is_string($this->_supported['STATUSMSG'])) {
            if (strpos($this->_supported['STATUSMSG'], $status) !== FALSE)
                return TRUE;
        }

        if ($status == '+' && isset($this->_supported['WALLVOICES']))
            return TRUE;

        if ($status == '@' && isset($this->_supported['WALLCHOPS']))
            return TRUE;

        return FALSE;
    }

    public function isChannel($chan)
    {
        if (!is_string($chan) || strlen($chan) < 1) {
            $translator = $this->getTranslator(NULL);
            throw new EErebotInvalidValue($translator->gettext(
                'Bad channel name'));
        }

        $prefix     = $chan[0];
        $allowed    =  (isset($this->_supported['CHANTYPES']) &&
                        is_string($this->_supported['CHANTYPES'])) ?
                        $this->_supported['CHANTYPES'] : '#&';
        return (strpos($allowed, $prefix) !== FALSE);
    }

    public function getMaxListSize($list)
    {
        $translator = $this->getTranslator(NULL);
        if (!is_int($list))
            throw new EErebotInvalidValue($translator->gettext(
                'Invalid list type'));

        switch ($list) {
            case self::LIST_BANS:
            case self::LIST_EXCEPTS:
            case self::LIST_INVITES:
                if (isset($this->_supported['MAXBANS']) &&
                    ctype_digit($this->_supported['MAXBANS']))
                    return (int) $this->_supported['MAXBANS'];
                throw new EErebotNotFound($translator->gettext(
                    'No limit specified'));

            case self::LIST_SILENCES:
                if (isset($this->_supported['SILENCE']) &&
                    ctype_digit($this->_supported['SILENCE']))
                    return (int) $this->_supported['SILENCE'];
                throw new EErebotNotFound($translator->gettext(
                    'No silence limit specified'));

            case self::LIST_WATCHES:
                if (isset($this->_supported['WATCH']) &&
                    ctype_digit($this->_supported['WATCH']))
                    return (int) $this->_supported['WATCH'];
                throw new EErebotNotFound($translator->gettext(
                    'No watch limit specified'));

            default:
                throw new EErebotInvalidValue($translator->gettext(
                    'Invalid list type'));
        }
    }

    public function getChanLimit($chanPrefix)
    {
        if (!is_string($chanPrefix)) {
            $translator = $this->getTranslator(NULL);
            throw new EErebotInvalidValue($translator->gettext(
                'Bad chan prefix'));
        }

        if (isset($this->_supported['CHANLIMIT'])) {
            foreach ($this->_supported['CHANLIMIT'] as $prefixes => $limit) {
                if (strpos($prefixes, $chanPrefix) !== FALSE) {
                    if ($limit == '')
                        return -1;

                    if (ctype_digit($limit))
                        return (int) $limit;
                }
            }
        }

        if (isset($this->_supported['MAXCHANNELS']) &&
            ctype_digit($this->_supported['MAXCHANNELS']))
            return (int) $this->_supported['MAXCHANNELS'];
        return -1;
    }

    public function getMaxTextLen($type)
    {
        $translator = $this->getTranslator(NULL);
        if (!is_int($type))
            throw new EErebotInvalidValue($translator->gettext(
                'Invalid text type'));

        switch ($type) {
            case self::TEXT_AWAY:
                if (isset($this->_supported['AWAYLEN']) &&
                    ctype_digit($this->_supported['AWAYLEN']))
                    return (int) $this->_supported['AWAYLEN'];
                break;

            case self::TEXT_CHAN_NAME:
                if (isset($this->_supported['CHANNELLEN']) &&
                    ctype_digit($this->_supported['CHANNELLEN']))
                    return (int) $this->_supported['CHANNELLEN'];
                return 200;

            case self::TEXT_KICK:
                if (isset($this->_supported['KICKLEN']) &&
                    ctype_digit($this->_supported['KICKLEN']))
                    return (int) $this->_supported['KICKLEN'];
                break;

            case self::TEXT_NICKNAME:
                if (isset($this->_supported['NICKLEN']) &&
                    ctype_digit($this->_supported['NICKLEN']))
                    return (int) $this->_supported['NICKLEN'];
                return 9;

            case self::TEXT_TOPIC:
                if (isset($this->_supported['TOPICLEN'])) {
                    if (ctype_digit($this->_supported['TOPICLEN']))
                        return (int) $this->_supported['TOPICLEN'];
                }
                return -1;

            default:
                throw new EErebotInvalidValue($translator->gettext(
                    'Invalid text type'));
        }
        throw new EErebotNotFound($translator->gettext(
            'No limit defined for this text'));
    }

    public function getCaseMapping()
    {
        if (isset($this->_supported['CASEMAPPING']) &&
            is_string($this->_supported['CASEMAPPING']))
            return strtolower($this->_supported['CASEMAPPING']);
        return 'rfc1459';
    }

    protected function cmp($a, $b, $mapping, $len)
    {
        if ($mapping !== NULL) {
            $a = strtr($a, $mapping);
            $b = strtr($b, $mapping);
        }

        if ($len == -1)
            return strcmp($a, $b);
        return strncmp($a, $b, $len);
    }

    public function irccmp($a, $b)
    {
        return $this->cmp($a, $b, NULL, -1);
    }

    public function ircncmp($a, $b, $len)
    {
        return $this->cmp($a, $b, NULL, $len);
    }

    public function irccasecmp($a, $b, $mappingName = NULL)
    {
        $translator = $this->getTranslator(NULL);
        if ($mappingName === NULL)
            $mappingName = $this->getCaseMapping();

        if (!is_string($mappingName))
            throw new EErebotInvalidValue($translator->gettext(
                'Invalid mapping name'));

        $mappingName = strtolower($mappingName);
        if (!isset(self::$_caseMappings[$mappingName]))
            throw new EErebotNotFound($translator->gettext(
                'No such mapping exists'));
        $mapping = self::$_caseMappings[$mappingName];

        return $this->cmp($a, $b, $mapping, -1);
    }

    public function ircncasecmp($a, $b, $len, $mappingName = NULL)
    {
        $translator = $this->getTranslator(NULL);
        if ($mappingName === NULL)
            $mappingName = $this->getCaseMapping();

        if (!is_string($mappingName))
            throw new EErebotInvalidValue($translator->gettext(
                'Invalid mapping name'));

        $mappingName = strtolower($mappingName);
        if (!isset(self::$_caseMappings[$mappingName]))
            throw new EErebotNotFound($translator->gettext(
                'No such mapping exists'));
        $mapping = self::$_caseMappings[$mappingName];

        return $this->cmp($a, $b, $mapping, $len);
    }

    public function getCharset()
    {
        if (isset($this->_supported['CHARSET']) &&
            is_string($this->_supported['CHARSET']))
            return $this->_supported['CHARSET'];
        $translator = $this->getTranslator(NULL);
        throw new EErebotNotFound($translator->gettext(
            'No charset specified'));
    }

    public function getNetworkName()
    {
        if (isset($this->_supported['NETWORK']) &&
            is_string($this->_supported['NETWORK']))
            return $this->_supported['NETWORK'];
        return '';
    }

    public function getChannelListMode($list)
    {
        $translator = $this->getTranslator(NULL);
        if (!is_int($list))
            throw new EErebotInvalidValue($translator->gettext(
                'Bad channel list ID'));

        switch ($list) {
            case self::LIST_BANS:
                return 'b';

            case self::LIST_EXCEPTS:
                if (!isset($this->_supported['EXCEPTS']))
                    throw new EErebotNotFound($translator->gettext(
                        'Excepts are not available on this server'));

                if ($this->_supported['EXCEPTS'] === TRUE)
                    return 'e';
                return $this->_supported['EXCEPTS'];
                break;

            case self::LIST_INVITES:
                if (!isset($this->_supported['INVEX']))
                    throw new EErebotNotFound($translator->gettext(
                        'Invites are not available on this server'));

                if ($this->_supported['INVEX'] === TRUE)
                    return 'I';
                return $this->_supported['INVEX'];
                break;

            default:
                throw new EErebotInvalidValue($translator->gettext(
                    'Invalid channel list ID'));
        }
    }

    public function getChanPrefixForMode($mode)
    {
        $translator = $this->getTranslator(NULL);
        if (!is_string($mode) || strlen($mode) != 1)
            throw new EErebotInvalidValue($translator->gettext('Invalid mode'));

        if (!isset($this->_supported['PREFIX']))
            throw new EErebotNotFound($translator->gettext(
                'No mapping for prefixes'));

        $ok = preg_match(self::PATTERN_PREFIX,
            $this->_supported['PREFIX'], $matches);

        if ($ok) {
            $pos = strpos($matches[1], $mode);
            if ($pos !== FALSE && strlen($matches[2]) > $pos)
                return $matches[2][$pos];
        }

        throw new EErebotNotFound($translator->gettext('No such mode'));
    }

    public function getChanModeForPrefix($prefix)
    {
        $translator = $this->getTranslator(NULL);
        if (!is_string($prefix) || strlen($prefix) != 1)
            throw new EErebotInvalidValue($translator->gettext(
                'Invalid prefix'));

        if (!isset($this->_supported['PREFIX']))
            throw new EErebotNotFound($translator->gettext(
                'No mapping for prefixes'));

        $ok = preg_match(self::PATTERN_PREFIX, $this->_supported['PREFIX'], $matches);
        if ($ok) {
            $pos = strpos($matches[2], $prefix);
            if ($pos !== FALSE && strlen($matches[1]) > $pos)
                return $matches[1][$pos];
        }

        throw new EErebotNotFound($translator->gettext('No such prefix'));
    }

    public function qualifyChannelMode($mode)
    {
        $translator = $this->getTranslator(NULL);
        if (!is_string($mode) || strlen($mode) != 1)
            throw new EErebotInvalidValue($translator->gettext(
                'Invalid mode'));

        if (!isset($this->_supported['CHANMODES']) ||
            !is_array($this->_supported['CHANMODES']))
            throw new EErebotNotFound('No such mode');

        $type = self::MODE_TYPE_A;
        foreach ($this->_supported['CHANMODES'] as &$modes) {
            if ($type > self::MODE_TYPE_D)  // Modes after type 4 are reserved
                break;                      // for future extensions.

            if (strpos($modes, $mode) !== FALSE)
                return $type;
            $type++;
        }
        unset($modes);
        throw new EErebotNotFound($translator->gettext('No such mode'));
    }

    public function getMaxTargets($cmd)
    {
        if (!is_string($cmd)) {
            $translator = $this->getTranslator(NULL);
            throw new EErebotInvalidValue($translator->gettext('Invalid command'));
        }

        $cmd = strtoupper($cmd);
        if (isset($this->_supported['TARGMAX'][$cmd])) {
            if ($this->_supported['TARGMAX'][$cmd] == '')
                return -1;

            if (ctype_digit($this->_supported['TARGMAX'][$cmd]))
                return (int) $this->_supported['TARGMAX'][$cmd];
        }

        else if (isset($this->_supported['MAXTARGETS']) &&
                ctype_digit($this->_supported['MAXTARGETS']))
            return (int) $this->_supported['MAXTARGETS'];

        return -1;
    }

    public function getMaxVariableModes()
    {
        if (isset($this->_supported['MODES'])) {
            if ($this->_supported['MODES'] == '')
                return -1;

            if (ctype_digit($this->_supported['MODES']))
                return (int) $this->_supported['MODES'];
        }

        return 3;
    }

    public function getMaxListModes($modes)
    {
        if (is_string($modes))
            $modes = str_split($modes);

        if (!is_array($modes)) {
            $translator = $this->getTranslator(NULL);
            throw new EErebotInvalidValue($translator->gettext(
                'Invalid modes'));
        }

        if (!isset($this->_supported['MAXLIST']))
            return $this->getMaxVariableModes();

        foreach ($this->_supported['MAXLIST'] as $maxs => $limit) {
            $maxs = str_split($maxs);
            if (!count(array_diff($modes, $maxs)) && ctype_digit($limit))
                return (int) $limit;
        }
        return $this->getMaxVariableModes();
    }

    public function getMaxParams()
    {
        if (isset($this->_supported['MAXPARA']) &&
            ctype_digit($this->_supported['MAXPARA']))
            return (int) $this->_supported['MAXPARA'];
        return 12;
    }

    public function getSSL()
    {
        if (isset($this->_supported['SSL'])) {
            if (is_string($this->_supported['SSL'])) {
                list($key, $val) = explode(':', $this->_supported['SSL']);
                return array($key => $val);
            }

            if (is_array($this->_supported['SSL']))
                return $this->_supported['SSL'];
        }

        $translator = $this->getTranslator(NULL);
        throw new EErebotNotFound($translator->gettext(
            'No SSL information available'));
    }

    public function getIdLength($prefix)
    {
        $translator = $this->getTranslator(NULL);
        if (!is_string($prefix) || strlen($prefix) != 1)
            throw new EErebotInvalidValue($translator->gettext('Bad prefix'));

        if (isset($this->_supported['IDCHAN'][$prefix]) &&
            ctype_digit($this->_supported['IDCHAN'][$prefix]))
            return (int) $this->_supported['IDCHAN'][$prefix];

        if (isset($this->_supported['CHIDLEN']) &&
            ctype_digit($this->_supported['CHIDLEN']))
            return (int) $this->_supported['CHIDLEN'];

        throw new EErebotNotFound($translator->gettext(
            'No safe channels on this server'));
    }

    public function supportsStandard($standard)
    {
        if (!is_string($standard)) {
            $translator = $this->getTranslator(NULL);
            throw new EErebotInvalidValue($translator->gettext(
                'Bad standard name'));
        }

        if (isset($this->_supported['STD'])) {
            $standards = array();

            if (is_string($this->_supported['STD']))
                $standards[] = $this->_supported['STD'];
            else if (is_array($this->_supported['STD']))
                $standards = $this->_supported['STD'];

            foreach ($standards as &$std) {
                if (!strcasecmp($std, $standard))
                    return TRUE;
            }
            unset($std);
        }

        if (!strcasecmp($standard, 'rfc2812') &&
            isset($this->_supported['RFC2812']))
            return TRUE;

        return FALSE;
    }

    public function getExtendedBanPrefix()
    {
        if (is_array($this->_supported['EXTBAN'])    &&
            isset($this->_supported['EXTBAN'][0])    &&
            strlen($this->_supported['EXTBAN'][0]) == 1) {
            return $this->_supported['EXTBAN'][0];
        }

        $translator = $this->getTranslator(NULL);
        throw new EErebotNotFound($translator->gettext(
            'Extended bans not supported on this server'));
    }

    public function getExtendedBanModes()
    {
        if (is_array($this->_supported['EXTBAN']) &&
            isset($this->_supported['EXTBAN'][1])) {
            return $this->_supported['EXTBAN'][1];
        }

        $translator = $this->getTranslator(NULL);
        throw new EErebotNotFound($translator->gettext(
            'Extended bans not supported on this server'));
    }
}

