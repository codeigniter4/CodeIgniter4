################
Running Your App
################

A CodeIgniter 4 app can be run in a number of different ways: using a web server,
using virtualization, or using PHP's built-in server. This section addresses how to use
each technique, and explains some of the pros and cons of them.

Initial Configuration & Setup
=================================================

Hosting with Apache
=================================================

Hosting with NGINX
=================================================

Hosting with Vagrant
=================================================

Local Development Server
=================================================

CodeIgniter 4 comes with a local development server, leveraging PHP's built-in web server 
with CodeIgniter routing. You can use the ``serve`` script to launch it, 
with the following command line in the main directory::

    > php spark serve

This will launch the server and you can now view your application in your browser at http://localhost:8080.

.. note:: The built-in development server should only be used on local development machines. It should NEVER
    be used on a production server.

Customization
=============

If you need to run the site on a host other than simply localhost, you'll first need to add the host
to your ``hosts`` file. The exact location of the file varies in each of the main operating systems, though
all *nix-type systems (include OS X) will typically keep the file at **/etc/hosts**.

Once that is done you can use the ``--host`` CLI option to specify a different host to run the application at::

    > php spark serve --host=example.dev

By default, the server runs on port 8080 but you might have more than one site running, or already have
another application using that port. You can use the ``--port`` CLI option to specify a different one::

    > php spark serve --port=8081

You can also specify a specific version of PHP to use, with the ``--php`` CLI option, with its value
set to the path of the PHP executable you want to use::

    > php spark serve --php=/usr/bin/php7.6.5.4

--------------------------------------------------------------------------

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

.. caution:: Using PHP's built-in web server is likely to cause problems,
	as it does not process the `.htaccess` file used to properly handle requests.

That's it!

If you're new to CodeIgniter, please read the :doc:`Getting
Started <../intro/index>` section of the User Guide
to begin learning how to build dynamic PHP applications. Enjoy!
