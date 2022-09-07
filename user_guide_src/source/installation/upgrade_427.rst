#############################
Upgrading from 4.2.6 to 4.2.7
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

-  ``Time::__toString()`` is now locale-independent. It returns database-compatible strings like '2022-09-07 12:00:00' in any locale. Most locales are not affected by this change. But in a few locales like `ar`, `fa`, ``Time::__toString()`` (or ``(string) $time`` or implicit casting to a string) no longer returns a localized datetime string. if you want to get a localized datetime string, use :ref:`Time::toDateTimeString() <time-todatetimestring>` instead.

Project Files
*************

A few files in the **project space** (root, app, public, writable) received cosmetic updates.
You need not touch these files at all. There are some third-party CodeIgniter modules available
to assist with merging changes to the project space: `Explore on Packagist <https://packagist.org/explore/?query=codeigniter4%20updates>`_.

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

*
