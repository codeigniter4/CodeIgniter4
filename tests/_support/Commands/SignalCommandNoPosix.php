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

namespace Tests\Support\Commands;

/**
 * Mock command that simulates missing POSIX extension
 */
class SignalCommandNoPosix extends SignalCommand
{
    /**
     * Override to simulate POSIX not being available
     */
    protected function isPosixAvailable(): bool
    {
        return false;
    }
}
