PingReply module
################

Description
===========

This module responds to ``PING`` messages with the equivalent ``PONG`` reply.


Configuration
=============

Options
-------

This module does not provide any configuration options.


Example
-------

In this example, we just make sure the module is available on all IRC
networks/servers so that the bot can stay connected.

..  parsed-code:: xml

    <?xml version="1.0"?>
    <configuration
      xmlns="http://localhost/Erebot/"
      version="0.20"
      language="fr-FR"
      timezone="Europe/Paris">

      <modules>
        <!-- Other modules ignored for clarity. -->

        <module name="\Erebot\Module\PingReply"/>
      </modules>
    </configuration>


Usage
=====

This module does not provide any command.

This modules simply replies to ``PING`` requests with a ``PONG`` message.
This is required on most IRC servers to avoid being disconnecting with a
``Ping timeout`` message.


.. vim: ts=4 et
