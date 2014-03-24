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

namespace Erebot\NumericProfile;

/**
 * \brief
 *      Numeric profile for InspIRCd-based IRC servers.
 */
class InspIRCd extends \Erebot\NumericProfile\Base
{
    /// \copydoc Erebot::Interfaces::Numerics::RPL_WELCOME.
    const RPL_WELCOME                   =   1;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_YOURHOSTIS.
    const RPL_YOURHOSTIS                =   2;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_SERVERCREATED.
    const RPL_SERVERCREATED             =   3;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_SERVERVERSION.
    const RPL_SERVERVERSION             =   4;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ISUPPORT.
    const RPL_ISUPPORT                  =   5;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_MAP.
    const RPL_MAP                       =   6;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDMAP.
    const RPL_ENDMAP                    =   7;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_SNOMASKIS.
    const RPL_SNOMASKIS                 =   8;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_YOURUUID.
    const RPL_YOURUUID                  =  42;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_UMODEIS.
    const RPL_UMODEIS                   = 221;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_RULES.
    const RPL_RULES                     = 232;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ADMINME.
    const RPL_ADMINME                   = 256;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ADMINLOC1.
    const RPL_ADMINLOC1                 = 257;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ADMINLOC2.
    const RPL_ADMINLOC2                 = 258;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ADMINEMAIL.
    const RPL_ADMINEMAIL                = 259;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_MAPUSERS.
    const RPL_MAPUSERS                  = 270;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_SYNTAX.
    const RPL_SYNTAX                    = 304;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_UNAWAY.
    const RPL_UNAWAY                    = 305;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_NOWAWAY.
    const RPL_NOWAWAY                   = 306;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_RULESTART.
    const RPL_RULESTART                 = 308;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_RULESEND.
    const RPL_RULESEND                  = 309;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_CHANNELMODEIS.
    const RPL_CHANNELMODEIS             = 324;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_CHANNELCREATED.
    const RPL_CHANNELCREATED            = 329;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_NOTOPICSET.
    const RPL_NOTOPICSET                = 331;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TOPIC.
    const RPL_TOPIC                     = 332;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TOPICTIME.
    const RPL_TOPICTIME                 = 333;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_INVITING.
    const RPL_INVITING                  = 341;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_INVITELIST.
    const RPL_INVITELIST                = 346;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFINVITELIST.
    const RPL_ENDOFINVITELIST           = 347;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_VERSION.
    const RPL_VERSION                   = 351;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_NAMREPLY.
    const RPL_NAMREPLY                  = 353;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFNAMES.
    const RPL_ENDOFNAMES                = 366;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_INFO.
    const RPL_INFO                      = 371;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFINFO.
    const RPL_ENDOFINFO                 = 374;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_MOTD.
    const RPL_MOTD                      = 372;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_MOTDSTART.
    const RPL_MOTDSTART                 = 375;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_ENDOFMOTD.
    const RPL_ENDOFMOTD                 = 376;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_YOUAREOPER.
    const RPL_YOUAREOPER                = 381;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_REHASHING.
    const RPL_REHASHING                 = 382;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_TIME.
    const RPL_TIME                      = 391;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_YOURDISPLAYEDHOST.
    const RPL_YOURDISPLAYEDHOST         = 396;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOSUCHNICK.
    const ERR_NOSUCHNICK                = 401;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOSUCHSERVER.
    const ERR_NOSUCHSERVER              = 402;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOSUCHCHANNEL.
    const ERR_NOSUCHCHANNEL             = 403;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_CANNOTSENDTOCHAN.
    const ERR_CANNOTSENDTOCHAN          = 404;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_TOOMANYCHANNELS.
    const ERR_TOOMANYCHANNELS           = 405;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_INVALIDCAPSUBCOMMAND.
    const ERR_INVALIDCAPSUBCOMMAND      = 410;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_UNKNOWNCOMMAND.
    const ERR_UNKNOWNCOMMAND            = 421;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOMOTD.
    const ERR_NOMOTD                    = 422;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NORULES.
    const ERR_NORULES                   = 434;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_USERNOTINCHANNEL.
    const ERR_USERNOTINCHANNEL          = 441;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOTONCHANNEL.
    const ERR_NOTONCHANNEL              = 442;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_USERONCHANNEL.
    const ERR_USERONCHANNEL             = 443;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_CANTCHANGENICK.
    const ERR_CANTCHANGENICK            = 447;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOTREGISTERED.
    const ERR_NOTREGISTERED             = 451;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NEEDMOREPARAMS.
    const ERR_NEEDMOREPARAMS            = 461;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_ALREADYREGISTERED.
    const ERR_ALREADYREGISTERED         = 462;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_BADCHANNELKEY.
    const ERR_BADCHANNELKEY             = 475;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_INVITEONLYCHAN.
    const ERR_INVITEONLYCHAN            = 473;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_CHANNELISFULL.
    const ERR_CHANNELISFULL             = 471;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_BANNEDFROMCHAN.
    const ERR_BANNEDFROMCHAN            = 474;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOPRIVILEGES.
    const ERR_NOPRIVILEGES              = 481;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_CHANOPRIVSNEEDED.
    const ERR_CHANOPRIVSNEEDED          = 482;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_ALLMUSTSSL.
    const ERR_ALLMUSTSSL                = 490;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_NOCTCPALLOWED.
    const ERR_NOCTCPALLOWED             = 492;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_DELAYREJOIN.
    const ERR_DELAYREJOIN               = 495;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_UNKNOWNSNOMASK.
    const ERR_UNKNOWNSNOMASK            = 501;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_USERSDONTMATCH.
    const ERR_USERSDONTMATCH            = 502;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_CANTJOINOPERSONLY.
    const ERR_CANTJOINOPERSONLY         = 520;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_CANTSENDTOUSER.
    const ERR_CANTSENDTOUSER            = 531;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_COMMANDS.
    const RPL_COMMANDS                  = 702;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_COMMANDSEND.
    const RPL_COMMANDSEND               = 703;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_WORDFILTERED.
    const ERR_WORDFILTERED              = 936;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_CANTUNLOADMODULE.
    const ERR_CANTUNLOADMODULE          = 972;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_UNLOADEDMODULE.
    const RPL_UNLOADEDMODULE            = 973;

    /// \copydoc Erebot::Interfaces::Numerics::ERR_CANTLOADMODULE.
    const ERR_CANTLOADMODULE            = 974;

    /// \copydoc Erebot::Interfaces::Numerics::RPL_LOADEDMODULE.
    const RPL_LOADEDMODULE              = 975;
}
