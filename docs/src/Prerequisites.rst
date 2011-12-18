Prerequisites
=============

This page assumes that the reader has a working PHP setup (either installed
using some distribution's package manager or manually) and lists
the dependencies required to use Erebot.
In case you compiled PHP yourself, you may need to recompile it to include
additional extensions (see the list of required `PHP dependencies`_ for more
information).

As of this writing, Erebot is known to work on PHP versions ranging from
PHP 5.2.1 up to PHP 5.4.0RC1. Also, Erebot should run correctly on both
Windows (XP or later) and Linux (most distros).
The code is tested using an automated process on both Windows XP (32 bits)
and Linux Debian Stable (64 bits), as reflected by our
`Continuous Integration server`_.

..  contents::


How to read this page
---------------------

Each dependency is associated with a "profile" which indicates who might be
interested in installing that particular dependency.
Currently, the following profiles are used:

"end-user"
    Someone who wants to run the bot, ie. have it connect to and interact
    with some IRC server. Only a few dependencies are required for this
    profile.

"packager"
    Someone who creates packages from Erebot's source code. Currently, this
    refers only to the people who create PEAR packages (both v1 and v2) or
    PHAR archives based on the code. This profile does not include
    distribution maintainers (yet).

"developer"
    Someone who contributes to the project, eg. by sending patches or pull
    requests. The developer profile is usually a superset of the end-user
    profile as developers tend to run the bot to test their work.

You only need to install the dependencies associated with the profile that
best matches what you plan on doing.


System dependencies
-------------------

The following table lists system dependencies. It is assumed that the reader
has a working package manager which can be used to install those dependencies,
usually by issuing one of these commands, followed by the name of the package
which contains the dependency:

*   For Linux distributions

    ..  sourcecode:: bash

        # For apt-based distributions (eg. Debian, Ubuntu).
        $ apt-get install <package>

        # For urpmi-based distributions (eg. Mandriva).
        $ urpmi <package>

        # For yum-based distributions (eg. RedHat, CentOS).
        $ yum install <package>

*   For Windows systems, please refer to the special instructions given
    below.

For each profile, a "yes" on a dependency's row indicates that users of the
given profile **MUST** install that dependency (requirement). Optional
dependencies are indicated using footnotes which state when the dependency
may be of any interest for the given profile.

For apt-based systems, an installation link is provided (using the ``apt``
URI scheme).

..  table:: System dependencies for Erebot

    +---------------+-----------------------------------+-----------+-----------+-----------+-----------------------------------+
    | Dependency    | APT link                          | Developer | Packager  | End-user  | Description                       |
    +===============+===================================+===========+===========+===========+===================================+
    | gettext       | `Debian/Ubuntu <apt:gettext>`__   | yes       | yes       |           | The gettext package provides      |
    |               |                                   |           |           |           | the ``xgettext`` command-line     |
    |               |                                   |           |           |           | program used to extract messages  |
    |               |                                   |           |           |           | marked for translation.           |
    |               |                                   |           |           |           | **Note**: this is **NOT** the     |
    |               |                                   |           |           |           | same as the PHP ``gettext``       |
    |               |                                   |           |           |           | extension.                        |
    +---------------+-----------------------------------+-----------+-----------+-----------+-----------------------------------+
    | doxygen       | `Debian/Ubuntu <apt:doxygen>`__   | yes       | yes       |           | doxygen is needed if you plan to  |
    |               |                                   |           |           |           | generate the documentation from   |
    |               |                                   |           |           |           | Erebot's' source files.           |
    |               |                                   |           |           |           | We recommend version 1.7.2 or     |
    |               |                                   |           |           |           | later as Erebot makes heavy use   |
    |               |                                   |           |           |           | of PHP type-hinting and doxygen   |
    |               |                                   |           |           |           | did not support that until 1.7.2. |
    +---------------+-----------------------------------+-----------+-----------+-----------+-----------------------------------+


Special instructions for Windows users
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

As Windows lacks a central package manager, a different set of instructions is
necessary.

I have yet to find a gettext binary for Windows that ships with a recent version
of gettext and hence can support all the options used by Erebot.
For now, a workaround is to `install Cygwin`_ and its gettext package on your
machine. Refer to `Cygwin's website`_ for additional information.

Hopefully, installing Doxygen on Windows is a lot simpler. Just grab the binary
release relevant for your system from `Doxygen's download page`_.

This setup is what we use (combined with PHP 5.3.8) to test Erebot with our
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

Erebot uses both kinds of dependencies. A PHP dependency (package) can be
identified using the following `ABNF grammar`_::

    dependency     =  [ channel "/" ] PackageName [ "-" release ]
                                     ; A PEAR/PECL package name, eg. "pear/PEAR".
                                     ; "channel" defaults to "pear.php.net"
                                     ; for pear and "pecl.php.net" for pecl.
                                     ; "release" defaults to the preferred state
                                     ; as defined in pear's configuration.

    channel        =  alias / hostname
                                     ; Either an alias for an already-discovered
                                     ; PEAR channel or its full name.

    alias          =  ALPHA *ALNUM
                                     ; Same as [A-Za-z][A-Za-z0-9]*
                                     ; Aliases containing only lowercase
                                     ; letters ([a-z]*) are preferred,
                                     ; eg. "erebot".

    hostname       = 1*( domainlabel "." ) toplabel
                                     ; Internet hostname, but refuses toplevel
                                     ; hostnames (eg. "org", "com", "net") as
                                     ; they conflict with channel aliases.
                                     ; eg. "pear.erebot.net".
    domainlabel    = ALNUM / ALNUM *( ALNUM / "-" ) ALNUM
    toplabel       = ALPHA / ALPHA *( ALNUM / "-" ) ALNUM

    PackageName    =  UPPER *( ALNUM / "_" / "." )
                                     ; Same as [A-Z][A-Za-z0-9_\-]*
                                     ; eg. "HTTP_Request2"

    release        =  state / version
                                     ; either a state (eg. "alpha")
                                     ; or a specific release (eg. "2.0.0alpha3").

    state          =  "alpha" / "beta" / "stable"
    version        =  vnumber [ vtag ]
                                     ; eg. "2.0.0dev1"

    vnumber        =  1*DIGIT 2( "." 1*DIGIT )
                                     ; Three numbers separated by dots.
                                     ; eg. "0.0.1", "2.0.0", etc.

    vtag           =  tag tcounter   ; "dev1", "alpha2", "beta3",
                                     ; "RC4", "snapshot42", etc.

    tag            =  "dev" / "alpha" / "beta" / "RC" / "snapshot"
    tcounter       =  1*DIGIT        ; "1", "11", "123", etc.

    UPPER          =  %x41-5A        ; Same as [A-Z]
    LOWER          =  %x61-7A        ; Same as [a-z]
    ALNUM          =  ALPHA / DIGIT  ; Same as [a-zA-Z0-9]

In this section, each dependency will be identified using the channel's
fullname and any version information that may be relevant
(eg. ``pear.erebot.net/Erebot_API-0.0.1alpha2``).

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

`Pyrus`_
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


..  note::
    At the time of this writing, `Pyrus`_ is still in development, with only
    alpha releases currently available. For now, `pear`_ is still the preferred
    tool to install Erebot.

..  note::
    Despite the previous note, `Pyrus`_ is actually **required** for packagers
    due to the way the packaging process is currently implemented.
    In this case, both `pear`_ and `pyrus`_ **must** be installed side-by-side
    on your computer.

..  warning::
    Due to a `bug in Pyrus <https://github.com/pyrus/Pyrus/issues/26>`_,
    installation of a PEAR (version 1) package containing static data files,
    configuration data, tests or webpages will result in a corrupted
    installation. This affects Erebot as well as some of its dependencies.
    As a result, we ask that you **DO NOT** use `Pyrus`_ to install Erebot
    or its dependencies until this bug has been fixed. It is still safe to
    use it to **package** Erebot or its dependencies though.


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

..  table:: PECL extensions used by Erebot

    +-------------------------------+-----------+-----------+-----------+-----------------------------------+
    | Dependency                    | Developer | Packager  | End-user  | Description                       |
    +===============================+===========+===========+===========+===================================+
    | `pecl.php.net/DOM`_           | yes       |           | yes       | The DOM extension parses an XML   |
    |                               |           |           |           | document into a Document Object   |
    |                               |           |           |           | Model (DOM), making it easier to  |
    |                               |           |           |           | work with from a developer's      |
    |                               |           |           |           | point of view.                    |
    +-------------------------------+-----------+-----------+-----------+-----------------------------------+
    | `pecl.php.net/intl`_          | yes       | yes       | yes       | Provides several helper classes   |
    |                               |           |           |           | to ease internationalization of   |
    |                               |           |           |           | PHP applications.                 |
    +-------------------------------+-----------+-----------+-----------+-----------------------------------+
    | `pecl.php.net/libxml`_        | yes       |           | yes       | This extension is a thin wrapper  |
    |                               |           |           |           | over the C `libxml2`_ library     |
    |                               |           |           |           | and is used by other extensions   |
    |                               |           |           |           | (DOM, SimpleXML, XML, etc.) to    |
    |                               |           |           |           | work with XML documents.          |
    +-------------------------------+-----------+-----------+-----------+-----------------------------------+
    | `pecl.php.net/openssl`_       |           |           | [#]_      | Provides `SSL`_/`TLS`_ support    |
    |                               |           |           |           | (secure communications) for PHP.  |
    +-------------------------------+-----------+-----------+-----------+-----------------------------------+
    | `pecl.php.net/pcntl`_         |           |           | [#]_      | Process management using PHP.     |
    |                               |           |           |           | The functions provided by this    |
    |                               |           |           |           | extension can be used to          |
    |                               |           |           |           | communicate with other processes  |
    |                               |           |           |           | from PHP (using signals) and to   |
    |                               |           |           |           | exercise some sort of control     |
    |                               |           |           |           | over them.                        |
    +-------------------------------+-----------+-----------+-----------+-----------------------------------+
    | `pecl.php.net/Phar`_          |           | [#]_      | [#]_      | This extension is used to create  |
    |                               |           |           |           | or access a PHP Archive (phar).   |
    +-------------------------------+-----------+-----------+-----------+-----------------------------------+
    | `pecl.php.net/POSIX`_         |           |           | [#]_      | Provides access to several        |
    |                               |           |           |           | functions only featured by        |
    |                               |           |           |           | `POSIX`_-compliant operating      |
    |                               |           |           |           | systems.                          |
    +-------------------------------+-----------+-----------+-----------+-----------------------------------+
    | `pecl.php.net/Reflection`_    | yes       |           | yes       | This extension makes it possible  |
    |                               |           |           |           | for some PHP code to inspect its  |
    |                               |           |           |           | own structure.                    |
    +-------------------------------+-----------+-----------+-----------+-----------------------------------+
    | `pecl.php.net/SimpleXML`_     | yes       |           | yes       | Wrapper around `libxml2`_         |
    |                               |           |           |           | designed to make working with XML |
    |                               |           |           |           | documents easier.                 |
    +-------------------------------+-----------+-----------+-----------+-----------------------------------+
    | `pecl.php.net/sockets`_       | yes       |           | yes       | This extensions provides          |
    |                               |           |           |           | networking means for PHP          |
    |                               |           |           |           | applications.                     |
    +-------------------------------+-----------+-----------+-----------+-----------------------------------+
    | `pecl.php.net/SPL`_           | yes       |           | yes       | The `Standard PHP Library`_       |
    |                               |           |           |           | provides several functions and    |
    |                               |           |           |           | classes meant to deal with common |
    |                               |           |           |           | usage patterns, with code reuse   |
    |                               |           |           |           | as the main focus.                |
    +-------------------------------+-----------+-----------+-----------+-----------------------------------+
    | `pecl.php.net/SQLite3`_       |           | yes [#]_  |           | Wrapper around version 3 of the   |
    |                               |           |           |           | C `SQLite`_ library.              |
    +-------------------------------+-----------+-----------+-----------+-----------------------------------+
    | `pecl.php.net/xdebug`_        | yes       |           |           | Debugging execution of PHP code   |
    |                               |           |           |           | is made possible by this          |
    |                               |           |           |           | extension. It can also be used to |
    |                               |           |           |           | retrieve some metrics on the code |
    |                               |           |           |           | (like code coverage information). |
    +-------------------------------+-----------+-----------+-----------+-----------------------------------+
    | `pecl.php.net/XMLReader`_     |           | yes [#]_  |           | A simple extension to read XML    |
    |                               |           |           |           | documents without having to build |
    |                               |           |           |           | a full Document Object Model in   |
    |                               |           |           |           | memory first.                     |
    +-------------------------------+-----------+-----------+-----------+-----------------------------------+
    | `pecl.php.net/XMLWriter`_     |           | yes [#]_  |           | XMLReader's counterpart to write  |
    |                               |           |           |           | XML documents.                    |
    +-------------------------------+-----------+-----------+-----------+-----------------------------------+
    | `pecl.php.net/mbstring`_ or   | yes       |           | yes       | These extensions make it possible |
    | `pecl.php.net/iconv`_ or      |           |           |           | to re-encode some text (also      |
    | `pecl.php.net/recode`_ or     |           |           |           | known as transcoding) from one    |
    | `pecl.php.net/XML`_           |           |           |           | character set to another.         |
    |                               |           |           |           | **mbstring** or **iconv** is      |
    |                               |           |           |           | recommended as they support a     |
    |                               |           |           |           | wider range of character sets     |
    |                               |           |           |           | when compared to the other        |
    |                               |           |           |           | extensions.                       |
    +-------------------------------+-----------+-----------+-----------+-----------------------------------+

..  _`pecl.php.net/DOM`:
    http://php.net/dom
..  _`pecl.php.net/intl`:
    http://php.net/intl
..  _`pecl.php.net/libxml`:
    http://php.net/libxml
..  _`pecl.php.net/openssl`:
    http://php.net/openssl
..  _`pecl.php.net/pcntl`:
    http://php.net/pcntl
..  _`pecl.php.net/Phar`:
    http://php.net/phar
..  _`pecl.php.net/POSIX`:
    http://php.net/posix
..  _`pecl.php.net/Reflection`:
    http://php.net/reflection
..  _`pecl.php.net/SimpleXML`:
    http://php.net/libxml
..  _`pecl.php.net/sockets`:
    http://php.net/sockets
..  _`pecl.php.net/SPL`:
..  _`Standard PHP Library`:
    http://php.net/spl
..  _`pecl.php.net/SQLite3`:
    http://php.net/sqlite3
..  _`pecl.php.net/xdebug`:
    http://xdebug.org/
..  _`pecl.php.net/XMLReader`:
    http://php.net/xmlreader
..  _`pecl.php.net/XMLWriter`:
    http://php.net/xmlwriter
..  _`pecl.php.net/mbstring`:
    http://php.net/mbstring
..  _`pecl.php.net/iconv`:
    http://php.net/iconv
..  _`pecl.php.net/recode`:
    http://php.net/recode
..  _`pecl.php.net/XML`:
    http://php.net/xml

..  [#] Only needed if you want to connect to IRC servers using a secure
    (encrypted) connection.
..  [#] Required for daemonization and to change user/group information
    upon startup. Not available on Windows.
..  [#] Only required to package Erebot as a ``.phar`` archive.
..  [#] Only required to run Erebot from a ``.phar`` archive.
..  [#] Required to change user/group information upon startup.
    Not available on Windows.
..  [#] This dependency is inherited from Pyrus (we need it to package Erebot).
..  [#] This dependency is inherited from Pyrus (we need it to package Erebot).
..  [#] This dependency is inherited from Pyrus (we need it to package Erebot).


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

..  table:: PEAR packages used by Erebot

    +---------------------------------------+-----------+-----------+-----------+-----------------------------------+
    | Dependency                            | Developer | Packager  | End-user  | Description                       |
    +=======================================+===========+===========+===========+===================================+
    | `pear.pdepend.org/PHP_Depend`_        | [#]_      |           |           |                                   |
    +---------------------------------------+-----------+-----------+-----------+-----------------------------------+
    | `pear.phing.info/Phing`_  >= 2.4.3    | yes       | yes       |           | phing (PHing Is Not GNU make) is  |
    |                                       |           |           |           | a PHP project build system/tool   |
    |                                       |           |           |           | based on `Apache Ant`_.           |
    |                                       |           |           |           | It is heavily used by Erebot      |
    |                                       |           |           |           | which provides phing targets for  |
    |                                       |           |           |           | most operations you may use.      |
    +---------------------------------------+-----------+-----------+-----------+-----------------------------------+
    | `pear.php.net/Console_CommandLine`_   | yes       |           | yes       | Parses command line arguments.    |
    |                                       |           |           |           | This is used by Erebot to provide |
    |                                       |           |           |           | options for the bot (eg. to       |
    |                                       |           |           |           | change the path to the            |
    |                                       |           |           |           | configuration file, to start the  |
    |                                       |           |           |           | bot in the background, etc.).     |
    +---------------------------------------+-----------+-----------+-----------+-----------------------------------+
    | `pear.php.net/File_Gettext`_          | yes       |           | yes       | Erebot uses this PEAR package to  |
    |                                       |           |           |           | handle internationalization. It   |
    |                                       |           |           |           | can be used to parse `gettext`_   |
    |                                       |           |           |           | translation catalogs, like the    |
    |                                       |           |           |           | ones provided with Erebot.        |
    +---------------------------------------+-----------+-----------+-----------+-----------------------------------+
    | `pear.php.net/PHP_CodeSniffer`_       | yes [#]_  |           |           | This package tokenizes PHP files  |
    |                                       |           |           |           | and detects violations of a       |
    |                                       |           |           |           | defined set of coding standards.  |
    |                                       |           |           |           | It is used by Erebot developers   |
    |                                       |           |           |           | to make sure sure new patches     |
    |                                       |           |           |           | comply with                       |
    |                                       |           |           |           | `Erebot's coding standard`_.      |
    +---------------------------------------+-----------+-----------+-----------+-----------------------------------+
    | `pear.phpmd.org/PHP_PMD`_             | [#]_      |           |           | The PHP Mess Detector parses PHP  |
    |                                       |           |           |           | files to detect overly complex    |
    |                                       |           |           |           | code patterns, making it easier   |
    |                                       |           |           |           | for developpers to refactor their |
    |                                       |           |           |           | code to improve readability.      |
    +---------------------------------------+-----------+-----------+-----------+-----------------------------------+
    | `pear.phpunit.de/phpcpd`_             | [#]_      |           |           | The PHP Copy/Paste Detector       |
    |                                       |           |           |           | detects abusive duplication of    |
    |                                       |           |           |           | PHP code.                         |
    +---------------------------------------+-----------+-----------+-----------+-----------------------------------+
    | `pear.phpunit.de/PHPUnit`_ >= 3.4.0   | [#]_      |           |           | PHP unit test framework used by   |
    |                                       |           |           |           | Erebot. Pull requests should      |
    |                                       |           |           |           | generally contain one or more     |
    |                                       |           |           |           | unit test before they can be      |
    |                                       |           |           |           | considered for review.            |
    +---------------------------------------+-----------+-----------+-----------+-----------------------------------+

..  [#] Required to use the ``qa_depend`` phing target.
..  [#] Required to use the ``qa_codesniffer`` phing target,
    which should **ALWAYS** be called before submitting a patch.
..  [#] Required to use the ``qa_mess`` phing target.
..  [#] Required to use the ``qa_duplicates`` phing target.
..  [#] Required to use any of the ``qa_coverage``, ``qa_test``,
    ``test`` or ``tests`` phing targets.

..  _`pear.pdepend.org/PHP_Depend`:
    http://pdepend.org/
..  _`pear.phing.info/Phing`:
    http://phing.info/
..  _`pear.php.net/Console_CommandLine`:
    http://pear.php.net/Console_CommandLine
..  _`pear.php.net/File_Gettext`:
    http://pear.php.net/File_Gettext
..  _`pear.php.net/PHP_CodeSniffer`:
    http://pear.php.net/PHP_CodeSniffer
..  _`pear.phpmd.org/PHP_PMD`:
    http://phpmd.org/
..  _`pear.phpunit.de/phpcpd`:
    https://github.com/sebastianbergmann/phpcpd
..  _`pear.phpunit.de/PHPUnit`:
    http://phpunit.de/


..  |---| unicode:: U+02014 .. em dash
    :trim:

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

.. vim: ts=4 et
