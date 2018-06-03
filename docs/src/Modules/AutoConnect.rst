AutoConnect module
##################

Description
===========

This module makes the bot connect to some IRC networks automatically upon startup.

Any network configuration with this module loaded (and active) will
be marked as requiring auto-connection upon startup.


Configuration
=============

Options
-------

This module offers no configuration options.


Example
-------

The recommended way to use this module is to have it loaded at the general
configuration level and to disable it only for specific networks.

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
            <module name="\\Erebot\\Module\\AutoConnect" />
        </modules>

        <networks>
            <!-- The bot WILL NOT auto-connect to this network upon startup. -->
            <network name="localhost">
                <modules>
                    <!--
                        Disable auto-connection for the local IRC server,
                        which is used only for debugging purposes.
                    -->
                    <module name="\\Erebot\\Module\\AutoConnect" active="false" />
                </modules>

                <servers>
                    <server url="irc://localhost:6667/" />
                </servers>
            </network>

            <!-- The bot WILL auto-connect to this network upon startup. -->
            <network name="IIEns">
                <servers>
                    <server url="irc://irc.iiens.net:6667/" />
                </servers>
            </network>
        </networks>
    </configuration>


Usage
=====

This module does not provide any command. Just add this module to your
configuration and you're done.


.. vim: ts=4 et
