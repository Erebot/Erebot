Installation
============

This pages contains instructions on how to install Erebot on your machine.
There are several ways to achieve that. Each method is described below.

..  contents:: :local:

..  warning::

    You cannot mix the different methods. Especially, **you must use the same
    method to install modules as the one you selected for Erebot itself**.

..  _`PHAR installation`:

Installation using PHAR archives
--------------------------------

A PHAR archive is simply a way of bundling all the necessary files in one big
file. However, PHAR's archive does not contain any module.
Thus, to get a working installation, you must install additional Erebot
modules. At a minimum, this includes the following modules:

-   `Erebot_Module_AutoConnect <./projects/autoconnect/>`_
-   `Erebot_Module_IrcConnector <./projects/ircconnector/>`_
-   `Erebot_Module_PingReply <./projects/pingreply/>`_

Installing Erebot from a PHAR archive involves only a few steps:

-   Make sure your installation fulfills all of the :doc:`/Prerequisites`.

    ..  note::

        As all of Erebot's PHAR archives (core and modules) are digitally
        signed, you must make sure the OpenSSL extension is enabled on your
        PHP installation. Failure to do so will result in an error when trying
        to run Erebot's PHAR archive.

-   Download the PHAR archive for Erebot itself. You can grab the latest
    version from our `release page <github.com/Erebot/Erebot/releases/latest/>`.
    You MUST also download the public signature for the archive.
    The signature is available for download alongside the PHAR archive.

-   Create a directory named :file:`modules` in the same folder as the PHAR.

-   Download the PHAR archive and its signature for each of the following
    modules:

    -   `Erebot_Module_AutoConnect <https://github.com/Erebot/Module_AutoConnect/releases/latest/>`_
    -   `Erebot_Module_IrcConnector <https://github.com/Erebot/Module_IrcConnector/releases/latest/>`_
    -   `Erebot_Module_PingReply <https://github.com/Erebot/Module_PingReply/releases/latest/>`_

    ..  note::

        You **MUST** copy both the PHAR archives and their signature in the
        :file:`modules` directory. Otherwise, PHP will refuse to load those
        PHAR archives because it cannot check their origin and integrity.

    Make sure you also read each module's documentation to look for any additional
    prerequisites.

-   (Optional) Download the PHAR archives & signature of any additional module
    you would like to use.

Your tree should now look like this:

    * Erebot/
        * :file:`Erebot-{X.Y.Z}.phar`
        * :file:`Erebot-{X.Y.Z}.phar.pubkey`
        * modules/
            * :file:`Erebot_Module_AutoConnect-{X.Y.Z}.phar`
            * :file:`Erebot_Module_AutoConnect-{X.Y.Z}.phar.pubkey`
            * :file:`Erebot_Module_IrcConnector-{X.Y.Z}.phar`
            * :file:`Erebot_Module_IrcConnector-{X.Y.Z}.phar.pubkey`
            * :file:`Erebot_Module_PingReply-{X.Y.Z}.phar`
            * :file:`Erebot_Module_PingReply-{X.Y.Z}.phar.pubkey`
            * *eventually, additional PHAR archives with their signature*

Once the PHAR archives have been retrieved, you may wish to change file
permissions on :file:`Erebot-{X.Y.Z}.phar`:

    ..  sourcecode:: bash

        $ chmod 0755 Erebot-*.phar

This way, you may later launch Erebot simply by executing the PHAR archive:

    ..  sourcecode:: bash

        $ ./Erebot-*.phar

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


..  _`Composer installation`:

Installation using Composer
---------------------------

First, make sure `Composer`_ is installed. If not, follow
the `installation instructions <https://getcomposer.org/download/>`
on their website.

-   Create a new folder named :file:`Erebot` and go into that folder:

    ..  sourcecode:: bash

        me@localhost:~/$ mkdir Erebot
        me@localhost:~/$ cd Erebot

-   Use `Composer`_ to install the bot's code:

    ..  sourcecode:: bash

        me@localhost:~/Erebot/$ php /path/to/composer.phar require --update-no-dev erebot/erebot erebot/ircconnector-module erebot/pingreply-module erebot/autoconnect-module

    You may pass additional module names if you want to use other modules.

-   Next, if you're an Erebot developer, install development dependencies as well:

    ..  sourcecode:: bash

        me@localhost:~/Erebot/$ php /path/to/composer.phar update

That's it! The bot is now installed.
Be sure to read the section on `final steps`_ for a summary of what to do next.


Final steps
-----------

Once Erebot (core files + a few modules) has been installed, you can
`write a configuration file <Configuration.html>`_ for Erebot (usually named :file:`Erebot.xml`).

When this is done, the bot can be started, assuming that PHP can be found
in your :envvar:`PATH` using one of the following commands.
Exactly what command must be used depends on the installation method.

..  sourcecode:: bash

    # For an installation using PHAR archives.
    # Must be run from the folder in which Erebot was installed.
    $ php ./Erebot-<version>.phar

    # For an installation using Composer.
    # Must be run from the folder in which Erebot was installed.
    $ php ./vendor/bin/Erebot

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

..  _`Composer`:
    https://getcomposer.org/

.. vim: ts=4 et
