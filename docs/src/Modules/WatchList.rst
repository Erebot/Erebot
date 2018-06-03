WatchList module
################

Description
===========

This module can be used to handle a WATCH list, ie. track when
some user (dis)connects.

The WATCH list is implemented using either the
``ISON`` command (see :rfc:`2812#section-4.9`) or the
``WATCH`` extension (see http://docs.dal.net/docs/misc.html#4).
Exactly which mechanism is used depends on what the IRC server supports.


Configuration
=============

Options
-------

This module provides only one configuration option.

..  table:: Options for \\Erebot\\Module\\WatchList

    +---------------+--------+---------------+------------------------------+
    | Name          | Type   | Default value | Description                  |
    +===============+========+===============+==============================+
    | nicks         | string | ""            | A space-separated list of    |
    |               |        |               | nicknames for which the bot  |
    |               |        |               | will receive notifications.  |
    +---------------+--------+---------------+------------------------------+


Example
-------

The recommended way to use this module is to have it loaded at the general
configuration level and to disable it only for specific networks, if needed.

..  parsed-code:: xml

    <?xml version="1.0"?>
    <configuration
      xmlns="http://localhost/Erebot/"
      version="0.20"
      language="fr-FR"
      timezone="Europe/Paris">

      <modules>
        <!-- Other modules ignored for clarity. -->

        <!--
            The bot will receive notifications whenever "Foo" or "Bar"
            joins/quits the IRC server.
        -->
        <module name="\\Erebot\\Module\\WatchList">
          <param name="nicks" value="Foo Bar"/>
        </module>
      </modules>
    </configuration>


Usage
=====

This module does not provide any command. Just add this module to your
configuration and you're done.


.. vim: ts=4 et
