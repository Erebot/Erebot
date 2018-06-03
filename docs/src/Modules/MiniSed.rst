MiniSed module
##############

Description
===========

This module provides basic substitutions in a :manpage:`sed(1)`-like fashion.


Configuration
=============

Options
-------

This module does not provide any configuration options.


Example
-------

In this example, we simply make this module available on all networks
and channels the bot is connected to.

..  parsed-code:: xml

    <?xml version="1.0"?>
    <configuration
      xmlns="http://localhost/Erebot/"
      version="0.20"
      language="fr-FR"
      timezone="Europe/Paris">

      <modules>
        <!-- Other modules ignored for clarity. -->

        <module name="\Erebot\Module\MiniSed"/>
      </modules>
    </configuration>


Usage
=====

Provided commands
-----------------

This module provides the following commands:

..  table:: Commands provided by \Erebot\Module\MiniSed

    +-----------+-----------------------------------------------------------+
    | Command   | Description                                               |
    +===========+===========================================================+
    | |cmd1| or | Search and replace *pattern* with *replacement string* in |
    | |cmd2|,   | the last sentence written in the current channel. This is |
    | etc.      | a very basic implementation of sed's ``s///`` command.    |
    |           | Flags cannot be used with this implementation.            |
    +-----------+-----------------------------------------------------------+

Example
-------

..  sourcecode:: irc

    20:37:25 <+Foobar> this is kewl
    20:37:27 <+Foobar> s/kewl/so cool/
    20:37:27 < Erebot> this is so cool

..  |cmd1| replace:: :samp:`s/{pattern}/{replacement string}/`
..  |cmd2| replace:: :samp:`s@{pattern}@{replacement string}@`


.. vim: ts=4 et
