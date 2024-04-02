#############################
Upgrading from 4.4.6 to 4.4.7
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

URI Security
============

The feature to check if URIs do not contain not permitted strings has been added.
This check is equivalent to the URI Security found in CodeIgniter 3.

We recommend you enable this feature. Add the following to **app/Config/App.php**::

        public string $permittedURIChars = 'a-z 0-9~%.:_\-';.

See :ref:`urls-uri-security` for details.

Error Files
===========

The error page has been updated. Please update the following files:

- app/Views/errors/html/debug.css
- app/Views/errors/html/error_exception.php

****************
Breaking Changes
****************

.. _upgrade-447-filter-paths:

Paths in Controller Filters
===========================

A bug where URI paths processed by :doc:`../incoming/filters` were not URL-decoded has been fixed.

.. note:: Note that :doc:`Router <../incoming/routing>` processes URL-decoded URI paths.

``Config\Filters`` has some places to specify the URI paths. If the paths have
different values when URL-decoded, change them to the URL-decoded values.

E.g.,:

.. code-block:: php

    public array $globals = [
        'before' => [
            'csrf' => ['except' => '%E6%97%A5%E6%9C%AC%E8%AA%9E/*'],
        ],
        // ...
    ];

↓

.. code-block:: php

    public array $globals = [
        'before' => [
            'csrf' => ['except' => '日本語/*'],
        ],
        // ...
    ];

Time::difference() and DST
==========================

In previous versions, when comparing dates with ``Time::difference()``, unexpected
results were returned if the date included a day different from 24 hours due to
Daylight Saving Time (DST). See :ref:`Note in Times and Dates <time-viewing-differences>`
for details.

This bug has been fixed, so date comparisons will now be shifted by one day in
such cases.

In the unlikely event that you wish to maintain the behavior of the previous
versions, change the time zone of both dates being compared to UTC before passing
them to ``Time::difference()``.

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

- app/Config/App.php
    - The property ``$permittedURIChars`` was added. See :ref:`urls-uri-security`
      for details.

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

- app/Config/App.php
- app/Config/Cache.php
- app/Config/ContentSecurityPolicy.php
- app/Config/Database.php
- app/Config/Exceptions.php
- app/Config/Filters.php
- app/Config/Format.php
- app/Config/Logger.php
- app/Config/Mimes.php
- app/Config/Routing.php
- app/Config/Toolbar.php
- app/Config/Validation.php
- app/Config/View.php
- app/Controllers/BaseController.php
- app/Views/errors/html/debug.css
- app/Views/errors/html/error_exception.php
- composer.json
