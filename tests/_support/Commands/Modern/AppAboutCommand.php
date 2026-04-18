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

namespace Tests\Support\Commands\Modern;

use CodeIgniter\CLI\AbstractCommand;
use CodeIgniter\CLI\Attributes\Command;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Input\Argument;
use CodeIgniter\CLI\Input\Option;
use CodeIgniter\CodeIgniter;
use CodeIgniter\Exceptions\RuntimeException;

#[Command(name: 'app:about', description: 'Displays basic application information.', group: 'Fixtures')]
final class AppAboutCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument(new Argument(name: 'required', description: 'Unused required argument.', required: true))
            ->addArgument(new Argument(name: 'optional', description: 'Unused optional argument.', default: 'val'))
            ->addArgument(new Argument(name: 'array', description: 'Unused array argument.', isArray: true, default: ['a', 'b']))
            ->addOption(new Option(name: 'foo', shortcut: 'f', description: 'Option that requires a value.', requiresValue: true, default: 'qux'))
            ->addOption(new Option(name: 'bar', shortcut: 'a', description: 'Option that optionally accepts a value.', acceptsValue: true))
            ->addOption(new Option(name: 'baz', shortcut: 'b', description: 'Option that allows multiple values.', requiresValue: true, isArray: true, default: ['a']))
            ->addOption(new Option(name: 'quux', description: 'Negatable option.', negatable: true, default: false))
            ->addUsage('app:about required-value');
    }

    protected function execute(array $arguments, array $options): int
    {
        CLI::write(sprintf('CodeIgniter Version: %s', CLI::color(CodeIgniter::CI_VERSION, 'red')));

        return EXIT_SUCCESS;
    }

    public function bomb(): int
    {
        try {
            CLI::color('test', 'white', 'Background');

            return EXIT_SUCCESS;
        } catch (RuntimeException $e) {
            $this->renderThrowable($e);

            return EXIT_ERROR;
        }
    }

    public function helpMe(): int
    {
        return $this->call('help');
    }

    /**
     * @param array<string, list<string|null>|string|null>|null $options
     */
    public function callHasUnboundOption(string $name, ?array $options = null): bool
    {
        return $this->hasUnboundOption($name, $options);
    }

    /**
     * @param array<string, list<string|null>|string|null>|null $options
     *
     * @return list<string|null>|string|null
     */
    public function callGetUnboundOption(string $name, ?array $options = null): array|string|null
    {
        return $this->getUnboundOption($name, $options);
    }

    /**
     * @return list<string>
     */
    public function callGetUnboundArguments(): array
    {
        return $this->getUnboundArguments();
    }

    public function callGetUnboundArgument(int $index): string
    {
        return $this->getUnboundArgument($index);
    }

    /**
     * @return array<string, list<string|null>|string|null>
     */
    public function callGetUnboundOptions(): array
    {
        return $this->getUnboundOptions();
    }

    /**
     * @return array<string, list<string>|string>
     */
    public function callGetValidatedArguments(): array
    {
        return $this->getValidatedArguments();
    }

    /**
     * @return list<string>|string
     */
    public function callGetValidatedArgument(string $name): array|string
    {
        return $this->getValidatedArgument($name);
    }

    /**
     * @return array<string, bool|list<string>|string|null>
     */
    public function callGetValidatedOptions(): array
    {
        return $this->getValidatedOptions();
    }

    /**
     * @return bool|list<string>|string|null
     */
    public function callGetValidatedOption(string $name): array|bool|string|null
    {
        return $this->getValidatedOption($name);
    }
}
