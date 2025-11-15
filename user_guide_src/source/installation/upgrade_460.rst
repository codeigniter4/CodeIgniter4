#############################
Upgrading from 4.5.8 to 4.6.0
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

Exception Changes
=================

Some classes have changed the exception classes that are thrown. Some exception
classes have changed parent classes.
See :ref:`ChangeLog <v460-behavior-changes-exceptions>` for details.

If you have code that catches these exceptions, change the exception classes.

.. _upgrade-460-time-create-from-timestamp:

Time::createFromTimestamp() Timezone Change
===========================================

When you do not explicitly pass a timezone, now
:ref:`Time::createFromTimestamp() <time-createfromtimestamp>` returns a Time
instance with **UTC**. In v4.4.6 to prior to v4.6.0, a Time instance with the
currently set default timezone was returned.

This behavior change normalizes behavior with changes in PHP 8.4 which adds a
new ``DateTimeInterface::createFromTimestamp()`` method.

If you want to keep the default timezone, you need to pass the timezone as the
second parameter::

    use CodeIgniter\I18n\Time;

    $time = Time::createFromTimestamp(1501821586, date_default_timezone_get());

.. _upgrade-460-time-keeps-microseconds:

Time keeps Microseconds
=======================

In previous versions, :doc:`Time <../libraries/time>` lost microseconds in some
cases. But the bugs have been fixed.

The results of the ``Time`` comparison may differ due to these fixes:

.. literalinclude:: upgrade_460/006.php
   :lines: 2-

In a such case, you need to remove the microseconds:

.. literalinclude:: upgrade_460/007.php
   :lines: 2-

The following cases now keeps microseconds:

.. literalinclude:: upgrade_460/002.php
   :lines: 2-

.. literalinclude:: upgrade_460/003.php
   :lines: 2-

Note that ``Time`` with the current time has been holding microseconds since before.

.. literalinclude:: upgrade_460/004.php
   :lines: 2-

Also, methods that returns an ``int`` still lose the microseconds.

.. literalinclude:: upgrade_460/005.php
   :lines: 2-

.. _upgrade-460-time-set-timestamp:

Time::setTimestamp() Behavior Fix
=================================

In previous versions, if you call ``Time::setTimestamp()`` on a Time instance with
a timezone other than the default timezone might return a Time instance with the
wrong date/time.

This bug has been fixed, and it now behaves in the same way as ``DateTimeImmutable``:

.. literalinclude:: upgrade_460/008.php
   :lines: 2-

Note that if you use the default timezone, the behavior is not changed:

.. literalinclude:: upgrade_460/009.php
   :lines: 2-

.. _upgrade-460-registrars-with-dirty-hack:

Registrars with Dirty Hack
==========================

To prevent Auto-Discovery of :ref:`registrars` from running twice, when a registrar
class is loaded or instantiated, if it instantiates a Config class (which extends
``CodeIgniter\Config\BaseConfig``), ``ConfigException`` will be raised.

This is because if Auto-Discovery of Registrars is performed twice, duplicate
values may be added to properties of Config classes.

All registrar classes (**Config/Registrar.php** in all namespaces) must be modified
so that they do not instantiate any Config class when loaded or instantiated.

If the packages/modules you are using provide such registrar classes, the registrar
classes in the packages/modules need to be fixed.

The following is an example of code that will no longer work:

.. literalinclude:: upgrade_460/001.php

.. _upgrade-460-sid-change:

Session ID (SID) Change
=======================

Now :doc:`../libraries/sessions` forces to use the PHP default 32 character SIDs,
with 4 bits of entropy per character. This change is to match the behavior of
PHP 9.

In other words, the following settings are always used:

.. code-block:: ini

    session.sid_bits_per_character = 4
    session.sid_length = 32

In previous versions, the PHP ini settings was respected. So this change may
change your SID length.

If you cannot accept this change, customize the Session library.

Interface Changes
=================

Some interface changes have been made. Classes that implement them should update
their APIs to reflect the changes. See :ref:`ChangeLog <v460-interface-changes>`
for details.

Method Signature Changes
========================

Some method signature changes have been made. Classes that extend them should
update their APIs to reflect the changes. See :ref:`ChangeLog <v460-method-signature-changes>`
for details.

Removed Deprecated Items
========================

Some deprecated items have been removed. If you are still using these items, or
extending these classes, upgrade your code.
See :ref:`ChangeLog <v460-removed-deprecated-items>` for details.

*********************
Breaking Enhancements
*********************

.. _upgrade-460-filters-changes:

Filters Changes
===============

The ``Filters`` class has been changed to allow multiple runs of the same filter
with different arguments in before or after.

If you are extending ``Filters``, you will need to modify it to conform to the
following changes:

- The structure of the array properties ``$filters`` and ``$filtersClasses`` have
  been changed.
- The properties ``$arguments`` and ``$argumentsClass`` are no longer used.
- ``Filters`` has been changed so that the same filter class is not instantiated
  multiple times. If a filter class is used both before and after, the same instance
  is used.

*************
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

Config
------

- app/Config/Feature.php
    - ``Config\Feature::$autoRoutesImproved`` has been changed to ``true``.
    - ``Config\Feature::$strictLocaleNegotiation`` has been added.
- app/Config/Routing.php
    - ``Config\Routing::$translateUriToCamelCase`` has been changed to ``true``.
- app/Config/Kint.php
    - ``Config\Kint::$richSort`` has been removed. Kint in v6 no longer uses ``AbstractRenderer::SORT_FULL``. Leaving this property in your code will cause a runtime error due to the undefined constant.

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

- app/Config/Cache.php
- app/Config/Constants.php
- app/Config/Database.php
- app/Config/Feature.php
- app/Config/Format.php
- app/Config/Kint.php
- app/Config/Routing.php
- app/Config/Security.php
- app/Views/errors/html/debug.css
- app/Views/errors/html/error_400.php
- preload.php
- public/index.php
- spark
