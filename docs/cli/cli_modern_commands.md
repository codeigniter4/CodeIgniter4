# Modern Spark Commands

!!! success "Available from version 4.8.0"

Modern commands are a newer style of [spark commands](cli_commands.md). Instead of declaring metadata through class properties, modern commands describe themselves through a `#[Command]` attribute and build their argument/option surface inside a `configure()` method. The framework then parses the command line, applies the declared defaults, validates what was passed, and finally calls `execute()` with typed, validated values.

Modern and legacy commands can coexist: existing `BaseCommand` classes continue to work, and the framework routes invocations to whichever command matches the requested name, regardless of style. `BaseCommand` itself is deprecated, though. See [Migrating From BaseCommand](#migrating-from-basecommand).

## Creating a Command

A modern command is a class that:

- extends `CodeIgniter\CLI\AbstractCommand`;
- declares a `#[Command]` attribute with a `name`, a `description` and a `group`;
- implements `execute(array $arguments, array $options): int` and returns an `EXIT_*` status code.

A minimal example:

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;

#[Command(name: 'app:greet', description: 'Prints a greeting.', group: 'App')]
class AppGreet extends AbstractCommand
{
    protected function execute(array $arguments, array $options): int
    {
        $this->getOutput()->write('Hello!', 'green');

        return EXIT_SUCCESS;
    }
}
```

### File Location

The rule is the same as for the legacy style: commands must live under a directory named **Commands** that is reachable through PSR-4 autoloading, for instance **app/Commands/**. The framework auto-discovers them the first time the command runner is used.

### The `#[Command]` Attribute

The attribute holds the command's identity:

- `name` is the token users type after `php spark`. It must not be empty, must not contain whitespace, and may use a colon to namespace related commands (`cache:clear`, `make:migration`). Leading, trailing, or consecutive colons are rejected.
- `description` is shown in the `list` output and at the top of `help <command>`.
- `group` controls how the command is grouped in the `list` output. A command with an empty `group` is skipped by discovery.
- `aliases` is an optional list of alternative names the command can also be invoked by. Each alias follows the same naming rules as `name` and must differ from it. Aliases resolve to the command at dispatch (`php spark <alias>` and `help <alias>` both work), are listed as their own rows in the `list` output, and are shown in an `Aliases:` section of `help <command>`.
- `hidden` is an optional flag, `false` by default, that keeps the command out of listings. See [Hidden Commands](#hidden-commands).
- `headerless` is an optional flag, `false` by default, that stops spark from printing its header before the command runs. See [Commands Without the Header](#commands-without-the-header).

The attribute itself validates these constraints at construction time. If you misspell `name`, you will see the error at discovery rather than at run time. An alias that collides with an existing command name or with another command's alias is a hard error at discovery, since the runner could not tell which command you meant.

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;

#[Command(
    name: 'app:deploy',
    description: 'Deploys the application.',
    group: 'App',
    aliases: ['app:ship', 'deploy'],
)]
class AppDeploy extends AbstractCommand
{
    protected function execute(array $arguments, array $options): int
    {
        // Reachable as `php spark app:deploy`, `php spark app:ship`, or `php spark deploy`.

        return EXIT_SUCCESS;
    }
}
```

### Hidden Commands

A hidden command is an ordinary command that spark does not advertise. It suits commands meant for scripts, cron jobs, or deployment tooling, which should stay runnable without cluttering the list people browse. Hide a command by setting `hidden: true` on its `#[Command]` attribute:

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;

#[Command(
    name: 'app:reindex',
    description: 'Rebuilds the search index.',
    group: 'App',
    hidden: true,
)]
class AppReindex extends AbstractCommand
{
    protected function execute(array $arguments, array $options): int
    {
        // Not shown by `php spark list`, but `php spark app:reindex` still runs it.

        return EXIT_SUCCESS;
    }
}
```

A hidden command and its aliases are left out of:

- the `list` output, including `list --simple`;
- the suggestions shown for a mistyped command name, including the offer to run one instead.

Anything that names the command exactly still works:

- `php spark app:reindex`, or any of its aliases;
- `command('app:reindex')`, and `call('app:reindex')` from another command;
- `php spark help app:reindex`, or `php spark app:reindex --help`.

To check the flag in code, use `isHidden()` on a command instance, or `Commands::isHiddenCommand()` with a command name or alias.

!!! note "Note"
    Hiding a command is not the same as leaving its `group` empty. A command with an empty group is never discovered, so it cannot run at all.

### Commands Without the Header

Spark prints a header with the framework version and the server time before running a command. A command whose output is read by another program, such as JSON piped to `jq`, can drop the header by setting `headerless: true` on its `#[Command]` attribute:

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;

