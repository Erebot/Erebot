AutoIdent module
################

..  contents::
    :local:

Description
===========

This module makes the bot authenticate itself to a nick server (usually ``NickServ``)
automatically whenever it is prompted for proof of its identity.

Configuration
=============

Options
-------

This module provides several configuration options.

..  table:: Options for \\Erebot\\Module\\AutoIdent

    +----------+--------+---------------+-------------------------------------+
    | Name     | Type   | Default value | Description                         |
    +==========+========+===============+=====================================+
    | nickserv | string | "nickserv"    | A space-separated list of nicknames |
    |          |        |               | NickServ may use to contact us.     |
    |          |        |               | Usually "nickserv".                 |
    +----------+--------+---------------+-------------------------------------+
    | password | string | n/a           | The password associated with the    |
    |          |        |               | bot's nickname                      |
    +----------+--------+---------------+-------------------------------------+
    | pattern  | string | n/a           | The pattern (regular expression) an |
    |          |        |               | incoming message must match before  |
    |          |        |               | the bot sends out the password on   |
    |          |        |               | the wire.                           |
    +----------+--------+---------------+-------------------------------------+


Example
-------

The following configuration has been used successfully on the
`EpiKnet IRC network`_ which uses ``Themis`` as NickServ's main nickname.

..  _`EpiKnet IRC network`:
    http://epiknet.org/


..  parsed-code:: xml

    <?xml version="1.0"?>
    <configuration
      xmlns="http://localhost/Erebot/"
      version="..."
      language="fr-FR"
      timezone="Europe/Paris"
      commands-prefix="!">

      <modules>
        <!-- Other modules ignored for clarity. -->

        <!-- Configure the bot's nickname, etc. -->
        <module name="\\Erebot\\Module\\AutoIdent">
          <param name="nickname" value="Erebot" />
          <param name="identity" value="Erebot" />
          <param name="hostname" value="Erebot" />
          <param name="realname" value="Doh!" />
        </module>
      </modules>

      <networks>
        <network name="EpiKnet">
           <modules>
             <module name="\\Erebot\\Module\\AutoIdent">
               <!--
                 "NickServ" is called "Themis" on EpiKnet.
                 Both names are whitelisted here.
               -->
               <param name="nickserv" value="Themis NickServ" />
               <param name="password" value="my-secret-password" />

               <!--
                 EpiKnet's nickname service is configured to use french
                 by default to communicate.
                 Therefore, The pattern needed to match the warning about
                 registered nicknames has been translated to french.

                 Of course, a pattern like ".*(enregistré|registered).*"
                 would also work and would match both the french
                 and english variants of the message.
               -->
               <param name="pattern"  value=".*enregistré.*" />
             </module>
           </modules>

           <servers>
              <server url="irc://irc.epiknet.org:6667/" />
           </servers>
        </network>
      </networks>
    </configuration>

Usage
=====

This module does not provide any command. Just add this module to your
configuration and you're done.


.. vim: ts=4 et
