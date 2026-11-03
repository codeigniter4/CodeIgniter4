# Spark Commands

CodeIgniter ships with the official command **spark** and built-in commands.

## Running Commands

### Running via CLI

The commands are run from the command line, in the project root directory. The command file **spark** is provided to run any of the CLI commands.

#### Showing List of Commands

When **spark** is called without specifying a command, a simple help page is displayed that also provides a list of available commands and their descriptions, sorted by categories:

```console
php spark
```

##### spark list

`php spark` is exactly the same as the `list` command:

```console
php spark list
```

You may also use the `--simple` option to get a raw list of all available commands, sorted alphabetically:

```console
php spark list --simple
```

!!! note "Note"
    [Hidden commands](cli_modern_commands.md#hidden-commands) are left out of both lists, but still run when called by name.

#### Showing Help

You can get help about any CLI command using the `help` command as follows:

```console
php spark help db:seed
```

Since v4.3.0, you can also use the `--help` option instead of the `help` command:

```console
php spark db:seed --help
```

#### Running a Command

You should pass the name of the command as the first argument to run that command:

```console
php spark migrate
```

Some commands take additional arguments, which should be provided directly after the command, separated by spaces:

```console
php spark db:seed DevUserSeeder
```

For all of the commands CodeIgniter provides, if you do not provide the required arguments, you will be prompted for the information it needs to run correctly:

```console
php spark make:controller

Controller class name :
```

#### Suppressing Header Output

When you run a command, the header with the CodeIgniter version and the current time is output:

```console
php spark env

CodeIgniter v4.3.5 Command Line Tool - Server Time: 2023-06-16 12:45:31 UTC+00:00

Your environment is currently set as development.
```

You may always pass `--no-header` to suppress the header output, which is helpful for parsing results:

```console
php spark env --no-header

Your environment is currently set as development.
```

!!! note "Note"
    A modern command can also opt out of the header for every run. See [Commands Without the Header](cli_modern_commands.md#commands-without-the-header).

#### Verbosity

!!! success "Available from version 4.8.0"

`--quiet` / `-q` hides everything but errors, including the header, and makes a modern command non-interactive. `--silent` hides the errors too. `--verbose` / `-v` shows more detail, `-vv` even more, and `-vvv` everything, including debug messages. A command shows the extra detail only when it writes some, and an uncaught exception shows its backtrace from `-v` on.

The flags can be combined, and the quietest one wins: `--silent` over `-q`, and `-q` over any `-v`.

```console
php spark lang:find -v
php spark migrate -q
```

Without these flags, the `SHELL_VERBOSITY` environment variable sets the level: `-2` for no output at all, `-1` for quiet, `0` for normal, and `1` to `3` for the three verbose levels. It can also be set in the **.env** file. Processes started by the command inherit the level.

```console
SHELL_VERBOSITY=-1 php spark migrate
```

!!! note "Note"
    Legacy commands follow the level too, and can still read `-v` and `-q` as their own options.

#### Correcting a Mistyped Command

!!! success "Available from version 4.8.0"

When the command name is not found, **spark** lists the closest matches. On an interactive run it also offers to run one of them: a single match asks for a `y`/`n` confirmation, and several matches present a menu with a "none of these" entry, picked with the arrow keys (see [select() and multiselect()](cli_library.md#select-and-multiselect)). Pressing Enter picks the highlighted entry. Non-interactive runs (`--no-interaction` / `-N`, or piped input) only print the suggestions and exit with an error, as before.

```console
php spark cache:clea

Command "cache:clea" not found.

Run "cache:clear" instead? [y, n]: y

Cache cleared using the "file" driver.
```

### Calling Commands

Commands can also be run from within your own code. This is most often done within a controller for cronjob tasks, but they can be used at any time. You do this by using the `command()` function. This function is always available.

```php
<?php

echo command('make:migration TestMigration');
```

The only argument is a string containing the command to call and any parameters. This appears exactly as you would call it from the command line.

All output from the command is captured when it is not run from the command line. It is returned from the command so that you can choose to display it or not.

!!! note "Note"
    The `command()` function does not catch exceptions thrown while the command runs. [Modern commands](cli_modern_commands.md) in particular throw when their input fails validation, such as a missing required argument or an unknown option. Wrap the call in a `try`/`catch` block if you need to handle these cases.
