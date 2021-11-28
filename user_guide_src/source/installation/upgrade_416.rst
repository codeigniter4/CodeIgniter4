#############################
Upgrading from 4.1.5 to 4.1.6
#############################

.. contents::
    :local:
    :depth: 2

Breaking Changes
****************

Breaking Enhancements
*********************

.. _strict-validation:

Strict Validation
=================

A new Validation class for strict validation.

.. important:: This feature is disabled by default. Because it breaks backward compatibility.

Traditional Validation
----------------------

The traditional Validation class implicitly assumes that string values are validated.
It works for most basic cases like validating POST data.

But, for example, if you use JSON input data, it may be type of bool/null/array.
When you validate the bool ``true``, it is converted to string ``'1'`` in the traditional Validation.
And if you validate it with the ``integer`` rule, ``'1'`` passes the validation.

Strict Validation
-----------------

The new Validation and its Rule classes now have ``declare(strict_types=1)`` for strict typing.
And we removed all implicit type casting and the types for the parameters to validate.

The next change is that the new Validation separates the validation process of multiple field and single field.
When a single field is an array data, the traditional Validation validates each element of the array.
The validation rule gets an element of the array as the parameter.
On the other hand, the new Validation passes the array to the validation rule as a whole.

Another breaking change is that if you have custom validation rules, and when you specify the data type to validate,
if incompatible typed data is passed to the validation rule, it will raise Type Error.

Using Strict Validation
------------------------

If you want to use this, you need to set the property ``$strictValidation`` ``true`` in **app/Config/Feature.php**.
And you need to change the rule classes in **app/Config/Validation.php**::

        public $ruleSets = [
            \CodeIgniter\ValidationStrict\CreditCardRules::class,
            \CodeIgniter\ValidationStrict\FileRules::class,
            \CodeIgniter\ValidationStrict\FormatRules::class,
            \CodeIgniter\ValidationStrict\Rules::class,
        ];

If you enable it, ``Config\Services::validation()`` will return a ``ValidationStrict\Validator`` object.

Project Files
*************

Numerous files in the project space (root, app, public, writable) received updates. Due to
these files being outside of the system scope they will not be changed without your intervention.
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

This is a list of all files in the project space that received changes;
many will be simple comments or formatting that have no effect on the runtime:

*
