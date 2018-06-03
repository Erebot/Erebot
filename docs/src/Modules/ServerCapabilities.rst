ServerCapabilities module
#########################

Description
===========

This module helps determine the features supported by an IRC server
(eg. channel modes, extensions, etc.).


Configuration
=============

Options
-------

This module offers no configuration options.


Example
-------

The recommended way to use this module is to have it loaded at the general
configuration level and to disable it only for specific networks if needed.

..  parsed-code:: xml

    <?xml version="1.0"?>
    <configuration
      xmlns="http://localhost/Erebot/"
      version="0.20"
      language="fr-FR"
      timezone="Europe/Paris">

      <modules>
        <!-- Other modules ignored for clarity. -->

        <module name="\\Erebot\\Module\\ServerCapabilities"/>
      </modules>
    </configuration>


Usage
=====

This module does not provide any command. Just add this module to your
configuration and you're done.


.. vim: ts=4 et