#[Command(
    name: 'app:status',
    description: 'Prints the application status as JSON.',
    group: 'App',
    headerless: true,
)]
class AppStatus extends AbstractCommand
{
    protected function execute(array $arguments, array $options): int
    {
        $this->getOutput()->write(json_encode(['status' => 'ok'], JSON_THROW_ON_ERROR));

        return EXIT_SUCCESS;
    }
}
```

This has the same effect as always passing `--no-header`, and it covers the command's aliases too. `php spark help app:status` and `php spark app:status --help` still print the header, since help output is meant for people.

To check the flag in code, use `isHeaderless()` on a command instance, or `Commands::isHeaderlessCommand()` with a command name or alias.

## Command Lifecycle

When the runner invokes a modern command, it walks through several phases in this order:

1.  **Construction.** The `#[Command]` attribute is read, then your `configure(): void` hook runs so you can register arguments, options, and extra usage examples. A default `--help` / `-h` flag, `--no-header` flag, `--no-interaction` / `-N` flag, `--silent` flag, `--quiet` / `-q` flag, and `--verbose` / `-v` flag are added automatically afterwards.
2.  `isAvailable(): bool` is called to check whether the command should execute (see [Restricting Command Execution](#restricting-command-execution)).
3.  `initialize(array &$arguments, array &$options): void` receives the raw arguments and options by reference. Useful when your command needs to massage input — for instance, to unfold an alias argument into the canonical form before anything else runs.
4.  `interact(array &$arguments, array &$options): void` also receives the raw arguments and options by reference. This is where you prompt the user for missing input, set values conditionally, or abort early. This hook is skipped when the command is non-interactive (see [Non-Interactive Mode](#non-interactive-mode)).
5.  **Bind & validate.** The framework maps the raw input to the definitions you declared in `configure()`, applies defaults, and rejects input that violates the definitions (missing required argument, unknown option, array option passed without a value, and so on).
6.  `execute(array $arguments, array $options): int` receives the bound and validated arguments and options, and returns an exit code.

You only have to implement `execute()`; the other hooks are optional.

## Arguments

Arguments are positional — the first token after the command name is bound to the first declared argument, the second token to the second declared argument, and so on. They are declared inside `configure()` using the `CodeIgniter\CLI\Input\Argument` value object:

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Input\Argument;

#[Command(name: 'app:greet', description: 'Greets people.', group: 'App')]
class AppGreet extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            // Required, no default — the runner rejects invocations that omit it.
            ->addArgument(new Argument(
                name: 'name',
                description: 'Who to greet.',
                required: true,
            ))
            // Optional — a default is mandatory.
            ->addArgument(new Argument(
                name: 'salutation',
                description: 'Optional salutation.',
                default: 'Hello',
            ))
            // Array — collects every remaining token. Must be declared last.
            ->addArgument(new Argument(
                name: 'extras',
                description: 'Any extra tokens.',
                isArray: true,
            ));
    }

    protected function execute(array $arguments, array $options): int
    {
        // ...

        return EXIT_SUCCESS;
    }
}
```

The following rules are enforced at configuration time. Violating any of them raises an `InvalidArgumentDefinitionException`:

- A required argument **must not** have a default value.
- An optional argument **must** have a default value.
- An array argument collects every remaining positional token. Only one array argument may be declared, and it must come last.
- An array argument cannot be required (but it can have a non-empty default).
- Required arguments must all come before optional arguments.
- Argument names must match `[A-Za-z0-9_-]+` and the name `extra_arguments` is reserved.

## Options

Options are name-based. They are declared with `CodeIgniter\CLI\Input\Option`:

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Input\Option;

#[Command(name: 'app:publish', description: 'Publishes assets.', group: 'App')]
class AppPublish extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            // Flag. Presence → true, absence → false.
            ->addOption(new Option(
                name: 'dry-run',
                shortcut: 'r',
                description: 'Show what would be published without copying.',
            ))
            // Value-required. --destination=path or -d path. A string default is mandatory.
            ->addOption(new Option(
                name: 'destination',
                shortcut: 'd',
                description: 'Destination folder.',
                requiresValue: true,
                default: 'public',
            ))
            // Value-optional. Both `--driver` and `--driver=redis` are accepted.
            ->addOption(new Option(
                name: 'driver',
                description: 'Optional driver override.',
                acceptsValue: true,
            ))
            // Array. `--tag=a --tag=b` collects to ['a', 'b']. Array options must require a value.
            ->addOption(new Option(
                name: 'tag',
                shortcut: 't',
                description: 'Tag to publish (may be repeated).',
                requiresValue: true,
                isArray: true,
            ))
            // Negatable. Both --clean and --no-clean are registered automatically.
            ->addOption(new Option(
                name: 'clean',
                description: 'Clean the destination first.',
                negatable: true,
                default: true,
            ));
    }

    protected function execute(array $arguments, array $options): int
    {
        // ...

        return EXIT_SUCCESS;
    }
}
```

Options support the following modes (they can be combined where it makes sense):

- **Flag** — the default. The option takes no value. Presence makes the bound value `true`; absence leaves it `false`.
- `requiresValue: true` — the option must be followed by a value when passed.
- `acceptsValue: true` — the option may be followed by a value, but the value is optional.
- `isArray: true` — the option may be passed multiple times; each value is appended to the bound array.
- `negatable: true` — a second long form `--no-<name>` is registered automatically. Passing `--name` sets the option to `true`; passing `--no-name` sets it to `false`.

Every option may also declare a single-character `shortcut` (e.g., `-f` for `--force`). Shortcuts must be a single alphanumeric character and unique within the command.

A few quirks are worth knowing:

- `requiresValue: true` and `isArray: true` both imply `acceptsValue: true`.
- An option that requires a value must be given a **string** default. The default is used only when the option is not passed at all; passing the option without a value throws at validation.
- An array option must require a value. Its default must be `null` or a non-empty array (`null` is normalized to an empty array internally).
- A negatable option cannot accept a value or be an array. Its default must be a boolean.
- A negatable option's auto-generated `--no-<name>` form will clash if another option is already named `no-<name>`.
- Option names must match `[A-Za-z0-9_-]+` and the name `extra_options` is reserved.
- The following options are reserved for the framework and registered on every command automatically:
    - `--help` / `-h`
    - `--no-header`
    - `--no-interaction` / `-N`
    - `--silent`
    - `--quiet` / `-q`
    - `--verbose` / `-v`

Configuration-time violations raise `InvalidOptionDefinitionException`.

!!! note "Note"
    The command-line parser does **not** understand bundled shortcuts or shortcuts with a glued value:

    - `-abc` is read as one option named `abc`, *not* as `-a -b -c`. The only exception is a run of `v`, such as `-vvv`, which sets the verbosity.
    - `-fvalue` is read as one option named `fvalue`; it is not split into shortcut `-f` with value `value`.

    Pass shortcut values with `-f=value` or `-f value` instead.

## Interacting With the User

`interact()` is designed for commands that need to prompt, confirm, or fill in missing input before validation runs. Its `$arguments` and `$options` parameters are **raw** — they are the tokens the framework parsed from the command line, *before* the values are mapped to your declared definitions.

Because the raw input may be keyed by the long name, the shortcut, or the negation form, two helpers make lookups alias-aware:

- `hasUnboundOption(string $name, ?array $options = null): bool`
- `getUnboundOption(string $name, ?array $options = null): array|string|null`

Inside `initialize()` and `interact()` pass `$options` explicitly — the instance snapshot is not populated yet. From `execute()` (or any helper reached from it) you can omit `$options` and the helpers will read from the snapshot taken right after `interact()` returns and before bind and validate.

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Input\Option;

#[Command(name: 'logs:clear', description: 'Clears logs.', group: 'Housekeeping')]
class ClearLogs extends AbstractCommand
{
    protected function configure(): void
    {
        $this->addOption(new Option(
            name: 'force',
            shortcut: 'f',
            description: 'Skip the confirmation prompt.',
        ));
    }

    protected function interact(array &$arguments, array &$options): void
    {
        // hasUnboundOption() resolves --force, -f, and --no-force in one call,
        // even though $options here is still the raw parsed input.
        if ($this->hasUnboundOption('force', $options)) {
            return;
        }

        if ($this->getPrompt()->ask('Delete the logs?', ['n', 'y']) === 'n') {
            return;
        }

        // Mutations made here flow through to bind(), validate(), and execute().
        // For a flag option, writing `null` models "the flag was passed".
        $options['force'] = null;
    }

    protected function execute(array $arguments, array $options): int
    {
        if ($this->getValidatedOption('force') === false) {
            $this->getOutput()->error('Aborted.');

            return EXIT_ERROR;
        }

        // ... actually delete the logs ...

        return EXIT_SUCCESS;
    }
}
```

Any change you make to `$arguments` or `$options` inside `interact()` carries through to bind, validate, and `execute()`.

### Prompting for Missing Arguments

A command can let the framework prompt for its missing required arguments instead of hand-rolling that logic in `interact()`. Opt in by implementing the `CodeIgniter\CLI\PromptsForMissingInputInterface` marker interface:

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Input\Argument;
use CodeIgniter\CLI\PromptsForMissingInputInterface;

#[Command(name: 'app:greet', description: 'Greets people.', group: 'App')]
class AppGreet extends AbstractCommand implements PromptsForMissingInputInterface
{
    protected function configure(): void
    {
        $this->addArgument(new Argument(
            name: 'name',
            description: 'Who to greet.',
            required: true,
        ));
    }

    protected function getArgumentPromptLabels(): array
    {
        return ['name' => 'Who should be greeted?'];
    }

    protected function execute(array $arguments, array $options): int
    {
        $this->getOutput()->write(sprintf('Hello, %s!', $arguments['name']), 'green');

        return EXIT_SUCCESS;
    }
}
```

On an interactive run, right before `interact()` is called, the framework compares the provided positional tokens against the declared required arguments and prompts (with the `required` validation rule) for each one still missing, in declaration order. Two hooks refine the behavior:

- `getArgumentPromptLabels(): array` returns a map of argument name to prompt label. Arguments without an entry fall back to a generic label naming the argument.
- `afterPrompting(array &$arguments, array &$options): void` runs after at least one prompt occurred. Useful to derive further input from the answers.

On a non-interactive run nothing is prompted, and a missing required argument fails validation with the usual missing-arguments error. Since the prompted values are appended before the raw input is snapshotted, they are also visible to `interact()`, the unbound accessors, and anything forwarded through `$this->call(...)`.

### Non-Interactive Mode

Every modern command accepts `--no-interaction` / `-N` out of the box. When the flag is present, or when the command is otherwise non-interactive, the `interact()` hook is skipped entirely and the command proceeds straight to bind, validate, and `execute()`.

Programmatically, the state is exposed through two public methods on `AbstractCommand`:

- `isInteractive(): bool`: reports the current state.
- `setInteractive(bool $interactive): static`: pins the state, overriding both the CLI flag and TTY detection. Returns `$this` for chaining.

The resolved state follows this precedence:

1.  An explicit `setInteractive(bool)` call wins. Useful when a command must force a specific mode for safety.
2.  Otherwise, the CLI flag `--no-interaction` / `-N` forces non-interactive state.
3.  Otherwise, **STDIN** is probed: if it is not a TTY (piped input, cron, CI, `nohup`), the command is non-interactive.
4.  Otherwise, the command is interactive.

When the current command invokes another via `$this->call(...)`, the parent's non-interactive state is propagated to the sub-command automatically. A caller that passes `no-interaction` (or `N`) in the sub-command's `$options` wins over that propagation.

The propagation can be overridden with the `$noInteractionOverride` parameter of `call()`:

- `null` (default): propagate the parent's state.
- `true`: force the sub-command non-interactive regardless of the parent.
- `false`: remove any forwarded `--no-interaction` / `-N` from the child `$options` so the sub-command resolves its own state. Note: TTY detection can still downgrade the sub-command if STDIN is not a TTY.

## Restricting Command Execution

Sometimes a command should not run in a specific runtime context. For example, development-only commands may need to be blocked in the `production` environment.

You can override `isAvailable()` to decide at runtime whether the command may execute. By default, this method returns `true`.

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;

#[Command(name: 'dev:only', description: 'A dev only command', group: 'Dev')]
class DevOnly extends AbstractCommand
{
    protected function isAvailable(): bool
    {
        // Only allow this command in the development environment
        return ENVIRONMENT === 'development';
    }

    protected function execute(array $arguments, array $options): int
    {
        // ...

        return EXIT_SUCCESS;
    }
}
```

The availability check runs before `initialize()`, `interact()`, argument and option binding, validation, and `execute()`. If the command is not available, a `CommandNotAvailableException` is thrown immediately.

!!! note "Note"
    This method prevents execution of the command, but does not remove it from help output or command discovery.

## Inside execute()

`execute()` receives two arrays that mirror your declared definitions:

- `$arguments` contains every declared argument, bound to the provided value or the declared default.
- `$options` contains every declared option plus the framework defaults (`help`, `no-header`, `no-interaction`, `silent`, `quiet`, `verbose`), bound to the provided value or the declared default.

Within `execute()` itself, reaching into `$arguments` / `$options` directly is the simplest thing to do. The same data is also available through helpers so you can reach it from sub-methods without having to thread the two arrays through every signature:

- `getValidatedArgument(string $name)` / `getValidatedArguments()`
- `getValidatedOption(string $name)` / `getValidatedOptions()`
- `getUnboundArgument(int $index)` / `getUnboundArguments()`
- `getUnboundOption(string $name, ?array $options = null)` / `getUnboundOptions()`

The *validated* variants expose the bound values (what your definition says). The *unbound* variants expose the raw input snapshot — useful when forwarding the command to another command, or when your logic needs to know whether a flag was actually passed rather than whether it resolved to a default value.

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Input\Argument;
use CodeIgniter\CLI\Input\Option;

#[Command(name: 'app:demo', description: 'Demonstrates accessors.', group: 'App')]
class AppDemo extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument(new Argument(name: 'name', required: true))
            ->addOption(new Option(name: 'loud', negatable: true, default: false));
    }

    protected function execute(array $arguments, array $options): int
    {
        // Directly from the parameters:
        $name = $arguments['name'];

        // Or via the validated accessors — throws LogicException if the name
        // is not declared on this command:
        $name = $this->getValidatedArgument('name');
        $loud = $this->getValidatedOption('loud');

        // Need to know whether --loud was actually passed, not just whether it
        // resolved to its declared default? Use the unbound accessors:
        $loudWasPassed = $this->hasUnboundOption('loud');

        // ...

        return EXIT_SUCCESS;
    }
}
```

Accessors that take a name throw `LogicException` when the name is not declared on the command.

## Writing Output

`$this->getOutput()` returns the `Output` of the current run. It is the same instance written to by the static `CLI` methods, so the two can be mixed and the line breaks stay consistent. `Output` carries the writing methods: `write()`, `print()`, `error()`, `newLine()`, `beep()`, `clearScreen()`, and `color()`. Tables and progress bars are rendered by `Table` and `ProgressBar`:

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Table;

#[Command(name: 'app:report', description: 'Prints a report.', group: 'App')]
class AppReport extends AbstractCommand
{
    protected function execute(array $arguments, array $options): int
    {
        $output = $this->getOutput();

        $output->write('Generating the report...', 'yellow');

        $rows = $this->collectRows();

        if ($rows === []) {
            $output->error('Nothing to report.');

            return EXIT_ERROR;
        }

        $output->write((new Table($rows, ['ID', 'Title']))->render());
        $output->write(sprintf('%d rows.', count($rows)), 'green');

        return EXIT_SUCCESS;
    }

    /**
     * @return list<array{int, string}>
     */
    private function collectRows(): array
    {
        return [[1, 'First'], [2, 'Second']];
    }
}
```

By default the run writes to the output shared through `CLI::getOutput()`. Pass an `Output` as the third argument of `run()` to write somewhere else, for instance to a `MockInputOutput` in a test. See [CLI Library](../cli/cli_library.md) for the classes and [Testing CLI Output](../testing/cli.md#testing-cli-output) for testing.

### Writing at a Verbosity

**spark** sets the `Verbosity` of the output from `--silent`, `--quiet` / `-q`, and `--verbose` / `-v`, `-vv`, `-vvv` (see [Verbosity](spark_commands.md#verbosity)). `print()`, `write()`, and `newLine()` take the lowest level that shows the text as their last parameter, and `isVerbose()`, `isVeryVerbose()`, `isDebug()`, and `isQuiet()` guard larger blocks:

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Table;
use CodeIgniter\CLI\Verbosity;

#[Command(name: 'app:sync', description: 'Synchronizes the records.', group: 'App')]
class AppSync extends AbstractCommand
{
    protected function execute(array $arguments, array $options): int
    {
        $output = $this->getOutput();
        $rows   = [[1, 'First'], [2, 'Second']];

        $output->write('Connecting to the remote...', verbosity: Verbosity::Verbose);
        $output->write(sprintf('%d records synchronized.', count($rows)), 'green');

        if ($output->isVeryVerbose()) {
            $output->write((new Table($rows, ['ID', 'Title']))->render());
        }

        return EXIT_SUCCESS;
    }
}
```

`--quiet` drops every write at the normal level, so only errors and text written at `Verbosity::Quiet` remain, and it makes the run non-interactive.

A command run through `call()` writes at the level of the calling command. Use `callSilently()` to hide its output.

## Calling Another Command

Inside `execute()`, a modern command can invoke another modern command through `$this->call()`. `call()` must not be used from `configure()`, `initialize()`, or `interact()` — the current command has not been bound and validated yet at those points, and its unbound state has not been snapshotted.

```php
<?php

// Inside execute():

// Call another modern command with positional arguments...
$this->call('cache:clear', arguments: ['file']);

// ...and/or with options. Use `null` for a flag's value to model "the flag was passed".
$this->call('logs:clear', options: ['force' => null]);
```

The `$arguments` and `$options` you pass are interpreted as raw input — they go through bind and validate on the target command, just like a user invocation.

To forward the caller's own input through to the target command, pass `$this->getUnboundArguments()` and `$this->getUnboundOptions()` to `call()`:

```php
<?php

// Inside execute():

// Forward every unbound argument and option to the target command as-is.
$this->call('cache:clear', $this->getUnboundArguments(), $this->getUnboundOptions());
```

### Calling Silently

When a command delegates a step to another command but wants to emit its own consolidated message instead of letting the sub-command's output leak through, use `$this->callSilently()`:

```php
<?php

// Inside execute():

// Run `cache:clear` without leaking its own output; emit our own message instead.
$exitCode = $this->callSilently('cache:clear');

if ($exitCode === EXIT_SUCCESS) {
    $this->getOutput()->write('Cache cleared as part of deploy step.', 'green');
}
```

The sub-command's output is suppressed and `$noInteractionOverride` defaults to `true`, since a silenced sub-command cannot meaningfully prompt. Pass an explicit value to override.

While the sub-command runs, `CLI::getOutput()` and `$this->getOutput()` return an output backed by `NullInputOutput`, so everything written through either, including the sub-command's own `callSilently()` or `call()` sub-commands, is discarded. The previous output is restored afterwards.

## Usage Examples

The default usage line is built automatically from the command name and the declared argument list. You can append additional example lines by calling `addUsage()` inside `configure()`:

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Input\Argument;

#[Command(name: 'app:greet', description: 'Greets people.', group: 'App')]
class AppGreet extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument(new Argument(name: 'name', required: true))
            // The default usage line is always generated from the declared
            // arguments; these extra lines are appended after it.
            ->addUsage('app:greet Alice')
            ->addUsage('app:greet "Bob the Builder"');
    }

    protected function execute(array $arguments, array $options): int
    {
        // ...

        return EXIT_SUCCESS;
    }
}
```

In the `help <command>` or `<command> --help` output the default usage line is shown first, followed by each `addUsage()` entry in the order it was added.

## Rendering an Exception

If your command catches a `Throwable` and wants to produce the same formatted output the framework uses for uncaught exceptions, call `$this->renderThrowable($exception)`. The helper is safe to call from any command, and it will not disturb the currently shared request.

## Migrating From `BaseCommand`

The modern command system is a superset of the legacy `BaseCommand` API — the same capabilities are there, just expressed through an attribute and explicit definitions rather than class properties and ad-hoc lookups.

**Identity**

`protected $name`  
Moves to `name:` on the `#[Command]` attribute.

