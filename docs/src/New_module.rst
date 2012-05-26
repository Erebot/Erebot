Writing a new module
====================

This page acts like a guide for those who may be interested in writing a new
module for Erebot.

General structure
-----------------

An Erebot module is a PHP class that extends ``Erebot_Module_Base``.
As such, it must have at least two methods (declared *abstract* in
``Erebot_Module_Base``):

-   ``_reload()`` is called when the module is (re)loaded with some
    flags giving more information about where is being reloaded.
    The flags are a bitwise-OR combination of the ``RELOAD_*`` constants
    found in ``Erebot_Module_Base``.

-   ``_unload()`` is called when the module is unloaded. Its purpose
    is to free any resource that may have been allocated by ``_reload()``,
    save the current state elsewhere, etc.

..  note::
    When a module is reloaded, only ``_reload()`` is called.
    The only time ``_unload()`` is ever called is when the module
    is being completely unloaded (usually, right before the bot
    exits).


Helping users
-------------

..  note::
    Adding an help method to your module is totally optional, but it is
    considered good practice as it provides some way for users to request
    help on your new module and its commands without having to read some
    online manual for example.

To provide help for your module, all you need is a method that handles
help requests. The name of that method does not matter (though this method
is called ``getHelp()`` in all modules that ship with Erebot).

When someone requests help on a module or command, the help methods are
looked up in order to find one that will acknowledge the request (see below).
This may result in one or more help methods being called to handle the request.

The help method **must** use the following signature.

..  sourcecode:: inline-php

    public function getHelp(
        Erebot_Interface_Event_Base_TextMessage $event,
        Erebot_Interface_TextWrapper            $words
    )

This method is responsible for either acknowledging the help request
(by returning ``TRUE``) or ignoring it (by returning ``FALSE`` or by
not returning anything at all). If your method chooses to ignore the
help request, the next help method in line will be called with the
same parameters, until either a method acknowledges the request
or there are no more help methods to try.

``$event`` will contain the original request as an event. This will either be
an event that implements the ``Erebot_Interface_Event_Base_Private`` interface
if the request was sent as a private query, or an event implementing the
``Erebot_Interface_Event_Base_Chan`` interface if it came from an IRC channel.

``$words`` contains the content of the request (derived from the text in the
original request in ``$event``), wrapped to make it easier to look at individual
words.

Now, there are two types of requests:

-   Requests for help on the module itself (``!help Erebot_Module_Foo``).
    In that case, ``$words`` will contain only one word:
    the name of the module itself (``Erebot_Module_Foo``).

-   Requests for help on a command/topic (``!help foo``, ``!help foo bar...``).
    In that case, ``$words`` will contain 2 or more words:

    *   The name of the current module.
    *   The name of the command (``foo``).
    *   Any additional parameters (``bar...``).

You can find out which type of request is in use by simply counting the number
of words in ``$words``, which is very easy as the wrapper implements the
``Countable`` interface:

..  sourcecode:: inline-php

    // If it's 1, it is a request for help on the module itself.
    // Otherwise, it's a request for help on some command/topic.
    $nbWords = count($words);

..  warning::
    Erebot has now way (yet) to know what module provides a given
    command/topic, so for such help requests, it calls every module's
    help method with the request until one acknowledges it.

    This means that your help method may receive requests about commands
    or topics it knows nothing about. You **must** ignore such requests
    (by returning ``FALSE`` or nothing at all) and you **must not**
    send a message indicating an error in the request to the user.

Once the code for your help method is ready, you have to tell Erebot about it
by using the ``registerHelpMethod()`` method inside your module's ``_reload()``
method. You must call ``registerHelpMethod()`` with an object implementing the
``Erebot_Interface_Callable`` interface and referring to your method.

This can be done using the following snippet:

..  sourcecode:: inline-php

    // First, we retrieve the factory to use to produce instances
    // implementing "Erebot_Interface_Callable".
    $cls = $this->getFactory('!Callable');

    // Next, we register our help method (here, the getHelp() method
    // from the current object) by wrapping a callback-compatible
    // value referring to it in a new callable object.
    $this->registerHelpMethod(new $cls(array($this, 'getHelp')));


Frequently Asked Questions
--------------------------

This sections contains random information aboout modules:

-   What features can I use in a new module?

    You can use any of the many features provided by the PHP language.
    This includes things such as sockets, databases, etc.

-   Likewise, are there patterns I should avoid?

    Even though you can do pretty much anything you want in a module,
    you should avoid long running tasks such as downloading a big file
    from a remote server.

    The reason is simple: PHP does not support multithreading, so while
    a long running task is being executed, the rest of the bot is literally
    stopped. This includes other modules (like ``Erebot_Module_PingReply``)
    responsible for keeping the connection alive. Hence, running a long task
    in your module may result in the bot being disconnected from IRC servers
    with a "Ping timeout" error.


.. vim: ts=4 et
