RateLimiter module
##################

Description
===========

This module provides means to can limit the bot's output rate
so as to prevent it from flooding IRC servers / other users.

The algorithm used is really basic and won't protect it against carefully
planned attacks (:abbr:`DoS (Denial of Service)`), but it is still better
than having nothing at all.


Configuration
=============

Options
-------

This module provides several configuration options.

..  table:: Options for \Erebot\Module\RateLimiter

    +-----------+-----------+-----------+-----------------------------------+
    | Name      | Type      | Default   | Description                       |
    |           |           | value     |                                   |
    +===========+===========+===========+===================================+
    | limit     | integer   | n/a       | How many messages may be sent to  |
    |           |           |           | a connection during a period of   |
    |           |           |           | time before the bot starts        |
    |           |           |           | throttling the output rate.       |
    +-----------+-----------+-----------+-----------------------------------+
    | period    | integer   | n/a       | Period of time (in seconds) which |
    |           |           |           | is used to control the output     |
    |           |           |           | rate.                             |
    +-----------+-----------+-----------+-----------------------------------+


Example
-------

In this example, we prevent the bot from sending out more than 4 messages
every 2 seconds.

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

        <module name="\Erebot\Module\RateLimiter">
          <param name="limit"  value="4" />
          <param name="period" value="2" />
        </module>
      </modules>
    </configuration>


Usage
=====

This module does not provide any command. Just add this module to your
configuration and you're done.


.. vim: ts=4 et