`protected $description`  
Moves to `description:` on the `#[Command]` attribute.

`protected $group`  
Moves to `group:` on the `#[Command]` attribute. An empty group skips the command at discovery.

**Input surface (declare inside** `configure()` **)**

`protected $usage`  
The default usage line is generated from the declared arguments. Register extras with `addUsage()`.

`protected $arguments`  
One `addArgument(new Argument(...))` call per argument.

`protected $options`  
One `addOption(new Option(...))` call per option. A long name is required; a legacy `-x`-style option becomes `new Option(name: 'something', shortcut: 'x')`.

**Runtime**

`run(array $params)`  
No longer the extension point — `run()` is `final` on `AbstractCommand` and drives the lifecycle itself. Move the body into `execute(array $arguments, array $options): int`, which must return an `EXIT_*` status.

`$params[0]`  
Use `$arguments['name']` or `$this->getValidatedArgument('name')`.

`$params['name']` / `CLI::getOption('name')`  
Use `$options['name']` or `$this->getValidatedOption('name')`. Call `$this->hasUnboundOption('name')` when you need to know whether the flag was actually passed.

`$this->call('other', $params)`  
Becomes `$this->call('other', $arguments, $options)`; only from inside `execute()`. To forward the caller's own raw input, pass `$this->getUnboundArguments()` and `$this->getUnboundOptions()`.

