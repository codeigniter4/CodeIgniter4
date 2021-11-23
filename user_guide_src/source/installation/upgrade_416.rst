#############################
Upgrading from 4.1.5 to 4.1.6
#############################

.. contents::
    :local:
    :depth: 1

Breaking Changes
================

Validation change
-----------------

For bug fixes, Validation and its Rule classes now have ``declare(strict_types=1)`` for strict typing.
And we removed all implicit type casting and the types for the parameters to validate in Validation.

This change may alter the result of validation. For example, when you validated the bool ``true``,
it was converted to string ``'1'`` in the previous versions.
If you validated it with the ``integer`` rule, ``'1'`` passed the validation.

Another breaking change is that if you have custom validation rules, and when you specify the data type to validate,
if incompatible typed data is passed to the validation rule, it will raise Type Error.

If you have extended the CI4's validation rules, you will need to modify the type of data passed to it.

Breaking Enhancements
=====================

Project Files
=============

Numerous files in the project space (root, app, public, writable) received updates. Due to
these files being outside of the system scope they will not be changed without your intervention.
There are some third-party CodeIgniter modules available to assist with merging changes to
the project space: `Explore on Packagist <https://packagist.org/explore/?query=codeigniter4%20updates>`_.

.. note:: Except in very rare cases for bug fixes, no changes made to files for the project space
    will break your application. All changes noted here are optional until the next major version,
    and any mandatory changes will be covered in the sections above.

Content Changes
---------------

The following files received significant changes (including deprecations or visual adjustments)
and it is recommended that you merge the updated versions with your application:

*

All Changes
-----------

This is a list of all files in the project space that received changes;
many will be simple comments or formatting that have no effect on the runtime:

*
