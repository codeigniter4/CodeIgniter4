#########################
Modern Generator Commands
#########################

.. versionadded:: 4.8.0

Generator commands are spark commands that create files from templates. The
built-in ``make:*`` commands documented in :doc:`cli_generators` are the
canonical examples. The modern counterpart of the legacy ``GeneratorTrait`` is
the ``AbstractGeneratorCommand`` base class paired with a ``#[GeneratorCommand]``
attribute: the attribute describes *what* to generate, and the base class carries
the whole generation pipeline so that a trivial generator needs no method body
at all.

.. note::

    ``GeneratorTrait`` continues to work for legacy ``BaseCommand`` generators.

.. contents::
    :local:
    :depth: 2

****************************
Creating a Generator Command
****************************

A modern generator command is a :doc:`modern spark command <cli_modern_commands>` that:

- extends ``CodeIgniter\CLI\AbstractGeneratorCommand`` (which itself extends ``AbstractCommand``);
- declares the usual ``#[Command]`` attribute **and** a ``#[GeneratorCommand]`` attribute.

A minimal generator needs nothing else:

.. literalinclude:: cli_modern_generators/001.php

Out of the box this command:

- declares a required ``name`` argument, and prompts for it when omitted on an
  interactive run (see :ref:`prompts-for-missing-input`);
- declares the ``--namespace`` / ``-n``, ``--suffix`` / ``-s``, and
  ``--force`` / ``-f`` options on top of the framework defaults;
- ships a default ``execute()`` that renders the template and writes the class
  file, returning ``EXIT_SUCCESS`` or ``EXIT_ERROR``.

The ``#[GeneratorCommand]`` Attribute
=====================================

The attribute holds the generation configuration:

- ``component`` is the component noun (``Controller``, ``Model``, ...). It drives suffix handling:
  an input whose trailing component is spelled with the wrong case is normalized
  (``make:widget foowidget`` generates ``FooWidget``), and passing ``--suffix`` appends the
  component to inputs that do not already end with it.
- ``template`` is the basename of the fallback view under ``CodeIgniter\Commands\Generators\Views``
  (see :ref:`generator-command-templates` for the lookup order).
- ``directory`` is the optional sub-namespace (and thus subdirectory) the class is generated into,
  e.g. ``Widgets`` places classes under ``App\Widgets``.
- ``classNameLang`` is the language string key used as the prompt label when the ``name``
  argument must be asked for interactively. Defaults to the generic
  ``CLI.generator.className.default`` label.
- ``namespace`` optionally pins the root namespace, ignoring the ``--namespace`` option.
- ``sortImports`` (default ``true``) controls whether the first contiguous ``use`` block of the
  generated content is sorted alphabetically.

Like ``#[Command]``, the attribute validates its inputs at construction time:
``component`` must be a class-name fragment (a letter followed by letters, digits,
or underscores), ``template`` must not be empty, and ``directory`` and
``namespace``, when given, must be valid backslash-separated namespace fragments.

.. _generator-command-templates:

*********
Templates
*********

Templates are regular view files. Because they are rendered through ``view()``,
a template cannot open with a literal PHP tag, so the pipeline recognizes a few
pseudo-placeholders that are substituted after rendering:

- ``<@php`` becomes ``<?php``
- ``{namespace}`` becomes the namespace of the generated class
- ``{class}`` becomes the short class name

A minimal template:

.. code-block:: php

    <@php

    namespace {namespace};

    class {class}
    {
    }

The view used for a run is resolved in this order:

1. the ``$templatePath`` property, when a subclass assigns a full namespaced view path at runtime;
2. the entry keyed by the command name in **app/Config/Generators.php**, when it is a string;
3. the ``template`` declared on the attribute, looked up under ``CodeIgniter\Commands\Generators\Views``.

To let users customize your template, register it in the config file:

.. literalinclude:: cli_modern_generators/002.php

*******************
Generation Pipeline
*******************

The default ``execute()`` simply returns ``$this->generateClass()``, which walks
through these steps:

1. The ``name`` argument is normalized: segments are converted to PascalCase, a
   miscased trailing component is fixed, and the component suffix is appended
   when applicable. The result is qualified against the root namespace and the
   attribute's ``directory``. A result that is not a valid class name (for example,
   one containing ``..`` segments) aborts with ``EXIT_ERROR``.
