Coding standard
===============

This page contains documentation on the coding standard used for Erebot's
development. It takes inspiration from other such documents, namely:

-   `Drupal coding standards`_
-   `Zend Framework coding style`_
-   `The PEAR coding standards`_

..  _`Drupal coding standards`:
    http://drupal.org/coding-standards
..  _`Zend Framework coding style`:
    http://framework.zend.com/manual/en/coding-standard.coding-style.html
..  _`The PEAR coding standards`:
    http://pear.php.net/manual/en/standards.php

Most of the information on this page is organized in the same way as the
Drupal coding standards.

..  contents:: Table of Contents
    :local:


Indenting and whitespace
------------------------

Use an indent of 4 spaces, with no tabs.

Lines should have no trailing whitespace at the end.

Files should be formatted with \\n as the line ending (Unix line endings),
not \\r\\n (Windows line endings).

All text files should end in a single newline (\\n). This avoids the verbose
"\\ No newline at end of file" patch warning and makes patches easier to read
since it's clearer what is being changed when lines are added to the end of
a file.


Operators
---------

All binary operators (operators that come between two values),
such as ``+``, ``-``, ``=``, ``!=``, ``==``, ``>``, etc. should have
a space before and after the operator, for readability.

For example, an assignment should be formatted as

..  sourcecode:: inline-php

        $foo = $bar;

rather than

..  sourcecode:: inline-php

        $foo=$bar;

As a special case for assigning operators, such as ``=``, ``&=``, ``<<=``, etc.
you may add several spaces before the operator when the code contains several
lines of assignments (lining up the equal signs) so as to improve readability:

..  sourcecode:: php

    <?php
        $foo    = 42;
        $bar   &= $foo;
        $bar  <<= 2;
    ?>

Unary operators (operators that operate on only one value), such as ``++``,
should not have a space between the operator and the variable or number
they are operating on.

When using the ternary operator, add parentheses around the condition:
``(...) ? ... : ...``.

Only wrap the ternary operator if the total length of the line exceeds
the |cs-chars-limit| chars limit. In that case, add a single newline
before the ``?`` and ``:`` symbols, while adding enough spaces before them
so as to line up the 3 (three) parts of the operator:

..  sourcecode:: php

    <?php
        $longVariableNameCausingWrapping =  ($config->overridesDefaultValues())
                                            ? $config->getOverrides()
                                            : $config->getDefaultValues();
    ?>


Casting
-------

Put a single space between the (type) and the operand of a cast:

..  sourcecode:: inline-php

        (int) $mynumber


Control structures
------------------

Control structures include ``if``, ``for``, ``while``, ``switch``, etc.
Here is a sample ``if`` statement, since it is the most complicated of them:

..  sourcecode:: inline-php

    if (condition1 || condition2) {
        action1;
    }
    else if (condition3 && condition4) {
        action2;
    }
    else {
        defaultaction;
    }

Control statements should have one space between the control keyword
and opening parenthesis, to distinguish them from function calls.

Use ``else if`` instead of ``elseif``.

You are strongly encouraged to always use curly braces even in situations
where they are technically optional.Having them increases readability
and decreases the likelihood of logic errors being introduced
when new lines are added.

The only exception to the rule above is to follow the "return/fail early"
principle where the action following a condition is a ``return`` or ``throw``
statement and no line is ever going to be added to the action part.
More information on this principle and why you should follow it can be found
in the following blog entries:

*   http://vocamus.net/dave/?p=1421 (by a long time Mozilla contributor)
*   http://saltybeagle.com/2011/06/fail-early/ (by Brett Bieber, one of the
    main contributors to Pyrus, the next generation PEAR installer)

For switch statements:

..  sourcecode:: inline-php

    switch (condition) {
        case 1:
            action1;
            break;

        case 2:
            action2;
            // defaultaction must also be executed in this case.

        default:
            defaultaction;
    }

..  note::
    It is sometimes useful to write a case statement which falls through
    to the next case by not including a ``break`` or ``return`` within
    that case. To distinguish these cases from bugs, any case statement
    where ``break`` or ``return`` are omitted should contain a comment
    indicating that this is the intended behaviour.

For do-while statements:

..  sourcecode:: inline-php

    do {
        actions;
    } while ($condition);

..  warning::
    Use of the alternative forms for these structures is prohibited.
    For example:

    ..  sourcecode:: php

        <?php
            // DON'T DO THIS
            while ($foo):
                ...
            endwhile;
        ?>


