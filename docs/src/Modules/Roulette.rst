Roulette module
###############

..  contents::
    :local:

Description
===========

This module provides a simple Russian Roulette game.


Configuration
=============

Options
-------

This module provides several configuration options.

..  table:: Options for \\Erebot\\Module\\Roulette

    +-----------+-----------+---------------+---------------------------------+
    | Name      | Type      | Default       | Description                     |
    |           |           | value         |                                 |
    +===========+===========+===============+=================================+
    | chambers  | integer   | 6             | How many chambers the cylinder  |
    |           |           |               | used in this game has.          |
    +-----------+-----------+---------------+---------------------------------+
    | trigger   | string    | "roulette"    | The command to use to pull the  |
    |           |           |               | trigger of the Roulette game.   |
    +-----------+-----------+---------------+---------------------------------+

..  warning::
    The trigger should only contain alphanumeric characters (in particular,
    do not add any prefix, like "!" to that value).

Example
-------

In this example, we configure the bot to check the latency every 2 minutes
(120 seconds). The IRC server has 15 seconds to respond to our latency checks.
If it does not answer by then, the bot will disconnect from that IRC server
and will wait for another full minute before attempting a reconnection.
Moreover, the command "!latency" can be used at any time to display
the current latency.

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

        <!--
          Configure the Roulette module:
          - the game will be started using the "!gun" command.
          - the gun will have 7 chambers.
        -->
        <module name="\\Erebot\\Module\\Roulette">
          <param name="chambers"    value="7" />
          <param name="trigger"     value="gun" />
        </module>
      </modules>
    </configuration>


Usage
=====

This section assumes default values are used for all triggers.
Please refer to :ref:`configuration options <configuration options>`
for more information on how to customize triggers.


Provided commands
-----------------

This module provides the following command:

..  table:: Commands provided by \\Erebot\\Module\\Roulette

    +---------------------------+-------------------------------------------+
    | Command                   | Description                               |
    +===========================+===========================================+
    | ``!roulette``             | Pulls the trigger on the Russian roulette |
    |                           | gun.                                      |
    +---------------------------+-------------------------------------------+

Example
-------

..  sourcecode:: irc

    15:34:45 < Foo> !roulette
    15:34:45 < Erebot> Foo: chamber 1 of 6 => +click+
    15:43:05 < Bar> !roulette
    15:43:06 < Erebot> Bar: chamber 2 of 6 => *BANG*
    15:43:06 * Erebot reloads


.. vim: ts=4 et
