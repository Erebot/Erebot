Styling
=======

This page is only meant to guide you through the use of formatting codes with
Erebot, it is not meant as a complete documentation of how styles work with IRC.
If you want more, ask your favourite search engine ;-)

Erebot provides two ways to format messages. Both methods are described here.

..  contents:: Table of Contents
    :local:

Please note that using raw codes (method 1) is considered bad practice.
Advanced styling (method 2) should be used in new code.

Method #1 : raw styles
----------------------

..  Warning::
    This method is now deprecated and should not be used in new code.
    This is because messages written using this method are very hard
    to internationalize. Please use the `second method`_ instead.

The `styling API`_ provides constants for the raw control codes that make up
styles. There are also constants for colors, see the source code for details.

For example:

..  sourcecode:: php

    <?php
        $user = "Foobar";

        // Using pseudo-HTML, this is the same as:
        // "<b>Hi <u>$user</u></b>"
        $message =  Erebot_Styling::CODE_BOLD.
                        "Hi ".
                        Erebot_Styling::CODE_UNDERLINE.
                            $user.
                        Erebot_Styling::CODE_UNDERLINE.
                    Erebot_Styling::CODE_BOLD;
        print $message . PHP_EOL;
    ?>

The example above would display ``Hi Foobar``. The message would be displayed
in bold, with the user's nickname underlined when sent to an IRC server.

..  _`second method`:

Method #2 : "advanced styling"
------------------------------

This method aims at separating the process of designing the format string
from the process of actually rendering it (producing the string that would
be sent to an IRC server).

