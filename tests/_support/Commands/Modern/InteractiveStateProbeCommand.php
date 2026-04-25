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

#[Command(name: 'test:probe', description: 'Fixture that records its interactive state so the caller can assert on it.', group: 'Fixtures')]
final class InteractiveStateProbeCommand extends AbstractCommand
{
    /**
     * Records whether `interact()` fired during the last run. This is a side-channel
     * for asserting on a child fixture created anonymously by `Commands::runCommand()`.
     */
    public static bool $interactCalled = false;

    public static ?bool $observedInteractive = null;

    public static function reset(): void
    {
        self::$interactCalled      = false;
        self::$observedInteractive = null;
    }

    protected function interact(array &$arguments, array &$options): void
    {
        self::$interactCalled = true;
    }

    protected function execute(array $arguments, array $options): int
    {
        self::$observedInteractive = $this->isInteractive();

        return EXIT_SUCCESS;
    }
}
