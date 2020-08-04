Running Your App
###############################################################################

.. contents::
    :local:
    :depth: 1

A CodeIgniter 4 app can be run in a number of different ways: hosted on a web server,
using virtualization, or using CodeIgniter’s command line tool for testing.
This section addresses how to use each technique, and explains some of the pros and cons of them.

If you’re new to CodeIgniter, please read the :doc:`Getting Started </intro/index>`
section of the User Guide to begin learning how to build dynamic PHP applications. Enjoy!

Initial Configuration & Set Up
=================================================

#. Open the **app/Config/App.php** file with a text editor and
   set your base URL. If you need more flexibility, the baseURL may
   be set within the ``.env`` file as **app.baseURL="http://example.com"**.
#. If you intend to use a database, open the
   **app/Config/Database.php** file with a text editor and set your
   database settings. Alternately, these could be set in your ``.env`` file.

One additional measure to take in production environments is to disable
PHP error reporting and any other development-only functionality. In
CodeIgniter, this can be done by setting the ``ENVIRONMENT`` constant, which
is more fully described on the :doc:`environments page </general/environments>`.
By default, the application will run using the "production" environment. To
take advantage of the debugging tools provided, you should set the environment
to "development".

.. note:: If you will be running your site using a web server (e.g. Apache or Nginx),
    you will need to modify the permissions for the ``writable`` folder inside
    your project, so that it is writable by the user or account used by your
    web server.

Local Development Server
=================================================

CodeIgniter 4 comes with a local development server, leveraging PHP's built-in web server
with CodeIgniter routing. You can use the ``serve`` script to launch it,
with the following command line in the main directory::

    php spark serve

This will launch the server and you can now view your application in your browser at http://localhost:8080.

.. note:: The built-in development server should only be used on local development machines. It should NEVER
    be used on a production server.

If you need to run the site on a host other than simply localhost, you'll first need to add the host
to your ``hosts`` file. The exact location of the file varies in each of the main operating systems, though
all unix-type systems (include OS X) will typically keep the file at **/etc/hosts**.

The local development server can be customized with three command line options:

- You can use the ``--host`` CLI option to specify a different host to run the application at::

    php spark serve --host example.dev

- By default, the server runs on port 8080 but you might have more than one site running, or already have
  another application using that port. You can use the ``--port`` CLI option to specify a different one::

    php spark serve --port 8081

- You can also specify a specific version of PHP to use, with the ``--php`` CLI option, with its value
  set to the path of the PHP executable you want to use::

    php spark serve --php /usr/bin/php7.6.5.4

Hosting with Apache
=================================================

A CodeIgniter4 webapp is normally hosted on a web server.
Apache’s ``httpd`` is the "standard" platform, and assumed in much of our documentation.

Apache is bundled with many platforms, but can also be downloaded in a bundle
with a database engine and PHP from `Bitnami <https://bitnami.com/stacks/infrastructure>`_.

.htaccess
-------------------------------------------------------

The “mod_rewrite” module enables URLs without “index.php” in them, and is assumed
in our user guide.

Make sure that the rewrite module is enabled (uncommented) in the main
configuration file, eg. ``apache2/conf/httpd.conf``::

    LoadModule rewrite_module modules/mod_rewrite.so

Also make sure that the default document root's <Directory> element enables this too,
in the "AllowOverride" setting::

    <Directory "/opt/lamp7.2/apache2/htdocs">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

Virtual Hosting
-------------------------------------------------------

We recommend using “virtual hosting” to run your apps.
You can set up different aliases for each of the apps you work on,

Make sure that the virtual hosting module is enabled (uncommented) in the main
configuration file, eg. ``apache2/conf/httpd.conf``::

    LoadModule vhost_alias_module modules/mod_vhost_alias.so

Add a host alias in your “hosts” file, typically ``/etc/hosts`` on unix-type platforms,
or ``c:/Windows/System32/drivers/etc/hosts`` on Windows.
Add a line to the file. This could be "myproject.local" or "myproject.test", for instance::

    127.0.0.1 myproject.local

Add a <VirtualHost> element for your webapp inside the virtual hosting configuration,
eg. ``apache2/conf/extra/httpd-vhost.conf``::

    <VirtualHost *:80>
        DocumentRoot "/opt/lamp7.2/apache2/htdocs/myproject/public"
        ServerName myproject.local
        ErrorLog "logs/myproject-error_log"
        CustomLog "logs/myproject-access_log" common
    </VirtualHost>

If your project folder is not a subfolder of the Apache document root, then your
<VirtualHost> element may need a nested <Directory> element to grant the web s
erver access to the files.

Testing
-------------------------------------------------------

With the above configuration, your webapp would be accessed with the URL ``http://myproject.local`` in your browser.

Apache needs to be restarted whenever you change its configuration.

Hosting with Nginx
=================================================
Nginx is the second most widely used HTTP server for web hosting.
Here you can find an example configuration using PHP 7.3 FPM (unix sockets) under Ubuntu Server.

This configuration enables URLs without “index.php” in them and using CodeIgniter's “404 - File Not Found” for URLs ending with “.php”.

.. code-block:: nginx

    server {
        listen 80;
        listen [::]:80;

        server_name example.com;

        root  /var/www/example.com/public;
        index index.php index.html index.htm;

        location / {
            try_files $uri $uri/ /index.php$is_args$args;
        }

        location ~ \.php$ {
            include snippets/fastcgi-php.conf;

            # With php-fpm:
            fastcgi_pass unix:/run/php/php7.3-fpm.sock;
            # With php-cgi:
            # fastcgi_pass 127.0.0.1:9000;
        }

        error_page 404 /index.php;

        # deny access to hidden files such as .htaccess
        location ~ /\. {
            deny all;
        }
    }

Hosting with Vagrant
=================================================

Virtualization is an effective way to test your webapp in the environment you
plan to deploy on, even if you develop on a different one.
Even if you are using the same platform for both, virtualization provides an
isolated environment for testing.

The codebase comes with a ``VagrantFile.dist``, that can be copied to ``VagrantFile``
and tailored for your system, for instance enabling access to specific database or caching engines.

Setting Up
-------------------------------------------------------

It assumes that you have installed `VirtualBox <https://www.virtualbox.org/wiki/Downloads>`_ and
`Vagrant <https://www.vagrantup.com/downloads.html>`_
for your platform.

The Vagrant configuration file assumes you have set up a `ubuntu/bionic64 Vagrant box
<https://app.vagrantup.com/ubuntu/boxes/bionic64>`_ on your system::

    vagrant box add ubuntu/bionic64

Testing
-------------------------------------------------------

Once set up, you can then launch your webapp inside a VM, with the command::

    vagrant up

Your webapp will be accessible at ``http://localhost:8080``, with the code coverage
report for your build at ``http://localhost:8081`` and the user guide for
it at ``http://localhost:8082``.