It uses an XML syntax to design the format string and a simple API to do
the rendering. This syntax is generally more verbose than the previous one,
but is also a lot easier to use and read.
Moreover, each message is validated (using a `RelaxNG schema`_ [#]_),
making it impossible to build invalid templates.

..  [#] For more information on RelaxNG schemae, see http://relaxng.org/.


Designing the format string
~~~~~~~~~~~~~~~~~~~~~~~~~~~

The format string contains XML tags which are recognized by the bot
and allow you to achieve many different formatting combinations.

Currently, the following tags are available:

    * ``<b>`` = bold
    * ``<u>`` = underline
    * ``<color>`` = change the (foreground and/or background) color of the text.
      This tag recognizes two optional attributes:

      - ``fg`` (optional) = changes the foreground color. The color's name
        should be one of the ``COLOR_`` constants defined in the styling
        class, with the ``COLOR_`` prefix stripped (eg. ``red``).
        The color's name is case-insensitive.

      - ``bg`` (optional) = changes the background color.
        See the description of ``fg`` for valid values.

    ..  note::
        At least one of the ``fg`` or ``bg`` must be provided, otherwise
        the message will be rejected as invalid.

    * ``<for>`` = loop other an array to format its content.
      This tag recognizes 2 required attributes and some optional ones:

      - ``from`` (required) = name of the variable which contains the
        array to format.

      - ``item`` (required) = a variable which will be created to store
        each value in the array (in turn).

      - ``key`` (optional) = a variable which will be created to store
        the key for that value (useful for associative arrays like in
        our example above).

      - ``separator`` (optional) = a separator to add between all entries
        in the array, except for the last two. Defaults to a comma followed
        by a single space.
      - ``sep`` (optional) = alias for ``separator``.

      - ``last_separator`` (optional) = a separator to add between
        the last 2 entries of the array. If no ``separator`` attribute has
        been set, defaults to an ampersand between two spaces.
        Otherwise, defaults to the value of the ``separator`` attribute.
      - ``last_sep`` (optional) = alias for ``last_separator``.

    * ``<var>`` = insert the value of the given variable at this point.
      The value will be rendered in a locale-dependent way, depending on
      the `type of variable`_ used. This tag accepts one attribute:

      - name (required) = variable to insert. See `template variables`_
        below for the various syntaxes supported by this attribute.

    * ``<plural>`` = use the correct plural form for that sentence.
      This tag has a required attribute called ``var`` that is used to
      determine the correct plural form to use. See `template variables`_
      below for the various syntaxes supported by this attribute.

      The content of this attribute should evaluate to an integer.
      Depending on the locale in use and this number, the appropriate plural
      form will be selected from a set of possibilities (cases).

      A ``<plural>`` tag contains one or more ``<case>`` subtags.
      Each ``<case>`` contains some inline text and comes with a required
      ``form`` attribute indicating when this text should be used [#]_.

      You **MUST** add a ``<case>`` subtag with the special form called
      ``other``. This special form will be used when no specific rule
      applies for this word's plural.

..  [#] The page at http://unicode.org/cldr/data/charts/supplemental/language_plural_rules.html
    lists all available forms.

..  warning::
    If you're used to `gettext's syntax for plurals`_ (using a predicate
    and a fixed array of translations), you'll notice the format used here
    is much more flexible, as it enables one to write something such as::

        There is/are <x> girl(s) and <y> boy(s) in this classroom.

    using the `correct form for each word`_ (noun or verb), while gettext
    would require you to either split the text in multiple sentences
    or define a complicated predicate to retrieve the correct plural.

    Also, please note that although gettext is used to store translations,
    the plural handling mechanism from gettext is never used by Erebot
    (ie. Erebot never calls ``ngettext`` or its variants).
    Instead, each message embeds both the singular and plural forms
    and an algorithm is used at runtime to decide which of the forms
    should be used.

..  note::
    See also the documentation on the `styling API`_ for more information.


..  _`type of variable`:

Strong typing
~~~~~~~~~~~~~

Each variable in a template has an associated type.
The following classes are available by default to represent some of the most
common types:

``Erebot_Styling_Integer``
    Represents an integer.

    ..  sourcecode:: php

        <?php
            $formatter = new Erebot_Styling($translator);
            $source = '<var name="leet"/>';
            $vars = array('leet' => new Erebot_Styling_Integer(1337));

            // This may be rendered as "1 337",
            // depending on the translator's locale.
            echo $formatter->_($source, $vars) . PHP_EOL;
        ?>

``Erebot_Styling_String``
    Represents a string. The value will be passed as is.

    ..  sourcecode:: php

        <?php
            $formatter = new Erebot_Styling($translator);
            $source = '<var name="name"/>';
            $vars = array('name' => new Erebot_Styling_String('Clicky'));
            echo $formatter->_($source, $vars) . PHP_EOL;
        ?>

``Erebot_Styling_Float``
    Represents a floating-point value.

    ..  sourcecode:: php

        <?php
            $formatter = new Erebot_Styling($translator);
            $source = '<var name="avg"/>';
            $vars = array('avg' => new Erebot_Styling_Float(1234.56));

            // This would be rendered as "1 234,56" in french.
            echo $formatter->_($source, $vars) . PHP_EOL;
        ?>

``Erebot_Styling_Currency``
    Represents a monetary value expressed in some currency.

    ..  sourcecode:: php

            <?php
                $formatter = new Erebot_Styling($translator);
                $source = '<var name="price"/>';

                // Note: the currency can be passed as an additional parameter.
                // If omitted, the currency from the locale configured in the
                // $transator is used.
                $vars = array('price' => new Erebot_Styling_Currency(1234.567, 'EUR'));

                // This would be rendered as "€1,234.57" for US english.
                // Note that monetary values are rounded to two places.
                echo $formatter->_($source, $vars) . PHP_EOL;
            ?>

``Erebot_Styling_DateTime``
    Represents a date and/or time.
    Some extra values (passed as additional parameters to this class)
    are necessary to represent such data. Thus, the arguments for this
    class' constructor are:

    *   ``$value``

        Either a `DateTime`_ object, an integer representing some
        Unix timestamp (seconds since Epoch, UTC) or an array using
        the same format as what is output by the `localtime()`_ PHP
        function.

        ..  note::
            `DateTime`_ objects are only supported since PHP 5.3.4,
            you should not rely on them in code intended to be backward
            compatible.

    *   ``$datetype``

        One of ``IntlDateFormatter::NONE``, ``IntlDateFormatter::FULL``,
        ``IntlDateFormatter::LONG``, ``IntlDateFormatter::MEDIUM`` or
        ``IntlDateFormatter::SHORT`` [#]_. This indicates how the date part
        of the value will be represented.

    *   ``$timetype``

        One of ``IntlDateFormatter::NONE``, ``IntlDateFormatter::FULL``,
        ``IntlDateFormatter::LONG``, ``IntlDateFormatter::MEDIUM`` or
        ``IntlDateFormatter::SHORT``. This indicates how the time part
        of the value will be represented.

    *   ``$timezone``

        A timezone identifier (such as "Europe/Paris"). This value is
        ignored when a Unix timestamp is passed as the ``$value``.

    ..  sourcecode:: php

        <?php
            $formatter = new Erebot_Styling($translator);
            $source = '<var name="now"/>';
            $vars = array(
                'now' => new Erebot_Styling_DateTime(
                    time(),
                    IntlDateFormatter::FULL,
                    IntlDateFormatter::FULL
                )
            );

            // In US English, this may be rendered like this:
            // "Wednesday, December 31, 1969 4:00:00 PM PT".
            echo $formatter->_($source, $vars) . PHP_EOL;
        ?>

    ..  [#] See http://php.net/class.intldateformatter.php for the meaning
        of each one of these constants.

``Erebot_Styling_Duration``
    Represents a duration in spelled out form, with a precision up to the
    seconds.

    ..  sourcecode:: php

        <?php
            $formatter = new Erebot_Styling($translator);
            $source = '<var name="duration"/>';
            $vars = array('duration' => new Erebot_Styling_Duration(1389722));

            // This would be rendered as:
            // "2 weeks, 2 days, 2 hours, 2 minutes, 2 seconds" in english.
            echo $formatter->_($source, $vars) . PHP_EOL;
        ?>

..  tip::
    If you need to represent a value without any modification, pass it
    as a string or wrap it in an instance of ``Erebot_Styling_String``.

..  note::

    For basic scalar types (integer, string or float), the API will wrap
    the value automatically for you using the appropriate class
    (``Erebot_Styling_Integer``, ``Erebot_Styling_String`` or
    ``Erebot_Styling_Float``, respectively).
    Arrays do not need to be wrapped in any class (but their values do!).

    You may change the default classes used to wrap scalar types for a
    specific template using the ``setClass()`` method, eg:

    ..  sourcecode:: php

        <?php
            $translator = new Erebot_I18n();
            $tpl = new Erebot_Styling($translator);

            // Change the classes used to wrap basic scalar types.
            $tpl->setClass('int',       'Custom_Int_Wrapper');
            $tpl->setClass('string',    'Custom_String_Wrapper');
            $tpl->setClass('float',     'Custom_Float_Wrapper');

            // Use $tpl as we'd normally do.
        ?>


..  _`template variables`:

Template variables
~~~~~~~~~~~~~~~~~~

When referencing a variable from a template using the ``<var name="..."/>``
or ``<plural var="..."/>`` tags, various syntaxes are available.

Hence, ``...`` may actually contain:

*   Actual variable passed to the template, eg. ``<var name="foo"/>``.

*   The sum or difference between two integer or floating-point values,
    eg. ``<var name="foo+bar"/>`` or ``<var name="foo-bar">``.
    Both types may be combined together (so, "foo" may refer to an integer,
    while "bar" refers to a floating-point value).

    You may use litteral integer or floating-point values as well,
    eg. ``<var name="years-18"/>`` or ``<var name="century+1"/>``.

    ..  tip::
        As a special bonus, you may also use the add operator (+) to append
        the values of one array to another using ``array_merge``. The original
        arrays are left intact when this feature is used.

    ..  warning::
        Any attempt to add or subtract values from incompatible types
        (eg. adding the value of an integer to a string) will result
        in an exception being thrown. In particular, subtracting one array
        from another is not supported yet.

    ..  warning::
        There is currently no plan to support the multiply (*) or divide (/)
        operators.

*   Parenthesized expressions, eg. ``<var name="totalCards-(nbCards+1)"/>``.

*   The number of elements in an array passed to the template, using the
    "count operator" (#), eg. ``<var name="#scores"/>``.

    ..  note::
        The count operator as higher precedence on the add/subtract operators,
        meaning that it is applied **before** any addition/substraction,
        unless parenthesis are used to override this.

    ..  warning::
        Use of the count operator on any other type may lead to
        unpredictable results.

*   Whitespace (spaces or tabs), eg. ``<var name="boys    +   girls"/>``.
    Such whitespace is ignored while processing the variable.

    ..  note::
        Due to limitations in the XML syntax, is it not possible to use
        newlines as whitespace.

*   Any combination of the previous syntaxes,
    eg. ``<var name=" # ( boys + girls ) "/>`` where ``boys`` and ``girls``
    both refer to arrays.

..  warning::
    Please keep in mind that variable names are case-sensitive.
    Any attempt to use an undefined variable in a template will
    result in an exception.


Using templates in your code
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Once the format string has been designed, you (as a programmer, not as
a designer) must add a few lines in your code in order to use it.

This is usually done with the following steps:

1.  Create an instance of `Erebot_Styling`_ by passing a translator object
    (an object implementing the `Erebot_Interface_I18n`_ interface) to its
    constructor.
    This is the creation step, where a formatter is created and bound to a
    translator.

2.  Prepare the values (either scalar types, objects implementing the
    `Erebot_Interface_Styling_Variable`_ interface or arrays made of
    scalar types/objects) that will be used in the template.
    This is the preparation step, where everything is setup for the final
    step.

    ..  note::
        Variable names may only contain alphanumeric characters or the
        underscore (_) and dot (.) characters.

    ..  warning::
        While designing the template, keep in mind that variable names
        are case-sensitive.

3.  Render the template (with ``$fmt->render()`` or ``$fmt->_()``) and use
    the result of that process in your code (eg. send it to an IRC channel).
    This is the rendering step.

    ..  sourcecode:: php

        <?php
            // The source for a template meant to display
            // the scores of each player in a fictitious game.
            $source =   '<b>Scores</b>: '.
                        '<for item="score" key="nick" from="scores" '.
                            'separator=", " last_separator=" &amp; ">'.
                            '<b>'.
                                '<u>'.
                                    '<color fg="green">'.
                                        '<var name="nick"/>'.
                                    '</color>'.
                                '</u>'.
                                ': <var name="score"/>'.
                            '</b>'.
                        '</for>';

            // Step 1:
            // Create a new translator and a new template from it.
            // By default, the locale for the translator is "en_US".
            $translator = new Erebot_I18n();
            $formatter  = new Erebot_Styling($translator);

            // Step 2:
            // Prepare some variables for the template.
            $vars = array(
                'scores' => array(
                    'Foo' => 42,
                    'Bar' => 23,
                    'Baz' => 16,
                    'Qux' => 15,
                    'Toto' => 8,
                    'Tata' => 4,
                ),
            );

            // Step 3:
            // Render the template with the given scores.
            //
            // This results in something like:
            // "Scores: Foo: 42, Bar: 23, Baz: 16, Qux: 15, Toto: 8 & Tata: 4"
            // with most of the words represented in bold
            // and the nicknames in green and underlined.
            //
            // Note: since we used "_()" to render the template,
            //       a translation is automatically selected (if available).
            echo $formatter->_($source, array('scores' => $scores)) . PHP_EOL;
        ?>

Here, ``$source`` has been split over many lines to make it easier to
figure out how the final message will look like. The template could actually
be written in a much more compact way.

You do not need to wrap your template (``$source``) in XML tags manually,
the bot already adds an enclosing tag automatically for you.

Also, the format string could be retrieved from anywhere:

* an array in a PHP script,
* an external process (eg. a database),
* a translation catalog (MO file),
* etc.

We prefer to have customizable format strings in a translation catalog,
as this gives more control to translators over the result and it is a format
they are used to working with.


.. _`correct form for each word`:

Plurals
~~~~~~~

Plurals are handled gracefully by Erebot using the ``<plural>`` and ``<case>``
tags.

Taking the sentence from earlier as an example::

    There is/are <x> girl(s) and <y> boy(s) in this classroom.

The equivalent as a template would be:

..  sourcecode:: php

    <?php

        $msg = 'There '.
                '<plural var="#(girls+boys)"/>'.
                    '<case form="one">is</case>'.
                    '<case form="other">are</case>'.
                '</plural> '.
                '<plural var="girls"/>'.
                    '<case form="one">one girl</case>'.
                    '<case form="other"><var name="girls"/> girls</case>'.
                '</plural> '.
                'and '.
                '<plural var="boys"/>'.
                    '<case form="one">one boy</case>'.
                    '<case form="other"><var name="boys"/> boys</case>'.
                '</plural> '.
                'in this classroom';

        $formatter = new Erebot_Styling(new Erebot_I18n());

        // Displays "There is one girl and 0 boys in this classroom".
        echo $formatter->_($msg, array('girls' => 1, 'boys' => 0)) . PHP_EOL;

        // Displays "There are 2 girls and one boy in this classroom".
        echo $formatter->_($msg, array('girls' => 2, 'boys' => 1)) . PHP_EOL;

        // Displays "There are one girl and 2 boys in this classroom".
        echo $formatter->_($msg, array('girls' => 1, 'boys' => 2)) . PHP_EOL;
    ?>

Notice how we represented the actual counts using either a spelled out form
("one girl" / "one boy") or an actual number ("2 girls" / "2 boys"), simply
by specifying different words for the different ``<cases>``.

You'll also notice that this string is electable for `Internationalization`_.
Translators have full control over the template used to render the sentence
and could easily adapt it to the plural rules used in their country.

..  note::
    There are often many different ways to represent the same message
    using templates. Here, we grouped words that were affected by the
    same variable together. Once again, **translators are the ones
    in charge** here. This is very important because they know better
    than you how the sentence should look like in their language.

Further reading
~~~~~~~~~~~~~~~

The documentation on the `styling API`_ always reflects the latest features
implemented, while this page may sometime fall a little behind in what it
showcases (please `open a ticket`_ if you notice any discrepancy!).

..  _`styling API`:
    https://buildbot.erebot.net/doc/api/Erebot/html/interfaceErebot__Interface__Styling.html
..  _`RelaxNG schema`:
    https://github.com/Erebot/Erebot/blob/master/data/styling.rng
..  _`Erebot_Styling`:
    https://buildbot.erebot.net/doc/api/Erebot/html/classErebot__Styling.html
..  _`Erebot_Interface_Styling_Variable`:
    https://buildbot.erebot.net/doc/api/Erebot/html/interfaceErebot__Interface__Styling__Variable.html
..  _`Erebot_Interface_I18n`:
    https://buildbot.erebot.net/doc/api/Erebot/html/interfaceErebot__Interface__I18n.html
..  _`Internationalization`:
    Internationalization.html
..  _`open a ticket`:
    https://github.com/Erebot/Erebot/issues/new
..  _`gettext's syntax for plurals`:
    http://www.gnu.org/s/hello/manual/gettext/Plural-forms.html
..  _`datetime`:
    http://php.net/class.datetime.php
..  _`localtime()`:
    http://php.net/function.localtime.php

.. vim: ts=4 et