`$this->showError($e)`  
Becomes `$this->renderThrowable($e)`.

`CLI::write()`, `CLI::error()`, and the other writers  
Use the same methods on `$this->getOutput()`. See [Writing Output](#writing-output).

`showHelp()` override  
Gone. The built-in `help` command builds the help output itself from the declared arguments, options, and usages.

Prompting the user goes through `$this->getPrompt()`, and the idiomatic spot moves from `run()` to `interact()` so validation can see whatever the user provides interactively.

A typical `BaseCommand` implementation:

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

class Publish extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'app:publish';
    protected $description = 'Publishes something.';
    protected $usage       = 'app:publish [<target>] [options]';
    protected $arguments   = ['target' => 'What to publish.'];
    protected $options     = [
        '--force'   => 'Overwrite existing output.',
        '--dry-run' => 'Print the plan without writing anything.',
    ];

    public function run(array $params)
    {
        try {
            $target = $params[0] ?? CLI::prompt('What should I publish?', null, 'required');

            $this->publish($target);

            return EXIT_SUCCESS;
        } catch (Throwable $e) {
            $this->showError($e);

            return EXIT_ERROR;
        }
    }

    private function publish(string $target): void
    {
        // Option values come from CLI's global state, so the sub-method still
        // has to thread the positional $target through but can reach the
        // named options without extra parameters.
        $force  = CLI::getOption('force') !== null;
        $dryRun = CLI::getOption('dry-run') !== null;

        // ...
        unset($force, $dryRun);

        CLI::write(sprintf('publishing %s', $target), 'green');
    }
}
```

…becomes, as a modern command:

```php
<?php

