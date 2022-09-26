Composer Installation
#####################

.. contents::
    :local:
    :depth: 2

Composer can be used in several ways to install CodeIgniter4 on your system.

The first technique describes creating a skeleton project
using CodeIgniter4, that you would then use as the base for a new webapp.
The second technique described below lets you add CodeIgniter4 to an existing
webapp,

.. note:: If you are using a Git repository to store your code, or for
   collaboration with others, then the ``vendor`` folder would normally
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

In the folder above your project root::

    > composer create-project codeigniter4/appstarter project-root

The command above will create a "project-root" folder.

If you omit the "project-root" argument, the command will create an
"appstarter" folder, which can be renamed as appropriate.

.. note:: CodeIgniter autoloader does not allow special characters that are illegal in filenames on certain operating systems.
    The symbols that can be used are ``/``, ``_``, ``.``, ``:``, ``\`` and space.
    So if you install CodeIgniter under the folder that contains the special characters like ``(``, ``)``, etc., CodeIgniter won't work.

If you don't need or want phpunit installed, and all of its composer
dependencies, then add the ``--no-dev`` option to the end of the above
command line. That will result in only the framework, and the three
trusted dependencies that we bundle, being composer-installed.

A sample such installation command, using the default project-root "appstarter"::

    > composer create-project codeigniter4/appstarter --no-dev

Initial Configuration
---------------------

After installation, a few initial configurations are required.
See :ref:`initial-configuration` for the details.

.. _app-starter-upgrading:

Upgrading
---------

Whenever there is a new release, then from the command line in your project root::

    > composer update

Read the :doc:`upgrade instructions <upgrading>`, and check Breaking Changes and Enhancements.

Pros
----

Simple installation; easy to update

Cons
----

You still need to check for ``app/Config`` changes after updating

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

In your project root::

    > php builds development

The command above will update **composer.json** to point to the ``develop`` branch of the
working repository, and update the corresponding paths in config and XML files. To revert
these changes run::

    > php builds release

After using the ``builds`` command be sure to run ``composer update`` to sync your vendor
folder with the latest target build.

Adding CodeIgniter4 to an Existing Project
==========================================

The same `CodeIgniter 4 framework <https://github.com/codeigniter4/framework>`_
repository described in "Manual Installation" can also be added to an
existing project using Composer.

Installation
------------

Develop your app inside the ``app`` folder, and the ``public`` folder
will be your document root.

In your project root::

    > composer require codeigniter4/framework

Setting Up
----------

    1. Copy the ``app``, ``public``, ``tests`` and ``writable`` folders from ``vendor/codeigniter4/framework`` to your project root
    2. Copy the ``env``, ``phpunit.xml.dist`` and ``spark`` files, from ``vendor/codeigniter4/framework`` to your project root
    3. You will have to adjust the ``$systemDirectory`` property in **app/Config/Paths.php** to refer to the vendor one, e.g., ``ROOTPATH . '/vendor/codeigniter4/framework/system'``.

Initial Configuration
---------------------

A few initial configurations are required.
See :ref:`initial-configuration` for the details.

.. _adding-codeigniter4-upgrading:

Upgrading
---------

Whenever there is a new release, then from the command line in your project root::

    > composer update

Read the :doc:`upgrade instructions <upgrading>`, and check Breaking Changes and Enhancements.

Pros
----

Relatively simple installation; easy to update

Cons
----

You still need to check for ``app/Config`` changes after updating

Structure
---------

Folders in your project after set up:

- app, public, tests, writable
- vendor/codeigniter4/framework/system

Translations Installation
=========================

If you want to take advantage of the system message translations,
they can be added to your project in a similar fashion.

From the command line inside your project root::

    > composer require codeigniter4/translations

These will be updated along with the framework whenever you do a ``composer update``.
