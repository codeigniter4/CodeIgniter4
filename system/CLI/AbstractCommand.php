<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\CLI;

use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Exceptions\ArgumentCountMismatchException;
use CodeIgniter\CLI\Exceptions\InvalidArgumentDefinitionException;
use CodeIgniter\CLI\Exceptions\InvalidOptionDefinitionException;
use CodeIgniter\CLI\Exceptions\OptionValueMismatchException;
use CodeIgniter\CLI\Exceptions\UnknownOptionException;
use CodeIgniter\CLI\Input\Argument;
use CodeIgniter\CLI\Input\Option;
use CodeIgniter\Exceptions\LogicException;
use CodeIgniter\HTTP\CLIRequest;
use Config\App;
use Config\Services;
use ReflectionClass;
use Throwable;

/**
 * Base class for all modern spark commands.
 *
 * Each command should extend this class and implement the `execute()` method.
 */
abstract class AbstractCommand
{
    private readonly string $name;
    private readonly string $description;
    private readonly string $group;

    /**
     * @var list<non-empty-string>
     */
    private array $usages = [];

    /**
     * @var array<non-empty-string, Argument>
     */
    private array $argumentsDefinition = [];

    /**
     * @var array<non-empty-string, Option>
     */
    private array $optionsDefinition = [];

    /**
     * Map of shortcut character to the option name that declared it.
     *
     * @var array<non-empty-string, non-empty-string>
     */
    private array $shortcuts = [];

    /**
     * Map of negated name to the option name it negates.
     *
     * @var array<non-empty-string, non-empty-string>
     */
    private array $negations = [];

    /**
     * Cached list of required argument names, populated as definitions are added.
     *
     * @var list<non-empty-string>
     */
    private array $requiredArguments = [];

    /**
     * Cache of resolved `Command` attributes keyed by class name.
     *
     * @var array<class-string<self>, Command>
     */
    private static array $commandAttributeCache = [];

    /**
     * The unbound arguments that can be passed to other commands when called via the `call()` method.
     *
     * @var list<non-empty-string>
     */
    private array $unboundArguments = [];

    /**
     * The unbound options that can be passed to child commands when called via the `call()` method.
     *
     * @var array<non-empty-string, list<string|null>|string|null>
     */
    private array $unboundOptions = [];

    /**
     * The validated arguments after binding, which will be passed to the `execute()` method.
     *
     * @var array<non-empty-string, list<string>|string>
     */
    private array $validatedArguments = [];

    /**
     * The validated options after binding, which will be passed to the `execute()` method.
     *
     * @var array<non-empty-string, bool|list<string>|string|null>
     */
    private array $validatedOptions = [];

    private ?string $lastOptionalArgument = null;
    private ?string $lastArrayArgument    = null;

    /**
     * @throws InvalidArgumentDefinitionException
     * @throws InvalidOptionDefinitionException
     * @throws LogicException
     */
    public function __construct(private readonly Commands $commands)
    {
        $attribute = $this->getCommandAttribute();

        $this->name        = $attribute->name;
        $this->description = $attribute->description;
        $this->group       = $attribute->group;

        $this->configure();
        $this->provideDefaultOptions();

        $this->createDefaultUsage();
    }

