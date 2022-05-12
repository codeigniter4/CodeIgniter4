##############
Spark Commands
##############

CodeIgniter ships with the official command **spark** and built-in commands.

.. contents::
    :local:
    :depth: 2

****************
Running Commands
****************

The commands are run from the command line, in the root directory.
A custom script, **spark** has been provided that is used to run any of the CLI commands::

    > php spark

When called without specifying a command, a simple help page is displayed that also provides a list of
available commands. You should pass the name of the command as the first argument to run that command::

    > php spark migrate

Some commands take additional arguments, which should be provided directly after the command, separated by spaces::

    > php spark db:seed DevUserSeeder

You may always pass ``--no-header`` to suppress the header output, helpful for parsing results::

    > php spark cache:clear --no-header

For all of the commands CodeIgniter provides, if you do not provide the required arguments, you will be prompted
for the information it needs to run correctly::

    > php spark migrate:version
    > Version?

Calling Commands
================

Commands can also be ran from within your own code. This is most often done within a controller for cronjob tasks,
but they can be used at any time. You do this by using the ``command()`` function. This function is always available.

.. literalinclude:: cli_commands/001.php

The only argument is string that is the command called and any parameters. This appears exactly as you would call
it from the command line.

All output from the command that is ran is captured when not run from the command line. It is returned from the command
so that you can choose to display it or not.

******************
Using Help Command
******************

You can get help about any CLI command using the help command as follows::

    > php spark help db:seed

Use the **list** command to get a list of available commands and their descriptions, sorted by categories.
You may also use ``spark list --simple`` to get a raw list of all available commands, sorted alphabetically.
