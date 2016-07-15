############
Installation
############

CodeIgniter4 can be installed manually, or using Composer.

Manual Installation
===================

CodeIgniter is installed in four steps:

#. Unzip the package.
#. Upload the CodeIgniter folders and files to your server. The
   **index.php** file will be in the **public** folder inside
   your project root.
#. Open the **application/Config/App.php** file with a text editor and
   set your base URL. If you intend to use encryption or sessions, set
   your encryption key.
#. If you intend to use a database, open the
   **application/Config/Database.php** file with a text editor and set your
   database settings.

If you wish to increase security by hiding the location of your
CodeIgniter files you can rename the system and application directories to
something more private. If you do rename them, you must open your main
*index.php* file and set the ``$system_directory`` and ``$application_directory``
variables at the top of the file with the new name you've chosen.

For the best security, both the system and any application directories
come placed above the web root so that they are not directly accessible
via a browser. By default, **.htaccess** files are included in each directory
to help prevent direct access, but it is best to remove them from public
access entirely in case the web server configuration changes or doesn't
abide by the **.htaccess**.

If you would like to keep your views public it is also possible to move
the **views** directory out of your **application** directory, to a
corresponding folder inside **public**. If you do this, remember to
open your main index.php file and set the
``$system_path``, ``$application_folder`` and ``$view_folder`` variables,
preferably with a full path, e.g. '*/www/MyUser/system*'.

One additional measure to take in production environments is to disable
PHP error reporting and any other development-only functionality. In
CodeIgniter, this can be done by setting the ``ENVIRONMENT`` constant, which
is more fully described on the :doc:`security page <../general/security>`.

That's it!

If you're new to CodeIgniter, please read the :doc:`Getting
Started <../overview/getting_started>` section of the User Guide
to begin learning how to build dynamic PHP applications. Enjoy!

Composer Installation
=====================

TODO


.. toctree::
	:hidden:
	:titlesonly:

	downloads
	self
	upgrading
	troubleshooting

