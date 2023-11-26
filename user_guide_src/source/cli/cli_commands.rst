#######################
Creating Spark Commands
#######################

While the ability to use Controllers via CLI like any other route is convenient, you might find times where you
need a little something different. That's where Spark commands come in. They are simple classes that do not
need to have routes defined for, making them perfect for building tools that developers can use to make
their jobs simpler, whether by handling migrations or database seeding, checking cronjob status, or even
building out custom code generators for your company.

.. contents::
    :local:
    :depth: 2

*********************
Creating New Commands
*********************

You can very easily create new commands to use in your own development. Each class must be in its own file,
and must extend ``CodeIgniter\CLI\BaseCommand``, and implement the ``run()`` method.

The following properties should be used in order to get listed in CLI commands and to add help functionality to your command:

* ``$group``: a string to describe the group the command is lumped under when listing commands. For example: ``Database``
* ``$name``: a string to describe the command's name. For example: ``make:controller``
* ``$description``: a string to describe the command. For example: ``Generates a new controller file.``
* ``$usage``: a string to describe the command usage. For example: ``make:controller <name> [options]``
* ``$arguments``: an array of strings to describe each command argument. For example: ``'name' => 'The controller class name.'``
* ``$options``: an array of strings to describe each command option. For example: ``'--force' => 'Force overwrite existing file.'``

**Help description will be automatically generated according to the above parameters.**

File Location
=============

Commands must be stored within a directory named **Commands**. However, that directory has to be located in the PSR-4 namespaces
so that the :doc:`Autoloader </concepts/autoloader>` can locate it. This could be in **app/Commands**, or
a directory that you keep commands in to use in all of your project development, like **Acme/Commands**.

.. note:: When the commands are executed, the full CodeIgniter CLI environment has been loaded, making it
 possible to get environment information, path information, and to use any of the tools you would use when making a Controller.

An Example Command
==================

Let's step through an example command whose only function is to report basic information about the application
itself, for demonstration purposes. Start by creating a new file at **app/Commands/AppInfo.php**. It
should contain the following code:

.. literalinclude:: cli_commands/002.php

If you run the **list** command, you will see the new command listed under its own ``Demo`` group. If you take
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
any CLI arguments after the command name for your use. If the CLI string was:

.. code-block:: console

    php spark foo bar baz

Then **foo** is the command name, and the ``$params`` array would be:

.. literalinclude:: cli_commands/003.php

This can also be accessed through the :doc:`CLI </cli/cli_library>` library, but this already has your command removed
from the string. These parameters can be used to customize how your scripts behave.

Our demo command might have a ``run()`` method something like:

.. literalinclude:: cli_commands/004.php

See the :doc:`CLI Library </cli/cli_library>` page for detailed information.

Command Termination
-------------------

By default, the command exits with a success code of ``0``. If an error is encountered while executing a command,
you can terminate the command by using the ``return`` language construct with an exit code in the ``run()`` method.

For example, ``return EXIT_ERROR;``

This approach can help with debugging at the system level, if the command, for example, is run via crontab.

You can use the ``EXIT_*`` exit code constants defined in the **app/Config/Constants.php** file.

***********
BaseCommand
***********

The ``BaseCommand`` class that all commands must extend have a couple of helpful utility methods that you should
be familiar with when creating your own commands. It also has a :doc:`Logger </general/logging>` available at
``$this->logger``.

.. php:namespace:: CodeIgniter\CLI

.. php:class:: BaseCommand

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

    .. php:method:: setPad(string $item, int $max, int $extra = 2, int $indent = 0): string

        :param string   $item: The string item.
        :param integer  $max: The max size.
        :param integer  $extra: How many extra spaces to add at the end.
        :param integer  $indent: The indent spaces.

        Pads our string out so that all titles are the same length to nicely line
        up descriptions:

        .. literalinclude:: cli_commands/007.php
            :lines: 2-

    .. php:method:: getPad($array, $pad)

        .. deprecated:: 4.0.5
            Use :php:meth:`CodeIgniter\\CLI\\BaseCommand::setPad()` instead.

        :param array    $array: The  $key => $value array.
        :param integer  $pad: The pad spaces.

        A method to calculate padding for ``$key => $value`` array output. The padding can be used to output a will formatted table in CLI.
