GoF module
##########

Description
===========

This module provides  provides an IRC implementation
of `Days of Wonder's Gang of Four`_.

..  _`Days of Wonder's Gang of Four`:
    http://www.daysofwonder.com/gangoffour/en/


Configuration
=============

Options
-------

This module provides several configuration options.

..  table:: Options for \\Erebot\\Module\\GoF

    +---------------+-----------+-----------+-------------------------------+
    | Name          | Type      | Default   | Description                   |
    |               |           | value     |                               |
    +===============+===========+===========+===============================+
    | |trigger_gof| | string    | "gof"     | The command to use to start   |
    |               |           |           | or end a Gang of Four game.   |
    +---------------+-----------+-----------+-------------------------------+
    | limit         | integer   | 100       | The game will stop after this |
    |               |           |           | limit is reached. The .       |
    |               |           |           | player(s) with the lowest     |
    |               |           |           | score win the game.           |
    +---------------+-----------+-----------+-------------------------------+
    | pause_delay   | integer   | 5         | How many seconds does the bot |
    |               |           |           | wait after a round ends       |
    |               |           |           | before it starts the next     |
    |               |           |           | round.                        |
    +---------------+-----------+-----------+-------------------------------+
    | start_delay   | integer   | 20        | How many seconds does the bot |
    |               |           |           | wait after enough players     |
    |               |           |           | have joined the game before   |
    |               |           |           | the game actually starts.     |
    +---------------+-----------+-----------+-------------------------------+
    | |trigger_ca|  | string    | "ca"      | The command to use to show    |
    |               |           |           | how many cards each player    |
    |               |           |           | has in his hand.              |
    +---------------+-----------+-----------+-------------------------------+
    | |trigger_cd|  | string    | "cd"      | The command to use to show    |
    |               |           |           | the last discarded combo.     |
    +---------------+-----------+-----------+-------------------------------+
    | |trigger_ch|  | string    | "ch"      | The command to use to choose  |
    |               |           |           | a card to give to the loser   |
    |               |           |           | of the previous round.        |
    +---------------+-----------+-----------+-------------------------------+
    | |trigger_jo|  | string    | "jo"      | The command to use to join a  |
    |               |           |           | game after it has been        |
    |               |           |           | created.                      |
    +---------------+-----------+-----------+-------------------------------+
    | |trigger_od|  | string    | "od"      | The command to use to show    |
    |               |           |           | playing order.                |
    +---------------+-----------+-----------+-------------------------------+
    | |trigger_pa|  | string    | "pa"      | The command to use to pass    |
    |               |           |           | a turn.                       |
    +---------------+-----------+-----------+-------------------------------+
    | |trigger_pl|  | string    | "pl"      | The command to use to play a  |
    |               |           |           | combination of cards. [#]_    |
    +---------------+-----------+-----------+-------------------------------+
    | |trigger_sc|  | string    | "sc"      | The command to use to display |
    |               |           |           | the current scores.           |
    +---------------+-----------+-----------+-------------------------------+
    | |trigger_ti|  | string    | "ti"      | The command to use to show    |
    |               |           |           | for how long a game has been  |
    |               |           |           | running.                      |
    +---------------+-----------+-----------+-------------------------------+
    | |trigger_tu|  | string    | "tu"      | The command to use to show    |
    |               |           |           | whose player's turn it is.    |
    +---------------+-----------+-----------+-------------------------------+

..  |trigger_gof|   replace:: trigger_create
..  |trigger_ca|    replace:: trigger_show_cards
..  |trigger_cd|    replace:: trigger_show_discard
..  |trigger_ch|    replace:: trigger_choose
..  |trigger_jo|    replace:: trigger_join
..  |trigger_od|    replace:: trigger_show_order
..  |trigger_pa|    replace:: trigger_pass
..  |trigger_pl|    replace:: trigger_play
..  |trigger_sc|    replace:: trigger_show_scores
..  |trigger_ti|    replace:: trigger_show_time
..  |trigger_tu|    replace:: trigger_show_turn

..  warning::
    All triggers should be written without any prefixes. Moreover, triggers
    should only contain alphanumeric characters.

..  [#] Valid combinations include:

    -   a single card, eg. ``g1``
    -   a pair, eg. ``g1y1``
    -   three of a kind, eg. ``g1y1r1``
    -   a straight, eg. ``m1r2y3g4r5``
    -   a flush, eg. ``g1g1g2g2g7``
    -   a full house, eg. ``g1y1r1g2g2``
    -   a straight flush, eg. ``g1g2g3g4g5``
    -   a gang, eg. ``g1g1y1y1`` for the lowest possible gang (a gang of four),
        up to ``g1g1y1y1r1r1m1`` for the highest gang (a gang of seven).

    See the official rules on `Days of Wonder's website`_ for more information
    on when you may play a given combination.

..  _`Days of Wonder's website`:
    http://www.daysofwonder.com/gangoffour/en/content/rules/


Example
-------

Here, we enable the Gang of Four module at the general configuration level.
Therefore, the game will be available on all networks/servers/channels.
Of course, you can use a more restrictive configuration file if it suits
your needs better.

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
          Configure the module:
          - the game will be started using the "!gangof4" command.
          - the game will start 2 minutes (120 seconds) after 3 players
            join it (to give time for a fourth player to join the game).
        -->
        <module name="\\Erebot\\Module\\GoF">
          <param name="trigger_create" value="gangof4" />
          <param name="start_delay"    value="120" />
        </module>
      </modules>
    </configuration>



Usage
=====

This section assumes default values are used for all triggers.
Please refer to :ref:`configuration options <configuration options>`
for more information on how to customize triggers.

Also, knowledge of the rules for the Gang of Four game is assumed.
The full rules for the game can be found (in multiple languages) on
`Days of Wonder's website`_.


Provided commands
-----------------

This module provides the following commands:

..  table:: Commands provided by \\Erebot\\Module\\GoF

    +---------------------------+-------------------------------------------+
    | Command                   | Description                               |
    +===========================+===========================================+
    | ``!gof``                  | Start a new Gang of Four game.            |
    +---------------------------+-------------------------------------------+
    | ``!gof cancel`` or        | Stop a currently running Gang of Four     |
    | ``!gof end`` or           | game. Can only be used by the person who  |
    | ``!gof off`` or           | started the game in the first place.      |
    | ``!gof stop``             |                                           |
    +---------------------------+-------------------------------------------+
    | ``ca``                    | Display the number of remaining cards in  |
    |                           | each player's hand.                       |
    +---------------------------+-------------------------------------------+
    | ``cd``                    | Display the last played (and thus         |
    |                           | discarded) card.                          |
    +---------------------------+-------------------------------------------+
    | :samp:`ch {card}`         | Choose a card to give to the loser of the |
    |                           | previous round. Can only be used at the   |
    |                           | end of a round by the winner of the       |
    |                           | previous round.                           |
    +---------------------------+-------------------------------------------+
    | ``jo``                    | Join a currently running Uno game.        |
    +---------------------------+-------------------------------------------+
    | ``od``                    | Display playing order.                    |
    +---------------------------+-------------------------------------------+
    | ``pa``                    | Pass instead of playing.                  |
    +---------------------------+-------------------------------------------+
    | :samp:`pl {combo}`        | Play the given *combo* of cards (see      |
    |                           | mnemonics below for the syntax used).     |
    |                           | Eg. ``pl g1y1`` to play a pair of 1s,     |
    |                           | containing a "Green 1" and a "Yellow 1".  |
    +---------------------------+-------------------------------------------+
    | ``sc``                    | Display the score of each player involved |
    |                           | in the current game.                      |
    +---------------------------+-------------------------------------------+
    | ``ti``                    | Display information on how long the       |
    |                           | current game has been running for.        |
    +---------------------------+-------------------------------------------+
    | ``tu``                    | Display the name of the player whose turn |
    |                           | it is to play.                            |
    +---------------------------+-------------------------------------------+


Mnemonics for cards
-------------------

The general format used to refer to cards is the first letter of the card's
color (in english) followed by the card's figure.

The following colors are available:

-   **g**\ reen
-   **y**\ ellow
-   **r**\ ed
-   **m**\ ulti

The following figures are available:

-   Numbers from 1 to 10 (inclusive).
-   Phoenixes.
-   Dragon.

The following table lists a few examples of valid mnemnics with the full name
of the card they refer to:

..  table:: Valid mnemonics for cards

    +-----------+-----------------------+
    | Mnemonic  | Actual card           |
    +===========+=======================+
    | ``g1``    | "Green 1"             |
    +-----------+-----------------------+
    | ``m1``    | "Multicolored 1"      |
    +-----------+-----------------------+
    | ``r10``   | "Red 10"              |
    +-----------+-----------------------+
    | ``gp``    | "Green Phoenix"       |
    +-----------+-----------------------+
    | ``yp``    | "Yellow Phoenix"      |
    +-----------+-----------------------+
    | ``rd``    | "Red Dragon"          |
    +-----------+-----------------------+

Not all combinations of colors and figures are valid. In particular, there is
only one multicolored figure, one red dragon, a green and a yellow phoenix.


.. vim: ts=4 et
