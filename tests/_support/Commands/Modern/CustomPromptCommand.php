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
use CodeIgniter\CLI\Prompt;
use CodeIgniter\CLI\PromptsForMissingInputInterface;

#[Command(
    name: 'test:custom-prompt',
    description: 'Fixture that answers its prompts through a Prompt subclass.',
    group: 'Fixtures',
)]
final class CustomPromptCommand extends AbstractCommand implements PromptsForMissingInputInterface
{
    /**
     * @var array<string, list<string>|string>
     */
    public static array $receivedArguments = [];

    protected function configure(): void
    {
        $this->addArgument(new Argument(name: 'name', required: true));
    }

    protected function getPrompt(): Prompt
    {
        return new class ($this->getOutput()) extends Prompt {
            public function ask(string $field, array|string|null $options = null, array|string|null $validation = null): string
            {
                return 'from the custom prompt';
            }
        };
    }

    protected function execute(array $arguments, array $options): int
    {
        self::$receivedArguments = $arguments;

        return EXIT_SUCCESS;
    }
}
