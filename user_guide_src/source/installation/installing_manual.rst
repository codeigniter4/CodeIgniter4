Manual Installation
###################

.. contents::
    :local:
    :depth: 2

The `CodeIgniter 4 framework <https://github.com/codeigniter4/framework>`_
repository holds the released versions of the framework.
It is intended for developers who do not wish to use Composer.

Develop your app inside the **app** folder, and the **public** folder
will be your public-facing document root. Do not change anything inside the **system**
folder!

.. note:: This is the installation technique closest to that described
   for `CodeIgniter 3 <https://codeigniter.com/userguide3/installation/index.html>`_.

Installation
============

Download the `latest version <https://github.com/CodeIgniter4/framework/releases/latest>`_,
and extract it to become your project root.

.. note:: Before v4.4.0, CodeIgniter autoloader did not allow special
    characters that are illegal in filenames on certain operating systems.
    The symbols that can be used are ``/``, ``_``, ``.``, ``:``, ``\`` and space.
    So if you installed CodeIgniter under the folder that contains the special
    characters like ``(``, ``)``, etc., CodeIgniter didn't work. Since v4.4.0,
    this restriction has been removed.

Initial Configuration
=====================

After installation, a few initial configurations are required.
See :ref:`initial-configuration` for the detail.

.. _installing-manual-upgrading:

Upgrading
=========

Download a new copy of the framework, and then replace the **system** folder.

Read the :doc:`upgrade instructions <upgrading>`, and check Breaking Changes and Enhancements.

Pros
====

Download and run.

Cons
====

You need to check for file changes in the **project space**
(root, app, public, tests, writable) and merge them by yourself.

Structure
=========

Folders in your project after set up:

- app, public, tests, writable, system

Translations Installation
=========================

If you want to take advantage of the system message translations,
they can be added to your project in a similar fashion.

Download the `latest version of them <https://github.com/codeigniter4/translations/releases/latest>`_.
Extract the downloaded zip, and copy the **Language** folder contents in it
to your **app/Languages** folder.

This would need to be repeated to incorporate any updates
to the translations.
