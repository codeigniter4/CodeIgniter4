#############################
Upgrading from 4.6.x to 4.7.0
#############################

Please refer to the upgrade instructions corresponding to your installation method.

- :ref:`Composer Installation App Starter Upgrading <app-starter-upgrading>`
- :ref:`Composer Installation Adding CodeIgniter4 to an Existing Project Upgrading <adding-codeigniter4-upgrading>`
- :ref:`Manual Installation Upgrading <installing-manual-upgrading>`

.. contents::
    :local:
    :depth: 2

****************
Breaking Changes
****************

PHP 8.2 Required
================

The minimum PHP requirement has been updated to **PHP 8.2**.

If your current runtime is older than PHP 8.2, upgrade PHP first before
upgrading CodeIgniter.

Validation ``regex_match`` Placeholders
=======================================

Placeholders in the ``regex_match`` validation rule must now use double curly
braces.

If you previously used single braces like
``regex_match[/^{placeholder}$/]``, update it to:
``regex_match[/^{{placeholder}}$/]``.

This avoids ambiguity with regex quantifiers such as ``{1,3}``.

Model Primary Key Validation Timing and Exceptions
==================================================

The ``insertBatch()`` and ``updateBatch()`` methods now honor model settings
like ``updateOnlyChanged`` and ``allowEmptyInserts``. This change ensures
consistent handling across all insert/update operations.

Primary key values are now validated before database queries in
``insert()``/``insertBatch()`` (without auto-increment), ``update()``, and
``delete()``.

Invalid primary key values now throw ``InvalidArgumentException`` instead of
database-layer ``DatabaseException``.

If your code catches ``DatabaseException`` for invalid primary keys, update it
to handle ``InvalidArgumentException`` as well.

Entity Change Detection Is Now Deep
===================================

``Entity::hasChanged()`` and ``Entity::syncOriginal()`` now perform deep
comparison for arrays and objects.

If you relied on the previous shallow (reference-based) behavior, review your
entity update flows and tests because nested changes are now detected.

Also, ``Entity::toRawArray()`` now recursively converts arrays of entities when
``$recursive`` is ``true``.

Encryption Handler Key State
============================

``OpenSSLHandler`` and ``SodiumHandler`` no longer mutate the handler's internal
key when a key is passed via ``$params`` to ``encrypt()``/``decrypt()``.

If your code depended on passing a key once and reusing it implicitly later,
move to explicit key configuration in ``Config\\Encryption`` (or pass a custom
config when creating the encrypter service).

Interface Changes
=================

Some interface changes have been made. Classes that implement framework
interfaces should update their APIs to reflect these changes.

See :ref:`ChangeLog <v470-interface-changes>` for details.

Method Signature Changes
========================

Some method signature changes have been made. Classes that extend framework
classes should update their method signatures to keep LSP compatibility.

See :ref:`ChangeLog <v470-method-signature-changes>` for details.

Property Signature Changes
==========================

Some property type signatures have changed (for example nullable
``Entity::$dataCaster``). If you extend these classes, update your code
accordingly.

See :ref:`ChangeLog <v470-property-signature-changes>` for details.

Removed Deprecated Items
========================

Some deprecated items have been removed. If your app still uses or extends
these APIs, update your code before upgrading.

See :ref:`ChangeLog <v470-removed-deprecated-items>` for details.

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

- app/Config/Migrations.php
    - ``Config\Migrations::$lock`` has been added, with a default value set to ``false``.

These files are new in this release:

- app/Config/Hostnames.php
- app/Config/WorkerMode.php

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

- app/Config/CURLRequest.php
- app/Config/Cache.php
- app/Config/ContentSecurityPolicy.php
- app/Config/Email.php
- app/Config/Encryption.php
- app/Config/Format.php
- app/Config/Hostnames.php
- app/Config/Images.php
- app/Config/Migrations.php
- app/Config/Optimize.php
- app/Config/Paths.php
- app/Config/Routing.php
- app/Config/Session.php
- app/Config/Toolbar.php
- app/Config/UserAgents.php
- app/Config/View.php
- app/Config/WorkerMode.php
- public/index.php
- spark