2. The target path is derived from the autoloader's mapping for the root
   namespace. An unknown namespace aborts with ``EXIT_ERROR`` and an error
   message.
3. The template is rendered with the view data, placeholders are replaced, and
   the imports are sorted when ``sortImports`` is enabled.
4. Safety checks run before writing: generating into the ``CodeIgniter``
   namespace asks for confirmation on interactive runs (and proceeds with a
   warning on non-interactive ones), and an existing target file aborts with
   ``EXIT_ERROR`` unless ``--force`` is passed.

Generators that produce more than one artifact can call ``generateView()`` for
non-class files, and may reassign the mutable ``$template`` / ``$templatePath``
properties between generation calls.

*******************
Customization Hooks
*******************

Placeholders and View Data
==========================

Most real generators need more than ``{namespace}`` and ``{class}``. Override
``getReplacements()`` to add placeholder substitutions and ``getTemplateData()``
to pass variables into the view:

.. literalinclude:: cli_modern_generators/003.php

Entries returned by ``getReplacements()`` take precedence over the core pairs,
so a generator can even override how ``{namespace}`` or ``{class}`` is derived.

Trimming Options and Forcing the Suffix
=======================================

The common generator options are registered through ``provideGeneratorOptions()``.
Override it to drop options that make no sense for your generator, and override
``shouldAppendSuffix()`` when suffixing is not driven by the ``--suffix`` flag:

.. literalinclude:: cli_modern_generators/004.php

The base class guards every read of ``--suffix`` and ``--force``, so omitting
them is safe.

Other Hooks
===========

- ``basename(string $filename): string`` changes the file basename before saving.
  Useful for components whose file name carries a date, like migrations.
- ``getNamespace(): string`` resolves the root namespace. The default returns the attribute's
  ``namespace`` or the ``--namespace`` option.
- ``buildPath(string $class): string`` maps the qualified class to a file path through the
  autoloader. Override for components with special file locations, like tests.
- ``renderTemplate(array $data = []): string`` renders the resolved view.

****************************
Prompting for the Class Name
****************************

``AbstractGeneratorCommand`` implements ``PromptsForMissingInputInterface``, so
running a generator interactively without a class name prompts for it instead of
failing validation. The prompt label comes from the attribute's ``classNameLang``
language key. Non-interactive runs (piped input, ``--no-interaction``) fail fast
with the usual missing-arguments error.

The mechanism is available to every modern command, not just generators. See
:ref:`prompts-for-missing-input`.

*********************************
Migrating From ``GeneratorTrait``
*********************************

The base class covers everything the trait did, but the configuration moves
from mutable properties and the ``$params`` array to the attribute and the
validated input accessors.

**Configuration properties**

``protected $component``, ``protected $template``, ``protected $directory``, ``protected $classNameLang``, ``protected ?string $namespace``
    Move to the corresponding parameters of the ``#[GeneratorCommand]`` attribute.

``protected ?string $templatePath``
    Stays a runtime property on the base class. Assign it only when switching
    templates mid-run (multi-artifact generators).

``$this->setSortImports(false)``
    Becomes ``sortImports: false`` on the attribute.

``$this->setEnabledSuffixing(false)``
    Gone. Override ``provideGeneratorOptions()`` to not register ``--suffix``, and
    ``shouldAppendSuffix()`` to control suffixing directly. Note that a user-passed
    ``--suffix``, which the trait silently ignored, then fails validation as an
    unknown option.

``$this->setHasClassName(false)``
    Gone. Override ``configure()`` without calling ``parent::configure()`` and declare
    whatever arguments your generator actually needs. Also override ``execute()``,
    since the default one calls ``generateClass()``, which requires the ``name``
    argument.

**Input handling**

``run(array $params)``
    Not used by modern commands. The default ``execute()`` already calls
    ``generateClass()``. Override ``execute()`` only when the run involves more
    than one generation step.

``$params[0]``
    The class name is now the declared ``name`` argument: ``$this->getValidatedArgument('name')``.

``$this->getOption('foo')`` / ``CLI::getOption('foo')``
    Become ``$this->getValidatedOption('foo')``. Extra options must be declared in
    ``configure()`` (after calling ``parent::configure()``).

Class-name prompting
    Automatic. The trait prompted inside its name normalization. The base class
    prompts through :ref:`prompts-for-missing-input` before binding, so the
    argument is guaranteed present by the time your code runs.

