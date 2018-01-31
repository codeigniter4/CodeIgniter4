#####################
Application Structure
#####################

To get the most out of CodeIgniter, you need to understand how the application is structured, by default, and what you
can change to meet the needs of your application.

Default Directories
===================

A fresh install has six directories: ``/application``, ``/system``, ``/public``,
``/writable``, ``/tests`` and ``/docs``.
Each of these directories has a very specific part to play.

application
-----------
The ``application`` directory is where all of your application code lives. This comes with a default directory
structure that works well for many applications. The following folders make up the basic contents:

.. code-block:: none

	/application
		/Config         Stores the configuration files
		/Controllers    Controllers determine the program flow
		/Helpers        Helpers store collections of standalone functions
		/Language       Multiple language support reads the language strings from here
		/Libraries      Useful classes that don't fit in another category
		/Models         Models work with the database to represent the business entities.
		/Views          Views make up the HTML that is displayed to the client.

Because the ``application`` directory is already namespaced, you should feel free to modify the structure
of this directory to suit your application's needs. For example, you might decide to start using the Repository
pattern and Entity Models to work with your data. In this case, you could rename the ``Models`` directory to
``Repositories``, and add a new ``Entities`` directory.

.. note:: If you rename the ``Controllers`` directory, though, you will not be able to use the automatic method of
		routing to controllers, and will need to define all of your routes in the routes file.

All files in this directory live under the ``App`` namespace, though you are free to change that in
**application/Config/Constants.php**.

system
------
This directory stores the files that make up the framework, itself. While you have a lot of flexibility in how you
use the application directory, the files in the system directory should never be modified. Instead, you should
extend the classes, or create new classes, to provide the desired functionality.

All files in this directory live under the ``CodeIgniter`` namespace.

public
------

The **public** folder holds the browser-accessible portion of your web application,
preventing direct access to your source code.
It contains the main **.htaccess** file, **index.php**, and any application
assets that you add, like CSS, javascript, or
images.

This folder is meant to be the "web root" of your site, and your web server
would be configured to point to it.

writable
--------
This directory holds any directories that might need to be written to in the course of an application's life.
This includes directories for storing cache files, logs, and any uploads a user might send. You should add any other
directories that your application will need to write to here. This allows you to keep your other primary directories
non-writable as an added security measure.

tests
-----
This directory is setup to hold your test files. The ``_support`` directory holds various mock classes and other
utilities that you can use while writing your tests. This directory does not need to be transferred to your
production servers.

docs
----
This directory holds the CodeIgniter documentation. The ``user_guide`` subfolder contains a local copy of the
User Guide, and the ``api_docs`` subfolder contains a local copy of the CodeIgniter components API reference.

Modifying Directory Locations
-----------------------------

If you've relocated any of the main directories, you can let the application
know the new location within the main ``index.php`` file.

Starting around line 50, you will find three variables that hold the location to the **application**,
**system**, and **writable** directories. These paths are relative to **index.php**.
