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

.. _upgrade-480-query-builder-bindings:

Query Builder Bindings
======================

``getCompiledSelect()``, ``getCompiledInsert()``, ``getCompiledUpdate()``,
``getCompiledDelete()``, ``delete()`` and ``countAllResults()`` now clear their
bindings and binding counters after compilation or execution when their reset
parameter is enabled (the default). This prevents accumulation when a builder
is reused, but ``getBinds()`` now returns an empty array after these calls.
Bindings are also cleared when the database query returns ``false``.

The :ref:`getCompiledSelect() documentation <query-builder-get-compiled-select>`
already states that the default reset should work just like ``get()``.
Clearing the bindings corrects that inconsistency while changing the observable
result of ``getBinds()``.

To retain the query and its bindings, pass ``false`` as the first argument to
any of the compiled methods or ``countAllResults()``, or as the third argument
to ``delete()``: ``$builder->delete('', null, false)``. This also retains query
clauses such as WHERE and LIMIT.

If you only need a snapshot of the bindings, read them before compilation or
execution instead. When passing conditions directly to ``delete()``, move them
into ``where()`` first so their bindings are available before execution.

Test mode retains bindings needed by the placeholders in its returned raw SQL.
In particular, ``getCompiledDelete()`` needs these bindings to finish compilation
and clears them afterwards according to its own reset parameter.

Query Builder JOIN Overrides
============================

``BaseBuilder::join()`` and ``BaseBuilder::compileJoinTable()`` now accept
``RawSql|string`` for the table argument. If a custom Query Builder subclass
overrides either method, change its ``$table`` parameter type from ``string``
to ``RawSql|string``. The method body must also handle ``RawSql`` without
escaping or prefixing it.

Console Exit Codes
==================

Previously, returning a non-integer value from a command run through ``spark`` would be treated as a successful execution (exit code ``0``).
Starting with v4.8.0, this behavior is still supported but will trigger a deprecation notice. Commands should now return an integer exit code
to ensure proper behavior across all platforms.

Command Alternatives Visibility
===============================

``CodeIgniter\CLI\Commands::getCommandAlternatives()`` is now ``public``. If you extend ``Commands`` and override this method,
change the override's visibility from ``protected`` to ``public``.

.. _upgrade-480-cli-error-output:

CLI Error Output
================

Uncaught exceptions in ``spark``, and errors shown with ``BaseCommand::showError()`` or
``AbstractCommand::renderThrowable()``, are now printed to STDERR instead of STDOUT, so they no
longer mix with output read by another program.
The exit code is unchanged.

If you redirect only STDOUT to a log file (``php spark my:command > my.log``), errors no longer
reach that file. Redirect STDERR as well to keep them (``php spark my:command > my.log 2>&1``).

A custom CLI error view that writes with ``echo`` instead of the ``CLI`` class still prints to
STDOUT.

.. _upgrade-480-cli-output-classes:

CLI Output Classes
==================

The static ``CLI`` class now delegates its output methods to instance classes:
``Output``, ``Terminal``, ``ProgressBar``, ``Table``, and ``Color``. Calling the static
methods keeps working, and the output is byte-identical, except that ``CLI::beep()`` now
goes through the ``InputOutput`` transport and that ``CLI::print()`` of text ending in
a line feed, or a progress bar drawn with ``CLI::showProgress()``, no longer adds a blank
line before the next ``CLI::write()``.

If you extend ``CLI`` and override ``color()``, or read the ``$lastWrite``, ``$isColored``,
``$io``, ``$height``, ``$width``, ``$foreground_colors``, or ``$background_colors``
properties, those overrides and reads no longer take effect. The properties are still
declared but unused. An override of ``write()`` is still reached by ``newLine()``,
``table()``, and ``clearScreen()``, and an override of ``fwrite()`` by ``wait()`` and
``clearScreen()``, but no longer by ``print()``, ``error()``, ``beep()``, or
``showProgress()``. Replace a transport override with ``CLI::setInputOutput()``, and a
colour or terminal override with an ``Output`` or ``Terminal`` subclass passed to
``CLI::setOutput()``. Overriding ``Output::color()`` covers ``print()``, ``write()``, and
``error()`` as well, since they colour through it.

Tests that reached those properties through reflection should use the instances
returned by ``CLI::getOutput()`` and ``CLI::getTerminal()`` instead.

.. _upgrade-480-cli-prompt:

CLI Prompt
==========

``CLI::prompt()``, ``CLI::promptByKey()``, and ``CLI::promptByMultipleKeys()`` now delegate to
the ``Prompt`` class, and ask and answer exactly as before.

If you extend ``CLI`` and override ``validate()``, the prompts no longer call it. Extend ``Prompt``
and override its ``validate()`` instead, then return your subclass from ``getPrompt()``: the static
``CLI::getPrompt()`` in your ``CLI`` subclass, or ``AbstractCommand::getPrompt()`` in a modern command.
To change only the validation, pass a validation instance of your own to the ``Prompt`` constructor.
An override of ``CLI::prompt()`` is still reached by calls to ``CLI::prompt()``, but no longer by
``promptByKey()`` or ``promptByMultipleKeys()``.

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

.. _upgrade-480-config-env-lookups:

Config Environment Lookups
==========================

``BaseConfig`` now reads the environment once and reuses the result, instead of
looking up every property of every config class. The matching rules and their
order are unchanged. One edge case behaves differently: a ``putenv()`` call
that does not also write ``$_ENV`` or ``$_SERVER``, made after the first config
class was built, may not be seen by config classes built later until
``BaseConfig::reset()`` runs. This applies whether the variable is added,
changed, or removed. In Worker Mode, this cache persists across requests, so
such changes may not be seen in later requests either. To make the change
visible, also set the value in ``$_ENV`` (or unset it there), or call
``BaseConfig::reset()``. Values loaded from the **.env** file are not
affected, since they are written to ``$_ENV`` and ``$_SERVER`` as well.

Config classes that override ``getEnvValue()`` or ``initEnvValue()`` keep the
previous behavior.

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
- app/Config/Logger.php
    - ``Config\Logger::$handlers`` added a new key ``stopChainOnFailure`` to the ``FileHandler`` and ``ErrorlogHandler`` settings.
- app/Config/Mimes.php
    - ``Config\Mimes::$mimes`` added a new key ``md`` for Markdown files.
- app/Config/Routing.php
    - ``Config\Routing::$placeholderSamples`` was added to provide sample values for custom route placeholders so the ``spark routes`` command can resolve their filters.
- app/Config/Security.php
    - ``Config\Security::$csrfFetchMetadata`` and ``Config\Security::$csrfFetchMetadataRejectSameSite`` were added for Fetch Metadata based CSRF protection.

Error Views
-----------

- app/Views/errors/cli/error_exception.php
    - Shows the backtrace also when the command runs with ``-v``.
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
