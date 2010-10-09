<?php

/* Define constant names for IRC RAW messages. */
define('RPL_WELCOME',             1);
define('RPL_YOURHOST',            2);
define('RPL_CREATED',             3);
define('RPL_MYINFO',              4);
define('RPL_ISUPPORT',            5);
define('RPL_PROTOCTL',            5);
define('RPL_BOUNCE',              5);
define('RPL_MAP',                 6);
define('RPL_MAPMORE',             6);
define('RPL_MAPEND',              7);
define('RPL_SNOMASK',             8);
define('RPL_STATMEM',            10);
define('RPL_STATMEMTOT',         10);
define('RPL_YOURCOOKIE',         14);
define('RPL_YOURID',             42);
define('RPL_SAVENICK',           43);
define('RPL_ATTEMPTINGJUNC',     50);
define('RPL_ATTEMPTINGREROUTE',  51);

define('RPL_TRACELINK',         200);
define('RPL_TRACECONNECTING',   201);
define('RPL_TRACEHANDSHAKE',    202);
define('RPL_TRACEUNKNOWN',      203);
define('RPL_TRACEOPERATOR',     204);
define('RPL_TRACEUSER',         205);
define('RPL_TRACESERVER',       206);
define('RPL_TRACESERVICE',      207);
define('RPL_TRACENEWTYPE',      208);
define('RPL_TRACECLASS',        209);
define('RPL_TRACECONNECT',      210);
define('RPL_STATSLINKINFO',     211);
define('RPL_STATSCOMMANDS',     212);
define('RPL_STATSCLINE',        213);
define('RPL_STATSNLINE',        214);
define('RPL_STATSOLDNLINE',     214);
define('RPL_STATSILINE',        215);
define('RPL_STATSKLINE',        216);
define('RPL_STATSPLINE',        217);
define('RPL_STATSQLINE',        217);
define('RPL_STATSYLINE',        218);
define('RPL_ENDOFSTATS',        219);
define('RPL_STATSBLINE',        220);
define('RPL_UMODEIS',           221);
define('RPL_SQLINE_NICK',       222);
define('RPL_STATS_E',           223);
define('RPL_STATS_D',           224);
define('RPL_STATSCLONE',        225);
define('RPL_STATSCOUNT',        226);
define('RPL_STATSGLINE',        227);
define('RPL_SERVICEINFO',       231);
define('RPL_ENDOFSERVICES',     232);
define('RPL_SERVICE',           233);
define('RPL_SERVLIST',          234);
define('RPL_SERVLISTEND',       235);
define('RPL_STATSLLINE',        241);
define('RPL_STATSUPTIME',       242);
define('RPL_STATSOLINE',        243);
define('RPL_STATSHLINE',        244);
define('RPL_STATSSLINE',        245);
define('RPL_STATSXLINE',        246);
define('RPL_STATSULINE',        248);
define('RPL_STATSDEBUG',        249);
define('RPL_STATSCONN',         250);
define('RPL_LUSERCLIENT',       251);
define('RPL_LUSEROP',           252);
define('RPL_LUSERUNKNOWN',      253);
define('RPL_LUSERCHANNELS',     254);
define('RPL_LUSERME',           255);
define('RPL_ADMINME',           256);
define('RPL_ADMINLOC1',         257);
define('RPL_ADMINLOC2',         258);
define('RPL_ADMINEMAIL',        259);
define('RPL_TRACELOG',          261);
define('RPL_TRACEPING',         262);
define('RPL_LOAD2HI',           263);
define('RPL_TRYAGAIN',          263);
define('RPL_LOCALUSERS',        265);
define('RPL_LUSERSC',           265);
define('RPL_GLOBALUSERS',       266);
define('RPL_LUSERSG',           266);
define('RPL_SILELIST',          271);
define('RPL_ENDOFSILELIST',     272);
define('RPL_STATSDLINE',        275);
define('RPL_GLIST',             280);
define('RPL_ENDOFGLIST',        281);
define('RPL_HELPHDR',           290);
define('RPL_HELPOP',            291);
define('RPL_HELPTLR',           292);
define('RPL_HELPHLP',           293);
define('RPL_HELPFWD',           294);
define('RPL_HELPIGN',           295);
define('RPL_NEWNICK',           298);

