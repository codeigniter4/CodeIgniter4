#####################
Modern Spark Commands
#####################

.. versionadded:: 4.8.0

Modern commands are a newer style of :doc:`spark commands <cli_commands>`.
Instead of declaring metadata through class properties, modern commands describe
themselves through a ``#[Command]`` attribute and build their argument/option
surface inside a ``configure()`` method. The framework then parses the command
line, applies the declared defaults, validates what was passed, and finally
calls ``execute()`` with typed, validated values.

Modern and legacy commands can coexist (for now): existing ``BaseCommand`` classes
continue to work, and the framework routes invocations to whichever command
matches the requested name, regardless of style.

.. contents::
    :local:
    :depth: 2

*******************
Creating a Command
*******************

A modern command is a class that:

- extends ``CodeIgniter\CLI\AbstractCommand``;
- declares a ``#[Command]`` attribute with a ``name``, a ``description`` and a ``group``;
- implements ``execute(array $arguments, array $options): int`` and returns an ``EXIT_*`` status code.

A minimal example:

.. literalinclude:: cli_modern_commands/001.php

File Location
=============

Same rule as the legacy style — commands must live under a directory named
**Commands** that is reachable through PSR-4 autoloading, for instance
**app/Commands/**. The framework auto-discovers them the first time the
command runner is used.

The ``#[Command]`` Attribute
============================

The attribute holds the command's identity:

- ``name`` is the token users type after ``php spark``. It must not be empty, must not contain
  whitespace, and may use a colon to namespace related commands (``cache:clear``, ``make:migration``).
  Leading, trailing, or consecutive colons are rejected.
- ``description`` is shown in the ``list`` output and at the top of ``help <command>``.
- ``group`` controls how the command is grouped in the ``list`` output. A command with an empty
  ``group`` is skipped by discovery.

The attribute itself validates these constraints at construction time — if you
misspell ``name``, you will see the error at discovery rather than at run time.

*****************
Command Lifecycle
*****************

When the runner invokes a modern command, it walks through several phases in
this order:

1. **Construction.** The ``#[Command]`` attribute is read, then your
   ``configure(): void`` hook runs so you can register arguments, options, and
   extra usage examples. A default ``--help``/ ``-h`` flag, ``--no-header``
   flag, and ``--no-interaction``/ ``-N`` flag are added automatically
   afterwards.
2. ``initialize(array &$arguments, array &$options): void`` receives the raw
   arguments and options by reference. Useful when your command needs to
   massage input — for instance, to unfold an alias argument into the canonical
   form before anything else runs.
3. ``interact(array &$arguments, array &$options): void`` also receives the
   raw arguments and options by reference. This is where you prompt the user
   for missing input, set values conditionally, or abort early. This hook is
   skipped when the command is non-interactive (see :ref:`non-interactive-mode`).
4. **Bind & validate.** The framework maps the raw input to the definitions
   you declared in ``configure()``, applies defaults, and rejects input that
   violates the definitions (missing required argument, unknown option, array
   option passed without a value, and so on).
5. ``execute(array $arguments, array $options): int`` receives the bound and
   validated arguments and options, and returns an exit code.

You only have to implement ``execute()``; the other hooks are optional.

*********
Arguments
*********

Arguments are positional — the first token after the command name is bound to
the first declared argument, the second token to the second declared argument,
and so on. They are declared inside ``configure()`` using the
``CodeIgniter\CLI\Input\Argument`` value object:

.. literalinclude:: cli_modern_commands/002.php

The following rules are enforced at configuration time. Violating any of them
raises an ``InvalidArgumentDefinitionException``:

- A required argument **must not** have a default value.
- An optional argument **must** have a default value.
- An array argument collects every remaining positional token.
  Only one array argument may be declared, and it must come last.
- An array argument cannot be required (but it can have a non-empty default).
- Required arguments must all come before optional arguments.
- Argument names must match ``[A-Za-z0-9_-]+`` and the name ``extra_arguments`` is reserved.

*******
Options
*******

Options are name-based. They are declared with ``CodeIgniter\CLI\Input\Option``:

.. literalinclude:: cli_modern_commands/003.php

Options support the following modes (they can be combined where it makes
sense):

- **Flag** — the default. The option takes no value. Presence makes the bound
  value ``true``; absence leaves it ``false``.
- ``requiresValue: true`` — the option must be followed by a value when passed.
- ``acceptsValue: true`` — the option may be followed by a value, but the value is optional.
- ``isArray: true`` — the option may be passed multiple times; each value is appended to the bound array.
- ``negatable: true`` — a second long form ``--no-<name>`` is registered automatically.
  Passing ``--name`` sets the option to ``true``; passing ``--no-name`` sets it to ``false``.

Every option may also declare a single-character ``shortcut`` (e.g., ``-f`` for ``--force``).
Shortcuts must be a single alphanumeric character and unique within the command.

A few quirks are worth knowing:

- ``requiresValue: true`` and ``isArray: true`` both imply ``acceptsValue: true``.
- An option that requires a value must be given a **string** default. The default is used only when the
  option is not passed at all; passing the option without a value throws at validation.
- An array option must require a value. Its default must be ``null`` or a non-empty array
  (``null`` is normalised to an empty array internally).
- A negatable option cannot accept a value or be an array. Its default must be a boolean.
- A negatable option's auto-generated ``--no-<name>`` form will clash if another option is already named ``no-<name>``.
- Option names must match ``[A-Za-z0-9_-]+`` and the name ``extra_options`` is reserved.
- The following options are reserved for the framework and registered on every command automatically:

  - ``--help`` / ``-h``
  - ``--no-header``
  - ``--no-interaction`` / ``-N``

Configuration-time violations raise ``InvalidOptionDefinitionException``.

.. note::

    The command-line parser does **not** understand bundled shortcuts or
    shortcuts with a glued value:

    - ``-abc`` is read as one option named ``abc``, *not* as ``-a -b -c``.
    - ``-fvalue`` is read as one option named ``fvalue``; it is not split into
      shortcut ``-f`` with value ``value``.

    Pass shortcut values with ``-f=value`` or ``-f value`` instead.

*************************
Interacting With the User
*************************

``interact()`` is designed for commands that need to prompt, confirm, or fill
in missing input before validation runs. Its ``$arguments`` and ``$options``
parameters are **raw** — they are the tokens the framework parsed from the
command line, *before* the values are mapped to your declared definitions.

Because the raw input may be keyed by the long name, the shortcut, or the
negation form, two helpers make lookups alias-aware:

- ``hasUnboundOption(string $name, ?array $options = null): bool``
- ``getUnboundOption(string $name, ?array $options = null): array|string|null``

Inside ``initialize()`` and ``interact()`` pass ``$options`` explicitly — the
instance snapshot is not populated yet. From ``execute()`` (or any helper
reached from it) you can omit ``$options`` and the helpers will read from the
snapshot taken right after ``interact()`` returns and before bind and validate.

.. literalinclude:: cli_modern_commands/004.php

Any change you make to ``$arguments`` or ``$options`` inside ``interact()``
carries through to bind, validate, and ``execute()``.

.. _non-interactive-mode:

Non-Interactive Mode
====================

Every modern command accepts ``--no-interaction`` / ``-N`` out of the box.
When the flag is present, or when the command is otherwise non-interactive,
the ``interact()`` hook is skipped entirely and the command proceeds straight
to bind, validate, and ``execute()``.

Programmatically, the state is exposed through two public methods on
``AbstractCommand``:

- ``isInteractive(): bool``: reports the current state.
- ``setInteractive(bool $interactive): static``: pins the state, overriding
  both the CLI flag and TTY detection. Returns ``$this`` for chaining.

The resolved state follows this precedence:

1. An explicit ``setInteractive(bool)`` call wins. Useful when a command
   must force a specific mode for safety.
2. Otherwise, the CLI flag ``--no-interaction`` / ``-N`` forces non-interactive state.
3. Otherwise, **STDIN** is probed: if it is not a TTY (piped input, cron,
   CI, ``nohup``), the command is non-interactive.
4. Otherwise, the command is interactive.

When the current command invokes another via ``$this->call(...)``, the
parent's non-interactive state is propagated to the sub-command
automatically. A caller that passes ``no-interaction`` (or ``N``) in the
sub-command's ``$options`` wins over that propagation.

The propagation can be overridden with the ``$noInteractionOverride``
parameter of ``call()``:

- ``null`` (default): propagate the parent's state.
- ``true``: force the sub-command non-interactive regardless of the parent.
- ``false``: remove any forwarded ``--no-interaction`` / ``-N`` from the
  child ``$options`` so the sub-command resolves its own state. Note: TTY
  detection can still downgrade the sub-command if STDIN is not a TTY.

******************
Inside execute()
******************

``execute()`` receives two arrays that mirror your declared definitions:

- ``$arguments`` contains every declared argument, bound to the provided value or the declared default.
- ``$options`` contains every declared option plus the framework defaults
  (``help``, ``no-header``, ``no-interaction``), bound to the provided value
  or the declared default.

Within ``execute()`` itself, reaching into ``$arguments`` / ``$options`` directly
is the simplest thing to do. The same data is also available through helpers
so you can reach it from sub-methods without having to thread the two arrays
through every signature:

- ``getValidatedArgument(string $name)`` / ``getValidatedArguments()``
- ``getValidatedOption(string $name)`` / ``getValidatedOptions()``
- ``getUnboundArgument(int $index)`` / ``getUnboundArguments()``
- ``getUnboundOption(string $name, ?array $options = null)`` / ``getUnboundOptions()``

The *validated* variants expose the bound values (what your definition says).
The *unbound* variants expose the raw input snapshot — useful when forwarding
the command to another command, or when your logic needs to know whether a
flag was actually passed rather than whether it resolved to a default value.

.. literalinclude:: cli_modern_commands/005.php

Accessors that take a name throw ``LogicException`` when the name is not declared on the command.

***********************
Calling Another Command
***********************

Inside ``execute()``, a modern command can invoke another modern command through
``$this->call()``. ``call()`` must not be used from ``configure()``, ``initialize()``,
or ``interact()`` — the current command has not been bound and validated yet at
those points, and its unbound state has not been snapshotted.

.. literalinclude:: cli_modern_commands/006.php

The ``$arguments`` and ``$options`` you pass are interpreted as raw input —
they go through bind and validate on the target command, just like a user
invocation.

To forward the caller's own input through to the target command, pass
``$this->getUnboundArguments()`` and ``$this->getUnboundOptions()`` to ``call()``:

.. literalinclude:: cli_modern_commands/008.php

**************
Usage Examples
**************

The default usage line is built automatically from the command name and the
declared argument list. You can append additional example lines by calling
``addUsage()`` inside ``configure()``:

.. literalinclude:: cli_modern_commands/007.php

In the ``help <command>`` or ``<command> --help`` output the default usage line is shown first,
followed by each ``addUsage()`` entry in the order it was added.

**********************
Rendering an Exception
**********************

If your command catches a ``Throwable`` and wants to produce the same
formatted output the framework uses for uncaught exceptions, call
``$this->renderThrowable($exception)``. The helper is safe to call from any
command, and it will not disturb the currently shared request.

**********************************
Migrating From ``BaseCommand``
**********************************

The modern command system is a superset of the legacy ``BaseCommand`` API — the
same capabilities are there, just expressed through an attribute and explicit
definitions rather than class properties and ad-hoc lookups.

**Identity**

``protected $name``
    Moves to ``name:`` on the ``#[Command]`` attribute.

``protected $description``
    Moves to ``description:`` on the ``#[Command]`` attribute.

``protected $group``
    Moves to ``group:`` on the ``#[Command]`` attribute. An empty group skips the command at discovery.

**Input surface (declare inside** ``configure()`` **)**

``protected $usage``
    The default usage line is generated from the declared arguments. Register extras with ``addUsage()``.

``protected $arguments``
    One ``addArgument(new Argument(...))`` call per argument.

``protected $options``
    One ``addOption(new Option(...))`` call per option. A long name is required; a legacy ``-x``-style
    option becomes ``new Option(name: 'something', shortcut: 'x')``.

**Runtime**

``run(array $params)``
    No longer the extension point — ``run()`` is ``final`` on ``AbstractCommand`` and drives the lifecycle itself.
    Move the body into ``execute(array $arguments, array $options): int``, which must return an ``EXIT_*`` status.

``$params[0]``
    Use ``$arguments['name']`` or ``$this->getValidatedArgument('name')``.

``$params['name']`` / ``CLI::getOption('name')``
    Use ``$options['name']`` or ``$this->getValidatedOption('name')``.
    Call ``$this->hasUnboundOption('name')`` when you need to know whether the flag was actually passed.

``$this->call('other', $params)``
    Becomes ``$this->call('other', $arguments, $options)``; only from inside ``execute()``.
    To forward the caller's own raw input, pass ``$this->getUnboundArguments()`` and ``$this->getUnboundOptions()``.

``$this->showError($e)``
    Becomes ``$this->renderThrowable($e)``.

``showHelp()`` override
    Gone. The built-in ``help`` command builds the help output itself from the declared arguments, options, and usages.

Prompting the user mid-run stays with ``CLI::prompt()``, but the idiomatic spot moves from ``run()``
to ``interact()`` so validation can see whatever the user provides interactively.

A typical ``BaseCommand`` implementation:

.. literalinclude:: cli_modern_commands/009.php

…becomes, as a modern command:

.. literalinclude:: cli_modern_commands/010.php

Two behavioural changes are worth calling out explicitly:

- **Validated, not raw.** Arguments and options are parsed, defaulted, and validated before ``execute()`` runs.
  If a required argument is missing or a ``requiresValue`` option was passed without a value, the framework
  raises a typed exception and your command is never entered.
- **Exit codes are mandatory.** Legacy ``run()`` could return ``null``. The modern ``execute()`` must return an
  integer; the framework emits a deprecation notice for any legacy command that still returns ``null``.

********************************
Coexistence With Legacy Commands
********************************

Legacy ``BaseCommand`` classes are still supported, and they are discovered
alongside modern commands. If the same name is claimed by both a legacy and a
modern command, the legacy one is invoked and a warning is printed once at
discovery time so you can rename or retire one of the two.

To detect the collision programmatically — for example, in a migration script
that verifies the legacy copy was removed — the ``Commands`` runner exposes two
read-only checks:

.. literalinclude:: cli_modern_commands/011.php

The ``help`` command understands both styles — it delegates to the legacy
``showHelp()`` method for legacy commands and renders a structured view for
modern ones.

.. note::

    Legacy commands remain supported while the framework's own built-in
    commands are being migrated to the modern style. Once that migration is
    complete, ``BaseCommand`` will start emitting deprecation notices. New
    commands should be written against ``AbstractCommand`` from the start.

***************
AbstractCommand
***************

The ``AbstractCommand`` class that all modern commands must extend exposes a
number of utility methods you call from within your own command. Hooks like
``configure()``, ``initialize()``, ``interact()``, and ``execute()`` are
covered in the sections above and are not listed here.

.. php:namespace:: CodeIgniter\CLI

.. php:class:: AbstractCommand

    .. php:method:: getCommandRunner(): Commands

        Returns the ``Commands`` runner the command was constructed with.
        Useful when you need to introspect other discovered commands (for
        instance, building a custom ``list``-style command).

    .. php:method:: getName(): string

        Returns the command name declared on the ``#[Command]`` attribute.

    .. php:method:: getDescription(): string

        Returns the command description declared on the ``#[Command]``
        attribute.

    .. php:method:: getGroup(): string

        Returns the command group declared on the ``#[Command]`` attribute.

    .. php:method:: getUsages(): array

        Returns every usage line registered for the command — the default
        line built from the argument list, followed by each ``addUsage()``
        entry in declaration order.

    .. php:method:: getArgumentsDefinition(): array

        Returns the ``Argument`` value objects registered on this command,
        keyed by argument name and ordered by declaration.

    .. php:method:: getOptionsDefinition(): array

        Returns the ``Option`` value objects registered on this command,
        keyed by option name.

    .. php:method:: getShortcuts(): array

        Returns the shortcut-to-option-name map (for example
        ``['f' => 'force']``). Empty when no shortcut is declared.

    .. php:method:: getNegations(): array

        Returns the negation-to-option-name map (for example
        ``['no-force' => 'force']``). Empty when no negatable option is
        declared.

    .. php:method:: addUsage(string $usage): static

        :param string $usage: An extra usage example line.

        Adds a usage example to the ``help <command>`` output. The default
        usage line derived from the argument list is always shown first.

    .. php:method:: addArgument(Argument $argument): static

        :param Argument $argument: The argument definition to register.

        Registers a positional argument. Call from ``configure()``.

    .. php:method:: addOption(Option $option): static

        :param Option $option: The option definition to register.

        Registers an option. Call from ``configure()``.

    .. php:method:: renderThrowable(Throwable $e): void

        :param Throwable $e: The throwable to render.

        Produces the same formatted output the framework uses for uncaught
        exceptions. Safe to call from any command.

    .. php:method:: hasArgument(string $name): bool

        :param string $name: The argument name to look up.

        Returns ``true`` if an argument with that name is declared on the
        command.

    .. php:method:: hasOption(string $name): bool

        :param string $name: The option name to look up.

        Returns ``true`` if an option with that name is declared on the
        command.

    .. php:method:: hasShortcut(string $shortcut): bool

        :param string $shortcut: The shortcut character to look up.

        Returns ``true`` if the shortcut is claimed by one of the declared
        options.

    .. php:method:: hasNegation(string $name): bool

        :param string $name: The negation name (for example ``no-force``) to look up.

        Returns ``true`` if the negation is registered by one of the
        declared options.

    .. php:method:: isInteractive(): bool

        Reports whether the command will prompt the user. See
        :ref:`non-interactive-mode` for the resolution order.

    .. php:method:: setInteractive(bool $interactive): static

        :param bool $interactive: The state to pin.
        :returns:                 The current command instance for chaining.

        Overrides both the ``--no-interaction`` / ``-N`` flag and TTY
        detection for this command instance. Typically called from
        ``initialize()`` or by an outer caller.

    .. php:method:: run(array $arguments, array $options): int

        :param array $arguments: The raw positional arguments parsed from the command line.
        :param array $options:   The raw option map parsed from the command line.
        :returns:                The exit code returned by ``execute()``.

        **Final.** Walks the command through ``initialize()``, ``interact()``,
        bind, validate, and finally ``execute()``. The framework calls this
        on your behalf — you rarely invoke it directly, but you can when
        driving a command manually (for instance, from a test).

    .. php:method:: call(string $command[, array $arguments = [], array $options = [], ?bool $noInteractionOverride = null]): int

        :param string    $command:                The name of the modern command to call.
        :param array     $arguments:              Positional arguments to forward.
        :param array     $options:                Options to forward, keyed by long name, shortcut, or negation.
        :param bool|null $noInteractionOverride:  Override the sub-command's interactive state.
                                                  ``null`` propagates the parent's state (default);
                                                  ``true`` forces non-interactive; ``false`` removes
                                                  any forwarded ``--no-interaction`` from ``$options``.
        :returns:                                 The exit code returned by the called command.

        Invokes another modern command. The arguments and options go through
        bind and validate on the target command, just like a user invocation.

    .. php:method:: getUnboundArguments(): array

        Returns the raw, parsed positional arguments as passed to the
        command.

    .. php:method:: getUnboundArgument(int $index): string

        :param int $index: The zero-based index of the argument to read.

        Returns a single raw positional argument. Throws
        ``LogicException`` when the index does not exist.

    .. php:method:: getUnboundOptions(): array

        Returns the raw, parsed option map, keyed by long name, shortcut,
        or negation.

    .. php:method:: getUnboundOption(string $name[, array|null $options = null]): array|string|null

        :param string     $name:    The declared option name to look up.
        :param array|null $options: Raw option map to read from. Required inside ``initialize()`` and ``interact()``, optional from ``execute()`` onwards.

        Returns the raw value the option was given, resolving its shortcut
        and negation. Returns ``null`` when the option was not provided —
        callers can use the ``??`` operator to supply a fallback, or
        :php:meth:`hasUnboundOption` to disambiguate presence from a ``null``
        value. Throws ``LogicException`` when the option is not declared on
        this command.

    .. php:method:: hasUnboundOption(string $name[, array|null $options = null]): bool

        :param string     $name:    The declared option name to look up.
        :param array|null $options: Raw option map to read from. Required inside ``initialize()`` and ``interact()``, optional from ``execute()`` onwards.

        Returns ``true`` if the option was provided under its long name,
        shortcut, or negation. Throws ``LogicException`` when the option is
        not declared on this command.

    .. php:method:: getValidatedArguments(): array

        Returns the bound and validated arguments, keyed by declared name.

    .. php:method:: getValidatedArgument(string $name): array|string

        :param string $name: The declared argument name to read.

        Returns the bound and validated value for a single argument. Throws
        ``LogicException`` when the argument is not declared on this command.

    .. php:method:: getValidatedOptions(): array

        Returns the bound and validated options, keyed by declared name.

    .. php:method:: getValidatedOption(string $name): bool|array|string|null

        :param string $name: The declared option name to read.

        Returns the bound and validated value for a single option. Throws
        ``LogicException`` when the option is not declared on this command.
