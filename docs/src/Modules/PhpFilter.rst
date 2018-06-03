PhpFilter module
################

Description
===========

This module transforms its input using a `PHP stream filter`_.

..  _`PHP stream filter`:
    http://php.net/filters


Configuration
=============

Options
-------

This module provides several configuration options.

..  table:: Options for \Erebot\Module\PhpFilter

    +-----------+-----------+-----------+-----------------------------------+
    | Name      | Type      | Default   | Description                       |
    |           |           | value     |                                   |
    +===========+===========+===========+===================================+
    | trigger   | string    | "filter"  | The command to use to ask the bot |
    |           |           |           | to transform a text using a       |
    |           |           |           | filter.                           |
    +-----------+-----------+-----------+-----------------------------------+
    | whitelist | string    | "|list|"  | A whitelist of allowed filters,   |
    |           |           |           | separated by commas. Wildcards    |
    |           |           |           | supported.                        |
    +-----------+-----------+-----------+-----------------------------------+

..  warning::
    The trigger should only contain alphanumeric characters (in particular,
    do not add any prefix, like "!" to that value).

Example
-------

In this example, we configure the bot to allow only a few string filters
to be used (toupper which turns all letters into uppercase, tolower which
turns them into lowercase letters and rot13 which applies a 13 letters
rotation to text, much like Caesar's cipher).

..  parsed-code:: xml

    <?xml version="1.0"?>
    <configuration
      xmlns="http://localhost/Erebot/"
      version="0.20"
      language="fr-FR"
      timezone="Europe/Paris">

      <modules>
        <!-- Other modules ignored for clarity. -->

        <module name="\Erebot\Module\PhpFilter">
          <param name="whitelist" value="string.toupper,string.tolower,string.rot13" />
        </module>
      </modules>
    </configuration>


..  |list| replace:: string.*,convert.*


Usage
=====

This section assumes default values are used for all triggers.
Please refer to :ref:`configuration options <configuration options>`
for more information on how to customize triggers.


Provided commands
-----------------

This module provides the following commands:

..  table:: Commands provided by \Erebot\Module\PhpFilter

    +---------------------------+-------------------------------------------+
    | Command                   | Description                               |
    +===========================+===========================================+
    | ``!filter``               | Displays available filters and a quick    |
    |                           | usage note.                               |
    +---------------------------+-------------------------------------------+
    | |filter|                  | Displays the result of using the given    |
    |                           | *filter* on the given *input*.            |
    +---------------------------+-------------------------------------------+

Example
-------

..  sourcecode:: irc

    20:37:25 <+Foobar> !filter string.rot13 V ybir CUC!
    20:37:27 < Erebot> string.rot13: I love PHP!

..  |filter| replace:: :samp:`!filter {filter} {input}`


.. vim: ts=4 et