Line length and wrapping
------------------------

The following rules apply to code:

*   In general, all lines of code should not be longer than |cs-chars-limit|
    chars.

*   Long control structure conditions should be wrapped into multiple lines
    so as not to break the |cs-chars-limit| chars rule.

    Whenever possible, try to prepare values related to the condition
    beforehand (storing them in temporary variables if necessary).

    So, instead of this:

    ..  sourcecode:: inline-php

        if ($something['with']['something']['else']['in']['here'] == mymodule_check_something($whatever['else'])) {
            ...
        }

    use the following snippet:

    ..  sourcecode:: inline-php

        $here = $something['with']['something']['else']['in']['here'];
        if ($here == mymodule_check_something($whatever['else'])) {
            ...
        }

    When breaking a test composed of several conditions, wrap the conditions
    after the operator (``&&`` or ``||``) and indent the next line
    using 4 spaces so as to line up the conditions.

    So, instead of this snippet:

    ..  sourcecode:: inline-php

        if (isset($something['what']['ever']) && $something['what']['ever'] > $infinite && user_access('galaxy')) {
            ...
        }

    use this one:

    ..  sourcecode:: inline-php

        if (isset($something['what']['ever']) &&
            $something['what']['ever'] > $infinite &&
            user_access('galaxy')) {
            ...
        }

*   Control structure conditions should also **NOT** attempt to win the
    *Most Compact Condition In Least Lines Of Code Award™*:

    ..  sourcecode:: inline-php

        // DON'T DO THIS!
        if ((isset($key) && !empty($user->uid) && $key == $user->uid) || (isset($user->cache) ? $user->cache : '') == ip_address() || isset($value) && $value >= time())) {
            ...
        }

    Instead, it is recommended practice to split out and prepare the conditions
    separately, which also permits documenting the underlying reasons for the
    conditions:

    ..  sourcecode:: inline-php

        // Key is only valid if it matches the current user's ID, as otherwise other
        // users could access any user's things.
        $is_valid_user = (isset($key) && !empty($user->uid) && $key == $user->uid);

        // IP must match the cache to prevent session spoofing.
        $is_valid_cache = (isset($user->cache) ? $user->cache == ip_address() : FALSE);

        // Alternatively, if the request query parameter is in the future, then it
        // is always valid, because the galaxy will implode and collapse anyway.
        $is_valid_query = $is_valid_cache || (isset($value) && $value >= time());

        if ($is_valid_user || $is_valid_query) {
          ...
        }

    ..  note::
        This example is still a bit dense. Always consider and decide on your
        own whether people unfamiliar with your code will be able to make sense
        of the logic.


Function/method calls
---------------------

Functions and methods should be called with no spaces between the function name,
the opening parenthesis, and the first parameter; spaces between commas
and each parameter, and no space between the last parameter,
the closing parenthesis, and the semicolon.

Here's an example:

..  sourcecode:: inline-php

    $var = foo($bar, $baz, $qux);

As displayed above, there should be one space on either side of an equals
sign used to assign the return value of a function to a variable
(as documented in the section on `Operators`_).

..  warning::

    Call-time pass-by-reference is strictly prohibited. See the section on
    :ref:`function/method declarations <cs-fn-decl>` for the proper way
    to pass function arguments by-reference.

In the case of a block of related assignments, more space may be inserted
to line up function calls and promote readability:

..  sourcecode:: inline-php

    $short         = foo($bar);
    $longVariable  = foo($baz);

