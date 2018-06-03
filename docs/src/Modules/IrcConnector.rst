IrcConnector module
###################

Description
===========

This module contains the code required to make Erebot connect to IRC(S) servers.


Configuration
=============

Options
-------

This module offers several configuration options.

..  table:: Options for \\Erebot\\Module\\IrcConnector

    +----------+--------+---------------+-------------------------------------+
    | Name     | Type   | Default value | Description                         |
    +==========+========+===============+=====================================+
    | hostname | string | "Erebot"      | The bot's hostname. This parameter  |
    |          |        |               | exists for historical reasons but   |
    |          |        |               | is actually ignored by IRC servers. |
    +----------+--------+---------------+-------------------------------------+
    | identity | string | "Erebot"      | The bot's identity. Servers will    |
    |          |        |               | usually only use it as a fallback   |
    |          |        |               | if the machine's real identity      |
    |          |        |               | cannot be determined.               |
    |          |        |               | This information is visible through |
    |          |        |               | ``WHOIS`` commands (which displays  |
    |          |        |               | information such as                 |
    |          |        |               | ``nick!ident@host``).               |
    +----------+--------+---------------+-------------------------------------+
    | nickname | string | n/a           | The nickname the bot will take when |
    |          |        |               | connecting to the server.           |
    +----------+--------+---------------+-------------------------------------+
    | password | string | ""            | The password required to connect to |
    |          |        |               | the current IRC server. By default, |
    |          |        |               | no password is needed to connect.   |
    +----------+--------+---------------+-------------------------------------+
    | realname | string | "Erebot"      | The bot's realname, sometimes also  |
    |          |        |               | known as the user's `GECOS field`_. |
    |          |        |               | This usually contains information   |
    |          |        |               | about the bot's administrator or    |
    |          |        |               | the bot's purpose for connecting.   |
    |          |        |               | This information is visible in      |
    |          |        |               | ``WHOIS`` commands.                 |
    +----------+--------+---------------+-------------------------------------+


Example
-------

..  parsed-code:: xml

    <?xml version="1.0" ?>
    <configuration
      xmlns="http://localhost/Erebot/"
      version="..."
      language="fr-FR"
      timezone="Europe/Paris"
      commands-prefix="!">

      <networks>
        <network name="localhost">
          <servers>
            <modules>
              <!--
                Makes the bot connect to the passworded IRC server at localhost
                under the nickname "Erebus" using "ASecretIsWhatMakesAWomanAWoman"
                as the password. The bot will use an insecure (plain-text) connection
                to the IRC server but will proceed to a security upgrade to make sure
                no one can eavesdrop on the connection.
              -->
              <module name="\\Erebot\\Module\\IrcConnector">
                  <param name="password" value="ASecretIsWhatMakesAWomanAWoman"/>
                  <param name="nickname" value="Erebus"/>
                  <param name="realname" value="I decide who must live or die"/>
              </module>
            </modules>

            <!--
                The "upgrade" parameter is responsible for the security upgrade.
                The IRC server the bot is connecting to must support the
                STARTTLS extension for this to work.
            -->
            <server url="irc://localhost:6667/?upgrade=1" />
          </servers>
        </network>
      </networks>
    </configuration>

.. _`GECOS field`:
    http://en.wikipedia.org/wiki/Gecos_field


Usage
=====

This module does not provide any command. Just add this module to your
configuration and you're done.

This module makes Erebot send credentials to IRC servers,
ie. the following sequence of commands:

    PASS password
    NICK nickname
    USER identity hostname server :Real name

..  note::

    The PASS command is only sent if a password was set in the configuration
    for that IRC server.

This module also supports forced "security upgrades" through the
`STARTTLS extension`_:
This feature can be enabled by adding an ``upgrade`` parameter in the
connection URL and setting it to a boolean truth value,
eg. ``irc://irc.example.com?upgrade=1``.

If you configure the bot to do a security upgrade, it will refuse to proceed
with the connection if the IRC server rejects the upgrade (to protect itself
against downgrade attacks).

..  _`STARTTLS extension`:
    http://wiki.inspircd.org/STARTTLS_Documentation.


.. vim: ts=4 et
