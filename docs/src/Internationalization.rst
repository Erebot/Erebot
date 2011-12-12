Internationalization
====================

..  contents::


A practical example
-------------------

Supporting I18N in your module is very simple. All you need is an instance
of a translator (an instance of an object that implements
`Erebot_Interface_I18n`_) for the module.
A translator for the module is automatically created along instances of your
module.

Once equipped with a translator, just call its ``gettext()`` method, passing
the string to translate to it.
Therefore, translating a string is as simple as the following snippet:

..  code-block:: php

    <?php
        $translator = $this->getTranslator($chan);
        $translator->gettext('This text will be translated');
    ?>

..  todo::
    Explain the meaning of ``getTranslator()``'s ``$chan`` parameter.

Module developpers may also be interested in the other methods provided by
the translator object, presented in the `API documentation`_ for that class.

Managing translations
---------------------

In the previous section, we saw how to integrate strings that will be
translated. So... how does Erebot finds out the correct translation?

There is another special file in ``data/i18n/<module>.po`` where ``<module>``
is the name of your module (eg. ``Erebot_Module_XYZ``).
This file uses the `gettext`_ format and lists all messages marked as requiring
a translation (as extracted from your source code when running ``phing i18n``).

Also, every Erebot module contains a set of translations in
``data/i18n/<locale>/LC_MESSAGES/<module>.po``, where ``<locale>`` is some
locale identifier, expressed using the following format: ``xx_YY``
(where ``xx`` is the ISO 639-1 code for the language [#]_ and ``yy`` is the
code for the country, eg. ``en_US``) and ``<module>`` is the name of your
module (eg. ``Erebot_Module_XYZ``).
Those files use the same format as the previous file and provide the
translations for the messages listed in ``data/i18n/<module>.po``.

To ease management of the translations, and especially the PO files (also
called "catalogs"), a few tools are provided by Erebot as `phing`_ targets.
These tools are discussed below.

..  [#] See http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes

Extracting strings marked for translation
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The ``extract_messages`` target can be used to parse the code of your module
and extract strings marked for translation. This will write out every string
marked for translation into ``data/i18n/<module>.po``.
Example::

    phing extract_messages


Adding translations for a new locale
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Translations for a new locale can be added by using the ``init_catalog``
target and passing a ``locale`` parameter, like so::

    phing init_catalog -Dlocale=de_DE

Updating existing catalogs
~~~~~~~~~~~~~~~~~~~~~~~~~~

Updating the catalogs is quite simple, just use the ``update_catalog`` target::

    phing update_catalog

Compiling the catalogs
~~~~~~~~~~~~~~~~~~~~~~

Last but not least, the catalog files cannot be used directly by the bot.
You first need to compile them using the ``compile_catalog`` phing target::

    phing compile_catalog


This will generate MO files for the miscellaneous PO files described above.

Plurals
-------

Correct pluralization of sentences is a big challenge when dealing with i18n.

..  warning::
    Even though the `gettext`_ family of tools has some (incomplete, at least
    from my point of view) support for plurals, the original feature from
    `gettext`_ is not used by Erebot.

Erebot handles plurals in an elegant way, using a special set of markup in
the `styling API`_. Readers may be interested in the documentation on
`styling`_ for more information on plurals support.

..  _`phing`:
    http://phing.info/
..  _`gettext`:
    http://www.gnu.org/s/gettext/
..  _`Erebot_Interface_I18n`:
..  _`API documentation`:
    https://buildbot.erebot.net/doc/html/Erebot/interfaceErebot__Interface__I18n.html
..  _`styling API`:
    https://buildbot.erebot.net/doc/html/Erebot/interfaceErebot__Interface__Styling.html
..  _`styling`:
    Styling.html

.. vim: ts=4 et
