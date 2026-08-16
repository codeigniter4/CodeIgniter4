#############################
Upgrading from 4.7.x to 4.8.0
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

Console Exit Codes
==================

Previously, returning a non-integer value from a command run through ``spark`` would be treated as a successful execution (exit code ``0``).
Starting with v4.8.0, this behavior is still supported but will trigger a deprecation notice. Commands should now return an integer exit code
to ensure proper behavior across all platforms.

Uploaded File Move Return Type
==============================

``CodeIgniter\HTTP\Files\UploadedFileInterface::move()`` now returns ``static``
instead of ``bool``, matching ``CodeIgniter\Files\File::move()`` which
``UploadedFile`` extends.

If you have a custom implementation of ``UploadedFileInterface``, or a class
extending ``UploadedFile`` that overrides ``move()``, return the instance
instead of ``true``:

.. code-block:: php

    // Before
    public function move(string $targetPath, ?string $name = null, bool $overwrite = false)
    {
        // ...

        return true;
    }

    // After
    public function move(string $targetPath, ?string $name = null, bool $overwrite = false)
    {
        // ...

        return $this;
    }

Calling code that only tests the result, such as ``if ($file->move($path))``,
needs no change because the returned instance is truthy. Code comparing the
result strictly against ``true`` must be updated.

Outgoing Request Constructor
============================

``CodeIgniter\HTTP\OutgoingRequest::__construct()`` now requires the ``$uri``
parameter, which was previously ``?URI $uri = null``. Consequently
``OutgoingRequest::getUri()`` now returns ``URI`` instead of ``URI|null``.

Passing ``null`` only worked when a ``Host`` header was supplied in the same
call, because the constructor's host check short-circuits before dereferencing
the URI. Such calls must now pass a ``URI``:

.. code-block:: php

    // Before
    $request = new OutgoingRequest('GET', null, ['Host' => 'example.com']);

    // After
    $request = new OutgoingRequest('GET', new URI('http://example.com'), ['Host' => 'example.com']);

Any other call that omitted ``$uri`` or passed ``null`` already failed with
``Call to a member function getHost() on null``, so it needs no migration.

*********************
Breaking Enhancements
*********************

Log Handler Interface
=====================

``CodeIgniter\Log\Handlers\HandlerInterface::handle()`` now accepts a third
parameter ``array $context = []``.

If you have a custom log handler that overrides the ``handle()`` method
(whether implementing ``HandlerInterface`` directly or extending a built-in
handler class), you must update your ``handle()`` method signature:

.. code-block:: php

    // Before
    public function handle($level, $message): bool

    // After
    public function handle($level, $message, array $context = []): bool

The context array may contain the CI global context data under the
``HandlerInterface::GLOBAL_CONTEXT_KEY`` (``'_ci_context'``) key when
``$logGlobalContext`` is enabled in ``Config\Logger``.

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

- app/Config/Filters.php
    - Added a new filter named ``requestid`` that adds a unique request ID to each request in the application's context.
- app/Config/Mimes.php
    - ``Config\Mimes::$mimes`` added a new key ``md`` for Markdown files.
- app/Config/Routing.php
    - ``Config\Routing::$placeholderSamples`` was added to provide sample values for custom route placeholders so the ``spark routes`` command can resolve their filters.
- app/Config/Security.php
    - ``Config\Security::$csrfFetchMetadata`` and ``Config\Security::$csrfFetchMetadataRejectSameSite`` were added for Fetch Metadata based CSRF protection.

Error Views
-----------

- app/Views/errors/html/debug.css
    - Added styles for the **Copy Details** button.
- app/Views/errors/html/debug.js
    - Added clipboard handling for the **Copy Details** button.
- app/Views/errors/html/error_exception.php
    - Added a **Copy Details** button to detailed HTML exception pages.
- app/Views/errors/html/error_report.php
    - Added a Markdown error report partial used by the **Copy Details** button.

All Changes
===========

This is a list of all files in the **project space** that received changes;
many will be simple comments or formatting that have no effect on the runtime:

- @TODO
