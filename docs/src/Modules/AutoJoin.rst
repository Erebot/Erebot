AutoJoin module
###############

Description
===========

This module makes the bot join some IRC channel automatically upon connecting
to a server.


Configuration
=============

Options
-------

This module offers no configuration options.


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
            <server url="irc://localhost:6667/" />
          </servers>

          <!--
            After it successfully connects to the IRC server,
            the bot will automatically join the #Erebot channel.
          -->
          <channel name="#Erebot">
            <modules>
              <module name="\Erebot\Module\AutoJoin" />
            </modules>
          </channel>
        </network>
      </networks>
    </configuration>


Usage
=====

This module does not provide any command. Just add this module to your
configuration and you're done.


.. vim: ts=4 et