define('RPL_AWAY',              301);
define('RPL_USERHOST',          302);
define('RPL_ISON',              303);
define('RPL_UNAWAY',            305);
define('RPL_NOWAWAY',           306);
define('RPL_USERIP',            307);
define('RPL_WHOISREGNICK',      307);
define('RPL_WHOISADMIN',        308);
define('RPL_WHOISSADMIN',       309);
define('RPL_WHOISSVCMSG',       310);
define('RPL_WHOISHELPOP',       310);
define('RPL_WHOISUSER',         311);
define('RPL_WHOISSERVER',       312);
define('RPL_WHOISOPERATOR',     313);
define('RPL_WHOWASUSER',        314);
define('RPL_ENDOFWHO',          315);
define('RPL_WHOISCHANOP',       316);
define('RPL_WHOISIDLE',         317);
define('RPL_ENDOFWHOIS',        318);
define('RPL_WHOISCHANNELS',     319);
define('RPL_LISTSTART',         321);
define('RPL_LIST',              322);
define('RPL_LISTEND',           323);
define('RPL_CHANNELMODEIS',     324);
define('RPL_UNIQOPIS',          325);
define('RPL_CREATIONTIME',      329);
define('RPL_NOTOPIC',           331);
define('RPL_TOPIC',             332);
define('RPL_TOPICWHOTIME',      333);
define('RPL_COMMANDSYNTAX',     334);
define('RPL_WHOISTEXT',         337);
define('RPL_WHOISACTUALLY',     338);
define('RPL_INVITING',          341);
define('RPL_SUMMONING',         342);
define('RPL_INVITELIST',        346);
define('RPL_ENDOFINVITELIST',   347);
define('RPL_EXCEPTLIST',        348);
define('RPL_EXEMPTLIST',        348);
define('RPL_ENDOFEXCEPTLIST',   349);
define('RPL_ENDOFEXEMPTLIST',   349);
define('RPL_VERSION',           351);
define('RPL_WHOREPLY',          352);
define('RPL_NAMREPLY',          353);
define('RPL_RWHOREPLY',         354);
define('RPL_CLOSING',           362);
define('RPL_CLOSEEND',          363);
define('RPL_LINKS',             364);
define('RPL_ENDOFLINKS',        365);
define('RPL_ENDOFNAMES',        366);
define('RPL_BANLIST',           367);
define('RPL_ENDOFBANLIST',      368);
define('RPL_ENDOFWHOWAS',       369);
define('RPL_INFO',              371);
define('RPL_MOTD',              372);
define('RPL_ENDOFINFO',         374);
define('RPL_MOTDSTART',         375);
define('RPL_ENDOFMOTD',         376);
define('RPL_ISASERVICE',        377);
define('RPL_WHOISHOST',         378);
define('RPL_YOUREOPER',         381);
define('RPL_REHASHING',         382);
define('RPL_MYPORTIS',          384);
define('RPL_TIME',              391);

