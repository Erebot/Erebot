..  _`prerequisites`:

Prerequisites
=============

This page assumes that the reader has a working PHP setup (either installed
using some distribution's package manager or manually) and lists
the dependencies required to use Erebot.
In case you compiled PHP yourself, you may need to recompile it to include
additional extensions (see the list of required `PHP dependencies`_ for more
information).

As of this writing, Erebot is known to work on all PHP versions since PHP 5.2.1.
Also, Erebot should run correctly on both Windows (XP or later) and most Linux
distributions.
The code is tested using an automated process on several operating systems,
as reflected by our `Continuous Integration server`_.

..  contents:: Table of Contents
    :local:


How to read this page
---------------------

Each dependency is associated with a "profile" which indicates who might be
interested in installing that particular dependency.
Currently, the following profiles are used:

..  glossary::
    :sorted:

    end-user
        Someone who wants to run the bot, ie. have it connect to and interact
        with some IRC server. Only a few dependencies are required for this
        profile.

    packager
        Someone who creates (PHAR) packages from Erebot's source code.
        This profile does not include distribution maintainers.

    developer
        Someone who contributes to the project, eg. by sending patches or pull
        requests. The developer profile is usually a superset of the end-user
        profile as developers tend to run the bot to test their work.

You only need to install the dependencies associated with the profile that
best matches what you plan on doing.


System dependencies
-------------------

The following table lists system dependencies. Make sure to read
the installation instructions relevant to your system **before**
you start the installation:

* `Instructions for Linux users`_
* `Instructions for Windows users`_

For each profile, a "yes" on a dependency's row indicates that users of the
given profile **MUST** install that dependency (requirement). Optional
dependencies are indicated using footnotes which state when the dependency
may be of any interest for the given profile.

For apt-based systems like Debian/Ubuntu, an installation link is also provided.

..  list-table:: System dependencies for Erebot
    :widths: 10 10 5 5 5 65
    :header-rows: 1

    *   -   Dependency
        -   APT link
        -   :term:`Developer`
        -   :term:`Packager`
        -   :term:`End-user`
        -   Description
    *   -   `doxygen <http://www.stack.nl/~dimitri/doxygen/download.html#latestsrc>`_
        -   `Install using apt <apt:doxygen>`__
        -   yes
        -   yes
        -
        -   ``doxygen`` is needed if you plan to generate the documentation
            from Erebot's source files. We recommend version 1.7.2 or later
            as Erebot makes heavy use of PHP type-hinting and ``doxygen``
            did not support that until 1.7.2.
    *   -   gettext
        -   `Install using apt <apt:gettext>`__
        -   yes
        -   yes
        -
        -   The ``gettext`` package provides the ``xgettext`` command-line
            program used to extract messages marked for translation.

            ..  note::

                This is **NOT** the same as the PHP ``gettext`` extension.
    *   -   `xmlstarlet <http://xmlstar.sourceforge.net/download.php>`_
        -   `Install using apt <apt:xmlstarlet>`__
        -
        -   yes
        -
        -   ``xmlstarlet`` is a |CLI| tool that simplifies XML files editing.
            We use it during packaging to set various settings in the
            :file:`package.xml` file.


Instructions for Linux users
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

It is assumed that the reader has a working package manager which can be used
to install those dependencies, usually by issuing one of the following commands
**as a privileged user**, followed by the name of the package that provides
the dependency:

..  sourcecode:: bash

    root@localhost:~# # For apt-based distributions (eg. Debian, Ubuntu).
    root@localhost:~# apt-get install <package>

    root@localhost:~# # For yum-based distributions (eg. Fedora, RedHat, CentOS).
    root@localhost:~# yum install <package>

    root@localhost:~# # For urpmi-based distributions (eg. Mandriva).
    root@localhost:~# urpmi <package>

    root@localhost:~# # For Zypper-based distributions (eg. SuSE)
    root@localhost:~# zypper install <package>

Instructions for Windows users
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

As Windows lacks a central package manager, each dependency must be downloaded
and installed separately, using its specific procedure.

For Doxygen, this is easy, just go to `Doxygen's download page`_.
The same goes for XMLStarlet whose Windows version can easily be downloaded
from their `download page <http://xmlstar.sourceforge.net/download.php>`_.

Installing gettext for Windows is a little bit thougher.
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
    and does not contain any special character (including spaces).

Once you are done, point your system's :envvar:`PATH` environment variable
to that folder's ``bin`` subdirectory (ie. ``C:\gettext\bin``).
The remaining folders (lib, include, share, etc.) are not required
and can safely be removed if disk space is an issue.

This setup is what we use to test Erebot on Windows on our
`Continuous Integration server`_.


PHP dependencies
----------------

There are two kinds of dependencies:

PEAR packages
    These packages contain (reusable) PHP code. They are downloaded from the
    `PHP Extension and Application Repository`_.

PECL packages
    These packages contain code (usually written in C) that extends PHP
    with new features or changes the behaviour of existing features.
    They are downloaded from the `PHP Extension Community Library`_.

For each dependency, a short description as well as the profiles that are
likely to be interested in installing that dependency are listed.


Compatible installers
~~~~~~~~~~~~~~~~~~~~~

To install Erebot's PHP dependencies, you will need a compatible installer.
There are currently two of them:

`pear`_
    The original installer, meant to install both PEAR and PECL packages.
    The simplest way to `install pear`_ is to grab a copy of
    `go-pear.phar <http://pear.php.net/go-pear.phar>`_ and run this command
    from a shell:

    ..  sourcecode:: bash

        $ php go-pear.phar

    Then, to install a dependency using `pear`_, run the following command:

    ..  sourcecode:: bash

        $ pear install <dependency>

`Pyrus`_ (highly experimental, see below)
    Successor for `pear`_, meant to replace it someday.
    Pyrus provides the means to install and manage installations for packages
    built using package.xml version 2.0 or newer. Pyrus is redesigned from
    the ground up for PHP 5.3 or newer, and provides significant improvements
    over the older PEAR Installer.
    The latest version can be downloaded from
    `this link <http://pear2.php.net/pyrus.phar>`_.

    To install a dependency using `Pyrus`_, run the following command:

    ..  sourcecode:: bash

        $ php pyrus.phar install <dependency>


..  warning::

    At the time of this writing, `Pyrus`_ is still in development, with only
    alpha releases currently available.
    Pyrus may corrupt your system when using its default configuration
    and is also known to install pear packages incorrectly under certain
    circumstances.
    Unless you know exactly what you are doing, we recommend that you stick
    to the regular `pear`_ tool to install Erebot.
    See https://github.com/pyrus/Pyrus/issues/8
    and https://github.com/pyrus/Pyrus/issues/26 for more information.


PECL extensions
~~~~~~~~~~~~~~~

The following table lists the PECL extensions needed to use Erebot.
You may notice that most of these extensions are actually part of PHP Core.

For each profile, a "yes" on a dependency's row indicates that users of the
given profile **MUST** install that dependency (requirement). Optional
dependencies are indicated using footnotes which state when the dependency
may be of any interest for the given profile.

Unless you have a good reason not to (such as when testing backward
compatibility), we recommend that you always install the latest version
available for each dependency.

..  list-table:: PECL extensions used by Erebot
    :widths: 15 5 5 5 70
    :header-rows: 1

    *   -   Dependency
        -   :term:`Developer`
        -   :term:`Packager`
        -   :term:`End-user`
        -   Description
    *   -   :php:`DOM`
        -   yes
        -
        -   yes
        -   The DOM extension parses an |XML| document into a |DOM|, making it
            easier to work with from a developer's point of view.
    *   -   :php:`intl`
        -   yes
        -   yes
        -   yes
        -   Provides several helper classes to ease work on |i18n|
            in PHP applications.
    *   -   :php:`libxml`
        -   yes
        -
        -   yes
        -   This extension is a thin wrapper above the C `libxml2`_ library
            and is used by other extensions (DOM, SimpleXML, XML, etc.) that
            deal with |XML| documents.
    *   -   :php:`openssl`
        -
        -
        -   [#footnotes_openssl]_
        -   Provides `SSL`_/`TLS`_ support (secure communications) for PHP.
    *   -   :php:`pcntl`
        -
        -
        -   [#footnotes_pcntl]_
        -   Process management using PHP. The functions provided by this
            extension can be used to communicate with other processes
            from PHP (using signals) and to exercise some sort of control
            over them.
    *   -   :php:`Phar`
        -
        -   [#footnotes_phar_package]_
        -   [#footnotes_phar_run]_
        -   This extension is used to create or access a PHP Archive (phar).
    *   -   :php:`POSIX`
        -
        -
        -   [#footnotes_posix]_
        -   Provides access to several functions only featured by
            `POSIX`_-compliant operating systems.
    *   -   :php:`Reflection`
        -   yes
        -
        -   yes
        -   This extension makes it possible for some PHP code to inspect its
            own structure.
    *   -   :php:`SimpleXML`
        -   yes
        -
        -   yes
        -   Wrapper around `libxml2`_ designed to make working with |XML|
            documents easier.
    *   -   :php:`sockets`
        -   yes
        -
        -   yes
        -   This extensions provides networking means for PHP applications.
    *   -   :php:`SPL`
        -   yes
        -
        -   yes
        -   The `Standard PHP Library`_ provides several functions and classes
            meant to deal with common usage patterns, improving code reuse.
    *   -   `xdebug <http://xdebug.org/>`_
        -   [#footnotes_xdebug]_
        -
        -
        -   Debugging execution of PHP code is made possible by this extension.
            It can also be used to retrieve some metrics on the code (like
            code coverage information).
    *   -   :php:`XSL`
        -   yes
        -
        -
        -   The XSL extension implements the XSL standard, performing
            `XSLT transformations`_ using the `libxslt library`_.
    *   -   :php:`mbstring`
            or :php:`iconv`
            or :php:`recode`
            or :php:`XML`
        -   yes
        -
        -   yes
        -   These extensions make it possible to re-encode some text (also
            known as transcoding) from one encoding to another.
            ``mbstring`` and ``iconv`` support a wider set of encodings than
            the other extensions and are thus recommended.


PEAR packages
~~~~~~~~~~~~~

The following table lists the PEAR packages needed to use Erebot.

For each profile, a "yes" on a dependency's row indicates that users of the
given profile **MUST** install that dependency (requirement). Optional
dependencies are indicated using footnotes which state when the dependency
may be of any interest for the given profile.

Unless you have a good reason not to (such as when testing backward
compatibility), we recommend that you always install the latest version
available for each dependency.

..  list-table:: PEAR packages used by Erebot
    :widths: 20 5 5 5 65
    :header-rows: 1

    *   -   Dependency
        -   Developer
        -   Packager
        -   End-user
        -   Description
    *   -   `pear.pdepend.org/PHP_Depend`_
        -   [#footnotes_qa_depend]_
        -
        -
        -   PHP Depend gives several metrics on PHP code such as adherence
            between classes.
    *   -   `pear.phing.info/Phing`_  >= 2.4.3
        -   yes
        -   yes
        -
        -   |phing| is a PHP project build tool based on `Apache Ant`_.
            It is heavily used by Erebot which provides phing targets for
            most operations you may use.
    *   -   :pear:`Console_CommandLine`
        -   yes
        -
        -   yes
        -   Parses command line arguments. This is used by Erebot to provide
            options for the bot (eg. to change the path to the configuration
            file, to start the bot in the background, etc.).
    *   -   :pear:`File_Gettext`
        -   yes
        -
        -   yes
        -   Erebot uses this PEAR package to handle |i18n|. It can be used to
            parse `gettext`_ translation catalogs, like the ones provided
            with Erebot.
    *   -   :pear:`PHP_CodeSniffer`
        -   yes [#footnotes_qa_codesniffer]_
        -
        -
        -   This package tokenizes PHP files and detects violations of a
            defined set of coding standards. It is used by Erebot developers
            to make sure new patches comply with `Erebot's coding standard`_.
    *   -   :pear:`PHP_ParserGenerator`
        -   yes
        -   yes
        -
        -   This package is is a port of the `Lemon parser generator`_ for PHP
            and is used by Erebot and its modules to create parsers for several
            grammars (eg. to parse expressions in styles).
    *   -   `pear.phpmd.org/PHP_PMD`_
        -   [#footnotes_qa_mess]_
        -
        -
        -   The PHP Mess Detector parses PHP files to detect overly complex
            code patterns, making it easier for developpers to refactor their
            code and to improve its readability.
    *   -   `pear.phpunit.de/phpcpd`_
        -   [#footnotes_qa_duplicates]_
        -
        -
        -   The PHP Copy/Paste Detector detects abusive duplication of PHP code.
    *   -   `pear.phpunit.de/PHPUnit`_ >= 3.4.0
        -   [#footnotes_phpunit]_
        -
        -
        -   PHP unit test framework used by Erebot. Pull requests should
            generally contain one or more unit test before they can be
            considered for review.

..  [#footnotes_openssl]
    Needed if you want to connect to IRC servers using a secure
    (encrypted) connection. Required when running Erebot from a PHAR archive
    (used to check the archive's origin and integrity).

..  [#footnotes_pcntl]
    Required for daemonization and to change user/group information
    upon startup. Not available on Windows.

..  [#footnotes_phar_package]
    Only required to package Erebot as a ``.phar`` archive.

..  [#footnotes_phar_run]
    Only required to run Erebot from a ``.phar`` archive.

..  [#footnotes_posix]
    Required to change user/group information upon startup.
    Not available on Windows.

..  [#footnotes_xdebug]
    Only required to run the test suite.

..  [#footnotes_qa_depend]
    Required to use the ``qa_depend`` phing target.

..  [#footnotes_qa_codesniffer]
    Required to use the ``qa_codesniffer`` phing target,
    which should **ALWAYS** be called before submitting a patch.

..  [#footnotes_qa_mess]
    Required to use the ``qa_mess`` phing target.

..  [#footnotes_qa_duplicates]
    Required to use the ``qa_duplicates`` phing target.

..  [#footnotes_phpunit]
    Required to use any of the ``qa_coverage``, ``qa_test``,
    ``test`` or ``tests`` phing targets.

..  |---| unicode:: U+02014 .. em dash
    :trim:
..  |CLI|   replace:: :abbr:`CLI (Command-Line Interface)`
..  |phing| replace:: :abbr:`phing (PHing Is Not GNU make)`
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
    http://pdepend.org/
..  _`pear.phing.info/Phing`:
    http://phing.info/
..  _`pear.phpmd.org/PHP_PMD`:
    http://phpmd.org/
..  _`pear.phpunit.de/phpcpd`:
    https://github.com/sebastianbergmann/phpcpd
..  _`pear.phpunit.de/PHPUnit`:
    http://phpunit.de/
..  _`Continuous Integration server`:
    https://buildbot.erebot.net/components/
..  _`PHP Extension and Application Repository`:
    http://pear.php.net/
..  _`PHP Extension Community Library`:
    http://pecl.php.net/
..  _`ABNF grammar`:
    http://en.wikipedia.org/wiki/Augmented_Backus%E2%80%93Naur_Form
..  _`pear`:
    http://pear.php.net/package/PEAR
..  _`install pear`:
    http://pear.php.net/manual/en/installation.php
..  _`Pyrus`:
    http://pyrus.net/
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
..  _`Apache Ant`:
    http://ant.apache.org/
..  _`Erebot's coding standard`:
    Coding_Standard.html
..  _`install Cygwin`:
    http://cygwin.com/setup.exe
..  _`Cygwin's website`:
    http://www.cygwin.com/
..  _`Doxygen's download page`:
    http://www.stack.nl/~dimitri/doxygen/download.html#latestsrc
..  _`Lemon parser generator`:
    http://www.hwaci.com/sw/lemon/lemon.html

.. vim: ts=4 et
