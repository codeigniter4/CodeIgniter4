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

use CodeIgniter\Autoloader\FileLocatorInterface;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\Exceptions\CommandNotFoundException;
use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\LogicException;
use CodeIgniter\Log\Logger;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;

/**
 * Command discovery and execution class.
 *
 * @phpstan-type legacy_commands array<string, array{class: class-string<BaseCommand>, file: string, group: string, description: string}>
 * @phpstan-type modern_commands array<string, array{class: class-string<AbstractCommand>, file: string, group: string, description: string}>
 */
class Commands
{
    /**
     * @var legacy_commands
     */
    protected $commands = [];

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Discovered modern commands keyed by command name. Kept `private` so
     * subclasses do not mutate the registry directly; use {@see getModernCommands()}.
     *
     * @var modern_commands
     */
    private array $modernCommands = [];

    /**
     * Guards {@see discoverCommands()} from re-scanning the filesystem on repeat calls.
     */
    private bool $discovered = false;

    /**
     * @param Logger|null $logger
     */
    public function __construct($logger = null)
    {
        $this->logger = $logger ?? service('logger');
        $this->discoverCommands();
    }

    /**
     * Runs a legacy command.
     *
     * @deprecated 4.8.0 Use {@see runLegacy()} instead.
     *
     * @param array<int|string, string|null> $params
     *
     * @return int
     */
    public function run(string $command, array $params)
    {
        @trigger_error(sprintf(
            'Since v4.8.0, "%s()" is deprecated. Use "%s::runLegacy()" instead.',
            __METHOD__,
            self::class,
        ), E_USER_DEPRECATED);

        return $this->runLegacy($command, $params);
    }

    /**
     * Runs a legacy command.
     *
     * @param array<int|string, string|null> $params
     */
    public function runLegacy(string $command, array $params): int
    {
        if (! $this->verifyCommand($command)) {
            return EXIT_ERROR;
        }

        Events::trigger('pre_command');

        $exitCode = $this->getCommand($command, legacy: true)->run($params);

        Events::trigger('post_command');

        if (! is_int($exitCode)) {
            @trigger_error(sprintf(
                'Since v4.8.0, commands must return an integer exit code. Last command "%s" exited with %s. Defaulting to EXIT_SUCCESS.',
                $command,
                get_debug_type($exitCode),
            ), E_USER_DEPRECATED);
            $exitCode = EXIT_SUCCESS; // @codeCoverageIgnore
        }

        return $exitCode;
    }

    /**
     * Runs a modern command.
     *
     * @param list<string>                                 $arguments
     * @param array<string, list<string|null>|string|null> $options
     */
    public function runCommand(string $command, array $arguments, array $options): int
    {
        if (! $this->verifyCommand($command, legacy: false)) {
            return EXIT_ERROR;
        }

        Events::trigger('pre_command');

        $exitCode = $this->getCommand($command, legacy: false)->run($arguments, $options);

        Events::trigger('post_command');

        return $exitCode;
    }

    /**
     * Provide access to the list of legacy commands.
     *
     * @return legacy_commands
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * Provide access to the list of modern commands.
     *
     * @return modern_commands
     */
    public function getModernCommands(): array
    {
        return $this->modernCommands;
    }

    /**
     * Checks if a legacy command with the given name has been discovered.
     */
    public function hasLegacyCommand(string $name): bool
    {
        return array_key_exists($name, $this->commands);
    }

    /**
     * Checks if a modern command with the given name has been discovered.
     *
     * A name present in both registries signals a collision; legacy wins
     * at runtime. Callers can combine this with {@see hasLegacyCommand()}
     * to detect that case.
     */
    public function hasModernCommand(string $name): bool
    {
        return array_key_exists($name, $this->modernCommands);
    }

    /**
     * @return ($legacy is true ? BaseCommand : AbstractCommand)
     *
     * @throws CommandNotFoundException
     */
    public function getCommand(string $command, bool $legacy = false): AbstractCommand|BaseCommand
    {
        if ($legacy && isset($this->commands[$command])) {
            $className = $this->commands[$command]['class'];

            return new $className($this->logger, $this);
        }

        if (! $legacy && isset($this->modernCommands[$command])) {
            $className = $this->modernCommands[$command]['class'];

            return new $className($this);
        }

        throw new CommandNotFoundException($command);
    }

