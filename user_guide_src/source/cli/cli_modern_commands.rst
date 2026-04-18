#####################
Modern Spark Commands
#####################

.. versionadded:: 4.8.0

Modern commands are a newer style of :doc:`Spark command <cli_commands>`.
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
   extra usage examples. A default ``--help``/ ``-h`` flag and ``--no-header``
   flag are added automatically afterwards.
2. ``initialize(array &$arguments, array &$options): void`` receives the raw
   arguments and options by reference. Useful when your command needs to
   massage input — for instance, to unfold an alias argument into the canonical
   form before anything else runs.
3. ``interact(array &$arguments, array &$options): void`` also receives the
   raw arguments and options by reference. This is where you prompt the user
   for missing input, set values conditionally, or abort early.
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
- ``--help`` / ``-h`` and ``--no-header`` are reserved for the framework and registered on every command automatically.

Configuration-time violations raise ``InvalidOptionDefinitionException``.

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
- ``getUnboundOption(string $name, ?array $options = null, $default = null)``

Inside ``interact()`` pass ``$options`` explicitly — the instance state is not
populated yet. Outside ``interact()`` (for example inside ``execute()``) you
can omit ``$options`` and the helpers will read from the instance snapshot
taken right before bind and validate.

.. literalinclude:: cli_modern_commands/004.php

Any change you make to ``$arguments`` or ``$options`` inside ``interact()``
carries through to bind, validate, and ``execute()``.

******************
Inside execute()
******************

``execute()`` receives two arrays that mirror your declared definitions:

- ``$arguments`` contains every declared argument, bound to the provided value or the declared default.
- ``$options`` contains every declared option plus the framework defaults
  (``help``, ``no-header``), bound to the provided value or the declared default.

The same data is available through typed helpers so you don't have to sprinkle
``is_string()`` / ``is_array()`` guards across your command:

- ``getValidatedArgument(string $name)`` / ``getValidatedArguments()``
- ``getValidatedOption(string $name)`` / ``getValidatedOptions()``
- ``getUnboundArgument(int $index)`` / ``getUnboundArguments()``
- ``getUnboundOption(string $name, ...)`` / ``getUnboundOptions()``

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

********************************
Coexistence With Legacy Commands
********************************

Legacy ``BaseCommand`` classes are still supported, and they are discovered
alongside modern commands. If the same name is claimed by both a legacy and a
modern command, the legacy one is invoked and a warning is printed once at
discovery time so you can rename or retire one of the two.

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

    .. php:method:: run(array $arguments, array $options): int

        :param array $arguments: The raw positional arguments parsed from the command line.
        :param array $options:   The raw option map parsed from the command line.
        :returns:                The exit code returned by ``execute()``.

        **Final.** Walks the command through ``initialize()``, ``interact()``,
        bind, validate, and finally ``execute()``. The framework calls this
        on your behalf — you rarely invoke it directly, but you can when
        driving a command manually (for instance, from a test).

    .. php:method:: call(string $command[, array $arguments = [], array $options = []]): int

        :param string        $command:   The name of the modern command to call.
        :param array         $arguments: Positional arguments to forward.
        :param array         $options:   Options to forward, keyed by long name, shortcut, or negation.
        :returns:                        The exit code returned by the called command.

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

    .. php:method:: getUnboundOption(string $name[, array|null $options = null, array|string|null $default = null]): array|string|null

        :param string                  $name:    The declared option name to look up.
        :param array|null              $options: Raw option map to read from. Required inside ``interact()``, optional elsewhere.
        :param array|string|null       $default: Value to return when the option was not provided.

        Returns the raw value the option was given, resolving its shortcut
        and negation. Falls back to ``$default`` when the option was not
        provided. Throws ``LogicException`` when the option is not declared
        on this command.

    .. php:method:: hasUnboundOption(string $name[, array|null $options = null]): bool

        :param string     $name:    The declared option name to look up.
        :param array|null $options: Raw option map to read from. Required inside ``interact()``, optional elsewhere.

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