    public function getCommandRunner(): Commands
    {
        return $this->commands;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @return list<non-empty-string>
     */
    public function getUsages(): array
    {
        return $this->usages;
    }

    /**
     * @return array<non-empty-string, Argument>
     */
    public function getArgumentsDefinition(): array
    {
        return $this->argumentsDefinition;
    }

    /**
     * @return array<non-empty-string, Option>
     */
    public function getOptionsDefinition(): array
    {
        return $this->optionsDefinition;
    }

    /**
     * Returns the map of shortcut character to its owning option name.
     *
     * @return array<non-empty-string, non-empty-string>
     */
    public function getShortcuts(): array
    {
        return $this->shortcuts;
    }

    /**
     * Returns the map of negated name to the option name it negates.
     *
     * @return array<non-empty-string, non-empty-string>
     */
    public function getNegations(): array
    {
        return $this->negations;
    }

    /**
     * Appends a usage example aside from the default usage.
     *
     * @param non-empty-string $usage
     */
    public function addUsage(string $usage): static
    {
        $this->usages[] = $usage;

        return $this;
    }

    /**
     * Adds an argument definition to the command.
     *
     * @throws InvalidArgumentDefinitionException
     */
    public function addArgument(Argument $argument): static
    {
        $name = $argument->name;

        if ($this->hasArgument($name)) {
            throw new InvalidArgumentDefinitionException(lang('Commands.duplicateArgument', [$name]));
        }

        if ($this->lastArrayArgument !== null) {
            throw new InvalidArgumentDefinitionException(lang('Commands.argumentAfterArrayArgument', [$name, $this->lastArrayArgument]));
        }

        if ($argument->required && $this->lastOptionalArgument !== null) {
            throw new InvalidArgumentDefinitionException(lang('Commands.requiredArgumentAfterOptionalArgument', [$name, $this->lastOptionalArgument]));
        }

        if ($argument->isArray) {
            $this->lastArrayArgument = $name;
        }

        if ($argument->required) {
            $this->requiredArguments[] = $name;
        } else {
            $this->lastOptionalArgument = $name;
        }

        $this->argumentsDefinition[$name] = $argument;

        return $this;
    }

    /**
     * Adds an option definition to the command.
     *
     * @throws InvalidOptionDefinitionException
     */
    public function addOption(Option $option): static
    {
        $name = $option->name;

        if ($this->hasOption($name)) {
            throw new InvalidOptionDefinitionException(lang('Commands.duplicateOption', [$name]));
        }

        if ($this->hasNegation($name)) {
            throw new InvalidOptionDefinitionException(lang('Commands.optionClashesWithExistingNegation', [$name, $this->negations[$name]]));
        }

        if ($option->shortcut !== null && $this->hasShortcut($option->shortcut)) {
            throw new InvalidOptionDefinitionException(lang('Commands.duplicateShortcut', [$option->shortcut, $name, $this->shortcuts[$option->shortcut]]));
        }

        if ($option->negation !== null && $this->hasOption($option->negation)) {
            throw new InvalidOptionDefinitionException(lang('Commands.negatableOptionNegationExists', [$name]));
        }

        if ($option->shortcut !== null) {
            $this->shortcuts[$option->shortcut] = $name;
        }

        if ($option->negation !== null) {
            $this->negations[$option->negation] = $name;
        }

        $this->optionsDefinition[$name] = $option;

        return $this;
    }

    /**
     * Renders the given `Throwable`.
     *
     * This is usually not needed to be called directly as the `Throwable` will be automatically rendered by the framework when it is thrown,
     * but it can be useful to call this method directly when you want to render a `Throwable` that is caught by the command itself.
     */
    public function renderThrowable(Throwable $e): void
    {
        // The exception handler picks a renderer based on the shared request
        // instance. Ensure it is a CLIRequest; if the current shared request is
        // not, swap it temporarily and restore it afterwards so other code paths
        // do not observe our mutation.
        $previous = Services::get('request');
        $swapped  = false;

        if (! $previous instanceof CLIRequest) {
            Services::createRequest(config(App::class), true);
            $swapped = true;
        }

        try {
            service('exceptions')->exceptionHandler($e);
        } finally {
            if ($swapped) {
                Services::override('request', $previous);
            }
        }
    }

    /**
     * Checks if the command has an argument defined with the given name.
     */
    public function hasArgument(string $name): bool
    {
        return array_key_exists($name, $this->argumentsDefinition);
    }

    /**
     * Checks if the command has an option defined with the given name.
     */
    public function hasOption(string $name): bool
    {
        return array_key_exists($name, $this->optionsDefinition);
    }

    /**
     * Checks if the command has a shortcut defined with the given name.
     */
    public function hasShortcut(string $shortcut): bool
    {
        return array_key_exists($shortcut, $this->shortcuts);
    }

    /**
     * Checks if the command has a negation defined with the given name.
     */
    public function hasNegation(string $name): bool
    {
        return array_key_exists($name, $this->negations);
    }

    /**
     * Runs the command.
     *
     * The lifecycle is:
     *
     *   1. {@see initialize()} and {@see interact()} are handed the raw parsed
     *      input by reference, in that order. Both can mutate the tokens before
     *      the framework interprets them against the declared definitions.
     *   2. The post-hook input is snapshotted into `$unboundArguments` and
     *      `$unboundOptions` so the unbound accessors can report the tokens
     *      carried into binding (as opposed to what defaults resolved to).
     *      Any mutations performed in `initialize()` or `interact()` are
     *      therefore reflected in the snapshot.
     *   3. {@see bind()} maps the raw tokens onto the declared arguments and
     *      options, applying defaults and coercing flag/negation values.
     *   4. {@see validate()} rejects the bound result if it violates any of the
     *      declarations — missing required argument, unknown option, value/flag
     *      mismatches, and so on.
     *   5. The bound-and-validated values are snapshotted into
     *      `$validatedArguments` / `$validatedOptions` and then passed to
     *      {@see execute()}, whose integer return is the command's exit code.
     *
     * @param list<string>                                 $arguments Parsed arguments from command line.
     * @param array<string, list<string|null>|string|null> $options   Parsed options from command line.
     *
     * @throws ArgumentCountMismatchException
     * @throws LogicException
     * @throws OptionValueMismatchException
     * @throws UnknownOptionException
     */
    final public function run(array $arguments, array $options): int
    {
        $this->initialize($arguments, $options);

        // @todo add interactive mode check
        $this->interact($arguments, $options);

        $this->unboundArguments = $arguments;
        $this->unboundOptions   = $options;

        [$boundArguments, $boundOptions] = $this->bind($arguments, $options);

        $this->validate($boundArguments, $boundOptions);

        $this->validatedArguments = $boundArguments;
        $this->validatedOptions   = $boundOptions;

        return $this->execute($boundArguments, $boundOptions);
    }

    /**
     * Configures the command's arguments and options definitions.
     *
     * This method is called from the constructor of the command.
     *
     * @throws InvalidArgumentDefinitionException
     * @throws InvalidOptionDefinitionException
     */
    protected function configure(): void
    {
    }

    /**
     * Initializes a command before the arguments and options are bound to their definitions.
     *
     * This is especially useful for commands that calls another commands, and needs to adjust the
     * arguments and options before calling the other command.
     *
     * @param list<string>                            $arguments Parsed arguments from command line.
     * @param array<string, list<string>|string|null> $options   Parsed options from command line.
     */
    protected function initialize(array &$arguments, array &$options): void
    {
    }

    /**
     * Interacts with the user before executing the command. This should only be called if the command is being run
     * in interactive mode, which is the default when running commands from the command line.
     *
     * This is especially useful for commands that needs to ask the user for confirmation before executing the command.
     * It can also be used to ask the user for additional information that is not provided in the command line arguments and options.
     *
     * @param list<string>                            $arguments Parsed arguments from command line.
     * @param array<string, list<string>|string|null> $options   Parsed options from command line.
     */
    protected function interact(array &$arguments, array &$options): void
    {
    }

    /**
     * Executes the command with the bound arguments and options.
     *
     * Validation of the bound arguments and options is done before this method is called.
     * As such, this method should not throw any exceptions. All exceptions should be rendered
     * with a non-zero exit code.
     *
     * @param array<string, list<string>|string>           $arguments Bound arguments using the command's arguments definition.
     * @param array<string, bool|list<string>|string|null> $options   Bound options using the command's options definition.
     */
    abstract protected function execute(array $arguments, array $options): int;

    /**
     * Calls another command from the current command.
     *
     * @param list<string>                            $arguments Parsed arguments from command line.
     * @param array<string, list<string>|string|null> $options   Parsed options from command line.
     */
    protected function call(string $command, array $arguments = [], array $options = []): int
    {
        return $this->commands->runCommand($command, $arguments, $options);
    }

    /**
     * Gets the unbound arguments that can be passed to other commands when called via the `call()` method.
     *
     * @return list<string>
     */
    protected function getUnboundArguments(): array
    {
        return $this->unboundArguments;
    }

    /**
     * Gets the unbound argument at the given index.
     *
     * @throws LogicException
     */
    protected function getUnboundArgument(int $index): string
    {
        if (! array_key_exists($index, $this->unboundArguments)) {
            throw new LogicException(sprintf('Unbound argument at index "%d" does not exist.', $index));
        }

        return $this->unboundArguments[$index];
    }

    /**
     * Gets the unbound options that can be passed to other commands when called via the `call()` method.
     *
     * @return array<string, list<string|null>|string|null>
     */
    protected function getUnboundOptions(): array
    {
        return $this->unboundOptions;
    }

    /**
     * Reads the raw (unbound) value of the option with the given declared name,
     * resolving through its shortcut and negation. Returns `null` when the
     * option was not provided under any of those aliases.
     *
     * Inside {@see interact()}, pass the `$options` parameter explicitly because
     * the instance state is not yet populated at that point. Elsewhere, omit
     * `$options` to read from the instance state.
     *
     * @param array<string, list<string|null>|string|null>|null $options
     *
     * @return list<string|null>|string|null
     *
     * @throws LogicException
     */
    protected function getUnboundOption(string $name, ?array $options = null): array|string|null
    {
        $this->assertOptionIsDefined($name);

        $options ??= $this->unboundOptions;

        if (array_key_exists($name, $options)) {
            return $options[$name];
        }

        $definition = $this->optionsDefinition[$name];

        if ($definition->shortcut !== null && array_key_exists($definition->shortcut, $options)) {
            return $options[$definition->shortcut];
        }

        if ($definition->negation !== null && array_key_exists($definition->negation, $options)) {
            return $options[$definition->negation];
        }

        return null;
    }

    /**
     * Returns whether the option with the given declared name was provided in
     * the raw (unbound) input — under its long name, shortcut, or negation.
     *
     * Inside {@see interact()}, pass the `$options` parameter explicitly; elsewhere
     * omit it to read from instance state.
     *
     * @param array<string, list<string|null>|string|null>|null $options
     *
     * @throws LogicException
     */
    protected function hasUnboundOption(string $name, ?array $options = null): bool
    {
        $this->assertOptionIsDefined($name);

        $options ??= $this->unboundOptions;

        if (array_key_exists($name, $options)) {
            return true;
        }

        $definition = $this->optionsDefinition[$name];

        if ($definition->shortcut !== null && array_key_exists($definition->shortcut, $options)) {
            return true;
        }

        return $definition->negation !== null && array_key_exists($definition->negation, $options);
    }

    /**
     * Gets the validated arguments after binding and validation.
     *
     * @return array<string, list<string>|string>
     */
    protected function getValidatedArguments(): array
    {
        return $this->validatedArguments;
    }

    /**
     * Gets the validated argument with the given name.
     *
     * @return list<string>|string
     *
     * @throws LogicException
     */
    protected function getValidatedArgument(string $name): array|string
    {
        if (! array_key_exists($name, $this->validatedArguments)) {
            throw new LogicException(sprintf('Validated argument with name "%s" does not exist.', $name));
        }

        return $this->validatedArguments[$name];
    }

    /**
     * Gets the validated options after binding and validation.
     *
     * @return array<string, bool|list<string>|string|null>
     */
    protected function getValidatedOptions(): array
    {
        return $this->validatedOptions;
    }

    /**
     * Gets the validated option with the given name.
     *
     * @return bool|list<string>|string|null
     *
     * @throws LogicException
     */
    protected function getValidatedOption(string $name): array|bool|string|null
    {
        if (! array_key_exists($name, $this->validatedOptions)) {
            throw new LogicException(sprintf('Validated option with name "%s" does not exist.', $name));
        }

        return $this->validatedOptions[$name];
    }

    protected function provideDefaultOptions(): void
    {
        $this
            ->addOption(new Option(name: 'help', shortcut: 'h', description: 'Display help for the given command.'))
            ->addOption(new Option(name: 'no-header', description: 'Do not display the banner when running the command.'));
    }

    /**
     * Binds the given raw arguments and options to the command's arguments and options
     * definitions, and returns the bound arguments and options.
     *
     * @param list<string>                                 $arguments Parsed arguments from command line.
     * @param array<string, list<string|null>|string|null> $options   Parsed options from command line.
     *
     * @return array{
     *   0: array<string, list<string>|string>,
     *   1: array<string, bool|list<bool|string>|string|null>,
     * }
     */
    private function bind(array $arguments, array $options): array
    {
        $boundArguments = [];
        $boundOptions   = [];

        // 1. Arguments are position-based, so we will bind them in the order they are defined
        //    as well as the order they are given in the command line.
        foreach ($this->argumentsDefinition as $name => $definition) {
            if ($definition->isArray) {
                if ($arguments !== []) {
                    $boundArguments[$name] = array_values($arguments);

                    $arguments = [];
                } elseif (! $definition->required) {
                    $boundArguments[$name] = $definition->default;
                }
            } elseif ($definition->required) {
                $argument = array_shift($arguments);

                if ($argument === null) {
                    continue; // Missing required argument. To skip for validation to catch later.
                }

                $boundArguments[$name] = $argument;
            } else {
                $boundArguments[$name] = array_shift($arguments) ?? $definition->default;
            }
        }

        // 2. If there are still arguments left that are not defined, we will mark them as extraneous.
        if ($arguments !== []) {
            $boundArguments['extra_arguments'] = array_values($arguments);
        }

        // 3. Options are name-based, so we will bind them by their names, shortcuts, and negations.
        //    Passed flag options will be set to `true`, otherwise, they will be set to `false`.
        //    Options that accept values will be set to the value passed or their default value if not passed.
        //    Negatable options will be set to `false` if the negation is passed.
        foreach ($this->optionsDefinition as $name => $definition) {
            if (array_key_exists($name, $options)) {
                $boundOptions[$name] = $options[$name];
                unset($options[$name]);
            } elseif ($definition->shortcut !== null && array_key_exists($definition->shortcut, $options)) {
                $boundOptions[$name] = $options[$definition->shortcut];
                unset($options[$definition->shortcut]);
            } elseif ($definition->negation !== null && array_key_exists($definition->negation, $options)) {
                $boundOptions[$name] = $options[$definition->negation] ?? false;

                if (is_array($boundOptions[$name])) {
                    // Edge case: passing a negated option multiple times should normalize to false
                    $boundOptions[$name] = array_map(static fn (mixed $v): mixed => $v ?? false, $boundOptions[$name]);
                }

                unset($options[$definition->negation]);
            } else {
                $boundOptions[$name] = $definition->default;
            }

            if ($definition->isArray && ! is_array($boundOptions[$name])) {
                $boundOptions[$name] = [$boundOptions[$name]];
            } elseif (! $definition->acceptsValue && ! $definition->negatable) {
                $boundOptions[$name] ??= true;
            } elseif ($definition->negatable) {
                if (is_array($boundOptions[$name])) {
                    $boundOptions[$name] = array_map(static fn (mixed $v): mixed => $v ?? true, $boundOptions[$name]);
                } else {
                    $boundOptions[$name] ??= true;
                }
            }
        }

        // 4. If there are still options left that are not defined, we will mark them as extraneous.
        foreach ($options as $name => $value) {
            if ($this->hasShortcut($name)) {
                // This scenario can happen when the command has an array option with a shortcut,
                // and the shortcut is used alongside the long name, causing it to be not bound
                // in the previous loop. The leftover shortcut value can itself be an array when
                // the shortcut was passed multiple times, so merge arrays and append scalars.
                $option = $this->shortcuts[$name];
                $values = is_array($value) ? $value : [$value];

                if (array_key_exists($option, $boundOptions) && is_array($boundOptions[$option])) {
                    $boundOptions[$option] = [...$boundOptions[$option], ...$values];
                } else {
                    $boundOptions[$option] = [$boundOptions[$option], ...$values];
                }

                continue;
            }

            if ($this->hasNegation($name)) {
                // This scenario can happen when the command has a negatable option,
                // and both the option and its negation are used, causing the negation
                // to be not bound in the previous loop. The leftover negation value can
                // be scalar (including a string when the negation was passed with a value)
                // or an array — normalise to an array before mapping null → false.
                $option = $this->negations[$name];
                $values = is_array($value) ? $value : [$value];
                $values = array_map(static fn (mixed $v): mixed => $v ?? false, $values);

                if (! is_array($boundOptions[$option])) {
                    $boundOptions[$option] = [$boundOptions[$option]];
                }

                $boundOptions[$option] = [...$boundOptions[$option], ...$values];

                continue;
            }

            $boundOptions['extra_options'] ??= [];
            $boundOptions['extra_options'][$name] = $value;
        }

        return [$boundArguments, $boundOptions];
    }

    /**
     * Validates the bound arguments and options.
     *
     * @param array<string, list<string>|string>                     $arguments Bound arguments using the command's arguments definition.
     * @param array<string, bool|list<bool|string|null>|string|null> $options   Bound options using the command's options definition.
     *
     * @throws ArgumentCountMismatchException
     * @throws LogicException
     * @throws OptionValueMismatchException
     * @throws UnknownOptionException
     */
    private function validate(array $arguments, array $options): void
    {
        $this->validateArguments($arguments);

        foreach ($this->optionsDefinition as $name => $definition) {
            $this->validateOption($name, $definition, $options[$name]);
        }

        if (array_key_exists('extra_options', $options)) {
            throw new UnknownOptionException(lang('Commands.unknownOptions', [
                count($options['extra_options']),
                $this->name,
                implode(', ', array_map(
                    static fn (string $key): string => strlen($key) === 1 ? sprintf('-%s', $key) : sprintf('--%s', $key),
                    array_keys($options['extra_options']),
                )),
            ]));
        }
    }

    /**
     * @param array<string, list<string>|string> $arguments
     *
     * @throws ArgumentCountMismatchException
     */
    private function validateArguments(array $arguments): void
    {
        if ($this->argumentsDefinition === [] && $arguments !== []) {
            assert(array_key_exists('extra_arguments', $arguments));

            throw new ArgumentCountMismatchException(lang('Commands.noArgumentsExpected', [
                $this->name,
                implode('", "', $arguments['extra_arguments']),
            ]));
        }

        if (array_diff($this->requiredArguments, array_keys($arguments)) !== []) {
            throw new ArgumentCountMismatchException(lang('Commands.missingRequiredArguments', [
                $this->name,
                count($this->requiredArguments),
                implode(', ', $this->requiredArguments),
            ]));
        }

        if (array_key_exists('extra_arguments', $arguments)) {
            throw new ArgumentCountMismatchException(lang('Commands.tooManyArguments', [
                $this->name,
                count($arguments['extra_arguments']),
                implode('", "', $arguments['extra_arguments']),
            ]));
        }
    }

    /**
     * @param bool|list<bool|string|null>|string|null $value
     *
     * @throws LogicException
     * @throws OptionValueMismatchException
     */
    private function validateOption(string $name, Option $definition, array|bool|string|null $value): void
    {
        if (! $definition->acceptsValue && ! $definition->negatable) {
            if (is_array($value) && ! $definition->isArray) {
                throw new LogicException(lang('Commands.flagOptionPassedMultipleTimes', [$name]));
            }

            if (! is_bool($value)) {
                throw new OptionValueMismatchException(lang('Commands.optionNotAcceptingValue', [$name]));
            }
        }

        if ($definition->acceptsValue && ! $definition->isArray && is_array($value)) {
            throw new OptionValueMismatchException(lang('Commands.nonArrayOptionWithArrayValue', [$name]));
        }

        if ($definition->requiresValue) {
            $elements = is_array($value) ? $value : [$value];

            foreach ($elements as $element) {
                if (! is_string($element)) {
                    throw new OptionValueMismatchException(lang('Commands.optionRequiresValue', [$name]));
                }
            }
        }

        if (! $definition->negatable || is_bool($value)) {
            return;
        }

        $this->validateNegatableOption($name, $definition, $value);
    }

    /**
     * @param list<bool|string|null>|string|null $value
     *
     * @throws LogicException
     * @throws OptionValueMismatchException
     */
    private function validateNegatableOption(string $name, Option $definition, array|string|null $value): void
    {
        if (! is_array($value)) {
            if (array_key_exists($name, $this->unboundOptions)) {
                throw new OptionValueMismatchException(lang('Commands.negatableOptionNoValue', [$name]));
            }

            throw new OptionValueMismatchException(lang('Commands.negatedOptionNoValue', [$definition->negation]));
        }

        // Both forms appearing together is the primary user mistake; flag it
        // regardless of whether either form carried a value.
        if (
            array_key_exists($name, $this->unboundOptions)
            && array_key_exists($definition->negation, $this->unboundOptions)
        ) {
            throw new LogicException(lang('Commands.negatableOptionWithNegation', [$name, $definition->negation]));
        }

        if (array_key_exists($name, $this->unboundOptions)) {
            throw new OptionValueMismatchException(lang('Commands.negatableOptionPassedMultipleTimes', [$name]));
        }

        throw new OptionValueMismatchException(lang('Commands.negatedOptionPassedMultipleTimes', [$definition->negation]));
    }

    /**
     * @throws LogicException
     */
    private function assertOptionIsDefined(string $name): void
    {
        if (! $this->hasOption($name)) {
            throw new LogicException(sprintf('Option "%s" is not defined on this command.', $name));
        }
    }

    /**
     * @throws LogicException
     */
    private function getCommandAttribute(): Command
    {
        $class = static::class;

        if (array_key_exists($class, self::$commandAttributeCache)) {
            return self::$commandAttributeCache[$class];
        }

        $attribute = (new ReflectionClass($this))->getAttributes(Command::class)[0]
            ?? throw new LogicException(lang('Commands.missingCommandAttribute', [$class, Command::class]));

        self::$commandAttributeCache[$class] = $attribute->newInstance();

        return self::$commandAttributeCache[$class];
    }

    /**
     * Create a default usage based on docopt style.
     *
     * @see http://docopt.org/
     */
    private function createDefaultUsage(): void
    {
        $usage = [$this->name];

        if ($this->optionsDefinition !== []) {
            $usage[] = '[options]';
        }

        if ($this->argumentsDefinition !== []) {
            $usage[] = '[--]';

            foreach ($this->argumentsDefinition as $name => $definition) {
                $usage[] = sprintf(
                    '%s<%s>%s%s',
                    $definition->required ? '' : '[',
                    $name,
                    $definition->isArray ? '...' : '',
                    $definition->required ? '' : ']',
                );
            }
        }

        array_unshift($this->usages, implode(' ', $usage));
    }
}
