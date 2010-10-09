<?php

class   ErebotModule_NickTracker
extends ErebotModuleBase
{
    protected $nicks;

    public function reload($flags)
    {
        if ($flags & self::RELOAD_MEMBERS) {
            $this->nicks = array();
        }

        if ($flags & self::RELOAD_HANDLERS) {
            $handler = new ErebotEventHandler(
                array($this, 'handleNick'),
                ErebotEvent::ON_NICK);
            $this->connection->addEventHandler($handler);

            $handler = new ErebotEventHandler(
                array($this, 'handlePartOrQuit'),
                ErebotEvent::ON_QUIT);
            $this->connection->addEventHandler($handler);
        }
    }

    public function startTracking($nick)
    {
        if (!is_string($nick)) {
            $translator = $this->getTranslator(NULL);
            throw new EErebotInvalidValue($translator->gettext(
                'Not a valid nick'));
        }

        $this->nicks[] = $nick;
        end($this->nicks);
        $key = key($this->nicks);
        return $key;
    }

    public function stopTracking($token)
    {
        if (!isset($this->nicks[$token])) {
            $translator = $this->getTranslator(NULL);
            throw new EErebotNotFound($translator->gettext('No such token'));
        }
        unset($this->nicks[$token]);
    }

    public function getNick($token)
    {
        if (!isset($this->nicks[$token])) {
            $translator = $this->getTranslator(NULL);
            throw new EErebotNotFound($translator->gettext('No such token'));
        }
        return $this->nicks[$token];
    }

    public function handleNick(ErebotEvent &$event)
    {
        $oldNick        = (string) $event->getSource();
        $newNick        = (string) $event->getNewNick();

        $capabilities   =   $this->connection->getModule(
                                'ServerCapabilities',
                                ErebotConnection::MODULE_BY_NAME);

        foreach ($this->nicks as $token => &$nick) {
            if (!$capabilities->irccasecmp($nick, $oldNick))
                $this->nicks[$token] = $newNick;
        }
        unset($nick);
    }

    public function handlePartOrQuit(ErebotEvent &$event)
    {
        $srcNick        = (string) $event->getSource();

        $capabilities   =   $this->connection->getModule(
                                'ServerCapabilities',
                                ErebotConnection::MODULE_BY_NAME);

        foreach ($this->nicks as $token => &$nick) {
            if (!$capabilities->irccasecmp($nick, $srcNick))
                unset($this->nicks[$token]);
        }
        unset($nick);
    }

    public function handleKick(ErebotEvent &$event)
    {
        $srcNick        = (string) $event->getKickNick();

        $capabilities   =   $this->connection->getModule(
                                'ServerCapabilities',
                                ErebotConnection::MODULE_BY_NAME);

        foreach ($this->nicks as $token => &$nick) {
            if (!$capabilities->irccasecmp($nick, $srcNick))
                unset($this->nicks[$token]);
        }
        unset($nick);
    }
}

?>