define('ERR_NOSUCHNICK',        401);
define('ERR_NOSUCHSERVER',      402);
define('ERR_NOSUCHCHANNEL',     403);
define('ERR_CANNOTSENDTOCHAN',  404);
define('ERR_TOOMANYCHANNELS',   405);
define('ERR_WASNOSUCHNICK',     406);
define('ERR_TOOMANYTARGETS',    407);
define('ERR_NOSUCHSERVICE',     408);
define('ERR_NOCTRLSONCHAN',     408);
define('ERR_NOORIGIN',          409);
define('ERR_NORECIPIENT',       411);
define('ERR_NOTEXTTOSEND',      412);
define('ERR_NOTOPLEVEL',        413);
define('ERR_WILDTOPLEVEL',      414);
define('ERR_BADMASK',           415);
define('ERR_QUERYTOOLONG',      416);
define('ERR_UNKNOWNCOMMAND',    421);
define('ERR_NOMOTD',            422);
define('ERR_NOADMININFO',       423);
define('ERR_FILEERROR',         424);
define('ERR_TOOMANYAWAY',       429);
define('ERR_NONICKNAMEGIVEN',   431);
define('ERR_ERRONEUSNICKNAME',  432);
define('ERR_NICKNAMEINUSE',     433);
define('ERR_BANONCHAN',         435);
define('ERR_NICKCOLLISION',     436);
define('ERR_BANNICKCHANGE',     437);
define('ERR_NICKTOOFAST',       438);
define('ERR_TARGETTOOFAST',     439);
define('ERR_SERVICESDOWN',      440);
define('ERR_USERNOTINCHANNEL',  441);
define('ERR_NOTONCHANNEL',      442);
define('ERR_USERONCHANNEL',     443);
define('ERR_NOLOGIN',           444);
define('ERR_SUMMONDISABLED',    445);
define('ERR_USERSDISABLED',     446);
define('ERR_NOTREGISTERED',     451);
define('ERR_HOSTILENAME',       455);
define('ERR_NEEDMOREPARAMS',    461);
define('ERR_ALREADYREGISTRED',  462);
define('ERR_NOPERMFORHOST',     463);
define('ERR_PASSWDMISMATCH',    464);
define('ERR_YOUREBANNEDCREEP',  465);
define('ERR_KEYSET',            467);
define('ERR_INVALIDUSERNAME',   468);
define('ERR_ONLYSERVERSCANCHANGE', 468);
define('ERR_CHANNELISFULL',     471);
define('ERR_UNKNOWNMODE',       472);
define('ERR_INVITEONLYCHAN',    473);
define('ERR_BANNEDFROMCHAN',    474);
define('ERR_BADCHANNELKEY',     475);
define('ERR_BADCHANMASK',       476);
define('ERR_NEEDREGGEDNICK',    477);
define('ERR_NOCHANMODES',       477);
define('ERR_BANLISTFULL',       478);
define('ERR_BADCHANNAME',       479);
define('ERR_NOPRIVILEGES',      481);
define('ERR_CHANOPPRIVSNEEDED', 482);
define('ERR_CANTKILLSERVER',    483);
define('ERR_ISCHANSERVICE',     484);
define('ERR_CHANBANREASON',     485);
define('ERR_NONONREG',          486);
define('ERR_MSGSERVICES',       487);
define('ERR_NOOPERHOST',        491);
define('ERR_OWNMODE',           494);

define('ERR_UMODEUNKNOWNFLAG',  501);
define('ERR_USERSDONTMATCH',    502);
define('ERR_SILELISTFULL',      511);
define('ERR_NOSUCHGLINE',       512);
define('ERR_TOOMANYWATCH',      512);
define('ERR_BADPING',           513);
define('ERR_TOOMANYDCC',        514);
define('ERR_LISTSYNTAX',        521);
define('ERR_WHOSYNTAX',         522);
define('ERR_WHOLIMEXCEED',      523);

define('RPL_LOGON',             600);
define('RPL_LOGOFF',            601);
define('RPL_WATCHOFF',          602);
define('RPL_WATCHSTAT',         603);
define('RPL_NOWON',             604);
define('RPL_NOWOFF',            605);
define('RPL_WATCHLIST',         606);
define('RPL_ENDOFWATCHLIST',    607);
define('RPL_DCCSTATUS',         617);
define('RPL_DCCLIST',           618);
define('RPL_ENDOFDCCLIST',      619);
define('RPL_DCCINFO',           620);

class ErebotRaw
{
    protected $connection;
    protected $raw;
    protected $source;
    protected $target;
    protected $text;

    public function __construct(ErebotConnection &$connection, $raw, $source, $target, $text)
    {
        $this->connection   =&  $connection;
        $this->raw          =   $raw;
        $this->source       =   $source;
        $this->target       =   $target;
        $this->text         =   $text;
    }

    public function __destruct()
    {
    }

    public function & getConnection()
    {
        return $this->connection;
    }

    public function getRaw()
    {
        return $this->raw;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function getText()
    {
        return $this->text;
    }
}

?>