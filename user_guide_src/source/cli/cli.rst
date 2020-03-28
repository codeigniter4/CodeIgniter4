############################
Running via the Command Line
############################

As well as calling an application's :doc:`Controllers </incoming/controllers>`
via the URL in a browser they can also be loaded via the command-line
interface (CLI).

.. contents::
    :local:
    :depth: 2

What is the CLI?
================

The command-line interface is a text-based method of interacting with
computers. For more information, check the `Wikipedia
article <https://en.wikipedia.org/wiki/Command-line_interface>`_.

Why run via the command-line?
=============================

There are many reasons for running CodeIgniter from the command-line,
but they are not always obvious.

-  Run your cron-jobs without needing to use *wget* or *curl*.
-  Make your cron-jobs inaccessible from being loaded in the URL by
   checking the return value of :php:func:`is_cli()`.
-  Make interactive "tasks" that can do things like set permissions,
   prune cache folders, run backups, etc.
-  Integrate with other applications in other languages. For example, a
   random C++ script could call one command and run code in your models!

Let's try it: Hello World!
==========================

Let's create a simple controller so you can see it in action. Using your
text editor, create a file called Tools.php, and put the following code
in it::

	<?php namespace App\Controllers;

        use CodeIgniter\Controller;

	class Tools extends Controller {

		public function message($to = 'World')
		{
			echo "Hello {$to}!".PHP_EOL;
		}
	}

Then save the file to your **app/Controllers/** directory.

Now normally you would visit your site using a URL similar to this::

	example.com/index.php/tools/message/to

Instead, we are going to open Terminal in Mac/Linux or go to Run > "cmd"
in Windows and navigate to our CodeIgniter project's web root.

.. code-block:: bash

	$ cd /path/to/project/public
	$ php index.php tools message

If you did it right, you should see *Hello World!* printed.

.. code-block:: bash

	$ php index.php tools message "John Smith"

Here we are passing it an argument in the same way that URL parameters
work. "John Smith" is passed as an argument and output is::

	Hello John Smith!

That's the basics!
==================

That, in a nutshell, is all there is to know about controllers on the
command line. Remember that this is just a normal controller, so routing
and ``_remap()`` works fine.

However, CodeIgniter provides additional tools to make creating CLI-accessible
scripts even more pleasant, include CLI-only routing, and a library that helps
you with CLI-only tools.

CLI-Only Routing
----------------

In your **Routes.php** file you can create routes that are only accessible from
the CLI as easily as you would create any other route. Instead of using the ``get()``,
``post()``, or similar method, you would use the ``cli()`` method. Everything else
works exactly like a normal route definition::

    $routes->cli('tools/message/(:segment)', 'Tools::message/$1');

For more information, see the :doc:`Routes </incoming/routing>` page.

The CLI Library
---------------

The CLI library makes working with the CLI interface simple.
It provides easy ways to output text in multiple colors to the terminal window. It also
allows you to prompt a user for information, making it easy to build flexible, smart tools.

See the :doc:`CLI Library </cli/cli_library>` page for detailed information.
