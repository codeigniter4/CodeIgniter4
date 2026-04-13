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
use CodeIgniter\Events\Events;
use CodeIgniter\Log\Logger;
use ReflectionClass;
use ReflectionException;

/**
 * Core functionality for running, listing, etc commands.
 *
 * @phpstan-type commands_list array<string, array{'class': class-string<BaseCommand>, 'file': string, 'group': string,'description': string}>
 */
class Commands
{
    /**
     * The found commands.
     *
     * @var commands_list
     */
    protected $commands = [];

    /**
     * Logger instance.
     *
     * @var Logger
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param Logger|null $logger
     */
    public function __construct($logger = null)
    {
        $this->logger = $logger ?? service('logger');
        $this->discoverCommands();
    }

    /**
     * Runs a command given
     *
     * @param array<int|string, string|null> $params
     *
     * @return int Exit code
     */
    public function run(string $command, array $params)
    {
        if (! $this->verifyCommand($command, $this->commands)) {
            return EXIT_ERROR;
        }

        $className = $this->commands[$command]['class'];
        $class     = new $className($this->logger, $this);

        Events::trigger('pre_command');

        $exitCode = $class->run($params);

        Events::trigger('post_command');

        if (! is_int($exitCode)) {
            @trigger_error(sprintf(
                'Since v4.8.0, commands must return an integer exit code. Last command "%s" exited with %s. Defaulting to EXIT_SUCCESS.',
                $command,
                get_debug_type($exitCode),
            ), E_USER_DEPRECATED);
            $exitCode = EXIT_SUCCESS;
        }

        return $exitCode;
    }

    /**
     * Provide access to the list of commands.
     *
     * @return commands_list
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * Discovers all commands in the framework and within user code,
     * and collects instances of them to work with.
     *
     * @return void
     */
    public function discoverCommands()
    {
        if ($this->commands !== []) {
            return;
        }

        /** @var FileLocatorInterface */
        $locator = service('locator');
        $files   = $locator->listFiles('Commands/');

        if ($files === []) {
            return;
        }

        foreach ($files as $file) {
            /** @var class-string<BaseCommand>|false */
            $className = $locator->findQualifiedNameFromPath($file);

            if ($className === false || ! class_exists($className)) {
                continue;
            }

            try {
                $class = new ReflectionClass($className);

                if (! $class->isInstantiable() || ! $class->isSubclassOf(BaseCommand::class)) {
                    continue;
                }

                $class = new $className($this->logger, $this);

                if ($class->group !== null && ! isset($this->commands[$class->name])) {
                    $this->commands[$class->name] = [
                        'class'       => $className,
                        'file'        => $file,
                        'group'       => $class->group,
                        'description' => $class->description,
                    ];
                }

                unset($class);
            } catch (ReflectionException $e) {
                $this->logger->error($e->getMessage());
            }
        }

        asort($this->commands);
    }

    /**
     * Verifies if the command being sought is found
     * in the commands list.
     *
     * @param commands_list $commands
     */
    public function verifyCommand(string $command, array $commands): bool
    {
        if (isset($commands[$command])) {
            return true;
        }

        $message = lang('CLI.commandNotFound', [$command]);

        $alternatives = $this->getCommandAlternatives($command, $commands);

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
     * Finds alternative of `$name` among collection
     * of commands.
     *
     * @param commands_list $collection
     *
     * @return list<string>
     */
    protected function getCommandAlternatives(string $name, array $collection): array
    {
        /** @var array<string, int> */
        $alternatives = [];

        /** @var string $commandName */
        foreach (array_keys($collection) as $commandName) {
            $lev = levenshtein($name, $commandName);

            if ($lev <= strlen($commandName) / 3 || str_contains($commandName, $name)) {
                $alternatives[$commandName] = $lev;
            }
        }

        ksort($alternatives, SORT_NATURAL | SORT_FLAG_CASE);

        return array_keys($alternatives);
    }
}
