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
use CodeIgniter\CLI\Input\Argument;
use CodeIgniter\CLI\PromptsForMissingInputInterface;

#[Command(name: 'test:prompting', description: 'Fixture that prompts for its missing required arguments.', group: 'Fixtures')]
final class PromptingFixtureCommand extends AbstractCommand implements PromptsForMissingInputInterface
{
    public static bool $afterHookCalled = false;

    /**
     * @var array<string, list<string>|string>
     */
    public static array $receivedArguments = [];

    public static function reset(): void
    {
        self::$afterHookCalled   = false;
        self::$receivedArguments = [];
    }

    protected function configure(): void
    {
        $this
            ->addArgument(new Argument(name: 'first', required: true))
            ->addArgument(new Argument(name: 'second', required: true));
    }

    protected function getArgumentPromptLabels(): array
    {
        return ['second' => 'What is the second value?'] + parent::getArgumentPromptLabels();
    }

    protected function afterPrompting(array &$arguments, array &$options): void
    {
        self::$afterHookCalled = true;
    }

    protected function execute(array $arguments, array $options): int
    {
        self::$receivedArguments = $arguments;

        return EXIT_SUCCESS;
    }
}
