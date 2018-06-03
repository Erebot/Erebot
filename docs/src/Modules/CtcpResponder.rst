CtcpResponder module
####################

Description
===========

This module responds to :abbr:`CTCP (Client-To-Client Protocol)` requests.

The following request types are currently supported by this module:

*   ``FINGER``
*   ``VERSION``
*   ``SOURCE``
*   ``CLIENTINFO``
*   ``ERRMSG``
*   ``PING``
*   ``TIME``

For each type of CTCP requests, you can choose to use either the default
response to this request (as provided by this module), to use a static string
or you may also suppress the response entirely (meaning the request gets
ignored entirely).


Configuration
=============

Options
-------

This module provides several configuration options.

..  table:: Options for \Erebot\Module\CtcpResponder

    +-------------------+-----------+-----------+---------------------------+
    | Name              | Type      | Default   | Description               |
    |                   |           | value     |                           |
    +===================+===========+===========+===========================+
    | allow_chan_ctcp   | boolean   | TRUE      | Whether the bot should    |
    |                   |           |           | respond to CTCP requests  |
    |                   |           |           | sent to IRC channels.     |
    |                   |           |           | If set to FALSE, the bot  |
    |                   |           |           | will only respond to      |
    |                   |           |           | requests which are        |
    |                   |           |           | directly sent to it.      |
    +-------------------+-----------+-----------+---------------------------+
    | ctcp_*            | string    | See notes | The static text to use in |
    |                   |           |           | a reply to a CTCP request |
    |                   |           |           | of type "*".              |
    +-------------------+-----------+-----------+---------------------------+


Notes:

    *   You may use several "ctcp_*" parameters for the different CTCP requests
        you want the bot to handle.
    *   Using an empty string as the value for "ctcp_*" makes the bot ignore
        CTCP requests of that type.
    *   The "*" part of "ctcp_*" is case-sensitive.
        The usual CTCP requests have their name written in uppercase
        (``VERSION``, ``TIME``, ``PING``, ...).
    *   By default, this module handles a few generic CTCP requests,
        listed in the table below:

..  table:: Default responses to the usual CTCP requests

    +-------------------+-----------------------+---------------------------+
    | CTCP type         | Default response      | Example                   |
    +===================+=======================+===========================+
    | FINGER            | Information about who | clicky@madlax (started 23 |
    |                   | started the bot, the  | secondes ago)             |
    |                   | name of the machine   |                           |
    |                   | it is running on and  |                           |
    |                   | its uptime.           |                           |
    +-------------------+-----------------------+---------------------------+
    | VERSION           | The bot's current     | Erebot v0.5.0-dev1 /      |
    |                   | version, as well as   | PHP 5.3.2-1ubuntu4.5 /    |
    |                   | PHP's version and     | Linux 2.6.32-27-generic   |
    |                   | information on the    |                           |
    |                   | operating system the  |                           |
    |                   | bot is running on     |                           |
    |                   | (name and version).   |                           |
    +-------------------+-----------------------+---------------------------+
    | SOURCE            | URL to use to         | http://pear.erebot.net/   |
    |                   | download the bot      |                           |
    +-------------------+-----------------------+---------------------------+
    | CLIENTINFO        | URL to use to get     | http://www.erebot.net/    |
    |                   | information on the    |                           |
    |                   | bot.                  |                           |
    +-------------------+-----------------------+---------------------------+
    | ERRMSG            | The latest error      | Success                   |
    |                   | message detected by   |                           |
    |                   | the bot or "Success". |                           |
    +-------------------+-----------------------+---------------------------+
    | PING              | Exactly the same text | n/a  |
    |                   | as in the request     |                           |
    |                   | (eg. some timestamp). |                           |
    +-------------------+-----------------------+---------------------------+
    | TIME              | The current date and  | Thu, 21 Dec 2000 16:01:07 |
    |                   | time where the bot is | +0200                     |
    |                   | running, using the    |                           |
    |                   | format from           |                           |
    |                   | :rfc:`2822`.          |                           |
    +-------------------+-----------------------+---------------------------+



Example
-------

Here, we make the bot ignore ``FINGER`` and ``ERRMSG`` requests, we replace
the default ``VERSION`` reply and we add a response to a custom type of CTCP
called ``USERINFO`` (which is in fact a type most IRC clients support).


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
          - ignore FINGER/ERRMSG requests.
          - replace VERSION string.
          - add custom CTCP type AWAKENING.
        -->
        <module name="Erebot_Module_CtcpResponder">
          <param name="ctcp_FINGER" value="" />
          <param name="ctcp_ERRMSG" value="" />
          <param name="ctcp_VERSION"  value="Erebot v0.0.1-alpha2" />
          <param name="ctcp_AWAKENING" value="Elda Taruta" />
        </module>
      </modules>
    </configuration>


Usage
=====

This module does not provide any command. Just add this module to your
configuration and you're done.

After that, the bot will automatically start responding to CTCP requests.

Examples
--------

The listing below shows examples of CTCP requests/responses.

..  sourcecode:: irc

    20:19:16 [ctcp(Erebot)] FINGER
    20:19:16 CTCP FINGER reply from Erebot: foo@localhost (démarré il y a 7 heures, 47 minutes, 4 secondes)
    20:19:27 [ctcp(Erebot)] VERSION
    20:19:28 CTCP VERSION reply from Erebot: Erebot v0.5.1 / PHP 5.3.9 / Linux 2.6.38.2-grsec-xxxx-grs-ipv6-64
    20:19:32 [ctcp(Erebot)] SOURCE
    20:19:32 CTCP SOURCE reply from Erebot: http://pear.erebot.net/
    20:19:35 [ctcp(Erebot)] CLIENTINFO
    20:19:35 CTCP CLIENTINFO reply from Erebot: http://www.erebot.net/
    20:19:42 [ctcp(Erebot)] ERRMSG
    20:19:42 CTCP ERRMSG reply from Erebot: Success
    20:19:49 [ctcp(Erebot)] PING foo
    20:19:50 CTCP PING reply from Erebot: foo
    20:19:52 [ctcp(Erebot)] TIME
    20:19:52 CTCP TIME reply from Erebot: Sun, 15 Jan 2012 20:19:52 +0100


.. vim: ts=4 et
