Countdown module
################

..  contents::
    :local:

Description
===========

This module provides a game called "Countdown" and inspired
by the TV show with the same name.
Given a set of numbers and a target number, contestants have 60 seconds
to propose formulae that produce the target number using only numbers
from the given set and the four basic operators (+, -, \*, /).

The winner is the one whose result is the closest to the target number.


Configuration
=============

Options
-------

This module provides several configuration options.

..  table:: Options for \\Erebot\\Module\\Countdown

    +---------------+-----------+-------------------+-------------------------------+
    | Name          | Type      | Default value     | Description                   |
    +===============+===========+===================+===============================+
    | allowed       | string    | "1 2 3 4 5 6 7 8  | A space-separated list of     |
    |               |           | 9 10 25 50 75     | numbers from which the bot    |
    |               |           | 100"              | will randomly select values   |
    |               |           |                   | meant to help contestants.    |
    +---------------+-----------+-------------------+-------------------------------+
    | delay         | integer   | 60                | How many seconds contestants  |
    |               |           |                   | have before the game ends.    |
    +---------------+-----------+-------------------+-------------------------------+
    | maximum       | integer   | 999               | The target number may not     |
    |               |           |                   | exceed this value.            |
    +---------------+-----------+-------------------+-------------------------------+
    | minimum       | integer   | 100               | The target number may not be  |
    |               |           |                   | less than this value.         |
    +---------------+-----------+-------------------+-------------------------------+
    | numbers       | integer   | 7                 | How many numbers will be      |
    |               |           |                   | given to help contestants.    |
    +---------------+-----------+-------------------+-------------------------------+
    | solver        | boolean   | FALSE             | Whether the bot should try to |
    |               |           |                   | solve the game or not.        |
    |               |           |                   | Enabling this option has a    |
    |               |           |                   | great impact on the bot's     |
    |               |           |                   | responsiveness. Only use it   |
    |               |           |                   | if you understand the         |
    |               |           |                   | consequences.                 |
    +---------------+-----------+-------------------+-------------------------------+
    | solver_class  | string    | "|solver_class|"  | The class to use to solve the |
    |               |           |                   | game (useless unless the      |
    |               |           |                   | ``solver`` option is set to   |
    |               |           |                   | TRUE).                        |
    +---------------+-----------+-------------------+-------------------------------+
    | trigger       | string    | "countdown"       | The command to use (without   |
    |               |           |                   | any prefix) to start a new    |
    |               |           |                   | Countdown game. This text     |
    |               |           |                   | should only contain           |
    |               |           |                   | alpha-numeric characters.     |
    +---------------+-----------+-------------------+-------------------------------+


Example
-------

In this example, we enable the module at the general configuration level.
Therefore, the game will be available on all networks/servers/channels.
Of course, you can use a more restrictive configuration file if it better
suits your needs.

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
          Configure the module:
          - the game will be started using the "!count" command.
          - contestants will have 2 minutes to make suggestions.
        -->
        <module name="\\Erebot\\Module\\Countdown">
          <param name="trigger" value="count" />
          <param name="delay"   value="120" />
        </module>
      </modules>
    </configuration>

..  |solver_class| replace:: \\Erebot\\Module\\Countdown\\Solver


Usage
=====

This section assumes default values are used for all triggers.
Please refer to :ref:`configuration options <configuration options>`
for more information on how to customize triggers.


Provided commands
-----------------

This module provides the following commands:

..  table:: Commands provided by |project|

    +-------------------+---------------------------------------------------+
    | Command           | Description                                       |
    +===================+===================================================+
    | ``!countdown``    | Starts a new game. If a game is already running,  |
    |                   | displays the target number, usable numbers and    |
    |                   | current leader of the game.                       |
    +-------------------+---------------------------------------------------+

Once a new game has been created, contestants can make propositions directly
by sending a new formula in the channel the game was started in.

The four basic operators (+ - / \*) and parenthesis may be used in the formula.


Examples
--------

The listing below shows a game played in french.

..  sourcecode:: irc

    17:29:20 < foobar> !countdown
    17:29:20 < Erebot> Une nouvelle partie des Chiffres et des Lettres commence. Vous devez obtenir 965 grâce aux nombres
                       suivants : 4, 2, 75, 25, 10, 7 & 8. Vous avez 60 secondes pour faire des propositions.
    17:29:31 < foobar> (75+25-4)*10
    17:29:31 < Erebot> Félicitations foobar ! Vous êtes le plus proche avec 960.
    17:29:37 < foobar> (75+25-4)*10+7-2
    17:29:37 < Erebot> BINGO ! foobar a obtenu 965 avec cette formule : (75+25-4)*10+7-2.


.. vim: ts=4 et
