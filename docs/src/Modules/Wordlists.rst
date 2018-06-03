Wordlists module
################

Description
===========

This module provides lists of words for other modules.


Configuration
=============

Options
-------

This module provides a single configuration option.

..  table:: Options for \\Erebot\\Module\\Wordlists

    +---------------+--------+---------------+------------------------------+
    | Name          | Type   | Default value | Description                  |
    +===============+========+===============+==============================+
    | policy        | string | '*' (allow    | A space-separated list of    |
    |               |        | any installed | (case-insensitive) patterns  |
    |               |        | wordlist to   | indicating the names of the  |
    |               |        | be used)      | wordlists that may or may    |
    |               |        |               | not be used.                 |
    |               |        |               |                              |
    |               |        |               | You may use ``?`` and ``*``  |
    |               |        |               | as wildcards to match        |
    |               |        |               | exactly one character and    |
    |               |        |               | one or more characters       |
    |               |        |               | (respectively). You may also |
    |               |        |               | prefix a pattern with ``!``  |
    |               |        |               | to reject wordlists that     |
    |               |        |               | match that pattern.          |
    |               |        |               |                              |
    |               |        |               | The patterns are evaluated   |
    |               |        |               | in the order they were       |
    |               |        |               | given. Wordlists that do not |
    |               |        |               | match any of the patterns    |
    |               |        |               | are allowed for use (as if   |
    |               |        |               | they had matched an implicit |
    |               |        |               | ``*`` pattern).              |
    +---------------+--------+---------------+------------------------------+

Example
-------

The recommended way to use this module is to have it loaded at the general
configuration level and to disable it only for specific networks.
In the following example, the module was set up to allow the use of wordlists
whose name starts with 'pkmn', but reject any other wordlist.

..  parsed-code:: xml

    <?xml version="1.0" ?>
    <configuration
      xmlns="http://localhost/Erebot/"
      version="..."
      language="fr-FR"
      timezone="Europe/Paris"
      commands-prefix="!">

      <modules>
        <!-- Other modules ignored for clarity. -->

        <!--
            Allow wordlists whose name starts with 'pkmn'
            and deny the use of any other wordlist.
        -->
        <module name="\\Erebot\\Module\\Wordlists">
            <param name="policy" value="pkmn* !*"/>
        </module>
      </modules>
    </configuration>


Usage
=====

This module does not provide any command. It is not meant as a module that
users may call directly. Instead, it is meant to be used by other modules
that need some lists of words to work properly.


.. vim: ts=4 et
