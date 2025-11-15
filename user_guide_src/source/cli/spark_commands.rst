##############
Spark Commands
##############

CodeIgniter ships with the official command **spark** and built-in commands.

.. contents::
    :local:
    :depth: 3

****************
Running Commands
****************

Running via CLI
===============

The commands are run from the command line, in the project root directory.
The command file **spark** has been provided that is used to run any of the CLI commands.

Showing List of Commands
------------------------

When called **spark** without specifying a command, a simple help page is displayed
that also provides a list of available commands and their descriptions, sorted by
categories:

.. code-block:: console

    php spark

spark list
^^^^^^^^^^

``php spark`` is the exactly same as the ``list`` command:

.. code-block:: console

    php spark list

You may also use the ``--simple`` option to get a raw list of all available commands,
sorted alphabetically:

.. code-block:: console

    php spark list --simple

Showing Help
------------

You can get help about any CLI command using the ``help`` command as follows:

.. code-block:: console

    php spark help db:seed

Since v4.3.0, you can also use the ``--help`` option instead of the ``help`` command:

.. code-block:: console

    php spark db:seed --help

Running a Command
-----------------

You should pass the name of the command as the first argument to run that command:

.. code-block:: console

    php spark migrate

Some commands take additional arguments, which should be provided directly after the command, separated by spaces:

.. code-block:: console

    php spark db:seed DevUserSeeder

For all of the commands CodeIgniter provides, if you do not provide the required arguments, you will be prompted
for the information it needs to run correctly:

.. code-block:: console

    php spark make:controller

    Controller class name :

Suppressing Header Output
-------------------------

When you run a command, the header with CodeIgniter version and the current time
is output:

.. code-block:: console

    php spark env

    CodeIgniter v4.3.5 Command Line Tool - Server Time: 2023-06-16 12:45:31 UTC+00:00

    Your environment is currently set as development.

You may always pass ``--no-header`` to suppress the header output, helpful for parsing results:

.. code-block:: console

    php spark env --no-header

    Your environment is currently set as development.

Calling Commands
================

Commands can also be ran from within your own code. This is most often done within a controller for cronjob tasks,
but they can be used at any time. You do this by using the ``command()`` function. This function is always available.

.. literalinclude:: cli_commands/001.php

The only argument is string that is the command called and any parameters. This appears exactly as you would call
it from the command line.

All output from the command that is ran is captured when not run from the command line. It is returned from the command
so that you can choose to display it or not.
