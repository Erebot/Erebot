Installation
============

This pages contains instructions on how to install Erebot on your machine.
There are several ways to achieve that. Each method is described below.

..  contents:: :local:

..  warning::

    You cannot mix the different methods. Especially, **you must use the same
    method to install modules as the one you selected for Erebot itself**.

..  note::

    We recommend using the `PHAR installation`_ method
    or the `composer installation`_ method, depending on
    whether your project already uses `Composer`_ or not.
    Installation from sources is reserved for advanced installations (mainly
    for Erebot's developers).


..  _`PHAR installation`:

Installation using PHAR archives
--------------------------------

A PHAR archive is simply a way of bundling all the necessary files in one big
file. However, PHAR's archive does not contain any module.
Thus, to get a working installation, you must install additional Erebot
modules. At a minimum, this includes: `Erebot_Module_IrcConnector`_,
`Erebot_Module_AutoConnect`_, `Erebot_Module_PingReply`_.

Installing Erebot as a PHAR archive only involves a few steps:

1.  Make sure your installation fulfills all of the `prerequisites`_.

    ..  note::

        As all of Erebot's PHAR archives (core and modules) are digitally
        signed, you must make sure the OpenSSL extension is enabled on your
        PHP installation. Failure to do so will result in an error when trying
        to run Erebot's PHAR archive.

2.  Download the PHAR archive for Erebot itself. You can grab the latest
    version from https://packages.erebot.net/get/Erebot-latest.phar.
    You MUST also download the public signature for the archive.
    The signature for the latest version is available at
    https://packages.erebot.net/get/Erebot-latest.phar.pubkey.

3.  Create a directory named :file:`modules` in the same folder as the PHAR.

4.  Go to the :file:`modules` directory and drop a copy of the following PHAR
    archives with their signature:

    *   Files for the `Erebot_Module_AutoConnect`_ module:

        -   `Erebot_Module_AutoConnect-latest.phar`_
        -   `Erebot_Module_AutoConnect-latest.phar's signature`_

    *   Files for the `Erebot_Module_IrcConnector`_ module:

        -   `Erebot_Module_IrcConnector-latest.phar`_
        -   `Erebot_Module_IrcConnector-latest.phar's signature`_

    *   Files for the `Erebot_Module_PingReply`_ module:

        -   `Erebot_Module_PingReply-latest.phar`_
        -   `Erebot_Module_PingReply-latest.phar's signature`_

    Make sure you also read each component's documentation (especially the list
    of prerequisites).

    ..  note::

        You **MUST** copy both the PHAR archives and their signature in the
        :file:`modules` directory. Otherwise, PHP will refuse to load those
        PHAR archives because it cannot check their origin and integrity.

5.  Optionally, download additional PHAR archives with their signature
    to install other modules.

Your tree should now look like this:

    * Erebot/
        * Erebot-latest.phar
        * Erebot-latest.phar.pubkey
        * modules/
            * Erebot_Module_AutoConnect-latest.phar
            * Erebot_Module_AutoConnect-latest.phar.pubkey
            * Erebot_Module_IrcConnector-latest.phar
            * Erebot_Module_IrcConnector-latest.phar.pubkey
            * Erebot_Module_PingReply-latest.phar
            * Erebot_Module_PingReply-latest.phar.pubkey
            * *eventually, additional PHAR archives with their signature*

..  note::

    The whole installation process using PHAR archives can be automated
    using the following commands:

    ..  sourcecode:: bash

        $ wget --no-check-certificate                                                   \
            https://packages.erebot.net/get/Erebot-latest.phar                              \
            https://packages.erebot.net/get/Erebot-latest.phar.pubkey                       \
            https://packages.erebot.net/get/Erebot_Module_AutoConnect-latest.phar           \
            https://packages.erebot.net/get/Erebot_Module_AutoConnect-latest.phar.pubkey    \
            https://packages.erebot.net/get/Erebot_Module_IrcConnector-latest.phar          \
            https://packages.erebot.net/get/Erebot_Module_IrcConnector-latest.phar.pubkey   \
            https://packages.erebot.net/get/Erebot_Module_PingReply-latest.phar             \
            https://packages.erebot.net/get/Erebot_Module_PingReply-latest.phar.pubkey
        $ mkdir modules
        $ mv Erebot_Module_*-latest.phar Erebot_Module_*-latest.phar.pubkey modules/

Once the PHAR archives have been retrieved, you may wish to change file
permissions on :file:`Erebot-latest.phar`, using this command:

    ..  sourcecode:: bash

        $ chmod 0755 Erebot-latest.phar

This way, you may later launch Erebot simply by executing:

    ..  sourcecode:: bash

        $ ./Erebot-latest.phar

