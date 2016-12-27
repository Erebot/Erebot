Usage
=====

..  program:: Erebot

Options
-------

..  option:: -c <FILE>, --config <FILE>

    Use the given configuration <FILE> instead of the default :file:`Erebot.xml`.
    The given path is interpreted relative to the current directory.

..  option:: -d, --daemon

    Run the bot in the background.

    ..  note::

        Both the POSIX and pcntl PHP extensions must be enabled for this
        option to work.

..  option:: -n, --no-daemon

    Run the bot in the foreground.
    This is the default unless :option:`Erebot -d` is used.

..  option:: -p <FILE>, --pidfile <FILE>

    Path to the file where the bot's :abbr:`PID (Process IDentifier)`
    will be written, relative to the current directory.

..  option:: -g <GROUP/GID>, --group <GROUP/GID>

    Switch to this group identity during startup.
    The group may be expressed as either a group name (eg. ``root``)
    or as a numeric :abbr:`GID (Group IDentifier)` (eg. ``0``).

..  option:: -u <USER/UID>, --user <USER/UID>

    Switch to this user identity during startup.
    The user may be expressed as either a user name (eg. ``root``)
    or as a numeric :abbr:`UID (User IDentifier)` (eg. ``0``).

..  option:: -h, --help

    Display the bot's help and exit.

..  option:: -v, --version

    Display the bot's version and exit. 


Environment variables
---------------------

Several environment variables related to language settings control the way
the bot produces its output. The first variable to appear in the environment
takes precedence over the others.

..  envvar:: LANGUAGE

    Defines the system's supported languages, as a colon-separated list
    of country names with optional regions.
    Eg.

    ..  sourcecode:: shell

        LANGUAGE=en_US:en

..  envvar:: LC_ALL

    Defines supported languages for various types of formatting operations,
    like message formatting, date/time formatting and so on.
    Setting this variable is equivalent to setting each of the other
    ``LC_*`` variables individually to the same value.

..  envvar:: LC_MESSAGES

    Defines supported languages when outputting textual messages.

..  envvar:: LC_MONETARY

    Defines supported languages when outputting monetary values.

..  envvar:: LC_TIME

    Defines supported languages when outputting dates/times.

..  envvar:: LC_NUMERIC

    Defines supported languages when outputting other numeric values
    (eg. floating-point values).

..  envvar:: LANG

    Defines the system's supported languages and encodings, as a colon-separated
    list of country names with their optional region and their encoding.
    Eg.

    ..  sourcecode:: shell

        LANG=en_US.utf8