..  warning::

    For methods/functions defined by the core of PHP or any of its extension,
    (that is, anything that isn't userland-define), always respect the case
    given by the PHP manual. Even though PHP is case-insensitive for most
    identifiers, there are recurring propositions about turning it into
    a case-sensitive language for everything. Using the official case
    from the start makes the code forward-compatible if such a change is
    ever made.


..  _`cs-fn-decl`:

Function/method declarations
----------------------------

..  note::

    We recommend that you use classes instead of functions in your code,
    even if it means creating classes containing static methods only.
    The rationale behind this decision being that it avoids global scope
    name pollution.

..  warning::
    Call-time pass-by-reference is strictly prohibited.

Always put the opening curly brace on a new line.

..  sourcecode:: inline-php

    function funstuff_system($field)
    {
        $system["description"] = t("This module inserts funny text into posts randomly.");
        return $system[$field];
    }

Arguments with default values go at the end of the argument list.


Use type-hints whenever possible, but only if the type-hint is ``array``
or **refers to an interface**.

..  sourcecode:: php

    <?php
        // Wrong:
        function make_cat_speak(GarfieldTheCat $cat) {
          print $cat->meow();
        }

        // Correct:
        function make_cat_speak(FelineInterface $cat) {
          print $cat->meow();
        }
    ?>

For classes provided by PHP or one of its extensions (eg. `DOMDocument`_),
consider writing an interface for it and use that as a type-hint.

Using an interface instead of a class name in the type-hint makes it easier
to use a class that provides the same features (the same API) through
a different implementation. This is especially useful when unit testing
the function.

When a function or method's arguments list exceeds the |cs-chars-limit| chars
limit, use a single newline after the opening parentheses, write each argument
on a separate line and put the closing parentheses on a separate line too.
Indent each argument's line by 4 (four) spaces and add extra spaces to line up
the arguments' dollar sign whenever type-hints and/or references are used
In this case, the closing parentheses and the opening curly brace
that follows it should still be on separate lines:

..  sourcecode:: inline-php

    function foobar(
        Foo_Interface                   $foo,
        FooBar_Converter_Interface      $converter,
                                       &$qux
    )
    {
        ...
    }

Last but not least, always attempt to return a meaningful value from a function
if one is appropriate. If no meaningful value exist, consider returning ``NULL``
or an empty array instead of ``FALSE``.

The return value must not be enclosed in parentheses.
This can hinder readability, in addition to breaking code
if a method is later changed to return by reference.

For example:

..  sourcecode:: inline-php

    function send_notificationWRONG(User_Interface $user, $message)
    {
        if (!$user->valid()) {
            // WRONG:   makes it harder to distinguish an invalid user
            //          from a failure while sending the notification.
            return FALSE;
        }

        // WRONG:   will trigger a fatal error if the function
        //          is ever modified to return by reference.
        return (mail($user->getMail(), 'Notification', $message));
    }

    function send_notificationOK(User_Interface $user, $message)
    {
        if (!$user->valid())
            return NULL;

        return mail($user->getMail(), 'Notification', $message);
    }

Exceptions may also be used instead of returning ``NULL``.
Whether an exception should be raised or ``NULL`` / an empty array
returned is left to the appreciation of developpers.


Class constructor calls
-----------------------

When calling class constructors with no arguments, always include parentheses:

..  sourcecode:: inline-php

    $foo = new MyClassName();

This is to maintain consistency with constructors that have arguments:

..  sourcecode:: inline-php

    $foo = new MyClassName($arg1, $arg2);

Note that if the class name is a variable, the variable will be evaluated
first to get the class name, and then the constructor will be called.

Use the same syntax:

..  sourcecode:: inline-php

    $bar = 'MyClassName';
    $foo = new $bar();
    $foo = new $bar($arg1, $arg2);


Arrays
------

Arrays should be formatted with a space separating each element
(after the comma), and spaces around the ``=>`` key association operator,
if applicable:

..  sourcecode:: inline-php

    $some_array = array('hello', 'world', 'foo' => 'bar');

Note that if the line declaring an array spans longer than |cs-chars-limit|
characters, each element should be broken into its own line, and indented
one level.
Extra spaces may be added before the ``=>`` operator to increase readability:

..  sourcecode:: inline-php

    $form['title'] = array(
        '#type'         => 'textfield',
        '#title'        => t('Title'),
        '#size'         => 60,
        '#maxlength'    => 128,
        '#description'  => t('The title of your node.'),
    );

..  note::
    Always add a comma at the end of the last array element.
    It helps prevent parsing errors if another element is placed at the end
    of the list later.

Quotes
------

Erebot does not have a hard standard for the use of single quotes vs.
double quotes. Where possible, keep consistency within each module,
and respect the personal style of other developers.

With that caveat in mind: single quote strings are known to be faster
because the parser doesn't have to look for in-line variables.
Their use is recommended except in two cases:

   1.   In-line variable usage, e.g. ``<h2>$header</h2>``.
   2.   Translated strings where one can avoid escaping single quotes
        by enclosing the string in double quotes.
        One such string would be "He's a good person."
        This string would become 'He\'s a good person.' with single quotes.
        Such escaping may not be handled properly by .pot file generators
        for text translation, and it's also somewhat awkward to read.

For long chunks of texts, you may also `heredoc/nowdoc strings`_,
except when the text needs to be translated, because the current parser
for translations does not pick them up.


String concatenations
---------------------

We recommend that you always use a space between the dot and the concatenated
parts to improve readability (we current ruleset does not enforce this rule
though).

..  sourcecode:: php

    <?php
        $string = 'Foo' . $bar;
        $string = $bar . 'foo';
        $string = bar() . 'foo';
        $string = 'foo' . 'bar';
    ?>

When you concatenate simple variables, you can use double quotes and add
the variable inside; otherwise, use single quotes.

..  sourcecode:: inline-php

      $string = "Foo $bar";

When using the concatenating assignment operator ``.=``, use a space
on each side as with the assignment operator:

..  sourcecode:: php

    <?php
        $string .= 'Foo';
        $string .= $bar;
        $string .= baz();
    ?>


Comments
--------

Don't use Perl-style commands (``# Comment``). For comments that span several
lines, we recommend that you use C++ comments (``/* Comment */``).

When using C++ comments, you may use asterisks ("stars") at the start of each
line.

..  warning::
    Use of comments such as ``/** ... */`` or ``///`` is reserved for API
    documentation purposes using `Doxygen commands`_.
    You **MAY NOT** use them to explain the logic of your code.
    Use the regular forms ``/* ... */`` & ``//`` instead in such cases.

For example,

..  sourcecode:: php

    <?php
        // Connects the bot to the default servers.
        $bot->connect();

        /* This is a very long comment about the purpose of the snippet
         * of code that goes right after this comment, so as to explain
         * what it does (in case this may not be easy to understand) as
         * well as how it is done (for example, to describe side-effects).
         */
        do_something_very_complex();

        # This kind of comments MUST NOT be used.
        oops();

        /**
         * \brief
         *      A well known pseudo-random number generator.
         *
         * This type of comments may only be used to describe the API,
         * using Doxygen commands.
         */
        class PRNG
        {
            // The next comment describes part of this class' API.
            /// Seed for the PRNG.
            const SEED = 4;

            /**
             * \brief
             *      Return a new random number.
             *
             * \retval int
             *      Some random number.
             */
            public static function getRandomNumber()
            {
                return self::SEED;
            }
        }
    ?>

..  todo:: Add a section on API documentation.


..  _`cs-fs-paths`:

Filesystem paths
----------------

Never use any OS-specific directory separator (eg. "/") directly to concatenate
parts of a path together. Always use the ``DIRECTORY_SEPARATOR`` constant
instead. It will take care of abstracting differences in the separator used
by each OS for you.


Including code
--------------

..  note::
    For code that is part of Erebot itself, you don't need to manually include
    any file as the autoloader will load the files on the fly whenever this is
    required.

Anywhere you are unconditionally including a class file, use ``require_once()``.
Anywhere you are conditionally including a class file (for example,
factory methods), use ``include_once()``.
Either of these will ensure that class files are included only once.
They share the same file list, so you don't need to worry about mixing them
|---| a file included with ``require_once()`` will not be included again
by ``include_once()``.

..  note::
    ``include_once()`` and ``require_once()`` are statements and not functions.
    Having said that, we recommend that you always put parentheses around the
    file name to be included, even though this is not necessary from a technical
    point of view. This makes the coding style coherent with that of functions.

Never use relative paths when including code, always build an absolute path.
You may use the ``__FILE__`` magical constant and the ``dirname()`` function
to help you build such a path.
See also the :ref:`conventions for filesystem paths <cs-fs-paths>`
for more information.

..  note::
    Even for external libraries, we recommend that you use the autoloader
    provided by those libraries if one is available instead of manually
    including their code.


PHP code tags
-------------

Always use :

..  sourcecode:: php

    <?php ?>

to delimit PHP code, not the shorthand,

..  sourcecode:: php

    <? ?>

or other exotic tags allowed by PHP::

    <script language="php">
        // DON'T USE THIS TAG.
        //
        // Many tools do not even recognize this block
        // as containing some PHP code.
        //
        // Also, the "language" attribute on script tags
        // has been deprecated since HTML 4.01.
    </script>

    <%
        // DON'T USE THIS TAG EITHER.
        // This tag conflicts with the one used for ASP code.
    %>


This is required for portability across differing operating systems
and set-ups.

..  warning::
    Never use a closing ``?>`` at the end of code files:

    *   Removing it eliminates the possibility for unwanted whitespace
        at the end of files which can cause strange outputs to the console.
    *   The closing delimiter at the end of a file is optional anyway.
    *   PHP.net itself removes the closing delimiter from the end of its files,
        so this can be seen as a "best practice."


Semicolons
----------

The PHP language requires semicolons at the end of most lines,
but allows them to be omitted at the end of code blocks.

Erebot coding standards require them, even at the end of code blocks.
In particular, for one-line PHP blocks:

..  sourcecode:: php

    <?php
        print $tax; // YES
    ?>
    <?php
        print $tax  // NO
    ?>


Example URLs
------------

Use ``example.com`` as the domain for all example URLs, per :rfc:`2606`.
You may also refer to subdomains of this domain, eg. ``irc.example.com``.


.. _`naming-conventions`:

Naming Conventions (Functions, Constants, Global Variables, Classes, Files)
---------------------------------------------------------------------------

..  _`cs-naming-files`:

Files
~~~~~

Files containing classes should be named after the content of the last
underscore (``_``) contained in the class name. If the class name does not
contain any underscore, the file should be named after the class name
as a whole.

..  warning::
    You must **ALWAYS** use a separate file for each class or interface
    defined in your code.

The file should also be placed in a hierarchy of directories that is directly
mapped from each segment of the class name obtained after splitting the class
name on underscores and removing the last segment (name of the file itself).

The same holds for interfaces.

The following table shows how files should be arranged depending on the name
of the class/interface they contain:

..  table:: Class/interface name to filesystem mapping

    +---------------------------+-------------------------------------------+
    | Class name                | Path on filesystem                        |
    +===========================+===========================================+
    | Erebot                    | :samp:`{src}/Erebot.php`                  |
    +---------------------------+-------------------------------------------+
    | Erebot_Module_Foo         | :samp:`{src}/Erebot/Module/Foo.php`       |
    +---------------------------+-------------------------------------------+
    | Erebot_Interface_I18n     | :samp:`{src}/Erebot/Interface/I18n.php`   |
    +---------------------------+-------------------------------------------+

This convention is required to make Erebot's autoloader work.

Classes and interfaces
~~~~~~~~~~~~~~~~~~~~~~

Classes should be named using "UpperCamelCase", a newline should be inserted
before the ``extends`` and ``implements`` keywords and before the opening
curly brace.

When a class implements several interfaces, add a single newline after each
comma separating the interfaces; do not put any space before the comma.
Add extra spaces to line up the class and interface names.

For example:

..  sourcecode:: php

    <?php
        abstract class  ConnectionPool
        extends         PDO
        implements      Countable,
                        IteratorAggregate
        {
            ...
        }
    ?>

Use underscores when you need to logically separate groups of classes.
For example, all classes belonging to an Erebot module start with the prefix
:samp:`Erebot_Module_{ModuleName}`.
See also :ref:`cs-naming-files` for implications.

For an interface, the text ``Interface`` should always appear in the
interface's name, preferably at the end (eg. ``Erebot_Module_FooInterface``).
If you prefer to use a separate directory where all the interfaces are stored,
this is also permitted (eg. ``Erebot_Module_Foo_Interface_Generic``).

Class methods and properties
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Class methods and properties should use "lowerCamelCase":

The use of ``public`` properties is strongly discouraged, as it allows for
unwanted side effects. It also exposes implementation specific details,
which in turn makes swapping out a class for another implementation
(one of the key reasons to use objects) much harder.
Properties should be considered internal to a class.

All methods and properties of classes must specify their visibility:
``public``, ``protected``, or ``private``. The PHP 4-style ``var``
declaration must not be used.

The use of ``private`` class methods and properties should be avoided |---| use
``protected`` instead, so that another class could extend your class and change
the behaviour of a method if necessary (eg. for unit testing purposes).

Use an underscore prefix for ``protected`` and ``private`` methods
and properties so as to make them easily identifiable.

You may use extra spaces before a property's name to line up all properties
of a class.

The following snippet summarizes all of those rules:

..  sourcecode:: php

    <?php
        class Foo
        {
            public      $statementCounter;
            protected   $_statement;
            private     $_lastStatement;
        }
    ?>

For methods that are ``static``/``abstract``/``final``, PHP allows use of the
keywords in any order. We do not impose any order either, except that the
visibility specifier (``public``, ``protected`` or ``private``) should always
be the last keyword.

For example, the snippet below defines four methods. Only the first 2 (two)
forms are accepted in Erebot, with the first form being the preferred one
(``final`` or ``abstract`` before ``static``):

..  sourcecode:: php

    <?php
        class Foo
        {
            // This is valid PHP code, and it is accepted in Erebot.
            final static public function foo()
            {
                ...
            }

            // This is valid PHP code, and it is also accepted in Erebot.
            static final public function bar()
            {
                ...
            }

            // This is valid PHP code, but it is forbidden in Erebot's code.
            final public static function baz()
            {
                ...
            }

            // This is valid PHP code, but it is forbidden in Erebot's code.
            public static final function qux()
            {
                ...
            }
        }

Class constructors
~~~~~~~~~~~~~~~~~~

Always use the ``__construct()`` method to define a class constructor.
Do not use the old PHP 4 convention where the constructor was named
after the class:

..  sourcecode:: php

    <?php
        // NO
        class Bar
        {
            public function Bar()
            {
                ...
            }
        }

        // YES
        class Baz
        {
            public function __construct()
            {
                ...
            }
        }
    ?>

Functions
~~~~~~~~~

Functions should be named using lowercase, and words should be separated
with an underscore. Functions should also have the module's name as a prefix,
to avoid name collisions between modules.

Constants
~~~~~~~~~

*   Constants should always be all-uppercase, with underscores to separate
    words. (This includes pre-defined PHP constants like ``TRUE``, ``FALSE``,
    and ``NULL``.)

*   Global constants defined by modules should also have their names prefixed
    by an uppercase spelling of the module that defines them.

    ..  note::
        Whenever possible, use class constants instead of global constants
        to avoid global naming space pollution.

*   Global constants should be defined using the ``const`` PHP language keyword
    (instead of ``define()``), for performance reasons:

    ..  sourcecode:: php

        <?php
            /**
            * Indicates that the item should be removed
            * at the next general cache wipe.
            */
            const CACHE_TEMPORARY = -1;
        ?>

    ..  note::
        The ``const`` keyword does not work with PHP expressions.
        ``define()`` should still be used when defining a constant
        conditionally or with a non-literal value:

        ..  sourcecode:: php

            <?php
                if (!defined('MAINTENANCE_MODE')) {
                    define('MAINTENANCE_MODE', 'error');
                }
            ?>

Global variables
~~~~~~~~~~~~~~~~

Global variables are strictly forbidden in Erebot and any of its modules;
this is non-negociable.


Check your code
---------------

To check that your code complies with these standards, install the following
PEAR packages on your machine:

*   :pear:`PHP_CodeSniffer`
*   `pear.phing.info/Phing <http://pear.phing.info/>`_

If you are creating your own module, write a ``build.xml`` file if you haven't
done so yet. You can start with a copy of `Erebot_Module_Skeleton`_'s own
`build.xml`_ file.
You'll also want to make sure that your module follows the same layout as
official Erebot modules and that you added `Erebot_Buildenv`_ as a
`git submodule`_ to your module.

Now, go to the root directory of the component and run:

..  sourcecode:: bash

    phing qa_codesniffer

This will check your code against the standards described here.

You may also be interested in
:ref:`installing other PEAR packages <prerequisites>` related to
:abbr:`QA (Quality Assurance)`, and then running the full
:abbr:`QA (Quality Assurance)` test suite with:

..  sourcecode:: bash

    phing qa


..  |cs-chars-limit|    replace:: 80
..  |---|               unicode:: U+02014 .. em dash
    :trim:

..  _`Doxygen commands`:
    http://www.stack.nl/~dimitri/doxygen/commands.html
..  _`heredoc/nowdoc strings`:
    http://php.net/manual/en/language.types.string.php#language.types.string.syntax.heredoc
..  _`DOMDocument`:
    http://php.net/DOMDocument
..  _`build.xml`:
    https://github.com/Erebot/Erebot_Module_Skeleton/raw/master/build.xml
..  _`Erebot_Module_Skeleton`:
    https://github.com/Erebot/Erebot_Module_Skeleton/
..  _`Erebot_Buildenv`:
    https://github.com/Erebot/Erebot_Buildenv/
..  _`git submodule`:
    http://book.git-scm.com/5_submodules.html

..  vim: et ts=4