namespace App\Commands;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Input\Argument;
use CodeIgniter\CLI\Input\Option;
use Throwable;

#[Command(name: 'app:publish', description: 'Publishes something.', group: 'App')]
class Publish extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument(new Argument(name: 'target', description: 'What to publish.', required: true))
            ->addOption(new Option(name: 'force', description: 'Overwrite existing output.'))
            ->addOption(new Option(name: 'dry-run', description: 'Print the plan without writing anything.'));
    }

    protected function interact(array &$arguments, array &$options): void
    {
        if ($arguments === []) {
            $arguments[] = $this->getPrompt()->ask('What should I publish?', null, 'required');
        }
    }

    protected function execute(array $arguments, array $options): int
    {
        try {
            $this->publish($arguments['target']);

            return EXIT_SUCCESS;
        } catch (Throwable $e) {
            $this->renderThrowable($e);

            return EXIT_ERROR;
        }
    }

    private function publish(string $target): void
    {
        // Unlike the legacy version, the sub-method can reach the validated
        // options through the helpers without threading $options through.
        $force  = $this->getValidatedOption('force') === true;
        $dryRun = $this->getValidatedOption('dry-run') === true;

        // ...
        unset($force, $dryRun);

        $this->getOutput()->write(sprintf('publishing %s', $target), 'green');
    }
}
```

Two behavioral changes are worth calling out explicitly:

- **Validated, not raw.** Arguments and options are parsed, defaulted, and validated before `execute()` runs. If a required argument is missing or a `requiresValue` option was passed without a value, the framework raises a typed exception and your command is never entered.
- **Exit codes are mandatory.** Legacy `run()` could return `null`. The modern `execute()` must return an integer; the framework emits a deprecation notice for any legacy command that still returns `null`.

## Coexistence With Legacy Commands

Legacy `BaseCommand` classes are still supported, and they are discovered alongside modern commands. If the same name is claimed by both a legacy and a modern command, the legacy one is invoked and a warning is printed to STDERR once at discovery time so you can rename or retire one of the two. Any aliases declared by the shadowed modern command are dropped at discovery, so they are neither listed nor runnable. Resolve the collision and the modern command, along with its aliases, becomes reachable again.

To detect the collision programmatically — for example, in a migration script that verifies the legacy copy was removed — the `Commands` runner exposes two read-only checks:

```php
<?php

