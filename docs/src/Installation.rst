Installation
============

This pages contains instructions on how to install Erebot on your machine.
There are several ways to achieve that. Each method is described below.

..  warning::
    You cannot mix the different methods. Especially, **you must use the same
    method to install modules as the one you selected for Erebot itself**.

..  contents::

..  note::
    We recommend that you install Erebot using its `PEAR channel`_.
    This will result in a system-wide installation which can be upgraded
    very easily later.
    If this is not feasible for you or if you prefer to keep the installation
    local (for a single user), we recommend that you go the PHAR way.
    Installation from sources is reserved for advanced installations (mainly
    for Erebot's developers).


Installation from Erebot's PEAR channel
---------------------------------------

This is by far the simplest way to install Erebot.
Hence, it's the recommended way for beginners.
Just use whatever tool your distribution provides to manage PEAR packages:

* Either `pear`_ (traditionnal tool)
* or `pyrus`_ (new experimental tool meant to replace pear someday)

..  warning::
    Pyrus currently has issues to install some PEAR packages.
    See https://github.com/pyrus/Pyrus/issues/26 for more information.

You can install either the latest stable release using a command such as::

    # pear channel-discover pear.erebot.net
    # pear install erebot/Erebot

... or you can install the latest unstable version instead, using::

    # pear channel-discover pear.erebot.net
    # pear install erebot/Erebot-alpha

Please note that the ``channel-discover`` command needs to be run only once
(pear and pyrus will refuse to discover a PEAR channel more than once anyway).
To use Pyrus to manage PEAR packages instead of the regular Pear tool,
just replace ``pear`` with ``pyrus`` in the commands above.

That's all! The bot is now ready for the next steps.
Be sure to read the section on `final steps`_ for a summary of what to do next.


Installation using PHAR archives
--------------------------------

When installing Erebot using a PHAR archive, a copy of all dependencies needed
by Erebot is bundled in the archive. Hence, the PHAR archive is slightly bigger
than a regular PEAR package, but overall the disk space requirements are the
same as for an installation using PEAR because you would have had to install
those dependencies as well anyway.

..  note::
    Actually, not all dependencies are bundled with Erebot.
    Especially, the PHAR archive does not contain any module.
    Thus, to get a working installation, you must install additional Erebot
    modules. At a minimum, this includes: `Erebot_Module_IrcConnector`_,
    `Erebot_Module_AutoConnect`_, `Erebot_Module_PingReply`_.

..  todo::
    Explain how to do this.

Be sure to read the section on `final steps`_ for a summary of what to do next.


Installation from source
------------------------

First, make sure a git client is installed on your machine::

    # apt-get install git   # for apt-based distributions such as Debian or Ubuntu

or::

    # yum install git       # for yum-based distributions such as Fedora / RHEL (RedHat)

or::

    # urpmi git             # for urpmi-based distributions such as SLES (SuSE) or MES (Mandriva)

..  note::
    Windows users may be interested in installing `Git for Windows`_ to get
    an equivalent git client. Also, make sure that ``git.exe`` is present
    on your account's ``PATH``. If not, you'll have to replace ``git`` by
    the full path to ``git.exe`` on every invocation
    (eg. ``"C:\Program Files\Git\bin\git.exe" clone ...``)

Also, make sure you have all the `required dependencies`_ installed as well.
Now, retrieve the bot's code from the repository, using the following command::

    $ git clone --recursive git://github.com/fpoirotte/Erebot.git
    $ cd Erebot/vendor/
    $ git clone --recursive git://github.com/fpoirotte/Erebot_Module_IrcConnector.git
    $ git clone --recursive git://github.com/fpoirotte/Erebot_Module_AutoConnect.git
    $ git clone --recursive git://github.com/fpoirotte/Erebot_Module_PingReply.git
    $ cd ..

..  note::
    Linux users (especially Erebot developers) may prefer to create a separate
    checkout for each component and then use symbolic links to join them
    together, like this::

        $ git clone --recursive git://github.com/fpoirotte/Erebot.git
        $ git clone --recursive git://github.com/fpoirotte/Erebot_Module_IrcConnector.git
        $ git clone --recursive git://github.com/fpoirotte/Erebot_Module_AutoConnect.git
        $ git clone --recursive git://github.com/fpoirotte/Erebot_Module_PingReply.git
        $ cd Erebot/vendor/
        $ ln -s ../../Erebot_Module_IrcConnector
        $ ln -s ../../Erebot_Module_AutoConnect
        $ ln -s ../../Erebot_Module_PingReply
        $ cd ..

Optionally, you can compile the translation files for each component.
However, this requires that `gettext`_ and `phing`_ be installed on your machine
as well. See the documentation on Erebot's `prerequisites`_ for additional
information on how to install these tools depending on your system.

Once you got those two up and running, the translation files can be compiled,
assuming you're currently in Erebot's folder, using these commands::

    $ phing
    $ cd vendor/Erebot_Module_IrcConnector/
    $ phing
    $ cd ../Erebot_Module_AutoConnect/
    $ phing
    $ cd ../Erebot_Module_PingReply/
    $ phing
    $ cd ../../

Be sure to read the section on `final steps`_ for a summary of what to do next.


Final steps
-----------

Once Erebot (core files + a few modules) has been installed, you can write a
configuration file (usually named ``Erebot.xml``) in the same folder where
the bot was installed.

When this is done, the bot can be started, assuming that PHP can be found on the
``PATH`` using one of the following commands. Exactly what command must be used
depends on the installation method.

For an installation using PEAR packages, simply run::

    $ php /path/to/PEAR/bin_dir/Erebot

For an installation using PHAR archives, from the folder in which you installed
Erebot, run::

    $ php ./Erebot-<version>.phar

And finally, for an installation using the source code, from the folder where
you installed Erebot, run::

    $ php ./scripts/Erebot

Let's call this command ``%EREBOT%``.

In each case, the bot reacts to a few command-line options.
Use the following command to get help on those options::

    $ %EREBOT% --help

..  note::
    For ease of use, Linux users may like to add the path where
    ``Erebot-<version>.phar`` or the ``Erebot`` script reside to
    their ``PATH``. This way, the bot can be started simply by launching
    ``Erebot`` or ``Erebot-<version>.phar`` from the command-line or by
    double-clicking on them from a graphical file browser.

..  note::
    Unfortunately for Windows users, there is no equivalent to the ``PATH``
    trick noted above.
    However, it is possible to associate the ``.phar`` extension with PHP.
    This way, if Erebot was installed using PHAR archives, the bot can be
    started simply by double-clicking on ``Erebot-<version>.phar``.


..  _`pear`:
    http://pear.php.net/package/PEAR
..  _`Pyrus`:
    http://pyrus.net/
..  _`PEAR channel`:
    https://pear.erebot.net/
..  _`gettext`:
    http://www.gnu.org/s/gettext/
..  _`Phing`:
    http://www.phing.info/
..  _`Git for Windows`:
    http://code.google.com/p/msysgit/downloads/list
..  _`prerequisites`:
..  _`required dependencies`:
    Prerequisites.html
..  _`Erebot_Module_AutoConnect`:
    /Erebot_Module_AutoConnect/
..  _`Erebot_Module_IrcConnector`:
    /Erebot_Module_IrcConnector/
..  _`Erebot_Module_PingReply`:
    /Erebot_Module_PingReply/

.. vim: ts=4 et
