##############################
Upgrading from 4.3.1 to 4.3.2
##############################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2

Breaking Changes
****************

base_url()
==========

The :php:func:`base_url()` behavior has been fixed. In previous versions, when you
call ``base_url()`` **without argument**, it returned baseURL without a trailing
slash (``/``). Now it returns baseURL with a trailing slash. For example:

- before: ``http://example.com``
- after: ``http://example.com/``

If you have code to call ``base_url()`` without argument, you may need to adjust the URLs.

.. _upgrade-432-uri-string:

uri_string()
============

The :php:func:`uri_string()` behavior has been fixed. In previous versions, when you
navigate to the baseURL, it returned ``/``. Now it returns an empty string (``''``).

If you have code to call ``uri_string()``, you may need to adjust it.

.. note:: The :php:func:`uri_string()` returns a URI path relative to baseURL.
    It is not a full URI path if the baseURL contains subfolders.
    If you use it for HTML links, it is better to use it with :php:func:`site_url()`
    like ``site_url(uri_string())``.

Mandatory File Changes
**********************

composer.json
=============

If you have installed CodeIgnter manually and are using or planning to use Composer,
remove the following line:

.. code-block:: text

    {
        ...
        "scripts": {
            "post-update-cmd": [
                "CodeIgniter\\ComposerScripts::postUpdate"  <-- Remove this line
            ],
            "test": "phpunit"
        },
        ...
    }

Project Files
*************

Some files in the **project space** (root, app, public, writable) received updates. Due to
these files being outside of the **system** scope they will not be changed without your intervention.

There are some third-party CodeIgniter modules available to assist with merging changes to
the project space: `Explore on Packagist <https://packagist.org/explore/?query=codeigniter4%20updates>`_.

Content Changes
===============

The following files received significant changes (including deprecations or visual adjustments)
and it is recommended that you merge the updated versions with your application:

- app/Config/Mimes.php
- app/Views/errors/html/error_exception.php
- composer.json
- public/.htaccess

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

- app/Config/App.php
- app/Config/Mimes.php
- app/Views/errors/html/error_exception.php
- composer.json
- public/.htaccess
