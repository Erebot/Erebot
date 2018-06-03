TriggerRegistry module
######################

Description
===========

This module can be used to register triggers, ie. commands that can be used
to interact with the bot.


Configuration
=============

Options
-------

This module does not provide any configuration options.


Example
-------

In this example, we just make sure this module is loaded.

..  parsed-code:: xml

    <?xml version="1.0"?>
    <configuration
      xmlns="http://localhost/Erebot/"
      version="0.20"
      language="fr-FR"
      timezone="Europe/Paris">

      <modules>
        <!-- Other modules ignored for clarity. -->

        <module name="\Erebot\Module\TriggerRegistry"/>
      </modules>
    </configuration>


Usage
=====

This module does not provide any command.

This module keeps a registry of triggers.
This prevents modules from generating conflicts with each other
for a given command.

You should rely on this module to write your own to avoid conflicts.


.. vim: ts=4 et
