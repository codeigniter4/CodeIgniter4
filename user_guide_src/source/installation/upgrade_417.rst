#############################
Upgrading from 4.1.6 to 4.1.7
#############################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2

Breaking Changes
****************

- ``get_cookie()`` when ``$xssClean`` is true changed the output. Now it uses ``FILTER_SANITIZE_FULL_SPECIAL_CHARS``, not ``FILTER_SANITIZE_STRING``. Make sure the change is acceptable or not. Note that using XSS filtering is a bad practice. It does not prevent XSS attacks perfectly. Using ``esc()`` with the correct ``$context`` in the views is recommended.

Breaking Enhancements
*********************

none.

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

*

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

*
