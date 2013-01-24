How to interact with Erebot from an external process
====================================================

This guide will show you how to setup Erebot so that an external process can
interact with an IRC server through the bot.
In the second part of this tutorial, we will also see how the logging system
can be used to receive feedback from the bot for commands we sent to it.

..  contents:: Table of Contents
    :local:


Sending commands through the bot
--------------------------------

Erebot embeds a class called ``Erebot_Prompt`` that can be used to control
the bot remotely using a UNIX socket. This can be used for example to build
a web frontend for the bot. It might be used to build a complete IRC client
too.

..  warning::
    This feature only offers a one-way communication channel with the
    bot. That is, it can be used to send commands to the bot, but it cannot be
    used to see the actual responses to those commands.

..  note::
    If you need bidirectional communications, you can combine this feature
    with Erebot's logging mechanism to intercept messages as the bot sends or
    receives them. See the section entitled « `Intercepting messages`_ »
    for more information.

..  warning::
    This feature is only available on platforms that implement UNIX
    sockets (especially, it is **not** available on Windows platforms).


Setting things up
+++++++++++++++++

Enabling the prompt is actually quite easy. All you need to do is add a
service named "prompt" to your ``defaults.xml`` configuration file.
That service will usually be an instance of the ``Erebot_Prompt`` class
and should be passed the bot's service (named ``bot``) as its first
parameter. It also accepts a few parameters, listed in the following table.

..  table:: Parameters accepted by ``Erebot_Prompt`` (in this order)

    =========== ======= =================== ========= ======================== ====================================
    Parameter   Type    Description         Required? Default value            Example value
    =========== ======= =================== ========= ======================== ====================================
    $bot        object  Instance of the     Yes       N/A                      N/A
                        ``bot`` service.
    $connector  string  Path to the UNIX    No        "``/tmp/Erebot.sock``"   "``/var/lib/Erebot/control.sock``"
                        socket to create.
    $group      string  UNIX group for      No        Primary group of the     "``nogroup``"
                        the new socket.               user running the bot.
    $perms      integer Permissions on the            ``0660`` (``rw-rw----``) ``0666`` (to allow any program
                        socket to create.                                      to control Erebot |---| this
                                                                               is considered dangerous, avoid
                                                                               if possible).
    =========== ======= =================== ========= ======================== ====================================

Therefore, a potential configuration for the prompt in the ``defaults.xml``
configuration file may look like this:

..  sourcecode:: xml
    :linenos:

    <service id="prompt" class="Erebot_Prompt">
        <argument type="service" id="bot"/>
        <argument>/var/lib/Erebot/control.sock</argument>
        <argument>nogroup</argument>
        <argument type="int">0666</argument>
    </service>


Passing commands to Erebot
++++++++++++++++++++++++++

What you need to know
~~~~~~~~~~~~~~~~~~~~~

To send commands to Erebot, you need two pieces of information:

*   The path to the UNIX socket that acts as Erebot's prompt.
*   The name of the IRC network (as declared in Erebot's configuration
    file) to send the commands to.

..  note::
    The latter is actually optional if you want to execute the command
    on all IRC networks (eg. an ``AWAY`` command before going to sleep),
    as we will see below.

A simple example
~~~~~~~~~~~~~~~~

Once you have those information, open the UNIX socket using your favorite
programming language.

..  note::
    UNIX sockets can be opened from any language that supports them,
    including |---| but not limited to |---| Bash, Perl, PHP, Python, Java, etc.

You may now send commands using the following format::

    <pattern> <command> <line ending>

where each token is described below:

``<pattern>``
    A pattern that will be used to match the network's name (as declared
    in Erebot's configuration file). You may use wildcard characters here
    (``?`` to match 0 or exactly 1 character, ``*`` to match 0 or more
    characters).
    The simplest way to target a specific IRC network is to simply pass
    that network's name as the ``<pattern>``.

``<command>``
    The IRC command you wish to send (eg. ``AWAY :Gone to sleep``).
    Please refer to :rfc:`2812` for information on valid commands.

``<line ending>``
    One of the 3 common line endings accepted by Erebot and noted below
    using C-style espace sequences:

    *   "``\r``" (Mac style)
    *   "``\n``" (Linux style)
    *   "``\r\n``" (Windows style)

..  note::
    When looking for the connections targeted by a command, a case-insensitive
    full-line match is performed. This means that a pattern such as
    "``mynetwork``" and "``mynet*``" will match a network named
    "``MyNetwork``", but "``mynet``" won't.

Here is an example using the socat command from a cron task to make
the bot quit the "``iiens``" IRC network every day at midnight:

..  sourcecode:: bash
    :linenos:

    # m h  dom mon dow   command
      0 0  *   *   *     echo 'iiens QUIT :Time to sleep!' | socat - UNIX-SENDTO:/tmp/Erebot.sock

Targeting multiple IRC networks at once
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

As seen in the format above, a pattern matching the target IRC network's
name is passed before the actual command. Hence, targeting multiple IRC
networks at once is only a matter of using the right pattern.
For example, if you have multiple connections to the same IRC network,
named "``MyNetwork1``", "``MyNetwork2``", etc. you could easily send
a command to all of these connections using "``MyNetwork*``" as the pattern.

