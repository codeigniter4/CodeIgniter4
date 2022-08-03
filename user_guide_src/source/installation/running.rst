Running Your App
################

.. contents::
    :local:
    :depth: 2

A CodeIgniter 4 app can be run in a number of different ways: hosted on a web server,
using virtualization, or using CodeIgniter's command line tool for testing.
This section addresses how to use each technique, and explains some of the pros and cons of them.

If you're new to CodeIgniter, please read the :doc:`Getting Started </intro/index>`
section of the User Guide to begin learning how to build dynamic PHP applications. Enjoy!

.. _initial-configuration:

Initial Configuration
=====================

#. Open the **app/Config/App.php** file with a text editor and
   set your base URL to ``$baseURL``. If you need more flexibility, the baseURL may
   be set within the **.env** file as ``app.baseURL = 'http://example.com/'``.
   (Always use a trailing slash on your base URL!)

    .. note:: If you don't set the ``baseURL`` correctly, in development mode,
        the debug toolbar may not load properly and web pages may take considerably
        longer to display.

#. If you intend to use a database, open the
   **app/Config/Database.php** file with a text editor and set your
   database settings. Alternately, these could be set in your **.env** file.
#. If it is not on the production server, set ``CI_ENVIRONMENT`` to ``development``
   in **.env** file to take advantage of the debugging tools provided. See
   :ref:`setting-development-mode` for the detail.

    .. important:: In production environments, you should disable error display and
        any other development-only functionality. In CodeIgniter, this can be done
        by setting the environment to "production". By default, the application will
        run using the "production" environment. See also :ref:`environment-constant`.

.. note:: If you will be running your site using a web server (e.g., Apache or Nginx),
    you will need to modify the permissions for the ``writable`` folder inside
    your project, so that it is writable by the user or account used by your
    web server.

Local Development Server
========================

CodeIgniter 4 comes with a local development server, leveraging PHP's built-in web server
with CodeIgniter routing. You can use the ``serve`` script to launch it,
with the following command line in the main directory::

    > php spark serve

This will launch the server and you can now view your application in your browser at http://localhost:8080.

.. note:: The built-in development server should only be used on local development machines. It should NEVER
    be used on a production server.

If you need to run the site on a host other than simply localhost, you'll first need to add the host
to your ``hosts`` file. The exact location of the file varies in each of the main operating systems, though
all unix-type systems (include OS X) will typically keep the file at **/etc/hosts**.

The local development server can be customized with three command line options:

- You can use the ``--host`` CLI option to specify a different host to run the application at::

    > php spark serve --host example.dev

- By default, the server runs on port 8080 but you might have more than one site running, or already have
  another application using that port. You can use the ``--port`` CLI option to specify a different one::

    > php spark serve --port 8081

- You can also specify a specific version of PHP to use, with the ``--php`` CLI option, with its value
  set to the path of the PHP executable you want to use::

    > php spark serve --php /usr/bin/php7.6.5.4

Hosting with Apache
===================

A CodeIgniter4 webapp is normally hosted on a web server.
Apache's ``httpd`` is the "standard" platform, and assumed in much of our documentation.

Apache is bundled with many platforms, but can also be downloaded in a bundle
with a database engine and PHP from `Bitnami <https://bitnami.com/stacks/infrastructure>`_.

.htaccess
---------

The "mod_rewrite" module enables URLs without "index.php" in them, and is assumed
in our user guide.

Make sure that the rewrite module is enabled (uncommented) in the main
configuration file, e.g., ``apache2/conf/httpd.conf``::

    LoadModule rewrite_module modules/mod_rewrite.so

Also make sure that the default document root's <Directory> element enables this too,
in the "AllowOverride" setting::

    <Directory "/opt/lamp/apache2/htdocs">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

Removing the index.php
----------------------

See :ref:`CodeIgniter URLs <urls-remove-index-php-apache>`.

Virtual Hosting
---------------

We recommend using "virtual hosting" to run your apps.
You can set up different aliases for each of the apps you work on,

Make sure that the virtual hosting module is enabled (uncommented) in the main
configuration file, e.g., ``apache2/conf/httpd.conf``::

    LoadModule vhost_alias_module modules/mod_vhost_alias.so

