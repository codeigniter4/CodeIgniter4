###################
Custom CLI Commands
###################

While the ability to use CLI commands like any other route is convenient, you might find times where you
need a little something different. That's where CLI commands come in. They are simple classes that do not
need to have routes defined for, making them perfect for building tools that developers can use to make
their jobs simpler, whether by handling migrations or database seeding, checking cronjob status, or even
building out custom code generators for your company.

.. contents::
    :local:
    :depth: 2

****************
Running Commands
****************

Commands are run from the command line, in the root directory. The same one that holds the **/app**
and **/system** directories. A custom script, **spark** has been provided that is used to run any of the
CLI commands::

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

*********************
Creating New Commands
*********************

You can very easily create new commands to use in your own development. Each class must be in its own file,
and must extend ``CodeIgniter\CLI\BaseCommand``, and implement the ``run()`` method.

The following properties should be used in order to get listed in CLI commands and to add help functionality to your command:

* ``$group``: a string to describe the group the command is lumped under when listing commands. For example: ``Database``
* ``$name``: a string to describe the command's name. For example: ``migrate:create``
* ``$description``: a string to describe the command. For example: ``Creates a new migration file.``
* ``$usage``: a string to describe the command usage. For example: ``migrate:create [name] [options]``
* ``$arguments``: an array of strings to describe each command argument. For example: ``'name' => 'The migration file name'``
* ``$options``: an array of strings to describe each command option. For example: ``'-n' => 'Set migration namespace'``

**Help description will be automatically generated according to the above parameters.**

File Location
=============

Commands must be stored within a directory named **Commands**. However, that directory can be located anywhere
that the :doc:`Autoloader </concepts/autoloader>` can locate it. This could be in **/app/Commands**, or
a directory that you keep commands in to use in all of your project development, like **Acme/Commands**.

.. note:: When the commands are executed, the full CodeIgniter CLI environment has been loaded, making it
 possible to get environment information, path information, and to use any of the tools you would use when making a Controller.

An Example Command
==================

Let's step through an example command whose only function is to report basic information about the application
itself, for demonstration purposes. Start by creating a new file at **/app/Commands/AppInfo.php**. It
should contain the following code:

.. literalinclude:: cli_commands/002.php

If you run the **list** command, you will see the new command listed under its own ``demo`` group. If you take
a close look, you should see how this works fairly easily. The ``$group`` property simply tells it how to organize
this command with all of the other commands that exist, telling it what heading to list it under.

The ``$name`` property is the name this command can be called by. The only requirement is that it must not contain
a space, and all characters must be valid on the command line itself. By convention, though, commands are lowercase,
with further grouping of commands being done by using a colon with the command name itself. This helps keep
multiple commands from having naming collisions.

The final property, ``$description`` is a short string that is displayed in the **list** command and should describe
what the command does.

run()
-----

The ``run()`` method is the method that is called when the command is being run. The ``$params`` array is a list of
any CLI arguments after the command name for your use. If the CLI string was::

    > php spark foo bar baz

Then **foo** is the command name, and the ``$params`` array would be:

.. literalinclude:: cli_commands/003.php

This can also be accessed through the :doc:`CLI </cli/cli_library>` library, but this already has your command removed
from the string. These parameters can be used to customize how your scripts behave.

Our demo command might have a ``run`` method something like:

.. literalinclude:: cli_commands/004.php

***********
BaseCommand
***********

The ``BaseCommand`` class that all commands must extend have a couple of helpful utility methods that you should
be familiar with when creating your own commands. It also has a :doc:`Logger </general/logging>` available at
**$this->logger**.

.. php:class:: CodeIgniter\\CLI\\BaseCommand

    .. php:method:: call(string $command[, array $params = []])

        :param string $command: The name of another command to call.
        :param array $params: Additional CLI arguments to make available to that command.

        This method allows you to run other commands during the execution of your current command:

        .. literalinclude:: cli_commands/005.php

    .. php:method:: showError(Throwable $e)

        :param Throwable $e: The exception to use for error reporting.

        A convenience method to maintain a consistent and clear error output to the CLI:

        .. literalinclude:: cli_commands/006.php

    .. php:method:: showHelp()

        A method to show command help: (usage,arguments,description,options)

    .. php:method:: getPad($array, $pad)

        :param array    $array: The  $key => $value array.
        :param integer  $pad: The pad spaces.

        A method to calculate padding for $key => $value array output. The padding can be used to output a will formatted table in CLI:

        .. literalinclude:: cli_commands/007.php
