########################
Local Development Server
########################

PHP provides a built-in web server that is can be used locally when developing an application without
the need to setup a dedicated web server like MAMP, XAMPP, etc. If you have PHP installed on your
development machine, you can use the ``serve`` script to launch PHP's built-in server and have
it all setup to work with your CodeIgniter application. To launch the server type the following
from the command line in the main directory::

    > php serve

This will launch the server and you can now view your application in your browser at http://localhost:8080.

.. note:: The built-in development server should only be used on local development machines. It should NEVER
    be used on a production server.

Customization
=============

If you need to run the site on a different host than simply localhost, you'll first need to add the host
to your ``hosts`` file. The exact location of the file varies in each of the main operating systems, though
all *nix-type systems (include OS X) will typically keep the file at **/etc/hosts**.

Once that is done you can use the ``--host`` CLI option to specify a different host to run the application at::

    > php serve --host=example.dev

By default, the server runs on port 8080 but you might have more than one site running, or already have
another application using that port. You can use the ``--port`` CLI option to specify a different one::

    > php serve --port=8081
