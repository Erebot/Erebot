Coding standard
===============

This page is only of interest for Erebot developers.

..  contents:: Table of Contents
    :local:


Writing code
------------

Erebot uses the :psr:`2` coding style for PHP code.

Compliance with this standard can be tested by running the following command
from the top directory:

..  sourcecode:: bash

    phing qa_codesniffer


Writing documentation
---------------------

For the code, we rely on `Doxygen commands <http://www.stack.nl/~dimitri/doxygen/commands.html>`_
to automatically extract the API documentation.
We use the ``\`` prefix for such commands as it is also recognized by other tools.
Also, we use the `\\copydoc <https://www.stack.nl/~dimitri/doxygen/manual/commands.html#cmdcopydoc>`_
command massively to avoid repeating ourselves while documenting the code.

The rest of the documentation (what you are currently reading) is managed using
the `Sphinx documentation generator <http://www.sphinx-doc.org>`_.

All the documentation is stored in the same Git repository as the code to help
keep both the code and documentation in sync.

Both documentation can be built by running this command from the top directory:

..  sourcecode:: bash

    phing doc


Writing tests
-------------

We use the `PHPUnit testing framework <https://phpunit.de/>`_ to write unit
and functionnal tests for the bot.

The tests are stored in the same Git repository as the code to help
keep both the code and its tests in sync.

The test suite can be run by using the following command from the top directory:

..  sourcecode:: bash

    phing tests


Other tools
-----------

We also use other tools to measure various metrics of the code,
like code complexity, code repetitions, and so on.
The full :abbr:`QA (Quality Assurance)` test suite can be run with:

..  sourcecode:: bash

    phing qa


..  |---|               unicode:: U+02014 .. em dash
    :trim:

..  vim: et ts=4

