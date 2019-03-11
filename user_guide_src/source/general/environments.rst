##############################
Handling Multiple Environments
##############################

Developers often desire different system behavior depending on whether
an application is running in a development or production environment.
For example, verbose error output is something that would be useful
while developing an application, but it may also pose a security issue
when "live". In development environments, you might want additional
tools loaded that you don't in production environments, etc.

The ENVIRONMENT Constant
========================

By default, CodeIgniter comes with the environment constant set to use
the value provided in ``$_SERVER['CI_ENVIRONMENT']``, otherwise defaulting to
'production'. This can be set in several ways depending on your server setup.

.env
----

The simplest method to set the variable is in your :doc:`.env file </general/configuration>`.

.. code-block:: ini

    CI_ENVIRONMENT = development

Apache
------

This server variable can be set in your ``.htaccess`` file or Apache
config using `SetEnv <https://httpd.apache.org/docs/2.2/mod/mod_env.html#setenv>`_.

.. code-block:: apache

    SetEnv CI_ENVIRONMENT development

nginx
-----

Under nginx, you must pass the environment variable through the ``fastcgi_params``
in order for it to show up under the `$_SERVER` variable. This allows it to work on the
virtual-host level, instead of using `env` to set it for the entire server, though that
would work fine on a dedicated server. You would then modify your server config to something
like:

.. code-block:: nginx

	server {
	    server_name localhost;
	    include     conf/defaults.conf;
	    root        /var/www;

	    location    ~* \.php$ {
	        fastcgi_param CI_ENVIRONMENT "production";
	        include conf/fastcgi-php.conf;
	    }
	}

Alternative methods are available for nginx and other servers, or you can
remove this logic entirely and set the constant based on the server's IP address
(for instance).

In addition to affecting some basic framework behavior (see the next
section), you may use this constant in your own development to
differentiate between which environment you are running in.

Boot Files
----------

CodeIgniter requires that a PHP script matching the environment's name is located
under **APPPATH/Config/Boot**. These files can contain any customizations that
you would like to make for your environment, whether it's updating the error display
settings, loading additional developer tools, or anything else. These are
automatically loaded by the system. The following files are already created in
a fresh install:

* development.php
* production.php
* testing.php

Effects On Default Framework Behavior
=====================================

There are some places in the CodeIgniter system where the ENVIRONMENT
constant is used. This section describes how default framework behavior
is affected.

Error Reporting
---------------

Setting the ENVIRONMENT constant to a value of 'development' will cause
all PHP errors to be rendered to the browser when they occur.
Conversely, setting the constant to 'production' will disable all error
output. Disabling error reporting in production is a
:doc:`good security practice </concepts/security>`.

Configuration Files
-------------------

Optionally, you can have CodeIgniter load environment-specific
configuration files. This may be useful for managing things like
differing API keys across multiple environments. This is described in
more detail in the Handling Different Environments section of the
:doc:`Working with Configuration Files </general/configuration>` documentation.
