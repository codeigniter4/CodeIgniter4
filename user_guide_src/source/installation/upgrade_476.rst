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

.. _upgrade-476-model-replace-allowed-fields:

Model::replace() Enforces Allowed Fields
========================================

For security and consistency with ``insert()`` and ``update()``, ``BaseModel::replace()`` now enforces mass assignment protection via ``$allowedFields``.

Previously, unlisted fields were passed directly to the database query without filtering. If your application intentionally passes fields not defined in ``$allowedFields`` to ``replace()``, either add those fields to ``$allowedFields`` or temporarily disable protection before calling ``replace()``:

.. code-block:: php

    $model->protect(false)->replace($data);

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
