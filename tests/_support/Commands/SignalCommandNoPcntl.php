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
 * Mock command that simulates missing PCNTL extension
 */
class SignalCommandNoPcntl extends SignalCommand
{
    /**
     * Override to simulate PCNTL not being available
     */
    protected function isPcntlAvailable(): bool
    {
        return false;
    }
}
