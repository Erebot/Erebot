<?php

class UserAccount
{
    protected $id;
    protected $username;
    protected $nick;

    public function __construct()
    {
        $this->nick     = $nick;
        $this->id       = NULL;
    }

    public function login($username, $password)
    {
        $this->username = $username;
    }

    public function getId()
    {
        return $this->id;
    }
}

class   ErebotModule_UserAccount
extends ErebotModuleBase
{
    public function reload($flags)
    {
        if ($reload & self::RELOAD_HANDLERS) {

        }
    }

}

?>