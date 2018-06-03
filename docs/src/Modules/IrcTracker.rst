IrcTracker module
#################

..  contents::
    :local:

Description
===========

This module provides means for other modules to keep track of users:
users currently present on the same channels as the bot,
information about their status on those channels, etc.

This module can keep track of users across short disconnections using a set
of timers and a smart algorithm that can detect nickname takeover attempts.


Configuration
=============

Options
-------

This module offers no configuration options.


Example
-------

In this example, we make this module available to any network/channel
so that other modules can rely on it. This is the recommended way of using
this module.

..  parsed-code:: xml

    <?xml version="1.0" ?>
    <configuration
      xmlns="http://localhost/Erebot/"
      version="0.20"
      language="fr-FR"
      timezone="Europe/Paris">

      <modules>
        <!-- Other modules ignored for clarity. -->

        <module name="\\Erebot\\Module\\IrcTracker"/>
      </modules>
    </configuration>


Usage
=====

This module does not provide any command. Just add this module to your
configuration and you're done.


.. vim: ts=4 et
