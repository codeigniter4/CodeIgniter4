Composer Installation
#####################

.. contents::
    :local:
    :depth: 2

Composer can be used in several ways to install CodeIgniter4 on your system.

.. important:: CodeIgniter4 requires Composer 2.0.14 or later.

.. note:: If you are not familiar with Composer, we recommend you read
    `Basic usage <https://getcomposer.org/doc/01-basic-usage.md>`_ first.

The first technique describes creating a skeleton project
using CodeIgniter4, that you would then use as the base for a new webapp.
The second technique described below lets you add CodeIgniter4 to an existing
webapp,

.. note:: If you are using a Git repository to store your code, or for
   collaboration with others, then the **vendor** folder would normally
   be "git ignored". In such a case, you will need to do a ``composer update``
   when you clone the repository to a new system.

App Starter
===========

The `CodeIgniter 4 app starter <https://github.com/codeigniter4/appstarter>`_
repository holds a skeleton application, with a composer dependency on
the latest released version of the framework.

This installation technique would suit a developer who wishes to start
a new CodeIgniter4 based project.

Installation
------------

In the folder above your project root:

.. code-block:: console

    composer create-project codeigniter4/appstarter project-root

The command above will create a **project-root** folder.

If you omit the "project-root" argument, the command will create an
"appstarter" folder, which can be renamed as appropriate.

.. note:: Before v4.4.0, CodeIgniter autoloader did not allow special
    characters that are illegal in filenames on certain operating systems.
    The symbols that can be used are ``/``, ``_``, ``.``, ``:``, ``\`` and space.
    So if you installed CodeIgniter under the folder that contains the special
    characters like ``(``, ``)``, etc., CodeIgniter didn't work. Since v4.4.0,
    this restriction has been removed.

.. important:: When you deploy to your production server, don't forget to run the
    following command:

    .. code-block:: console

        composer install --no-dev

    The above command will remove the Composer packages only for development
    that are not needed in the production environment. This will greatly reduce
    the vendor folder size.

Initial Configuration
---------------------

After installation, a few initial configurations are required.
See :ref:`initial-configuration` for the details.

.. _app-starter-upgrading:

Upgrading
---------

Whenever there is a new release, then from the command line in your project root:

.. code-block:: console

    composer update

Read the :doc:`upgrade instructions <upgrading>`, and check Breaking Changes and Enhancements.

Pros
----

Simple installation; easy to update.

Cons
----

You still need to check for file changes in the **project space**
(root, app, public, writable) and merge them after updating.

.. note:: There are some third-party CodeIgniter modules available to assist
    with merging changes to the project space:
    `Explore on Packagist <https://packagist.org/explore/?query=codeigniter4%20updates>`_.

Structure
---------

Folders in your project after set up:

- app, public, tests, writable
- vendor/codeigniter4/framework/system

Latest Dev
----------

The App Starter repo comes with a ``builds`` scripts to switch Composer sources between the
current stable release and the latest development branch of the framework. Use this script
for a developer who is willing to live with the latest unreleased changes, which may be unstable.

The `development user guide <https://codeigniter4.github.io/CodeIgniter4/>`_ is accessible online.
Note that this differs from the released user guide, and will pertain to the
develop branch explicitly.

Update for Latest Dev
^^^^^^^^^^^^^^^^^^^^^

In your project root:

.. code-block:: console

    php builds development

The command above will update **composer.json** to point to the ``develop`` branch of the
working repository, and update the corresponding paths in config and XML files.

After using the ``builds`` command be sure to run ``composer update`` to sync your vendor
folder with the latest target build. Then, check the :doc:`upgrading` and update project
files if necessary.

Next Minor Version
^^^^^^^^^^^^^^^^^^

If you want to use the next minor version branch, after using the ``builds`` command
edit **composer.json** manually.

If you try the ``4.4`` branch, change the version to ``4.4.x-dev``::

    "require": {
        "php": "^7.4 || ^8.0",
        "codeigniter4/codeigniter4": "4.4.x-dev"
    },

And run ``composer update`` to sync your vendor
folder with the latest target build. Then, check the Upgrading Guide
(**user_guide_src/source/installation/upgrade_{version}.rst**) and
update project files if necessary.

Revert to Stable Release
^^^^^^^^^^^^^^^^^^^^^^^^

To revert the changes run:

.. code-block:: console

    php builds release

Adding CodeIgniter4 to an Existing Project
==========================================

The same `CodeIgniter 4 framework <https://github.com/codeigniter4/framework>`_
repository described in "Manual Installation" can also be added to an
existing project using Composer.

Installation
------------

Develop your app inside the ``app`` folder, and the ``public`` folder
will be your document root.

In your project root:

.. code-block:: console

    composer require codeigniter4/framework

.. important:: When you deploy to your production server, don't forget to run the
    following command:

.. code-block:: console

    composer install --no-dev

    The above command will remove the Composer packages only for development
    that are not needed in the production environment. This will greatly reduce
    the vendor folder size.

Setting Up
----------

    1. Copy the **app**, **public**, **tests** and **writable** folders from **vendor/codeigniter4/framework** to your project root
    2. Copy the **env**, **phpunit.xml.dist** and **spark** files, from **vendor/codeigniter4/framework** to your project root
    3. You will have to adjust the ``$systemDirectory`` property in **app/Config/Paths.php** to refer to the vendor one, e.g., ``__DIR__ . '/../../vendor/codeigniter4/framework/system'``.

Initial Configuration
---------------------

A few initial configurations are required.
See :ref:`initial-configuration` for the details.

.. _adding-codeigniter4-upgrading:

Upgrading
---------

Whenever there is a new release, then from the command line in your project root:

.. code-block:: console

    composer update

Read the :doc:`upgrade instructions <upgrading>`, and check Breaking Changes and Enhancements.

Pros
----

Relatively simple installation; easy to update.

Cons
----

You still need to check for file changes in the **project space**
(root, app, public, writable) after updating.

.. note:: There are some third-party CodeIgniter modules available to assist
    with merging changes to the project space:
    `Explore on Packagist <https://packagist.org/explore/?query=codeigniter4%20updates>`_.

Structure
---------

Folders in your project after set up:

- app, public, tests, writable
- vendor/codeigniter4/framework/system

Translations Installation
=========================

If you want to take advantage of the system message translations,
they can be added to your project in a similar fashion.

From the command line inside your project root:

.. code-block:: console

    composer require codeigniter4/translations

These will be updated along with the framework whenever you do a ``composer update``.
