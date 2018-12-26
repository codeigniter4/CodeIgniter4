################
Running Your App
################

A CodeIgniter 4 app can be run in a number of different ways: using a web server,
using virtualization, or using PHP's built-in server. This section addresses how to use
each technique, and explains some of the pros and cons of them.

Initial Configuration & Setup
=================================================

#. Open the **app/Config/App.php** file with a text editor and
   set your base URL. If you intend to use encryption or sessions, set
   your encryption key. If you need more flexibility, the baseURL may
   be set within the .env file as **app.baseURL="http://example.com"**.
#. If you intend to use a database, open the
   **app/Config/Database.php** file with a text editor and set your
   database settings.

One additional measure to take in production environments is to disable
PHP error reporting and any other development-only functionality. In
CodeIgniter, this can be done by setting the ``ENVIRONMENT`` constant, which
is more fully described on the :doc:`environments page </general/environments>`.
By default, the application will run using the "production" environment. To
take advantage of the debugging tools provided, you should set the environment
to "develop".

Hosting with Apache
=================================================

Directions coming with the next release.

Hosting with NGINX
=================================================

Directions coming with the next release.

Hosting with Vagrant
=================================================

Directions coming with the next release.

Local Development Server
=================================================

CodeIgniter 4 comes with a local development server, leveraging PHP's built-in web server
with CodeIgniter routing. You can use the ``serve`` script to launch it,
with the following command line in the main directory::

    > php spark serve

This will launch the server and you can now view your application in your browser at http://localhost:8080.

.. note:: The built-in development server should only be used on local development machines. It should NEVER
    be used on a production server.

If you need to run the site on a host other than simply localhost, you'll first need to add the host
to your ``hosts`` file. The exact location of the file varies in each of the main operating systems, though
all *nix-type systems (include OS X) will typically keep the file at **/etc/hosts**.

The local development server can be customized with three command line options:

- Once that is done you can use the ``--host`` CLI option to specify a different host to run the application at::

    > php spark serve --host=example.dev

- By default, the server runs on port 8080 but you might have more than one site running, or already have
  another application using that port. You can use the ``--port`` CLI option to specify a different one::

    > php spark serve --port=8081

- You can also specify a specific version of PHP to use, with the ``--php`` CLI option, with its value
  set to the path of the PHP executable you want to use::

    > php spark serve --php=/usr/bin/php7.6.5.4



If you're new to CodeIgniter, please read the :doc:`Getting
Started <../intro/index>` section of the User Guide
to begin learning how to build dynamic PHP applications. Enjoy!
