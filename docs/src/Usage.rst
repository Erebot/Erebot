Usage
=====

..  program:: Erebot

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

