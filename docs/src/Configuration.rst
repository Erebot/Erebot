Erebot's configuration
======================

Erebot's configuration is stored in an XML file. This file is usually
called "``Erebot.xml``", though you could name it otherwise and use
the :option:`Erebot -c` option when running Erebot to point it to your file.

This file is composed of a hierarchy of settings, with inner sections
being able to inherit settings from outer sections.

The configuration is based on 3 structures:

* general settings
* logging configuration
* IRC-related settings

The general settings include things such as information on the current
timezone, the locale (language) the bot should use to display messages
in the console, etc.

The logging configuration is what defines what information the bot will
print to the logs, how the log are organized (do we store them in a syslog,
a database, or print them directly in the console) and how they appear
(how they're formatted).

Last but not least, the rest of the configuration is dedicated to IRC,
with information on what networks/servers the bot should contact,
what modules it should enable, etc.

The rest of this page gives information on available options and possible
values and is directly mapped to the actual hierarchy used in the XML
configuration file.

..  contents::

..  note::
    The tags may be used in any order. Therefore, one could swap the general
    configuration for `\<modules\>`_ with the configuration for the
    `\<logging\>`_ subsystem in the tree above.
    You still need to maintain the hierarchy however. Therefore, a
    `\<channels\>`_ or `\<servers\>`_ tag may only be a descendant
    of a `\<network\>`_ tag.


<configuration>
---------------

The `\<configuration\>`_ tag deals with settings related to the machine
Erebot is running on more than to IRC itself.

The following table lists attributes of this tag with their role.

..  table:: Valid attributes for the <configuration> tag

    +-----------+-----------+-----------+-----------------------------------+
    | Attribute | Default   | Required  | Role                              |
    |           | value     |           |                                   |
    +===========+===========+===========+===================================+
    | |prefix|  | n/a       | **Yes**   | The prefix used to identify       |
    |           |           |           | commands adressed to the bot.     |
    |           |           |           | Common values include: ``!``,     |
    |           |           |           | ``'``, ``@``, etc.                |
    +-----------+-----------+-----------+-----------------------------------+
    | daemon    | n/a       | No        | Whether to start the bot as a     |
    |           |           |           | daemon (``True``) or not          |
    |           |           |           | (``False``).                      |
    +-----------+-----------+-----------+-----------------------------------+
    | group     | n/a       | No        | Once started, assume that group's |
    |           |           |           | identity (given as a GID or as    |
    |           |           |           | a name).                          |
    +-----------+-----------+-----------+-----------------------------------+
    | language  | n/a       | **Yes**   | The preferred locale to use, as   |
    |           |           |           | an IETF language tag (eg.         |
    |           |           |           | ``en-US`` or ``fr-FR``). The      |
    |           |           |           | usual Linux format for locales    |
    |           |           |           | (``en_US``) is also supported.    |
    +-----------+-----------+-----------+-----------------------------------+
    | pidfile   | n/a       | No        | Store the bot's PID in this file. |
    +-----------+-----------+-----------+-----------------------------------+
    | timezone  | n/a       | **Yes**   | A string describing the           |
    |           |           |           | computer's current timezone, such |
    |           |           |           | as ``Europe/Paris``. [#]          |
    +-----------+-----------+-----------+-----------------------------------+
    | user      | n/a       | No        | Once started, assume that user's  |
    |           |           |           | identity (given as a UID or as    |
    |           |           |           | a name).                          |
    +-----------+-----------+-----------+-----------------------------------+
    | version   | n/a       | **Yes**   | Must match the Erebot's version.  |
    |           |           |           | It is currently used as a         |
    |           |           |           | failsafe to prevent the bot from  |
    |           |           |           | running with an outdated          |
    |           |           |           | configuration file.               |
    +-----------+-----------+-----------+-----------------------------------+

..  [#] The list of supported timezones can be found on
        http://php.net/manual/en/timezones.php
..  |prefix|    replace:: commands-prefix

..  note::
    The values of the ``daemon``, ``user``, ``group`` & ``pidfile`` options
    can be overriden from the command-line. The values given here only act
    as default ones in case the command line does not override them.

<logging>
~~~~~~~~~

The logging system used by Erebot is highly customizable. It uses the same
kind of API as the Python logging module as it is actually a port of that module
for PHP, hence its name (Python Logging On PHP, or "PLOP").

It was developped as a subproject of Erebot and ships with its own
documentation. The syntax for PLOP's configuration is described in details
@TODO.

<modules>
~~~~~~~~~

Each of the `\<configuration\>`_, `\<network\>`_, `\<server\>`_ and
`\<channel\>`_ tags may have a `\<modules\>`_ subtag to specify which modules
should be made available at that level.

This tag is a simple container for zero or more `\<module\>`_ tags.

<module>
########

This tag defines a module that will be available at the current level
(ie. either globally or for the current network/server/channel).

Settings for a module at one level will override settings for the same module
at some higher level (hence, settings for a module in a `\<channel\>`_ section
will replace settings defined at the `\<network\>`_ level). `\<channel\>`_
is considered as being at a lower level as `\<server\>`_ for the purposes
of this mechanism.

You may choose to enable/disable a module at a particular level by setting
its ``active`` attribute to ``True`` or ``False`` (respectively).

The following table lists attributes of this tag, their default value
and their role.

..  table:: Valid attributes for the <module> tag

    +-----------+---------------+-------------------------------------------+
    | Attribute | Default value | Role                                      |
    +===========+===============+===========================================+
    | name      | n/a           | The name of the module to load/unload.    |
    +-----------+---------------+-------------------------------------------+
    | active    | ``True``      | Indicates whether the module should be    |
    |           |               | enabled at that level (``True``), or      |
    |           |               | disabled (``False``).                     |
    +-----------+---------------+-------------------------------------------+

A <module> tag may contain zero or more `\<param\>`_ tags to specify
additional parameters the module should take into account (such as
specific settings).

<param>
@@@@@@@

This tag can be used to define a parameter for a module. It has 2 (two)
mandatory attributes, as described in the table below.

..  table:: Valid attributes for the <param> tag

    +-----------+---------------+-------------------------------------------+
    | Attribute | Default value | Role                                      |
    +===========+===============+===========================================+
    | name      | n/a           | The name of the parameter.                |
    +-----------+---------------+-------------------------------------------+
    | value     | n/a           | The value for that parameter. Different   |
    |           |               | types of values are accepted. The precise |
    |           |               | type to use depends on the module and     |
    |           |               | parameter.                                |
    |           |               | Read each module's documentation for more |
    |           |               | information.                              |
    +-----------+---------------+-------------------------------------------+

A <param> tag may NOT contain any subtags.

<networks>
~~~~~~~~~~

This tag is a simple container for zero or more `\<network\>`_.

<network>
#########

This tag represents an IRC network.
The following table lists attributes of this tag with their role.

..  table:: Valid attributes for the <network> tag

    +-----------+---------------+-------------------------------------------+
    | Attribute | Default value | Role                                      |
    +===========+===============+===========================================+
    | name      | n/a           | The name of that IRC network.             |
    +-----------+---------------+-------------------------------------------+

The <network> tag **MUST** contain a `\<servers\>`_ subtag, used to describe
IRC servers belonging to that IRC network.

It may contain a `\<modules\>`_ subtag to change the settings of a module
for this IRC server.

It may also contain a `\<channels\>`_ subtag to change the settings of a module
for some IRC channels on this network.

<servers>
@@@@@@@@@

This tag is a simple container for **one** or more `\<server\>`_.

<server>
""""""""

This tag represents the configuration of an IRC server.
The following table lists attributes of this tag with their role.

..  table:: Valid attributes for the <server> tag

    +-----------+---------------+-------------------------------------------+
    | Attribute | Default value | Role                                      |
    +===========+===============+===========================================+
    | url       | n/a           | Connection URLs to use to contact this    |
    |           |               | IRC server.                               |
    +-----------+---------------+-------------------------------------------+

The ``url`` attribute contains a series of connection URLs. A connection URL
simply gives information on how to connect to a particular IRC server.
A valid connection URL looks like this:
``ircs://irc.iiens.net:7000/?verify_peer=0``

The scheme part may be either ``irc`` for plain text communications
or ``ircs`` for IRC over SSL/TLS (encrypted communications).
The host part indicates the IP address or hostname of the IRC server.
The port part can be used to override the default port value for
the given scheme.

By default, plain text IRC uses port 194 while IRC over SSL/TLS uses port 994.
However, since both of these ports require root permissions on linux to launch
a server, most IRC servers use different values like 6667 or 7000 for plain
text communications and 6697 or 7002 for encrypted communications.

Last but not least, additional parameters may be used to control various
aspects of the connection phase. At present time, these settings only affect
encrypted connections (IRC over SSL/TLS), but they may be later extended
to affect plain-text connections as well. The following table lists currently
supported parameters:

..  table:: Valid parameters for connection URLs

    +-------------------+-------------------+-------------------------------+
    | Name              | Valid values      | Description                   |
    +===================+===================+===============================+
    | verify_peer       | ``0`` or ``1``    | Check if the certificate      |
    |                   |                   | really belongs to the target  |
    |                   |                   | IRC server.                   |
    +-------------------+-------------------+-------------------------------+
    | allow_self_signed | ``0`` or ``1``    | Consider self-signed          |
    |                   |                   | certificates to be valid.     |
    +-------------------+-------------------+-------------------------------+
    | ciphers           | a list of ciphers | Acceptable ciphers to use to  |
    |                   | separated by      | encrypt communications with   |
    |                   | colons            | the server.                   |
    +-------------------+-------------------+-------------------------------+

See also http://php.net/manual/en/context.ssl.php for additional information
on those settings.

You may also specify an HTTP or SOCKS 5 server through which the connection
should be proxied by adding a proxy URL to the ``url`` attribute.
Several proxies can be used by prepending their URLs to that attribute,
separated by spaces:

..  sourcecode:: xml

  <!-- Use an HTTP proxy with username/password authentication. -->
  <server url="http://user:pass@proxy.example.com irc://irc.example.com"/>

  <!-- Use a SOCKS 5 proxy with username/password authentication. -->
  <server url="socks://user:pass@proxy.example.com irc://irc.example.com"/>

  <!--
    Chain two proxies before connecting to the final IRC server.
    The first one is an HTTP proxy running on non-standard port 8080.
    The second one is a regular SOCKS proxy.
  -->
  <server url="http://http-proxy.example.com:8080/ socks://socks-proxy.example.com/ irc://irc.example.com"/>

This tag may contain a `\<modules\>`_ subtag to change the settings of a module
for this IRC server.

<channels>
@@@@@@@@@@

This tag is a simple container for zero or more `\<channel\>`_ tags.

<channel>
"""""""""

This tag represents the configuration of an IRC channel.
The following table lists attributes of this tag with their role.

..  table:: Valid attributes for the <channel> tag.

    +-----------+---------------+-------------------------------------------+
    | Attribute | Default value | Role                                      |
    +===========+===============+===========================================+
    | name      | n/a           | The name of the IRC channel being         |
    |           |               | configured.                               |
    +-----------+---------------+-------------------------------------------+

This tag may contain a `\<modules\>`_ subtag to change the settings of a module
for this IRC channel.

.. vim: ts=4 et