Add a host alias in your "hosts" file, typically ``/etc/hosts`` on unix-type platforms,
or ``c:/Windows/System32/drivers/etc/hosts`` on Windows.
Add a line to the file. This could be "myproject.local" or "myproject.test", for instance::

    127.0.0.1 myproject.local

Add a <VirtualHost> element for your webapp inside the virtual hosting configuration,
e.g., ``apache2/conf/extra/httpd-vhost.conf``::

    <VirtualHost *:80>
        DocumentRoot "/opt/lamp/apache2/htdocs/myproject/public"
        ServerName myproject.local
        ErrorLog "logs/myproject-error_log"
        CustomLog "logs/myproject-access_log" common
    </VirtualHost>

If your project folder is not a subfolder of the Apache document root, then your
<VirtualHost> element may need a nested <Directory> element to grant the web server access to the files.

With mod_userdir (shared hosts)
--------------------------------

A common practice in shared hosting environments is to use the Apache module "mod_userdir" to enable per-user Virtual Hosts automatically. Additional configuration is required to allow CodeIgniter4 to be run from these per-user directories.

The following assumes that the server is already configured for mod_userdir. A guide to enabling this module is available `in the Apache documentation <https://httpd.apache.org/docs/2.4/howto/public_html.html>`_.

Because CodeIgniter4 expects the server to find the framework front controller at ``/public/index.php`` by default, you must specify this location as an alternative to search for the request (even if CodeIgniter4 is installed within the per-user web directory).

The default user web directory ``~/public_html`` is specified by the ``UserDir`` directive, typically in ``/apache2/mods-available/userdir.conf`` or ``/apache2/conf/extra/httpd-userdir.conf``::

    UserDir public_html

So you will need to configure Apache to look for CodeIgniter's public directory first before trying to serve the default::

    UserDir "public_html/public" "public_html"

Be sure to specify options and permissions for the CodeIgniter public directory as well. A ``userdir.conf`` might look like::

    <IfModule mod_userdir.c>
        UserDir "public_html/public" "public_html"
        UserDir disabled root

        <Directory /home/*/public_html>
                AllowOverride All
                Options MultiViews Indexes FollowSymLinks
                <Limit GET POST OPTIONS>
                        # Apache <= 2.2:
                        # Order allow,deny
                        # Allow from all

                        # Apache >= 2.4:
                        Require all granted
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        # Apache <= 2.2:
                        # Order deny,allow
                        # Deny from all

                        # Apache >= 2.4:
                        Require all denied
                </LimitExcept>
        </Directory>

        <Directory /home/*/public_html/public>
                AllowOverride All
                Options MultiViews Indexes FollowSymLinks
                <Limit GET POST OPTIONS>
                        # Apache <= 2.2:
                        # Order allow,deny
                        # Allow from all

                        # Apache >= 2.4:
                        Require all granted
                </Limit>
                <LimitExcept GET POST OPTIONS>
                        # Apache <= 2.2:
                        # Order deny,allow
                        # Deny from all

                        # Apache >= 2.4:
                        Require all denied
                </LimitExcept>
        </Directory>
    </IfModule>

Setting Environment
-------------------

See :ref:`Handling Multiple Environments <environment-apache>`.

Testing
-------

With the above configuration, your webapp would be accessed with the URL ``http://myproject.local`` in your browser.

Apache needs to be restarted whenever you change its configuration.

Hosting with Nginx
==================

Nginx is the second most widely used HTTP server for web hosting.
Here you can find an example configuration using PHP 7.3 FPM (unix sockets) under Ubuntu Server.

default.conf
------------

This configuration enables URLs without "index.php" in them and using CodeIgniter's "404 - File Not Found" for URLs ending with ".php".

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

Setting Environment
-------------------

See :ref:`Handling Multiple Environments <environment-nginx>`.

Bootstrapping the App
=====================

In some scenarios you will want to load the framework without actually running the whole
application. This is particularly useful for unit testing your project, but may also be
handy for using third-party tools to analyze and modify your code. The framework comes
with a separate bootstrap script specifically for this scenario: ``system/Test/bootstrap.php``.

Most of the paths to your project are defined during the bootstrap process. You may use
pre-defined constants to override these, but when using the defaults be sure that your
paths align with the expected directory structure for your installation method.
