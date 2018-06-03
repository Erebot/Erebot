LagChecker module
#################

..  contents::
    :local:

Description
===========

This module detects lag and automatically disconnects and then reconnects
the bot when the lag is above a certain threshold.


Configuration
=============

Options
-------

This module provides several configuration options.

..  table:: Options for \\Erebot\\Module\\LagChecker

    +-----------+-----------+-----------+-----------------------------------+
    | Name      | Type      | Default   | Description                       |
    |           |           | value     |                                   |
    +===========+===========+===========+===================================+
    | check     | integer   | n/a       | The delay (in seconds) between    |
    |           |           |           | two consecutive lag checks.       |
    |           |           |           | This should not be set to high to |
    |           |           |           | avoir risking a disconnection due |
    |           |           |           | to a timeout between checks (thus |
    |           |           |           | defeating the whole point of this |
    |           |           |           | module). However, it should not   |
    |           |           |           | be set too low either to avoid    |
    |           |           |           | flooding the IRC server with lag  |
    |           |           |           | checks. A delay of 60 seconds     |
    |           |           |           | (1 minute) seems reasonable.      |
    +-----------+-----------+-----------+-----------------------------------+
    | reconnect | integer   | n/a       | The delay the bot will wait after |
    |           |           |           | disconnecting from an IRC server  |
    |           |           |           | due to high latency before an     |
    |           |           |           | attempt is made to reconnect.     |
    |           |           |           | This is meant to delay operations |
    |           |           |           | a little so that the latency gets |
    |           |           |           | lower and to implement some kind  |
    |           |           |           | of "reconnection throttling".     |
    |           |           |           | You should probably set this to a |
    |           |           |           | value higher or equal to the      |
    |           |           |           | value for the ``check`` option.   |
    +-----------+-----------+-----------+-----------------------------------+
    | timeout   | integer   | n/a       | The number of seconds the bot     |
    |           |           |           | will wait for a response after it |
    |           |           |           | sends a periodic latency check.   |
    |           |           |           | If no response is received by     |
    |           |           |           | then, this module will consider   |
    |           |           |           | the connection to be unresponsive |
    |           |           |           | and will (possibly forcefully)    |
    |           |           |           | disconnect the bot from the       |
    |           |           |           | associated IRC server.            |
    |           |           |           | You may set this to a low value   |
    |           |           |           | on broadband connections (eg.     |
    |           |           |           | 5 seconds).                       |
    +-----------+-----------+-----------+-----------------------------------+
    | trigger   | string    | "lag"     | The command to use to ask the bot |
    |           |           |           | about the current lag.            |
    +-----------+-----------+-----------+-----------------------------------+

..  note::
    Depending on your connection, setting the value of the ``timeout`` option
    to a value that is too low may result in excessive cycles of disconnections
    and reconnections from/to IRC servers.

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
      version="0.20"
      language="fr-FR"
      timezone="Europe/Paris">

      <modules>
        <!-- Other modules ignored for clarity. -->

        <module name="\\Erebot\\Module\\LagChecker">
          <param name="check"     value="120" />
          <param name="timeout"   value="15" />
          <param name="reconnect" value="60" />
          <param name="trigger"   value="latency" />
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

This module provides the following commands:

..  table:: Commands provided by \\Erebot\\Module\\LagChecker

    +---------------------------+-------------------------------------------+
    | Command                   | Description                               |
    +===========================+===========================================+
    | ``!lag``                  | Displays the current lag, as measured     |
    |                           | during the bot's last check.              |
    +---------------------------+-------------------------------------------+


.. vim: ts=4 et
