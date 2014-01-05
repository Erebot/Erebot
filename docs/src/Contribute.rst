Contribute
==========

So, you took interest in Erebot and would like to contribute back?
This is the right page!

There are actually several ways by which would may contribute
to the project:

*   by `reporting new issues`_ (or asking for new features)
*   by submitting `new modules`_
*   by forking the code and sending `pull requests`_
*   by running the tests suite (manually or by providing a `test machine`_)
*   by submitting `new translations`_ (or patches for existing ones)

Whichever one it is, you may also `join our IRC channel`_ to discuss issues,
new ideas / feature requests and follow the bot's development.

..  _`new modules`:

New modules
-----------

If you wish to contribute new modules, you should probably start by reading
our :ref:`development guides <Writing a new module>`.
Once your code is ready, make sure to send an email to erebot@erebot.net
with a link to it so that your module can be added to our list of
:ref:`Third-party modules`.

..  _`pull requests`:

Code/patch for Erebot's core and API
------------------------------------

If you plan on sending patches (or pull requests), please read our
documentation on the `coding standard`_ used by Erebot developers first.
Your patch will have greater chances to be approved if it abides by that
standard when you submit it.

To contribute a patch, you will need a GitHub account. Then you can simply:

-   `Fork the code`_ to your own account.
-   Create a new branch.
-   Patch things up as much as you want.
-   Create a pull request with your changes.

Once your pull request has been received, it will undergo a review process
to decide whether it can be accepted as-is, needs more changes before having
a chance to be accepted or is utterly rejected.


..  _`test machine`:

..  toctree::
    :hidden:
    :maxdepth: 0

    Buildslaves

Test machines
-------------

So, you have some spare CPU cycles to contribute? We're glad to hear that!

The Erebot project uses `Buildbot`_ for its continuous integration.
The process of setting up a test machine (more oftenly called a "buildslave"
in Buildbot's terminology) is a bit tedious and deserves
`a page of its own <Buildslaves.html>`_.


..  _`new translations`:

Translations
------------

The project uses the `Transifex service`_ to manage translations.
All submissions or patches to translations should be submitted through
`Erebot's project page on Transifex`_.

To submit a new translation or a patch for an already existing translation,
you will need a Transifex account. Then, apply for one of the translation teams
or request the creation of a new team in case none currently exists for your
language. As soon as you have joined one of the translation teams, you may
proceed with your changes.

Just like for the code, translations undergo manual review. Once a translation
has been formally reviewed, it will automatically be merged in the project
alongside other translations.

..  note::
    There is a tight integration between the code and the translations.
    The translations on Transifex are periodically synchronized with the
    messages in the repository, meaning that any change / new message
    in the code quickly appears on the Transifex page.

    This goes both ways: changes to the translations are automatically
    merged in the code's repository once they have been approved by one
    of your team's reviewers.

..  note::
    It usually takes from 5 to 10 minutes after a translation has been
    approved before the changes are visible in the repository.


..  _`reporting new issues`:
    https://github.com/Erebot/Erebot/issues/new
..  _`sending pull requests`:
    https://github.com/Erebot/Erebot/pulls
..  _`join our IRC channel`:
    irc://irc.iiens.net/Erebot
..  _`coding standard`:
    Coding_Standard.html
..  _`Fork the code`:
    https://github.com/Erebot/Erebot/fork_select
..  _`Buildbot`:
    http://buildbot.net/
..  _`Transifex service`:
    https://www.transifex.net/
..  _`Erebot's project page on Transifex`:
    https://www.transifex.net/projects/p/Erebot/

.. vim: ts=4 et
