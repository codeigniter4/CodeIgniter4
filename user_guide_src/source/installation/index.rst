############
Installation
############

CodeIgniter4 can be installed manually, or using Composer.

Manual Installation
===================

CodeIgniter is installed via manual download and unzip the package.

Composer Installation
=====================

While not required, CodeIgniter can be installed via `composer <https://getcomposer.org>`_ create-project command.

::

    composer create-project codeigniter4/framework

Running
=======

#. Upload the CodeIgniter folders and files to your server. The
   **index.php** file will be in the **public** folder inside
   your project root.
#. Open the **application/Config/App.php** file with a text editor and
   set your base URL. If you intend to use encryption or sessions, set
   your encryption key. If you need more flexibility, the baseURL may
   be set within the .env file as **app.baseURL="http://example.com"**.
#. If you intend to use a database, open the
   **application/Config/Database.php** file with a text editor and set your
   database settings.

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
is more fully described on the :doc:`environments page </general/environments>`.
By default, the application will run using the "production" environment. To
take advantage of the debugging tools provided, you should set the environment
to "develop".

That's it!

If you're new to CodeIgniter, please read the :doc:`Getting
Started <../intro/index>` section of the User Guide
to begin learning how to build dynamic PHP applications. Enjoy!

.. toctree::
    :hidden:
    :titlesonly:

    downloads
    self
    upgrading
    troubleshooting
    local_server
