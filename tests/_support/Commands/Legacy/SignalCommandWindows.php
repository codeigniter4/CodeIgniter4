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

namespace Tests\Support\Commands\Legacy;

/**
 * Mock command that simulates Windows: no PCNTL or POSIX extension, but a console
 * control handler. The handler is recorded instead of being installed,
 * as `sapi_windows_set_ctrl_handler()` only exists on Windows.
 */
class SignalCommandWindows extends SignalCommand
{
    /**
     * Whether the simulated `sapi_windows_set_ctrl_handler()` succeeds.
     */
    public bool $ctrlHandlerSucceeds = true;

    /**
     * The handler the command tried to add (`true`) or remove (`false`), in order.
     *
     * @var list<array{handler: callable, add: bool}>
     */
    public array $ctrlHandlerCalls = [];

    protected function isPcntlAvailable(): bool
    {
        return false;
    }

    protected function isPosixAvailable(): bool
    {
        return false;
    }

    protected function isWindowsCtrlHandlerAvailable(): bool
    {
        return true;
    }

    protected function setWindowsCtrlHandler(callable $handler, bool $add): bool
    {
        $this->ctrlHandlerCalls[] = ['handler' => $handler, 'add' => $add];

        return $this->ctrlHandlerSucceeds;
    }

    public function testHandleWindowsCtrlEvent(int $event): void
    {
        $this->handleWindowsCtrlEvent($event);
    }
}
