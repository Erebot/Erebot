..  _`prerequisites`:

Prerequisites
=============

This page assumes that the reader has a working PHP setup (either installed
using some distribution's package manager or manually) and lists
the dependencies required to use Erebot.

For now, Erebot is known to work on all PHP versions >= 5.3.3.
Also, Erebot should run correctly on both Windows and most Linux distributions.

..  contents:: Table of Contents
    :local:


.. _`dependencies_for_regular_users`:

Dependencies for regular users
------------------------------

The following table lists the PHP extensions that need to be available
for the bot to work correctly.

Most of these extensions are actually part of PHP's core extensions
and are thus usually enabled by default. Nonetheless, in case you compiled
PHP yourself, you may need to recompile it to include the necessary extensions.

..  list-table:: Required PECL extensions for regular users
    :widths: 20 80
    :header-rows: 1

    *   -   Dependency
        -   Description

    *   -   :php:`ctype`
        -   The ``ctype`` extension provides functions to test various
            properties of an input string.

    *   -   :php:`DOM`
        -   The ``DOM`` extension parses an |XML| document into a |DOM|,
            making it easier to work with from a developer's point of view.

    *   -   :php:`intl`
        -   Provides several helper classes to ease work on |i18n|
            in PHP applications.

    *   -   :php:`libxml`
        -   This extension is a thin wrapper above the C `libxml2`_ library
            and is used by other extensions (DOM, SimpleXML, XML, etc.) that
            deal with |XML| documents.

    *   -   :php:`openssl` [#footnotes_openssl]_
        -   Provides `SSL`_/`TLS`_ support (secure communications) for PHP.

    *   -   :php:`pcntl` [#footnotes_pcntl]_
        -   Process management using PHP. The functions provided by this
            extension can be used to communicate with other processes
            from PHP (using signals) and to exercise some sort of control
            over them.

    *   -   :php:`PCRE`
        -   Provides Perl-Compatible Regular Expressions for PHP.

    *   -   :php:`Phar` [#footnotes_phar_run]_
        -   This extension is used to access a PHP Archive (phar) files.

    *   -   :php:`POSIX` [#footnotes_posix]_
        -   Provides access to several functions only featured by
            `POSIX`_-compliant operating systems.

    *   -   :php:`Reflection`
        -   This extension makes it possible for some PHP code to inspect its
            own structure.

    *   -   :php:`SimpleXML`
        -   Wrapper around `libxml2`_ designed to make working with |XML|
            documents easier.

    *   -   :php:`sockets`
        -   This extensions provides networking means for PHP applications.

    *   -   :php:`SPL`
        -   The `Standard PHP Library`_ provides several functions and classes
            meant to deal with common usage patterns, improving code reuse.

    *   -   One of :php:`mbstring`, :php:`iconv`, :php:`recode` or :php:`XML`
        -   These extensions make it possible to re-encode some text (also
            known as transcoding) from one encoding to another.
            ``mbstring`` and ``iconv`` support a wider set of encodings
            and are thus recommended over the other extensions.


Additional dependencies for Erebot developers
---------------------------------------------

These dependencies are only necessary if you want to participate into
the bot's development. For regular usage, refer to the list of
:ref:`dependencies_for_regular_users`.

Erebot developers should install both the dependencies from the regular set
plus the ones listed below in order to get a working setup.

For Linux, those dependencies can usually be installed by issuing
one of the following commands **as a privileged user**:

..  sourcecode:: bash

    root@localhost:~# # For apt-based distributions (eg. Debian, Ubuntu).
    root@localhost:~# apt-get install <package>

    root@localhost:~# # For yum-based distributions (eg. Fedora, RedHat, CentOS).
    root@localhost:~# yum install <package>

    root@localhost:~# # For recent versions of Fedora, RedHat and CentOS.
    root@localhost:~# dnf install <package>

    root@localhost:~# # For urpmi-based distributions (eg. Mandriva).
    root@localhost:~# urpmi <package>

    root@localhost:~# # For Zypper-based distributions (eg. SuSE)
    root@localhost:~# zypper install <package>

Please refer to your distribution's documentation for more information.

For Windows, each dependency must be downloaded and installed separately
as there is no central package manager like on Linux.


System dependencies
~~~~~~~~~~~~~~~~~~~

The following table lists the necessary system dependencies required for
Erebot's development. For apt-based systems like Debian/Ubuntu,
an installation link is also provided. Instructions for Windows users
are also provided.

..  list-table:: System dependencies for developers
    :widths: 15 20 65
    :header-rows: 1

    *   -   Dependency
        -   APT link
        -   Description

    *   -   `doxygen <http://www.stack.nl/~dimitri/doxygen/download.html#latestsrc>`_
        -   `Install this package <apt:doxygen>`__
        -   ``doxygen`` is needed if you plan to generate the documentation
            from Erebot's source files. We recommend version 1.8.0 or later.

            Windows users may download a pre-built binary release
            from `Doxygen's download page`_.


    *   -   gettext
        -   `Install this package <apt:gettext>`__
        -   The ``gettext`` package provides the ``xgettext`` command-line
            program used to extract messages marked for translation.

            ..  note::

                This is **NOT** the same as the PHP ``gettext`` extension.

            Installing gettext for Windows is a bit more tedious.
            First, go to http://ftp.gnome.org/pub/gnome/binaries/win32/dependencies/
            and download the latest version (0.18.1.1-2 as of this writing)
            of the following archives :

            * `gettext-runtime-dev <http://ftp.gnome.org/pub/gnome/binaries/win32/dependencies/gettext-runtime-dev_0.18.1.1-2_win32.zip>`_
            * `gettext-runtime <http://ftp.gnome.org/pub/gnome/binaries/win32/dependencies/gettext-runtime_0.18.1.1-2_win32.zip>`_
            * `gettext-tools-dev <http://ftp.gnome.org/pub/gnome/binaries/win32/dependencies/gettext-tools-dev_0.18.1.1-2_win32.zip>`_

            Unzip each of these files to the same target folder (eg. ``C:\gettext``).

            ..  note::

                So as to avoid potential issues, we recommend that you unzip the files
                in a folder whose name is both short (eg. your disk drive's root)
                and does not contain any special character (eg. no spaces).

            Once you are done, point your system's :envvar:`PATH` environment variable
            to that folder's ``bin`` subdirectory (ie. ``C:\gettext\bin``).
            The remaining folders (lib, include, share, etc.) are not required
            and can safely be removed if disk space is an issue.

    *   -   `xmlstarlet <http://xmlstar.sourceforge.net/download.php>`_
        -   `Install this package <apt:xmlstarlet>`__
        -   ``xmlstarlet`` is a |CLI| tool that simplifies XML files editing.
            We use it during packaging to set various settings in the
            :file:`package.xml` file.

            Windows users may download a pre-build binary release
            from the project's `download page <http://xmlstar.sourceforge.net/download.php>`_.


PHP extensions
~~~~~~~~~~~~~~ 

The following table lists additional PHP extension that need to be installed
by Erebot developers.

..  list-table:: Required PECL extensions for Erebot developers
    :widths: 20 80
    :header-rows: 1

    *   -   Dependency
        -   Description

    *   -   :php:`Phar` [#footnotes_phar_package]_
        -   This extension is used to create a PHP Archive (phar) containing
            the bot's code, providing users with an easy way to install Erebot.

            This extension is part of PHP core on Windows and so, Windows users
            don't need to do anything specific to benefit from it.

    *   -   `xdebug <http://xdebug.org/>`_ [#footnotes_xdebug]_
        -   Debugging execution of PHP code is made possible by this extension.
            It is also used to retrieve code coverage information while testing
            the code.

            Pre-built binary releases for Windows can be downloaded from
            `Xdebug's download page <https://xdebug.org/download.php>`_.
            Make sure to download the build matching your PHP installation
            (same VC version, same thread-safe support & same architecture).

    *   -   :php:`XSL`
        -   The XSL extension implements the XSL standard, performing
            `XSLT transformations`_ using the `libxslt library`_.

            This extension is bundled with PHP on Windows and so, Windows users
            only need to activate it through the :file:`php.ini` configuration
            file.

..  [#footnotes_openssl]
    Needed if you want to connect to IRC servers using a secure
    (encrypted) connection. Also required when running Erebot
    from a PHAR archive to check the archive's integrity.

..  [#footnotes_pcntl]
    Required for daemonization and to change user/group information
    upon startup. (not available on Windows)

..  [#footnotes_phar_package]
    Required to package Erebot as a ``.phar`` archive.

..  [#footnotes_phar_run]
    Required to run Erebot from a ``.phar`` archive.

..  [#footnotes_posix]
    Required to change user/group information upon startup.
    (not available on Windows)

..  [#footnotes_xdebug]
    Only required to run the test suite.

..  |---| unicode:: U+02014 .. em dash
    :trim:
..  |CLI|   replace:: :abbr:`CLI (Command-Line Interface)`
..  |i18n|  replace:: :abbr:`i18n (internationalization)`
..  |XML|   replace:: :abbr:`XML (eXtensible Markup Language)`
..  |DOM|   replace:: :abbr:`DOM (Document Object Model)`

..  _`Standard PHP Library`:
    http://php.net/spl
..  _`XSLT transformations`:
    http://www.w3.org/TR/xslt
..  _`libxslt library`:
    http://xmlsoft.org/XSLT/
..  _`pear.pdepend.org/PHP_Depend`:
..  _`gettext`:
    http://www.gnu.org/s/gettext/
..  _`libxml2`:
    http://xmlsoft.org/
..  _`SSL`:
    http://en.wikipedia.org/wiki/Secure_Sockets_Layer
..  _`TLS`:
    http://en.wikipedia.org/wiki/Transport_Layer_Security
..  _`POSIX`:
    http://en.wikipedia.org/wiki/Posix
..  _`SQLite`:
    http://www.sqlite.org/
..  _`install Cygwin`:
    http://cygwin.com/setup.exe
..  _`Cygwin's website`:
    http://www.cygwin.com/
..  _`Doxygen's download page`:
    http://www.stack.nl/~dimitri/doxygen/download.html#latestsrc

.. vim: ts=4 et
