#####################
Application Structure
#####################

To get the most out of CodeIgniter, you need to understand how the application is structured, by default, and what you
can change to meet the needs of your application.

Default Directories
===================

A fresh install has four directories: ``/application``, ``/system``, ``/writable``, and ``/tests``. Each of these
directories has a very specific part to play.

application
-----------
The ``application`` directory is where all of your application code lives. This comes with a default directory
structure that works well for many applications. The following folders make up the basic contents::

	/application
		/config         Stores the configuration files
		/controllers    Controllers determine the program flow
		/helpers        Helpers store collections of standalone functions
		/language       Multiple language support reads the language strings from here
		/models         Models work with the database to represent the business entities.
		/views          Views make up the HTML that is displayed to the client.


Because the ``application`` directory is already namespaced, you should feel free to modify the structure
of this directory to suit your application's needs. For example, you might decide to start using the Repository
pattern and Entity Models to work with your data. In this case, you could rename the ``models`` directory to
``repositories``, and add a new ``entities`` directory. If you rename the ``controllers`` directory, though,
you will not be able to use the "legacy" method of routing to controllers, and will need to define all of your
routes in the routes file.

system
------
This directory stores the files that make up the framework, itself. While you have a lot of flexibility in how you
use the application directory, the files in the system directory should never be modified. Instead, you should
extend the classes, or create new classes, to provide the desired functionality.

writable
--------
This directory holds any other directories that might need to be written to in the course of an application's life.
This includes directories for storing cache files, logs, and any uploads a user might send. You should add any other
directories that your application will need to write to. This allows you to keep your other primary directories
non-writable as an added security measure.


tests
-----
This directory is setup to hold your test files. The ``_support`` directory holds various mock classes and other
utilities that you can use while writing your tests. This directory does not need to be transferred to your
production servers.


Security Considerations
=======================
Many people believe that leaving these directories in the web root can be a security concern. It is possible that
a server mis-configuration can display source code to your users instead of the intended pages. If the .htaccess
rules get mis-configured, it can allow attackers to browse the application files. These are just a couple of the
potential security issues that can happen from the standard layout. If you have the ability, it is recommended
to move the main folders out of web root to place that could never be viewed from a browser. This would leave only
files like the main .htaccess file, index.php, and any application assets that you add, like CSS, javascript, or
images, in the web root.

To do this, you'll need to let the application know where to find the directories.


Modifying Directory Locations
-----------------------------

When you've relocated any of the main directories, you can let the application know the new location within
the main ``index.php`` file.

Starting around line 50, you will find three variables that hold the location to the ``application``,
``system``, and ``writable`` directories. These paths are relative to the ``index.php``. An example should
clarify things.

For this example, you've created a new directory called ``public`` that will serve as your web root. You move
``.htaccess`` and ``index.php`` into the new directory, and point your web server to ``public``. You would
need to edit the variables to the following values to get your application running again::

	$system_path = '../system';
	$application_folder = '../application';
	$writable_directory = '../writable';

All three are modified to say that they are a single directory up from ``index.php``. When the application
is ran, the full server paths will be computed from these, being converted to something like
``/var/www/sitea/application``. The constants, ``BASEPATH``, ``WRITEPATH``,  ``SYSDIR``, and ``APPPATH``
will also point to the modified location.
