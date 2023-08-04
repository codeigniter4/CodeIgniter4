##########################
Creating Composer Packages
##########################

You can make the :doc:`../general/modules` you create into Composer packages,
or can create a Composer package for CodeIgniter 4.

.. contents::
    :local:
    :depth: 2

****************
Folder Structure
****************

Here's a typical directory structure for a Composer package::

    your-package-name/
    ├── .gitattributes
    ├── .gitignore
    ├── LICENSE
    ├── README.md
    ├── composer.json
    ├── src/
    │   └── YourClass.php
    └── tests/
        └── YourClassTest.php

**********************
Creating composer.json
**********************

In the root of your package directory, create a **composer.json** file. This file
defines metadata about your package and its dependencies.

The ``composer init`` command helps you create it.

For example, **composer.json** might look like this::

    {
        "name": "your-vendor/your-package",
        "description": "Your package description",
        "type": "library",
        "license": "MIT",
        "autoload": {
            "psr-4": {
                "YourVendor\\YourPackage\\": "src/"
            }
        },
        "authors": [
            {
                "name": "Your Name",
                "email": "yourname@example.com"
            }
        ],
        "require": {
            // Any dependencies required by your package go here
        },
        "require-dev": {
            // Any development dependencies (e.g., PHPUnit) go here
        }
    }

Package Name
============

The ``name`` field is important here. Package names are generally written in the
format "vendor-name/package-name" with all lowercase. Here is a common example:

- ``your-vendor-name``: The name that identifies the vendor (creator of the package),
  such as your name or your organization.
- ``your-package-name``: The name of the package you are creating.

Thus, it is important to make the name unique to distinguish it from other packages.
Uniqueness is especially important when publishing.

Namespace
=========

The package name then determines the vendor namespace in ``autoload.psr4``.

If your package name is ``your-vendor/your-package``, the vendor namespace must
be ``YourVendor``. So you would write like the following::

    "autoload": {
        "psr-4": {
            "YourVendor\\YourPackage\\": "src/"
        }
    },

This setting instructs Composer to autoload the source code for your package.

Choosing License
================

If you are not familiar with open source licenses, see https://choosealicense.com/.
Many PHP packages, including CodeIgniter, use the MIT license.

***************************
Preparing Development Tools
***************************

There are many tools that help ensure quality code. So you should use them.
You can easily install and configure such tools with
`CodeIgniter DevKit <https://github.com/codeigniter4/devkit>`_.

Installing DevKit
=================

In the root of your package directory, run the following commands:

.. code-block:: console

    composer config minimum-stability dev
    composer config prefer-stable true
    composer require --dev codeigniter4/devkit

The DevKit installs various Composer packages that helps your development, and
installs templates for them in **vendor/codeigniter4/devkit/src/Template**.
Copy the files in it to your project root folder, and edit them for your needs.

Configuring Coding Standards Fixer
==================================

DevKit provides Coding Standards Fixer with
`CodeIgniter Coding Standard <https://github.com/CodeIgniter/coding-standard>`_
based on `PHP-CS-Fixer <https://github.com/PHP-CS-Fixer/PHP-CS-Fixer>`_.

Copy **vendor/codeigniter4/devkit/src/Template/.php-cs-fixer.dist.php** to your
project root folder.

Create the **build** folder for the cache file::

    your-package-name/
    ├── .php-cs-fixer.dist.php
    ├── build/

Open **.php-cs-fixer.dist.php** in your editor, and fix the folder path::

    --- a/.php-cs-fixer.dist.php
    +++ b/.php-cs-fixer.dist.php
    @@ -7,7 +7,7 @@ use PhpCsFixer\Finder;
     $finder = Finder::create()
         ->files()
         ->in([
    -        __DIR__ . '/app/',
    +        __DIR__ . '/src/',
             __DIR__ . '/tests/',
         ])
         ->exclude([

That't it. Now you can run Coding Standards Fixer:

.. code-block:: console

    vendor/bin/php-cs-fixer fix --ansi --verbose --diff

If you add ``scripts.cs-fix`` in your **composer.json**, you can run it with
``composer cs-fix`` command::

    {
        // ...
        },
        "scripts": {
            "cs-fix": "php-cs-fixer fix --ansi --verbose --diff"
        }
    }

************
Config Files
************

Allowing Users to Override Settings
===================================

If your package has a configuration file and you want users to be able to override
the settings, use :php:func:`config()` with the short classname like ``config('YourConfig')``
to call the configuration file.

Users can then override the package configuration by placing a configuration class
with the same short classname in **app/Config** that extends the package Config
class like ``YourVendor\YourPackage\Config\YourConfig``.

Overriding Settings in app/Config
=================================

If you need to override or add to known configurations in the **app/Config** folder,
you can use :ref:`Implicit Registrars <registrars>`.

**********
References
**********

We have published some official packages. You can use these packages as references
when creating your own packages:

- https://github.com/codeigniter4/shield
- https://github.com/codeigniter4/settings
- https://github.com/codeigniter4/tasks
- https://github.com/codeigniter4/cache