use CodeIgniter\CLI\Commands;

/** @var Commands $commands */
$commands = service('commands');

if ($commands->hasLegacyCommand('foo') && $commands->hasModernCommand('foo')) {
    // Both registries claim the name; the legacy version will run.
}
```

The `help` command understands both styles — it delegates to the legacy `showHelp()` method for legacy commands and renders a structured view for modern ones.

A legacy command can call a modern one through `$this->call()`: integer-keyed params become positional arguments and string-keyed params become options, and the modern command validates them like any other input. Unknown options or extra arguments that a legacy target would have ignored are rejected.

!!! danger "Deprecated since version 4.8.0"
    `BaseCommand` is deprecated, and running a legacy command triggers a deprecation notice (see [Logging Deprecation Warnings](../general/errors.md#logging-deprecation-warnings)). New commands should be written against `AbstractCommand` from the start.

## AbstractCommand

The `AbstractCommand` class that all modern commands must extend exposes a number of utility methods you call from within your own command. Hooks like `configure()`, `initialize()`, `interact()`, and `execute()` are covered in the sections above and are not listed here.

## CodeIgniter\CLI

### AbstractCommand

#### getCommandRunner(): Commands

Returns the `Commands` runner the command was constructed with. Useful when you need to introspect other discovered commands (for instance, building a custom `list`-style command).

#### getName(): string

Returns the command name declared on the `#[Command]` attribute.

