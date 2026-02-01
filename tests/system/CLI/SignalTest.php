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

namespace CodeIgniter\CLI;

use CodeIgniter\Log\Logger;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Commands\SignalCommand;
use Tests\Support\Commands\SignalCommandNoPcntl;
use Tests\Support\Commands\SignalCommandNoPosix;

/**
 * @internal
 */
#[Group('Others')]
final class SignalTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private SignalCommand $command;
    private Logger $logger;

    protected function setUp(): void
    {
        if (is_windows()) {
            $this->markTestSkipped('Signal handling is not supported on Windows.');
        }

        if (! extension_loaded('pcntl')) {
            $this->markTestSkipped('PCNTL extension is required for signal handling tests.');
        }

        if (! extension_loaded('posix')) {
            $this->markTestSkipped('POSIX extension is required for signal handling tests.');
        }

        $this->resetServices();
        parent::setUp();

        $this->logger  = service('logger');
        $this->command = new SignalCommand($this->logger, service('commands'));
    }

    public function testSignalRegistration(): void
    {
        $this->command->testRegisterSignals([SIGTERM, SIGINT], [SIGTERM => 'customTermHandler']);

        $this->assertTrue($this->command->testHasSignals());
        $this->assertSame([SIGTERM, SIGINT], $this->command->testGetSignals());

        $state = $this->command->testGetProcessState();
        $this->assertSame(2, $state['registered_signals']);
        $this->assertSame(['SIGTERM', 'SIGINT'], $state['registered_signals_names']);
        $this->assertSame(1, $state['explicit_mappings']);
    }

    public function testSignalRegistrationWithoutPcntl(): void
    {
        $command = new SignalCommandNoPcntl($this->logger, service('commands'));

        $command->testRegisterSignals([SIGTERM, SIGINT]);

        $this->assertFalse($command->testHasSignals());
        $this->assertSame([], $command->testGetSignals());
    }

    public function testSignalRegistrationFiltersPosixDependentSignals(): void
    {
        $this->resetStreamFilterBuffer();

        $commandNoPosix = new SignalCommandNoPosix($this->logger, service('commands'));

        $commandNoPosix->testRegisterSignals([SIGTERM, SIGTSTP, SIGCONT], [SIGTSTP => 'onPause']);

        $output = $this->getStreamFilterBuffer();
        $this->assertStringContainsString(
            'SIGTSTP/SIGCONT handling requires POSIX extension',
            $output,
        );

        $this->assertSame([SIGTERM], $commandNoPosix->testGetSignals());
    }

    public function testProcessState(): void
    {
        $this->command->testRegisterSignals([SIGTERM, SIGINT, SIGUSR1]);

        $state = $this->command->testGetProcessState();

        // Process identification
        $this->assertArrayHasKey('pid', $state);
        $this->assertIsInt($state['pid']);
        $this->assertTrue($state['running']);

        // Signal handling status
        $this->assertTrue($state['pcntl_available']);
        $this->assertSame(3, $state['registered_signals']);
        $this->assertSame(['SIGTERM', 'SIGINT', 'SIGUSR1'], $state['registered_signals_names']);
        $this->assertFalse($state['signals_blocked']);
        $this->assertSame(0, $state['explicit_mappings']);

        // System resources
        $this->assertArrayHasKey('memory_usage_mb', $state);
        $this->assertArrayHasKey('memory_peak_mb', $state);
        $this->assertIsFloat($state['memory_usage_mb']);
        $this->assertIsFloat($state['memory_peak_mb']);
    }

    public function testProcessStateIncludesPosixInfo(): void
    {
        $state = $this->command->testGetProcessState();

        $this->assertArrayHasKey('session_id', $state);
        $this->assertArrayHasKey('process_group', $state);
        $this->assertArrayHasKey('has_controlling_terminal', $state);

        $this->assertIsInt($state['session_id']);
        $this->assertIsInt($state['process_group']);
        $this->assertIsBool($state['has_controlling_terminal']);
    }

    public function testRunningState(): void
    {
        $this->assertTrue($this->command->testIsRunning());
        $this->assertFalse($this->command->testShouldTerminate());

        $this->command->testRequestTermination();

        $this->assertFalse($this->command->testIsRunning());
        $this->assertTrue($this->command->testShouldTerminate());
    }

    public function testSignalBlocking(): void
    {
        $this->assertFalse($this->command->testSignalsBlocked());

        $result = $this->command->testWithSignalsBlocked(function (): string {
            $this->assertTrue($this->command->testSignalsBlocked());

            return 'test_result';
        });

        $this->assertSame('test_result', $result);
        $this->assertFalse($this->command->testSignalsBlocked());
    }

    public function testSignalMethodMapping(): void
    {
        $this->command->testMapSignal(SIGUSR1, 'customHandler');

        $state = $this->command->testGetProcessState();
        $this->assertSame(1, $state['explicit_mappings']);
    }

    public function testResetState(): void
    {
        $this->command->testRequestTermination();
        $this->command->testWithSignalsBlocked(static fn (): bool => true);

        $this->assertTrue($this->command->testShouldTerminate());

        $this->command->testResetState();

        $this->assertFalse($this->command->testShouldTerminate());
        $this->assertFalse($this->command->testSignalsBlocked());
    }

    public function testSignalNameGeneration(): void
    {
        $this->assertSame('SIGTERM', $this->command->testGetSignalName(SIGTERM));
        $this->assertSame('SIGINT', $this->command->testGetSignalName(SIGINT));
        $this->assertSame('SIGUSR1', $this->command->testGetSignalName(SIGUSR1));
        $this->assertSame('Signal 999', $this->command->testGetSignalName(999));
    }

    public function testUnregisterSignals(): void
    {
        $this->command->testRegisterSignals([SIGTERM, SIGINT]);
        $this->assertTrue($this->command->testHasSignals());

        $this->command->testUnregisterSignals();
        $this->assertFalse($this->command->testHasSignals());
        $this->assertSame([], $this->command->testGetSignals());
    }

    public function testCustomSignalHandlerCall(): void
    {
        $this->command->testRegisterSignals([SIGTERM], [SIGTERM => 'testCustomHandler']);

        $this->command->testCallSignalHandler(SIGTERM);

        $this->assertTrue($this->command->customHandlerCalled);
        $this->assertSame(SIGTERM, $this->command->lastSignalReceived);
    }

    public function testFallbackSignalHandlerCall(): void
    {
        $this->command->testRegisterSignals([SIGINT]); // No explicit mapping

        $this->command->testCallSignalHandler(SIGINT);

        $this->assertTrue($this->command->fallbackHandlerCalled);
        $this->assertSame(SIGINT, $this->command->lastSignalReceived);
    }

    public function testSignalHandlerUpdatesRunningState(): void
    {
        $this->command->testRegisterSignals([SIGTERM]);

        $this->assertTrue($this->command->testIsRunning());

        $this->command->testCallSignalHandler(SIGTERM);

        $this->assertFalse($this->command->testIsRunning());
        $this->assertTrue($this->command->testShouldTerminate());
    }
}
