#############################
Upgrading from 4.1.5 to 4.1.6
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

Validation Result Changes
=========================

Due to a bug fix, the Validation now might change the validation results when you validate an array item (see :ref:`Changelog <changelog-v416-validation-changes>`). So check the validation results for all the code that validates the array. Validating multiple fields like ``contacts.*.name`` is not affected.

If you have the following form::

    <input type='text' name='invoice_rule[1]'>
    <input type='text' name='invoice_rule[2]'>

And you have the validation rule like this::

    'invoice_rule' =>  ['rules' => 'numeric', 'errors' => ['numeric' => 'Not numeric']]

Change the rule key to ``invoice_rule.*`` and the validation will work.

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

* ``app/Config/Filters.php``
* ``app/Config/Mimes.php``
* ``app/Config/Security.php``
* ``app/Config/Toolbar.php``

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

* ``app/Config/Filters.php``
* ``app/Config/Mimes.php``
* ``app/Config/Security.php``
* ``app/Config/Toolbar.php``
* ``app/Views/errors/html/error_exception.php``