#### getDescription(): string

Returns the command description declared on the `#[Command]` attribute.

#### getGroup(): string

Returns the command group declared on the `#[Command]` attribute.

#### isHidden(): bool

Returns whether the `#[Command]` attribute marks the command as hidden. See [Hidden Commands](#hidden-commands).

#### isHeaderless(): bool

Returns whether the `#[Command]` attribute opts the command out of the header. See [Commands Without the Header](#commands-without-the-header).

#### getUsages(): array

Returns every usage line registered for the command — the default line built from the argument list, followed by each `addUsage()` entry in declaration order.

#### getArgumentsDefinition(): array

Returns the `Argument` value objects registered on this command, keyed by argument name and ordered by declaration.

#### getOptionsDefinition(): array

Returns the `Option` value objects registered on this command, keyed by option name.

#### getShortcuts(): array

Returns the shortcut-to-option-name map (for example `['f' => 'force']`). Empty when no shortcut is declared.

#### getNegations(): array

Returns the negation-to-option-name map (for example `['no-force' => 'force']`). Empty when no negatable option is declared.

#### addUsage(string $usage): static

- **Parameters**  
    - **$usage** `string` An extra usage example line.

Adds a usage example to the `help <command>` output. The default usage line derived from the argument list is always shown first.

#### addArgument(Argument $argument): static

- **Parameters**  
    - **$argument** `Argument` The argument definition to register.

Registers a positional argument. Call from `configure()`.

#### addOption(Option $option): static

- **Parameters**  
    - **$option** `Option` The option definition to register.

Registers an option. Call from `configure()`.

#### renderThrowable(Throwable $e): void

- **Parameters**  
    - **$e** `Throwable` The throwable to render.

Produces the same formatted output on STDERR that the framework uses for uncaught exceptions. Safe to call from any command.

#### hasArgument(string $name): bool

- **Parameters**  
    - **$name** `string` The argument name to look up.

Returns `true` if an argument with that name is declared on the command.

#### hasOption(string $name): bool

- **Parameters**  
    - **$name** `string` The option name to look up.

Returns `true` if an option with that name is declared on the command.

#### hasShortcut(string $shortcut): bool

- **Parameters**  
    - **$shortcut** `string` The shortcut character to look up.

Returns `true` if the shortcut is claimed by one of the declared options.

#### hasNegation(string $name): bool

- **Parameters**  
    - **$name** `string` The negation name (for example `no-force`) to look up.

Returns `true` if the negation is registered by one of the declared options.

#### isInteractive(): bool

Reports whether the command will prompt the user. See [Non-Interactive Mode](#non-interactive-mode) for the resolution order.

#### setInteractive(bool $interactive): static

- **Parameters**  
    - **$interactive** `bool` The state to pin.

- **Returns**: The current command instance for chaining.

Overrides both the `--no-interaction` / `-N` flag and TTY detection for this command instance. Typically called from `initialize()` or by an outer caller.

#### run(array $arguments, array $options[, ?Output $output = null]): int

- **Parameters**  
    - **$arguments** `array` The raw positional arguments parsed from the command line.
    - **$options** `array` The raw option map parsed from the command line.
    - **$output** `Output|null` The output to run against, or `null` for `CLI::getOutput()`.

- **Returns**: The exit code returned by `execute()`.

**Final.** Walks the command through `initialize()`, `interact()`, bind, validate, and finally `execute()`. The framework calls this on your behalf — you rarely invoke it directly, but you can when driving a command manually (for instance, from a test).

A passed `$output` replaces `CLI::getOutput()` for the duration of the run, so sub-commands called with `call` inherit it. The previous output is restored afterwards, also when the run throws, so an uncaught exception is rendered on the previous output.

#### getOutput(): Output

Returns the `Output` of the current run, which is the same instance written to by the static `CLI` methods. See [Writing Output](#writing-output).

#### getPrompt(): Prompt

Returns a `Prompt` reading through the output of the current run. See [The Classes Behind the Facade](cli_library.md#the-classes-behind-the-facade). Override it to return a `Prompt` subclass, which then also asks for missing arguments (see [Prompting for Missing Arguments](#prompting-for-missing-arguments)) and the generators' confirmation.

#### call(string $command[, array $arguments = [], array $options = [], ?bool $noInteractionOverride = null]): int

- **Parameters**  
    - **$command** `string` The name of the modern command to call.
    - **$arguments** `array` Positional arguments to forward.
    - **$options** `array` Options to forward, keyed by long name, shortcut, or negation.
    - **$noInteractionOverride** `bool|null` Override the sub-command's interactive state. `null` propagates the parent's state (default); `true` forces non-interactive; `false` removes any forwarded `--no-interaction` from `$options`.

- **Returns**: The exit code returned by the called command.

Invokes another modern command. The arguments and options go through bind and validate on the target command, just like a user invocation.

#### callSilently(string $command[, array $arguments = [], array $options = [], ?bool $noInteractionOverride = true]): int

- **Parameters**  
    - **$command** `string` The name of the modern command to call.
    - **$arguments** `array` Positional arguments to forward.
    - **$options** `array` Options to forward, keyed by long name, shortcut, or negation.
    - **$noInteractionOverride** `bool|null` See `call`. Defaults to `true`.

- **Returns**: The exit code returned by the called command.

Like `call`, but suppresses the sub-command's output.

#### getUnboundArguments(): array

Returns the raw, parsed positional arguments as passed to the command.

#### getUnboundArgument(int $index): string

- **Parameters**  
    - **$index** `int` The zero-based index of the argument to read.

Returns a single raw positional argument. Throws `LogicException` when the index does not exist.

#### getUnboundOptions(): array

Returns the raw, parsed option map, keyed by long name, shortcut, or negation.

#### getUnboundOption(string $name[, array|null $options = null]): array|string|null

- **Parameters**  
    - **$name** `string` The declared option name to look up.
    - **$options** `array|null` Raw option map to read from. Required inside `initialize()` and `interact()`, optional from `execute()` onwards.

Returns the raw value the option was given, resolving its shortcut and negation. Returns `null` when the option was not provided — callers can use the `??` operator to supply a fallback, or `hasUnboundOption` to disambiguate presence from a `null` value. Throws `LogicException` when the option is not declared on this command.

#### hasUnboundOption(string $name[, array|null $options = null]): bool

- **Parameters**  
    - **$name** `string` The declared option name to look up.
    - **$options** `array|null` Raw option map to read from. Required inside `initialize()` and `interact()`, optional from `execute()` onwards.

Returns `true` if the option was provided under its long name, shortcut, or negation. Throws `LogicException` when the option is not declared on this command.

#### getValidatedArguments(): array

Returns the bound and validated arguments, keyed by declared name.

#### getValidatedArgument(string $name): array|string

- **Parameters**  
    - **$name** `string` The declared argument name to read.

Returns the bound and validated value for a single argument. Throws `LogicException` when the argument is not declared on this command.

#### getValidatedOptions(): array

Returns the bound and validated options, keyed by declared name.

#### getValidatedOption(string $name): bool|array|string|null

- **Parameters**  
    - **$name** `string` The declared option name to read.

Returns the bound and validated value for a single option. Throws `LogicException` when the option is not declared on this command.
