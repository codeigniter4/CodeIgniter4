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

Command Alternatives Visibility
===============================

``CodeIgniter\CLI\Commands::getCommandAlternatives()`` is now ``public``. If you extend ``Commands`` and override this method,
change the override's visibility from ``protected`` to ``public``.

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

RedirectException Default Status Code
======================================

``CodeIgniter\HTTP\Exceptions\RedirectException`` no longer redeclares the inherited ``$code`` property to store the status applied to a
``Response`` whose status is outside the 301-308 range. If you have a subclass that overrode ``$code`` for this purpose, override the new
``$defaultStatusCode`` property instead:

.. code-block:: php

    // Before
    class MyRedirectException extends RedirectException
    {
        protected $code = 307;
    }

    // After
    class MyRedirectException extends RedirectException
    {
        protected int $defaultStatusCode = 307;
    }

RouteCollection Identifier Transformation
=========================================

``RouteCollection`` no longer applies ``esc(strip_tags())`` to namespace,
controller, and method values, including custom controllers passed to
``resource()`` and ``presenter()``. Applications relying on HTML tags being
stripped or special characters being HTML-encoded must preprocess these values
before passing them to the router. Values originating from untrusted sources
should be restricted to an explicit allowlist of permitted handlers.

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

``HandlerInterface::handle()`` now also returns ``int`` instead of ``bool``.
The return value tells the ``Logger`` whether to run the remaining handlers:

- ``HandlerInterface::RESULT_CONTINUE`` (``1``): the remaining handlers run.
- ``HandlerInterface::RESULT_STOP`` (``2``): the chain stops and the handlers
  that have not run yet are skipped.

Previously, returning ``false`` stopped the chain and ``true`` continued it. The
built-in handlers returned ``false`` when they failed to write, so a failing
``FileHandler`` prevented the handlers after it from logging. They now always
return ``RESULT_CONTINUE``.

If you have a custom log handler that overrides ``handle()``, you must update the
return type, and return the new constants:

.. code-block:: php

    // Before
    public function handle($level, $message, array $context = []): bool
    {
        // ...
        return true;  // continue with the next handler
        // return false;  // stop the chain
    }

    // After
    public function handle($level, $message, array $context = []): int
    {
        // ...
        return self::RESULT_CONTINUE;  // continue with the next handler
        // return self::RESULT_STOP;  // stop the chain
    }

.. note:: A ``handle()`` method that still declares a ``bool`` return type is
    incompatible with the interface and will cause a fatal error when the class
    is loaded.

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
- app/Config/Generators.php
    - ``Config\Generators::$views`` added entries for ``make:request``, ``make:test``, and ``make:transformer``, and dropped the stale ``session:migration`` entry.
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