    /**
     * Discovers all commands in the framework and within user code,
     * and collects instances of them to work with.
     *
     * @return void
     */
    public function discoverCommands()
    {
        if ($this->discovered) {
            return;
        }

        $this->discovered = true;

        /** @var FileLocatorInterface $locator */
        $locator = service('locator');

        foreach ($locator->listFiles('Commands/') as $file) {
            $className = $locator->findQualifiedNameFromPath($file);

            if ($className === false || ! class_exists($className)) {
                continue;
            }

            $class = new ReflectionClass($className);

            if (! $class->isInstantiable()) {
                continue;
            }

            if ($class->isSubclassOf(BaseCommand::class)) {
                $this->registerLegacyCommand($class, $file);
            } elseif ($class->isSubclassOf(AbstractCommand::class)) {
                $this->registerModernCommand($class, $file);
            }
        }

        ksort($this->commands);
        ksort($this->modernCommands);

        foreach (array_keys(array_intersect_key($this->commands, $this->modernCommands)) as $name) {
            CLI::write(
                CLI::wrap(
                    lang('Commands.duplicateCommandName', [
                        $name,
                        $this->commands[$name]['class'],
                        $this->modernCommands[$name]['class'],
                    ]),
                ),
                'yellow',
            );
        }
    }

    /**
     * Verifies if the command being sought is found in the commands list.
     *
     * @param legacy_commands $commands (no longer used)
     */
    public function verifyCommand(string $command, array $commands = [], bool $legacy = true): bool
    {
        if ($commands !== []) {
            @trigger_error(sprintf('Since v4.8.0, the $commands parameter of %s() is no longer used.', __METHOD__), E_USER_DEPRECATED);
        }

        if (isset($this->commands[$command]) && $legacy) {
            return true;
        }

        if (isset($this->modernCommands[$command]) && ! $legacy) {
            return true;
        }

        $message = lang('CLI.commandNotFound', [$command]);

        $alternatives = $this->getCommandAlternatives($command);

        if ($alternatives !== []) {
            $message = sprintf(
                "%s\n\n%s\n    %s",
                $message,
                count($alternatives) === 1 ? lang('CLI.altCommandSingular') : lang('CLI.altCommandPlural'),
                implode("\n    ", $alternatives),
            );
        }

        CLI::error($message);

        return false;
    }

    /**
     * Finds alternative of `$name` across both legacy and modern commands.
     *
     * @param legacy_commands $collection (no longer used)
     *
     * @return list<string>
     */
    protected function getCommandAlternatives(string $name, array $collection = []): array
    {
        if ($collection !== []) {
            @trigger_error(sprintf('Since v4.8.0, the $collection parameter of %s() is no longer used.', __METHOD__), E_USER_DEPRECATED);
        }

        /** @var array<string, int> */
        $alternatives = [];

        foreach (array_keys($this->commands + $this->modernCommands) as $commandName) {
            $lev = levenshtein($name, $commandName);

            if ($lev <= strlen($commandName) / 3 || str_contains($commandName, $name)) {
                $alternatives[$commandName] = $lev;
            }
        }

        ksort($alternatives, SORT_NATURAL | SORT_FLAG_CASE);

        return array_keys($alternatives);
    }

    /**
     * @param ReflectionClass<BaseCommand> $class
     */
    private function registerLegacyCommand(ReflectionClass $class, string $file): void
    {
        try {
            /** @var BaseCommand $instance */
            $instance = $class->newInstance($this->logger, $this);
        } catch (ReflectionException $e) {
            $this->logger->error($e->getMessage());

            return;
        }

        if ($instance->group === null || isset($this->commands[$instance->name])) {
            return;
        }

        $this->commands[$instance->name] = [
            'class'       => $class->getName(),
            'file'        => $file,
            'group'       => $instance->group,
            'description' => $instance->description,
        ];
    }

    /**
     * @param ReflectionClass<AbstractCommand> $class
     */
    private function registerModernCommand(ReflectionClass $class, string $file): void
    {
        /** @var list<ReflectionAttribute<Command>> $attributes */
        $attributes = $class->getAttributes(Command::class);

        if ($attributes === []) {
            return;
        }

        try {
            $attribute = $attributes[0]->newInstance();
        } catch (LogicException $e) {
            $this->logger->error($e->getMessage());

            return;
        }

        if ($attribute->group === '' || isset($this->modernCommands[$attribute->name])) {
            return;
        }

        $this->modernCommands[$attribute->name] = [
            'class'       => $class->getName(),
            'file'        => $file,
            'group'       => $attribute->group,
            'description' => $attribute->description,
        ];
    }
}
