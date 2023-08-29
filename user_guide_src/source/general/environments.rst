##############################
Handling Multiple Environments
##############################

Developers often desire different system behavior depending on whether
an application is running in a development or production environment.
For example, verbose error output is something that would be useful
while developing an application, but it may also pose a security issue
when "live". In development environments, you might want additional
tools loaded that you don't in production environments, etc.

.. contents::
    :local:
    :depth: 3

************************
The Defined Environments
************************

By default, CodeIgniter has three environments defined.

- ``production`` for production
- ``development`` for development
- ``testing`` for PHPUnit testing

.. important:: The environment ``testing`` is reserved for PHPUnit testing. It
    has special conditions built into the framework at various places to assist
    with that. You can't use it for your development.

If you want another environment, e.g., for staging, you can add custom environments.
See `Adding Environments`_.

*******************
Setting Environment
*******************

.. _environment-constant:

The ENVIRONMENT Constant
========================

To set your environment, CodeIgniter comes with the ``ENVIRONMENT`` constant.
If you set ``$_SERVER['CI_ENVIRONMENT']``, the value will be used,
otherwise defaulting to ``production``.

This can be set in several ways depending on your server setup.

.env
----

The simplest method to set the variable is in your :ref:`.env file <dotenv-file>`.

.. code-block:: ini

    CI_ENVIRONMENT = development

.. note:: You can change the ``CI_ENVIRONMENT`` value in **.env** file by ``spark env`` command:

    .. code-block:: console

        php spark env production

.. _environment-apache:

Apache
------

This server variable can be set in your **.htaccess** file or Apache
config using `SetEnv <https://httpd.apache.org/docs/2.4/mod/mod_env.html#setenv>`_.

.. code-block:: apache

    SetEnv CI_ENVIRONMENT development


.. _environment-nginx:

nginx
-----

Under nginx, you must pass the environment variable through the ``fastcgi_params``
in order for it to show up under the ``$_SERVER`` variable. This allows it to work on the
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

*******************
Adding Environments
*******************

To add custom environments, you just need to add boot files for them.

Boot Files
==========

CodeIgniter requires that a PHP script matching the environment's name is located
under **APPPATH/Config/Boot**. These files can contain any customizations that
you would like to make for your environment, whether it's updating the error display
settings, loading additional developer tools, or anything else. These are
automatically loaded by the system. The following files are already created in
a fresh install:

* development.php
* production.php
* testing.php

For example, if you want to add ``staging`` environment for staging, all you need
to do is:

1. copy **APPPATH/Config/Boot/production.php** to **staging.php**.
2. customize settings in **staging.php** if you want.

**********************************
Confirming the Current Environment
**********************************

To confirm the current environment, simply echo the constant ``ENVIRONMENT``.

You can also check the current environment by ``spark env`` command:

.. code-block:: console

    php spark env

*************************************
Effects on Default Framework Behavior
*************************************

There are some places in the CodeIgniter system where the ``ENVIRONMENT``
constant is used. This section describes how default framework behavior
is affected.

Error Reporting
===============

Setting the ``ENVIRONMENT`` constant to a value of ``development`` will cause
all PHP errors to be rendered to the browser when they occur.
Conversely, setting the constant to ``production`` will disable all error
output. Disabling error reporting in production is a
:doc:`good security practice </concepts/security>`.
