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

#[Command(name: 'test:parent-interact', description: 'Fixture that delegates to test:probe via call().', group: 'Fixtures')]
final class ParentCallsInteractFixtureCommand extends AbstractCommand
{
    /**
     * Forwarded verbatim as the `$noInteractionOverride` argument of `call()`.
     * `null` leaves the default propagation behavior in place.
     */
    public ?bool $childNoInteractionOverride = null;

    /**
     * Forwarded verbatim as the `$options` argument of `call()`. Lets tests
     * exercise the resolver's caller-provided-flag code paths.
     *
     * @var array<string, list<string|null>|string|null>
     */
    public array $childOptions = [];

    protected function execute(array $arguments, array $options): int
    {
        return $this->call('test:probe', options: $this->childOptions, noInteractionOverride: $this->childNoInteractionOverride);
    }
}
