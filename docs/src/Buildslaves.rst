Buildslaves
===========

A buildslave is a machine that runs continuous integration tasks for the
project. Such tasks include running unit tests after every commit, building
the online documentation, packaging the project, etc.

If you wish to contribute a machine for the project, this page explains how to
do so.

..  contents::

Foreword
--------

..  note::
    Even though a buildslave can do many things, we only discuss use of
    buildslaves to run unit tests in the context of this page. That's because
    we do not intend to make remote machines handle critical tasks such as
    packaging that involve digital signatures.

..  warning::
    When running a buildslave, some PHP code will be run on your system without
    requiring any prior confirmation. This may represent a risk for your
    machine. Even though we do our best to prevent malicious code execution
    on the buildslaves, we still depend on external services (GitHub, Transifex,
    etc.) and we cannot guarantee that no malicious code will ever be injected
    in the repository.

    We therefore strongly encourage you to take every necessary precaution
    **before** running the buildslave. This means that you should at least:

    -   Make sure your system is running an up-to-date antivirus.
    -   Create a specific unprivileged account for the buildslave.
    -   Run the buildslave in a jail/chroot'ed environment on platforms
        that support this feature.

    By providing a buildslave, you agree that you understand and accept all of
    the risks mentionned above and that you are sole responsible for ensuring
    the security of your machine. If you don't, please **do not** submit a
    request to be registered as a buildslave.

We already host one buildslave and a few virtual machines that each come with
their own buildslave. At the time of this writing, Erebot is therefore tested
against the following operating systems / distributions:

-   Microsoft Windows XP SP3 i386 (virtual machine)
-   CentOS 6.2 i386
-   Debian 6.0 x86_64

Each buildslave runs with a variety of PHP versions (usually only one, but
sometimes more). We use `phpfarm`_ to manage the different versions.
Each version of PHP is tested separately and a buildslave can use
up to 10 different PHP versions.

Since the buildmaster (the server that tells the buildslaves to test something)
has no knowledge of what versions a given buildslave has, the buildslave must
declare the versions it supports. To do so, code such as the following must be
added at the very beginning of the buildslave's :file:`buildbot.tac` file:

..  sourcecode:: python

    import os
    os.environ['PHP1_PATH'] = '/home/foo/phpfarm/inst/php-5.2.17-debug/bin/:/home/foo/phpfarm/inst/php-5.2.17-debug/'
    os.environ['PHP2_PATH'] = '/home/foo/phpfarm/inst/php-5.3.10-debug/bin/:/home/foo/phpfarm/inst/php-5.3.10-debug/'
    os.environ['PHP3_PATH'] = '/home/foo/phpfarm/inst/php-5.4.0-debug/bin/:/home/foo/phpfarm/inst/php-5.4.0-debug/'
    os.environ['PHP1_DESC'] = '5.2.17-debug'
    os.environ['PHP2_DESC'] = '5.3.10-debug'
    os.environ['PHP3_DESC'] = '5.4.0-debug'
    os.environ['PHP_MAIN'] = '3'

The :envvar:`PHPx_PATH` environment variables specify additional directories
to add to the :envvar:`PATH` environment variable when using the PHP version
with identifier ``x``. You may specify multiple paths by using the appropriate
separator for your operating system (eg. colon on Linux, semi-colon on Windows).

The :envvar:`PHPx_DESC` lines specify the name of the executable to use to run
the PHP version with identifier ``x``.

Last but not least, :envvar:`PHP_MAIN` specifies what is considered the "main" PHP
version supported by the buildslave. When some PHP code must be run but we do
not care what version of PHP is used, the version with that identifier will be
used. In the example above, the main version is '3', which refers to
PHP 5.4.0-debug.

..  note::
    The versions may be numbered from 1 to 10 **with no gap in between**.
    Any gap in the numbering will result in the versions following the gap
    to not be tested at all.

    If you want to test the code against more than 10 different versions,
    either run a separate buildslave on the same machine with additional
    versions or contact us on IRC or GitHub so that we increase the current
    limit.

..  warning::
    At a minimum, you must define at least 3 variables (:envvar:`PHP1_PATH`,
    :envvar:`PHP1_DESC` and :envvar:`PHP_MAIN`, where :envvar:`PHP_MAIN`
    equals "1").

    When adding a new version of PHP to test against, you must always specify
    both the :envvar:`PHP{x}_PATH` and :envvar:`PHP{x}_DESC` variables


Microsoft Windows
-----------------

@TODO

Linux
-----

@TODO

..  _`phpfarm`:
    https://github.com/fpoirotte/phpfarm

.. vim: ts=4 et