**Content hooks**

``prepare(string $class)`` override
    Gone. Return extra placeholder pairs from ``getReplacements()`` and view data
    from ``getTemplateData()`` instead of calling ``parseTemplate()`` with arrays.

``basename(string $filename)`` override
    Same hook name and signature on the base class.

``getNamespace()`` / ``buildPath()`` overrides
    Same hook names and signatures on the base class.

A typical ``GeneratorTrait`` generator:

.. literalinclude:: cli_modern_generators/005.php

…becomes, as a modern generator command:

.. literalinclude:: cli_modern_generators/001.php

Behavioural changes we need to be aware of when migrating:

- **Failures produce failing exit codes.** The trait's ``generateClass()`` returned ``void``, so a
  generator reported ``EXIT_SUCCESS`` even when the target file already existed. The base class
  returns ``EXIT_ERROR`` for an existing file without ``--force``, a write failure, and an undefined
  namespace. Declining the ``CodeIgniter`` namespace confirmation still exits with ``EXIT_SUCCESS``.
- **The** ``CodeIgniter`` **namespace confirmation only prompts on interactive runs.** Non-interactive
  runs print the warning and proceed instead of blocking on input that will never arrive.
- **The** ``CodeIgniter`` **namespace confirmation keys off the resolved namespace.** The trait compared
  the raw ``--namespace`` option, so an attribute-pinned ``CodeIgniter`` namespace or a spelling like
  ``--namespace CodeIgniter/`` did not warn. The base class resolves through ``getNamespace()`` first.
- **Placeholder replacement is single-pass.** Replacements are applied with ``strtr()``, so a
  replacement value that happens to contain another placeholder is no longer substituted again.

************************
AbstractGeneratorCommand
************************

.. php:namespace:: CodeIgniter\CLI

.. php:class:: AbstractGeneratorCommand

    All hooks below are ``protected``: they are called or overridden from within
    your own generator, never from the outside.

    .. php:method:: generateClass(): int

        Runs the full pipeline for the qualified class name and returns an
        ``EXIT_*`` status. This is what the default ``execute()`` calls.

    .. php:method:: generateView(string $view): int

        :param string $view: Namespaced view name to generate.

        Like :php:meth:`generateClass`, but for non-class artifacts. The name is
        used as-is, without qualification or suffix handling.

    .. php:method:: getReplacements(string $class): array

        :param string $class: The namespaced class (or view) being generated.

        Returns extra placeholder replacements. Entries take precedence over the
        core ``{namespace}`` / ``{class}`` pairs. Defaults to ``[]``.

    .. php:method:: getTemplateData(string $class): array

        :param string $class: The namespaced class (or view) being generated.

        Returns view data passed to the template when rendering. Defaults to ``[]``.

    .. php:method:: shouldAppendSuffix(): bool

        Whether the component suffix should be appended to the class name. The
        default reads the ``--suffix`` flag when it is declared.

    .. php:method:: basename(string $filename): string

        :param string $filename: The computed target file path.

        Returns the file basename to save under. Override to decorate it, for
        example with a timestamp.

    .. php:method:: getNamespace(): string

        Resolves the root namespace from the attribute override or the
        ``--namespace`` option.

    .. php:method:: buildPath(string $class): string

        :param string $class: The namespaced class (or view) being generated.

        Maps the class to its target file path through the autoloader. Returns
        an empty string (after printing an error) when the namespace is not
        registered.

    .. php:method:: renderTemplate(array $data = []): string

        :param array $data: View data for the template.

        Renders the resolved generator view (see
        :ref:`generator-command-templates` for the resolution order).

    .. php:method:: provideGeneratorOptions(): void

        Registers the common generator options. The default registers all three
        of ``--namespace``, ``--suffix``, and ``--force`` through the ``final``
        helpers :php:meth:`addNamespaceOption`, :php:meth:`addSuffixOption`, and
        :php:meth:`addForceOption`. Override to register a subset.

    .. php:method:: addNamespaceOption(): static

        Registers the ``--namespace`` / ``-n`` option, defaulting to ``APP_NAMESPACE``.

    .. php:method:: addSuffixOption(): static

        Registers the ``--suffix`` / ``-s`` flag.

    .. php:method:: addForceOption(): static

        Registers the ``--force`` / ``-f`` flag.
