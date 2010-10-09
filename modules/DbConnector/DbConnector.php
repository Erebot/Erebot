<?php

class   ErebotModule_DbConnector
extends ErebotModuleBase
{
    protected $DSN;
    protected $user;
    protected $pass;

    public function reload($flags)
    {
        if ($flags & self::RELOAD_MEMBERS) {
            $this->DSN      = $this->parseString('DSN');
            $this->username = $this->parseString('username', 'root');
            $this->password = $this->parseString('password', '');
        }
    }

    public function getDbConnection($options = array())
    {
        /// @TODO: makes runkit crash...
#        static $PDO = NULL;

        if ($PDO === NULL) {
            $PDO  = new PDO($this->DSN, $this->username, $this->password, $options);
            $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $PDO->setAttribute(PDO::ATTR_AUTOCOMMIT, FALSE);
        }

        return $PDO;
    }
}

?>
