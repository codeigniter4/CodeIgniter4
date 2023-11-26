############
CLI Overview
############

CodeIgniter 4 provides built-in command **spark** and useful commands and library.
You can also create spark commands, and run controllers via CLI.

.. contents::
    :local:
    :depth: 2

****************
What is the CLI?
****************

The command-line interface is a text-based method of interacting with
computers. For more information, check the `Wikipedia
article <https://en.wikipedia.org/wiki/Command-line_interface>`_.

*****************************
Why Run via the Command-Line?
*****************************

There are many reasons for running CodeIgniter from the command-line,
but they are not always obvious.

-  Run your cron-jobs without needing to use *wget* or *curl*.
-  Make interactive "tasks" that can do things like set permissions,
   prune cache folders, run backups, etc.
-  Integrate with other applications in other languages. For example, a
   random C++ script could call one command and run code in your models!

******************
The Spark Commands
******************

CodeIgniter ships with the official command **spark** and built-in commands.

You can run the spark and see the help:

.. code-block:: console

    php spark

See the :doc:`spark_commands` page for detailed information.

***************
The CLI Library
***************

The CLI library makes working with the CLI interface simple.
It provides easy ways to output text in multiple colors to the terminal window. It also
allows you to prompt a user for information, making it easy to build flexible, smart tools.

See the :doc:`CLI Library </cli/cli_library>` page for detailed information.
