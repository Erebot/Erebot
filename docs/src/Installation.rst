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
    Pyrus currently has issues with some PEAR packages. It is thus recommended
    that you use the regular pear tool to install Erebot.
    See https://github.com/pyrus/Pyrus/issues/26 for more information.

You can install (**as a privileged user**) either the latest stable release
using a command such as:

..  sourcecode:: bash

    $ pear channel-discover pear.erebot.net
    $ pear install erebot/Erebot

... or you can install the latest unstable version instead, using:

..  sourcecode:: bash

    $ pear channel-discover pear.erebot.net
    $ pear install erebot/Erebot-alpha

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

Installing Erebot as a PHAR archive only involves a few steps:

1.  Make sure your installation fulfills all of the `prerequisites`_

2.  Download the PHAR archive for Erebot itself. You can grab the latest
    version from https://pear.erebot.net/get/Erebot-latest.phar.

3.  Create a directory named ``modules`` in the same folder as the PHAR.

4.  Go to the ``modules`` directory and drop a copy of the PHAR archive
    for the following components:

    *   `Erebot_Module_IrcConnector`_ (direct link:
        https://pear.erebot.net/get/Erebot_Module_IrcConnector-latest.phar)

    *   `Erebot_Module_AutoConnect`_ (direct link:
        https://pear.erebot.net/get/Erebot_Module_AutoConnect-latest.phar)

    *   `Erebot_Module_PingReply`_ (direct link:
        https://pear.erebot.net/get/Erebot_Module_PingReply-latest.phar)

    Make sure you read each component's documentation (especially the list
    of prerequisites).

5.  Optionally, download additional PHAR archives for other modules.

Your tree should now look like this:

    * Erebot/
        * Erebot-latest.phar
        * modules/
            * Erebot_Module_IrcConnector-latest.phar
            * Erebot_Module_AutoConnect-latest.phar
            * Erebot_Module_PingReply-latest.phar
            * *eventually, additional PHAR archives*

That's it! You may now read the section on `final steps`_ for a summary of
what to do next.

..  note::
    The whole installation process using PHAR archives can be automated
    using the following commands:

    ..  sourcecode:: bash

        $ wget --no-check-certificate                                           \
            https://pear.erebot.net/get/Erebot-latest.phar                      \
            https://pear.erebot.net/get/Erebot_Module_IrcConnector-latest.phar  \
            https://pear.erebot.net/get/Erebot_Module_AutoConnect-latest.phar   \
            https://pear.erebot.net/get/Erebot_Module_PingReply-latest.phar
        $ mkdir modules
        $ mv Erebot_Module_*-latest.phar modules/

    However, please note that these commands do not attempt to check that
    the machine they're running on matches the bot's and the module's
    prerequisites. You should read the documentation of each component
    to verify that yourself.


Installation from source
------------------------

First, make sure a git client is installed on your machine.
Under Linux, **from a root shell**, run the command that most closely matches
the tools provided by your distribution:

..  sourcecode:: bash

    # For apt-based distributions such as Debian or Ubuntu
    $ apt-get install git

    # For yum-based distributions such as Fedora / RHEL (RedHat)
    $ yum install git

    # For urpmi-based distributions such as SLES (SuSE) or MES (Mandriva)
    $ urpmi git

..  note::
    Windows users may be interested in installing `Git for Windows`_ to get
    an equivalent git client. Also, make sure that ``git.exe`` is present
    on your account's ``PATH``. If not, you'll have to replace ``git`` by
    the full path to ``git.exe`` on every invocation
    (eg. ``"C:\Program Files\Git\bin\git.exe" clone ...``)

Also, make sure you have all the `required dependencies`_ installed as well.
Now, retrieve the bot's code from the repository, using the following command:

..  sourcecode:: bash

    $ git clone --recursive git://github.com/fpoirotte/Erebot.git
    $ cd Erebot/vendor/
    $ git clone --recursive git://github.com/fpoirotte/Erebot_Module_IrcConnector.git
    $ git clone --recursive git://github.com/fpoirotte/Erebot_Module_AutoConnect.git
    $ git clone --recursive git://github.com/fpoirotte/Erebot_Module_PingReply.git
    $ cd ..

..  note::
    Linux users (especially Erebot developers) may prefer to create a separate
    checkout for each component and then use symbolic links to join them
    together, like this:

    ..  sourcecode:: bash

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
assuming you're currently in Erebot's folder, using these commands:

..  sourcecode:: bash

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

Once Erebot (core files + a few modules) has been installed, you can
`write a configuration file`_ for Erebot (usually named ``Erebot.xml``).

When this is done, the bot can be started, assuming that PHP can be found on the
``PATH`` using one of the following commands. Exactly what command must be used
depends on the installation method.

..  sourcecode:: bash

    # For an installation using PEAR packages.
    $ php /path/to/PEAR/bin_dir/Erebot

    # For an installation using PHAR archives.
    # Must be run from the folder in which Erebot was installed.
    $ php ./Erebot-<version>.phar

    # For an installation using the source code.
    # Must be run from the folder in which Erebot was installed.
    $ php ./scripts/Erebot

Let's call this command ``%EREBOT%``.

In each case, the bot reacts to a few command-line options.
Use the following command to get help on those options.

..  sourcecode:: bash

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
..  _`write a configuration file`:
    Configuration.html

.. vim: ts=4 et
