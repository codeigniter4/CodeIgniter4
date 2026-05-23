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

#[Command(name: 'test:unavailable', description: 'Fixture command to test runtime availability checks.', group: 'Tests')]
final class UnavailableFixtureCommand extends AbstractCommand
{
    public static bool $initializeCalled = false;
    public static bool $interactCalled   = false;
    public static bool $executeCalled    = false;
    public static bool $available        = true;

    public static function reset(): void
    {
        self::$initializeCalled = false;
        self::$interactCalled   = false;
        self::$executeCalled    = false;
        self::$available        = true;
    }

    protected function isAvailable(): bool
    {
        return self::$available;
    }

    protected function initialize(array &$arguments, array &$options): void
    {
        self::$initializeCalled = true;
    }

    protected function interact(array &$arguments, array &$options): void
    {
        self::$interactCalled = true;
    }

    protected function execute(array $arguments, array $options): int
    {
        self::$executeCalled = true;

        return EXIT_SUCCESS;
    }
}
