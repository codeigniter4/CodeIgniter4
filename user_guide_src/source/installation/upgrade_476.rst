#############################
Upgrading from 4.7.5 to 4.7.6
#############################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2

**********************
Mandatory File Changes
**********************

****************
Breaking Changes
****************

.. _upgrade-476-cache-delete-matching:

File Cache deleteMatching() No Longer Matches Subdirectories
============================================================

For security reasons, ``FileHandler::deleteMatching()`` now matches the pattern against
the names of files in the cache directory only. See the
`Security advisory GHSA-9hf2-g3w9-pfmp <https://github.com/codeigniter4/CodeIgniter4/security/advisories/GHSA-9hf2-g3w9-pfmp>`_
for more information.

Previously, the pattern was appended to the cache path and passed to ``glob()``, so a
pattern containing a directory separator, such as ``dir/key_*``, also deleted matching
files in a subdirectory of the cache directory. Such a pattern now never matches
anything and the method returns ``0``.

Most applications are not affected. The File handler stores every cache item directly
in the cache directory, and the default ``Config\Cache::$reservedCharacters`` forbid
``/`` and ``\`` in cache keys, so cache items could not be stored in subdirectories.
If your application places its own files in subdirectories of the cache directory and
removed them with ``deleteMatching()``, delete them directly instead, for example with
the :php:func:`delete_files()` helper function.

*********************
Breaking Enhancements
*********************

*************
Project Files
*************

Some files in the **project space** (root, app, public, writable) received updates. Due to
these files being outside of the **system** scope they will not be changed without your intervention.

.. note:: There are some third-party CodeIgniter modules available to assist
    with merging changes to the project space:
    `Explore on Packagist <https://packagist.org/explore/?query=codeigniter4%20updates>`_.

Content Changes
===============

The following files received significant changes (including deprecations or visual adjustments)
and it is recommended that you merge the updated versions with your application:

Config
------

- @TODO

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

- @TODO
