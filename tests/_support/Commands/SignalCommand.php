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

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\SignalTrait;

/**
 * Mock command class that uses SignalTrait for testing
 */
class SignalCommand extends BaseCommand
{
    use SignalTrait;

    protected $name                    = 'test:signal';
    protected $description             = 'Test signal handling';
    public bool $customHandlerCalled   = false;
    public bool $fallbackHandlerCalled = false;
    public ?int $lastSignalReceived    = null;

    public function run(array $params): int
    {
        return 0;
    }

    // Test method to trigger custom handler
    public function testCustomHandler(int $signal): void
    {
        $this->customHandlerCalled = true;
        $this->lastSignalReceived  = $signal;
    }

    // Fallback handler for testing
    public function onInterruption(int $signal): void
    {
        $this->fallbackHandlerCalled = true;
        $this->lastSignalReceived    = $signal;
    }

    // Public test methods to access protected trait methods
    public function testRegisterSignals(array $signals, array $methodMap = []): void
    {
        $this->registerSignals($signals, $methodMap);
    }

    public function testCallSignalHandler(int $signal): void
    {
        $this->handleSignal($signal);
    }

    public function testIsRunning(): bool
    {
        return $this->isRunning();
    }

    public function testShouldTerminate(): bool
    {
        return $this->shouldTerminate();
    }

    public function testRequestTermination(): void
    {
        $this->requestTermination();
    }

    public function testResetState(): void
    {
        $this->resetState();
    }

    public function testWithSignalsBlocked(callable $operation)
    {
        return $this->withSignalsBlocked($operation);
    }

    public function testSignalsBlocked(): bool
    {
        return $this->signalsBlocked();
    }

    public function testMapSignal(int $signal, string $method): void
    {
        $this->mapSignal($signal, $method);
    }

    public function testGetSignalName(int $signal): string
    {
        return $this->getSignalName($signal);
    }

    public function testUnregisterSignals(): void
    {
        $this->unregisterSignals();
    }

    public function testHasSignals(): bool
    {
        return $this->hasSignals();
    }

    public function testGetSignals(): array
    {
        return $this->getSignals();
    }

    public function testGetProcessState(): array
    {
        return $this->getProcessState();
    }
}
