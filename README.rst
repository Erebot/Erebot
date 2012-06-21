Erebot
======

What is it?
-----------

Erebot is an IRC bot written in PHP thought up with modularity in mind.
It is fully compatible with PHP 5.2.1 up to the very latest versions of PHP.


What does it offer?
-------------------

Erebot implements a few useful features:

-   Connections to IRC servers using either plain-text or encrypted connections,
    with support for passworded IRC servers and "security upgrades" (STARTTLS).

-   Autoconnect and autojoin modules, so you don't need to worry about having
    to make the bot join channels by yourself.

-   A lag checker, which can kill the connection and force the bot to reconnect
    if it's lagging too badly.

-   A rate-limiting module so you don't have to worry about the bot being
    disconnecting because it is sending too many messages at once.

-   A module that displays information on TV schedules .

-   An implementation of the popular Uno game, mostly inspired by that of rbot,
    but also providing other variants, making the game even funnier.

-   An implementation of the TV gameshow "Countdown".

-   An implementation of the traditionnal game "Gang of Four".

-   `Many other modules`_ (more than 20 for now, and counting!)

And if it lacks a feature you need, it is quite easy to `roll your own module`_
to add that feature.


Installation
------------

Erebot can be installed using different modes, depending on your requirements.
Read the `installation instructions`_ for more information.


License
-------

Erebot is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Erebot is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Erebot.  If not, see <http://www.gnu.org/licenses/>.


..  _`Many other modules`:
    http://erebot.github.com/Erebot/Modules.html
..  _`roll your own module`:
    http://erebot.github.com/Erebot/New_module.html
..  _`installation instructions`:
    http://erebot.github.com/Erebot/Installation.html

.. vim: ts=4 et