..  warning::

    Even though the command above should work on most installations,
    a few known problems may occur due to incompatibilities with certain
    PHP features and extensions. To avoid such issues, it is usually a good
    idea to check the following items:

    -   Make sure ``detect_unicode`` is set to ``Off`` in your :file:`php.ini`.
        This is especially important on MacOS where this setting tends to be
        ``On`` for a default PHP installation.

    -   If you applied the Suhosin security patch to your PHP installation,
        make sure ``phar`` is listed in your :file:`php.ini` under the
        ``suhosin.executor.include.whitelist`` directive.

    -   Please be aware of certain incompatibilities between the Phar extension
        and the ionCube Loader extension. To run Erebot from a PHAR archive,
        you will need to remove the following line from your :file:`php.ini`:

        .. sourcecode:: ini

            zend_extension=/usr/lib/php5/20090626+lfs/ioncube_loader_lin_5.3.so

        (the path and versions may be different for your installation).

..  note::

    When run from a PHAR archive, Erebot will first try to determine whether
    all requirements needed to run the bot and its modules are respected.
    In case an error is displayed, follow the indications given in the error
    message and try running the bot again.

That's it! You may now read the section on `final steps`_ for a summary of
what to do next.


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

    # For urpmi-based distributions such as MES (Mandriva)
    $ urpmi git

    # For Zypper-based distributions such as SLES (SuSE)
    $ zypper install git

..  note::

    Windows users may be interested in installing `Git for Windows`_ to get
    an equivalent git client. Also, make sure that :program:`git` is present
    on your account's :envvar:`PATH`. If not, you'll have to replace
    :command:`git` by the full path to :file:`git.exe` on every invocation
    (eg. :command:`"C:\\Program Files\\Git\\bin\\git.exe" clone ...`)

Also, make sure you have all the `required dependencies`_ installed as well.
Now, retrieve the bot's code from the repository, using the following command:

..  sourcecode:: bash

    $ git clone --recursive git://github.com/Erebot/Erebot.git
    $ mkdir -p Erebot/vendor/ && cd Erebot/vendor/
    $ git clone --recursive git://github.com/Erebot/Erebot_Module_IrcConnector.git
    $ git clone --recursive git://github.com/Erebot/Erebot_Module_AutoConnect.git
    $ git clone --recursive git://github.com/Erebot/Erebot_Module_PingReply.git
    $ cd ..

..  note::
    Linux users (especially Erebot developers) may prefer to create a separate
    checkout for each component and then use symbolic links to join them
    together, like this:

    ..  sourcecode:: bash

        $ git clone --recursive git://github.com/Erebot/Erebot.git
        $ git clone --recursive git://github.com/Erebot/Erebot_Module_IrcConnector.git
        $ git clone --recursive git://github.com/Erebot/Erebot_Module_AutoConnect.git
        $ git clone --recursive git://github.com/Erebot/Erebot_Module_PingReply.git
        $ mkdir -p Erebot/vendor/ && cd Erebot/vendor/
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
`write a configuration file`_ for Erebot (usually named :file:`Erebot.xml`).

When this is done, the bot can be started, assuming that PHP can be found
in your :envvar:`PATH` using one of the following commands.
Exactly what command must be used depends on the installation method.

..  sourcecode:: bash

    # For an installation using PHAR archives.
    # Must be run from the folder in which Erebot was installed.
    $ php ./Erebot-<version>.phar

    # For an installation from sources or using Composer.
    # Must be run from the folder in which Erebot was installed.
    $ php ./scripts/Erebot

Let's call this command ``%EREBOT%``.

In each case, the bot reacts to a few command-line options.
Use the following command to get help on those options.

..  sourcecode:: bash

    $ %EREBOT% --help

..  note::

    For ease of use, Linux users may prefer to add the path where
    :file:`Erebot-{version}.phar` or the :command:`Erebot` script resides to
    their :envvar:`PATH`. This way, the bot can be started simply by launching
    :command:`Erebot` or :file:`Erebot-{version}.phar` from the command-line
    or by double-clicking on them from a graphical file browser.

..  note::

    Unfortunately for Windows users, there is no equivalent to the
    :envvar:`PATH` trick noted above.
    However, it is possible to associate the ``.phar`` extension with PHP.
    This way, if Erebot was installed using PHAR archives, the bot can be
    started simply by double-clicking on :file:`Erebot-{version}.phar`.


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
..  _`Erebot_Module_AutoConnect-latest.phar`:
    https://packages.erebot.net/get/Erebot_Module_AutoConnect-latest.phar
..  _`Erebot_Module_AutoConnect-latest.phar's signature`:
    https://packages.erebot.net/get/Erebot_Module_AutoConnect-latest.phar.pubkey
..  _`Erebot_Module_IrcConnector-latest.phar`:
    https://packages.erebot.net/get/Erebot_Module_IrcConnector-latest.phar
..  _`Erebot_Module_IrcConnector-latest.phar's signature`:
    https://packages.erebot.net/get/Erebot_Module_IrcConnector-latest.phar.pubkey
..  _`Erebot_Module_PingReply-latest.phar`:
    https://packages.erebot.net/get/Erebot_Module_PingReply-latest.phar
..  _`Erebot_Module_PingReply-latest.phar's signature`:
    https://packages.erebot.net/get/Erebot_Module_PingReply-latest.phar.pubkey

.. vim: ts=4 et
