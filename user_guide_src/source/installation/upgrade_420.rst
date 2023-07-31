#############################
Upgrading from 4.1.9 to 4.2.0
#############################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2

Mandatory File Changes
**********************

index.php and spark
===================

The following files received significant changes and
**you must merge the updated versions** with your application:

* ``public/index.php``
* ``spark``

.. important:: If you don't update the above two files, CodeIgniter will not work at all
    after running ``composer update``.

    The upgrade procedure, for example, is as follows:

    .. code-block:: console

        composer update
        cp vendor/codeigniter4/framework/public/index.php public/index.php
        cp vendor/codeigniter4/framework/spark .

Config/Constants.php
====================

The constants ``EVENT_PRIORITY_LOW``, ``EVENT_PRIORITY_NORMAL`` and ``EVENT_PRIORITY_HIGH`` are deprecated, and the definitions are moved to ``app/Config/Constants.php``. If you use these constants, define them in ``app/Config/Constants.php``. Or use new class constants ``CodeIgniter\Events\Events::PRIORITY_LOW``, ``CodeIgniter\Events\Events::PRIORITY_NORMAL`` and ``CodeIgniter\Events\Events::PRIORITY_HIGH``.

composer.json
=============

If you use Composer, when you installed CodeIgniter v4.1.9 or before, and
if there are ``App\\`` and ``Config\\`` namespaces in your ``/composer.json``'s ``autoload.psr-4``
like the following, you need to remove these lines, and run ``composer dump-autoload``.

.. code-block:: text

    {
        ...
        "autoload": {
            "psr-4": {
                "App\\": "app",             <-- Remove this line
                "Config\\": "app/Config"    <-- Remove this line
            }
        },
        ...
    }

Breaking Changes
****************

- The ``system/bootstrap.php`` file no longer returns a ``CodeIgniter`` instance, and does not load the ``.env`` file (now handled in ``index.php`` and ``spark``). If you have code that expects these behaviors it will no longer work and must be modified. This has been changed to make `Preloading <https://www.php.net/manual/en/opcache.preloading.php>`_ easier to implement.

Breaking Enhancements
*********************

- The method signature of ``Validation::setRule()`` has been changed. The ``string`` typehint on the ``$rules`` parameter was removed. Extending classes should likewise remove the parameter so as not to break LSP.
- The method signature of ``CodeIgniter\Database\BaseBuilder::join()`` and ``CodeIgniter\Database\*\Builder::join()`` have been changed. The ``string`` typehint on the ``$cond`` parameter was removed. Extending classes should likewise remove the parameter so as not to break LSP.

Project Files
*************

Numerous files in the **project space** (root, app, public, writable) received updates. Due to
these files being outside of the **system** scope they will not be changed without your intervention.
There are some third-party CodeIgniter modules available to assist with merging changes to
the project space: `Explore on Packagist <https://packagist.org/explore/?query=codeigniter4%20updates>`_.

.. note:: Except in very rare cases for bug fixes, no changes made to files for the project space
    will break your application. All changes noted here are optional until the next major version,
    and any mandatory changes will be covered in the sections above.

Content Changes
===============

The following files received significant changes (including deprecations or visual adjustments)
and it is recommended that you merge the updated versions with your application:

* ``app/Config/Routes.php``
    * To make the default configuration more secure, auto-routing has been changed to disabled by default.

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

* app/Config/App.php
* app/Config/Constants.php
* app/Config/ContentSecurityPolicy.php
* app/Config/Database.php
* app/Config/Events.php
* app/Config/Feature.php
* app/Config/Filters.php
* app/Config/Format.php
* app/Config/Logger.php
* app/Config/Mimes.php
* app/Config/Publisher.php
* app/Config/Routes.php
* app/Config/Security.php
* app/Config/Validation.php
* app/Config/View.php
* app/Controllers/BaseController.php
* app/Views/errors/html/debug.css
* app/Views/errors/html/debug.js
* app/Views/errors/html/error_404.php
* app/Views/errors/html/error_exception.php
* app/Views/errors/html/production.php
* app/Views/welcome_message.php
* app/index.html
* preload.php
* public/index.php
* spark
