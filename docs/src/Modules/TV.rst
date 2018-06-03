TV module
#########

..  contents::
    :local:

Description
===========

This module fetches information about TV timetables from the Internet.


Configuration
=============

Options
-------

This module provides several configuration options.

..  table:: Options for \\Erebot\\Module\\TV

    +---------------+-----------+---------------+-------------------------------+
    | Name          | Type      | Default       | Description                   |
    |               |           | value         |                               |
    +===============+===========+===============+===============================+
    | fetcher_class | string    | "|fetcher|"   | The class to use to retrieve  |
    |               |           |               | TV schedules. The default is  |
    |               |           |               | fine unless you have specific |
    |               |           |               | needs for something else.     |
    |               |           |               | This class should implement   |
    |               |           |               | the |fetcherIface|_           |
    |               |           |               | interface.                    |
    +---------------+-----------+---------------+-------------------------------+
    | |groups|      | string    | n/a           | A list of comma-separated TV  |
    |               |           |               | channel names, that form a    |
    |               |           |               | common group.                 |
    |               |           |               | The "*name*" part of the      |
    |               |           |               | parameter is used as the name |
    |               |           |               | of the group. This option may |
    |               |           |               | be used several times (with   |
    |               |           |               | varying "*name*" parts) to    |
    |               |           |               | create additional groups.     |
    |               |           |               | This parameter is optional.   |
    +---------------+-----------+---------------+-------------------------------+
    | default_group | string    | n/a           | If no TV channel has been     |
    |               |           |               | given to the bot when         |
    |               |           |               | requesting TV schedules, it   |
    |               |           |               | will retrieve schedules for   |
    |               |           |               | channels in this group        |
    |               |           |               | instead. This parameter is    |
    |               |           |               | optional.                     |
    +---------------+-----------+---------------+-------------------------------+
    | trigger       | string    | "tv"          | The command to use to display |
    |               |           |               | TV schedules.                 |
    +---------------+-----------+---------------+-------------------------------+

..  warning::
    The trigger should only contain alphanumeric characters (in particular,
    do not add any prefix, like "!" to that value).

Example
-------

In this example, we use a custom fetching class called ``My_TV_Fetcher``
and we define a group called "``hertzien``" which will contain the
7 basic french TV channels available using classical terrestrial TV technology.
This will also be the default group if the bot is queried for TV schedules
without any additional parameter.

..  parsed-code:: xml

    <?xml version="1.0"?>
    <configuration
      xmlns="http://localhost/Erebot/"
      version="0.20"
      language="fr-FR"
      timezone="Europe/Paris">

      <modules>
        <!-- Other modules ignored for clarity. -->

        <module name="\\Erebot\\Module\\TV">
          <!-- Override the default fetcher. -->
          <param name="fetcher_class"    value="My_TV_Fetcher" />
          <!-- Create a group called "hertzien". -->
          <param name="group_hertzien"   value="TF1,France2,France3,Canal+,France5,M6,Arte" />
          <!-- And use it as the default group. -->
          <param name="default_group"    value="hertzien" />
        </module>
      </modules>
    </configuration>


..  |fetcher|       replace:: Erebot_Module_TV_Fetcher
..  |fetcherIface|  replace:: Erebot_Module_TV_Fetcher_Interface
..  _`fetcherIface`:
    https://buildbot.erebot.net/doc/api/Erebot_Module_TV/html/404
..  |groups|        replace:: :samp:`group_{name}`


Usage
=====

This section assumes default values are used for all triggers.
Please refer to :ref:`configuration options <configuration options>`
for more information on how to customize triggers.


Provided commands
-----------------

This module provides the following commands:

..  table:: Commands provided by \\Erebot\\Module\\TV

    +---------------------------+-------------------------------------------+
    | Command                   | Description                               |
    +===========================+===========================================+
    | ``!tv``                   | Displays information about currently      |
    |                           | airing TV programs for the default        |
    |                           | channels group.                           |
    +---------------------------+-------------------------------------------+
    | :samp:`!tv {time}`        | Displays information about TV programs    |
    |                           | for the default channels group at the     |
    |                           | given *time*.                             |
    |                           | *time* may be given in either 12h or 24h  |
    |                           | format.                                   |
    +---------------------------+-------------------------------------------+
    | |tv|                      | Displays TV schedules for the given       |
    |                           | *channels* at the given *time*.           |
    |                           | You may also use a                        |
    |                           | :ref:`channel group <channel groups>`     |
    |                           | in place of *channels*.                   |
    |                           | *time* may be given in either 12h or 24h  |
    |                           | format.                                   |
    +---------------------------+-------------------------------------------+

..  _`channel groups`:
..  note::
    A list of valid channel groups can be retrieved using ``!help tv``.


Example
-------

..  sourcecode:: irc

    20:58:13 <@Clicky> !tv
    20:58:20 < Erebot> Programmes TV du January 17, 2012 8:58:00 PM : TF1 : Les experts : Manhattan (20:50 - 21:35) - France 2 : Le cinquième élément (20:35 -
                       22:35) - France 3 : Famille d'accueil (20:35 - 21:30) - Canal+ : Another Year (20:55 - 23:00) - France 5 : Une pieuvre nommée Bercy
                       (20:35 - 21:45) - Arte : L'effet domino (20:40 - 22:15) - M6 : Cauchemar en cuisine (20:50 - 22:05)

    20:58:29 <@Clicky> !tv 22h
    20:58:33 < Erebot> Programmes TV du January 17, 2012 10:00:00 PM : TF1 : Les experts : Manhattan (21:35 - 22:25) - France 2 : Le cinquième élément (20:35 -
                       22:35) - France 3 : Famille d'accueil (21:30 - 22:25) - Canal+ : Another Year (20:55 - 23:00) - France 5 : Le monde en face (21:45 -
                       22:15) - Arte : L'effet domino (20:40 - 22:15) - M6 : Cauchemar en cuisine (20:50 - 22:05)

    21:28:56 <@Clicky> !tv 23h TF1
    21:29:02 < Erebot> Programmes TV du January 17, 2012 11:00:00 PM : TF1 : Les experts : Manhattan (22:25 - 23:20)


..  |tv| replace:: :samp:`!tv {time} {channels...}`


.. vim: ts=4 et