Following the same logic, it is possible to send a command to **all**
the servers the bot is currently connected to by using "``*``" as the
pattern, since this will match any network, regardless of its name.


Intercepting messages
---------------------

The technic described below makes it possible to intercept both incoming and
outgoing messages. It is ideal if you're trying to build a frontend for Erebot
because:

#.  You can capture outgoing messages to get feedback on the actual commands
    being sent by the bot (keep in mind that modules may prevent certain
    commands from being sent for example).

#.  You can capture incoming messages too, which means that you can process
    them using external tools if needed (eg. display them on your website).

..  important::
    Even if you could easily process messages with an external tool then
    feed the results back to Erebot using the UNIX socket, it is often a lot
    more efficient to write a module for Erebot directly (using the assets
    provided by the PHP toolbox).

..  todo::
    Explain how to do that with Erebot.


Troubleshooting
---------------

This paragraph lists the most common problems you may encounter while following
this tutorial, as well as explanations as to why they appear and possible
solutions or workarounds.

``PHP Warning: stream_socket_server(): unable to connect to udg:///... (Unknown error) in .../Erebot/Prompt.php on line ...``
++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

**Example**::

    PHP Warning:  stream_socket_server(): unable to connect to udg:///tmp/Erebot.sock (Unknown error) in /home/looksup/Documents/Erebot/core/trunk/src/Erebot/Prompt.php on line 44
    PHP Stack trace:
    PHP   1. {main}() /home/looksup/Documents/Erebot/core/trunk/scripts/Erebot:0
    PHP   2. Erebot_CLI::run() /home/looksup/Documents/Erebot/core/trunk/scripts/Erebot:99
    PHP   3. sfServiceContainer->__get() /var/local/buildbot/pear/php/SymfonyComponents/DependencyInjection/sfServiceContainer.php:0
    PHP   4. sfServiceContainerBuilder->getService() /var/local/buildbot/pear/php/SymfonyComponents/DependencyInjection/sfServiceContainer.php:276
    PHP   5. sfServiceContainerBuilder->createService() /var/local/buildbot/pear/php/SymfonyComponents/DependencyInjection/sfServiceContainerBuilder.php:86
    PHP   6. ReflectionClass->newInstanceArgs() /var/local/buildbot/pear/php/SymfonyComponents/DependencyInjection/sfServiceContainerBuilder.php:248
    PHP   7. Erebot_Prompt->__construct() /home/looksup/Documents/Erebot/core/trunk/src/Erebot/Prompt.php:0
    PHP   8. stream_socket_server() /home/looksup/Documents/Erebot/core/trunk/src/Erebot/Prompt.php:44

**Origins**:

This error usually appears after the bot was stopped in a non-clean fashion
(eg. after it has been killed). This is caused by a left-over UNIX socket
created by the previous instance.
You can fix the problem by manually removing the socket.

**Solution**:

Issue the following command (adapt the path depending on the content of the error message)::

    rm -f /tmp/Erebot.sock

``PHP Fatal error: Uncaught exception 'Exception' with message 'Could not change group to '...' for '...'' in ...``
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

**Example**::

    PHP Fatal error:  Uncaught exception 'Exception' with message 'Could not change group to 'nogroup' for '/tmp/Erebot.sock'' in /home/looksup/Documents/Erebot/core/trunk/src/Erebot/Prompt.php:56
    Stack trace:
    #0 [internal function]: Erebot_Prompt->__construct(Object(Erebot), '/tmp/Erebot.soc...', 'nogroup', 384)
    #1 /var/local/buildbot/pear/php/SymfonyComponents/DependencyInjection/sfServiceContainerBuilder.php(248): ReflectionClass->newInstanceArgs(Array)
    #2 /var/local/buildbot/pear/php/SymfonyComponents/DependencyInjection/sfServiceContainerBuilder.php(86): sfServiceContainerBuilder->createService(Object(sfServiceDefinition))
    #3 /var/local/buildbot/pear/php/SymfonyComponents/DependencyInjection/sfServiceContainer.php(276): sfServiceContainerBuilder->getService('prompt')
    #4 /home/looksup/Documents/Erebot/core/trunk/src/Erebot/CLI.php(363): sfServiceContainer->__get('prompt')
    #5 /home/looksup/Documents/Erebot/core/trunk/scripts/Erebot(99): Erebot_CLI::run()
    #6 {main}
      thrown in /home/looksup/Documents/Erebot/core/trunk/src/Erebot/Prompt.php on line 56

**Origins**:

Possible reasons for this error include:

*   The given group name or GID does not exist.
*   The current user is not the superuser (root) and is not a member of the
    given group (this is a limitation from the low-level chgrp system call).
    See also http://php.net/chgrp for more information.

**Solution**:

Make sure the given group exists and the user running the bot is a member
of that group (or is the superuser).

..  |---| unicode:: U+02014 .. em dash
    :trim:

.. vim: ts=4 et
