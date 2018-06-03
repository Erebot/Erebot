Math module
###########

..  contents::
    :local:

Description
===========

This module provides a basic calculator, useful for simple computations.

It supports the four basic operators (+, -, \*, /), exponentiation (^),
modules (%) and parenthesis. It focuses on usefulness rather than completeness.


Configuration
=============

Options
-------

This module provides several configuration options.

..  table:: Options for \\Erebot\\Module\\Math

    +----------+--------+---------------+-------------------------------------+
    | Name     | Type   | Default value | Description                         |
    +==========+========+===============+=====================================+
    | trigger  | string | "math"        | The command to use to ask the bot   |
    |          |        |               | to compute a new formula.           |
    |          |        |               | The trigger should only contain     |
    |          |        |               | alpha-numeric characters and should |
    |          |        |               | not be prefixed.                    |
    +----------+--------+---------------+-------------------------------------+


Example
-------

In this example, we configure the bot to compute formulae when the ``!calc``
command is used.

..  parsed-code:: xml

    <?xml version="1.0"?>
    <configuration
      xmlns="http://localhost/Erebot/"
      version="0.20"
      language="fr-FR"
      timezone="Europe/Paris">

      <modules>
        <!-- Other modules ignored for clarity. -->

        <module name="\\Erebot\\Module\\Math">
          <param name="trigger" value="calc" />
        </module>
      </modules>
    </configuration>


Usage
=====

This section assumes default values are used for all triggers.
Please refer to :ref:`configuration options <configuration options>`
for more information on how to customize triggers.


Provided commands
-----------------

This module provides the following commands:

..  table:: Commands provided by \\Erebot\\Module\\Math

    +---------------------------+-------------------------------------------+
    | Command                   | Description                               |
    +===========================+===========================================+
    | :samp:`!math {formula}`   | Asks the bot to compute the given         |
    |                           | *formula*. The following operators can be |
    |                           | used in the formula: "+" (addition), "-"  |
    |                           | (subtraction), "*" (multiplication), "/"  |
    |                           | (division), "%" (module), "^" (power),    |
    |                           | "(" & ")" (priority).                     |
    +---------------------------+-------------------------------------------+


.. vim: ts=4 et
