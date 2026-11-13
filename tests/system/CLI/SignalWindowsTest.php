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

use Closure;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem;
use Tests\Support\Commands\Legacy\SignalCommandNoPcntl;
use Tests\Support\Commands\Legacy\SignalCommandWindows;

/**
 * Signal handling through the Windows console control handler.
 *
 * It does not need PCNTL, nor Windows: the handler is simulated, and the SIG*
 * constants are not used, as they do not exist on Windows.
 *
 * @internal
 */
#[Group('Others')]
final class SignalWindowsTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private const SIGINT_NUMBER = 2;

    private SignalCommandWindows $command;

    protected function setUp(): void
    {
        $this->resetServices();
        parent::setUp();

        $this->command = new SignalCommandWindows(service('logger'), service('commands'));
    }

    protected function tearDown(): void
    {
        CLI::reset();

        parent::tearDown();
    }

    public function testRegisterAddsTheCtrlHandler(): void
    {
        $this->command->testRegisterSignals([]);

        $this->assertCount(1, $this->command->ctrlHandlerCalls);
        $this->assertTrue($this->command->ctrlHandlerCalls[0]['add']);
        $this->assertTrue($this->command->testHasSignals());
        $this->assertSame([self::SIGINT_NUMBER], $this->command->testGetSignals());
    }

    public function testTheCtrlHandlerCanBeCalledFromAnyScope(): void
    {
        $this->command->testRegisterSignals([]);

        // PHP calls the handler from whichever scope it interrupts. A callable
        // array naming the protected method would not be accessible from here.
        $handler = $this->command->ctrlHandlerCalls[0]['handler'];

        $this->assertInstanceOf(Closure::class, $handler);

        $handler(0);

        $this->assertTrue($this->command->testShouldTerminate());
    }

    public function testRegisterWithTheInterruptSignal(): void
    {
        $this->command->testRegisterSignals([self::SIGINT_NUMBER], [self::SIGINT_NUMBER => 'testCustomHandler']);

        $this->assertCount(1, $this->command->ctrlHandlerCalls);
        $this->assertSame([self::SIGINT_NUMBER], $this->command->testGetSignals());

        $state = $this->command->testGetProcessState();
        $this->assertSame(1, $state['explicit_mappings']);
        $this->assertSame(['SIGINT'], $state['registered_signals_names']);
    }

    public function testRegisterIgnoresOtherSignals(): void
    {
        $this->command->testRegisterSignals([15, 1]);

        $this->assertSame([], $this->command->ctrlHandlerCalls);
        $this->assertFalse($this->command->testHasSignals());
    }

    public function testRegisterTwiceAddsTheHandlerOnce(): void
    {
        $this->command->testRegisterSignals([]);
        $this->command->testRegisterSignals([self::SIGINT_NUMBER]);

        $this->assertCount(1, $this->command->ctrlHandlerCalls);
        $this->assertSame([self::SIGINT_NUMBER], $this->command->testGetSignals());
    }

    public function testRegisterReportsAFailureToAddTheHandler(): void
    {
        $this->resetStreamFilterBuffer();

        $this->command->ctrlHandlerSucceeds = false;
        $this->command->testRegisterSignals([]);

        $this->assertFalse($this->command->testHasSignals());
        $this->assertStringContainsString('SIGINT', $this->getStreamFilterBuffer());
    }

    public function testCtrlEventRequestsTermination(): void
    {
        $this->command->testRegisterSignals([]);

        $this->assertTrue($this->command->testIsRunning());

        $this->command->testHandleWindowsCtrlEvent(0);

        $this->assertFalse($this->command->testIsRunning());
        $this->assertTrue($this->command->testShouldTerminate());
    }

    public function testCtrlBreakEventRequestsTermination(): void
    {
        $this->command->testRegisterSignals([]);
        $this->command->testHandleWindowsCtrlEvent(1);

        $this->assertTrue($this->command->testShouldTerminate());
    }

    public function testCtrlEventCallsTheMappedHandler(): void
    {
        $this->command->testRegisterSignals([], [self::SIGINT_NUMBER => 'testCustomHandler']);

        $this->command->testHandleWindowsCtrlEvent(0);

        $this->assertTrue($this->command->customHandlerCalled);
        $this->assertFalse($this->command->fallbackHandlerCalled);
        $this->assertSame(self::SIGINT_NUMBER, $this->command->lastSignalReceived);
    }

    public function testCtrlEventFallsBackToOnInterruption(): void
    {
        $this->command->testRegisterSignals([]);

        $this->command->testHandleWindowsCtrlEvent(0);

        $this->assertFalse($this->command->customHandlerCalled);
        $this->assertTrue($this->command->fallbackHandlerCalled);
        $this->assertSame(self::SIGINT_NUMBER, $this->command->lastSignalReceived);
    }

    public function testUnregisterRemovesTheCtrlHandler(): void
    {
        $this->command->testRegisterSignals([], [self::SIGINT_NUMBER => 'testCustomHandler']);
        $this->command->testUnregisterSignals();

        $this->assertCount(3, $this->command->ctrlHandlerCalls);

        // The handler is detached...
        $this->assertFalse($this->command->ctrlHandlerCalls[1]['add']);
        $this->assertInstanceOf(Closure::class, $this->command->ctrlHandlerCalls[1]['handler']);

        // ...then PHP is told to drop the callable it keeps, which would retain the command.
        $this->assertFalse($this->command->ctrlHandlerCalls[2]['add']);
        $this->assertNull($this->command->ctrlHandlerCalls[2]['handler']);

        $this->assertFalse($this->command->testHasSignals());
        $this->assertSame([], $this->command->testGetSignals());
        $this->assertSame(0, $this->command->testGetProcessState()['explicit_mappings']);
    }

    public function testUnregisterDoesNotReleaseTheCallableWhenRemovalFails(): void
    {
        $this->command->testRegisterSignals([]);

        $this->command->ctrlHandlerSucceeds = false;
        $this->command->testUnregisterSignals();

        $this->assertCount(2, $this->command->ctrlHandlerCalls);
        $this->assertFalse($this->command->ctrlHandlerCalls[1]['add']);
        $this->assertNotNull($this->command->ctrlHandlerCalls[1]['handler']);
    }

    public function testUnregisterWithoutRegisteringDoesNothing(): void
    {
        $this->command->testUnregisterSignals();

        $this->assertSame([], $this->command->ctrlHandlerCalls);
    }

    public function testRegisterAgainAfterUnregister(): void
    {
        $this->command->testRegisterSignals([]);
        $this->command->testUnregisterSignals();
        $this->command->testRegisterSignals([]);

        $this->assertCount(4, $this->command->ctrlHandlerCalls);
        $this->assertTrue($this->command->ctrlHandlerCalls[3]['add']);
        $this->assertTrue($this->command->testHasSignals());
    }

    public function testBlockingSignalsIsANoOp(): void
    {
        $result = $this->command->testWithSignalsBlocked(function (): string {
            $this->assertFalse($this->command->testSignalsBlocked());

            return 'done';
        });

        $this->assertSame('done', $result);
    }

    public function testSignalNamesWithoutPcntl(): void
    {
        $this->assertSame('SIGINT', $this->command->testGetSignalName(self::SIGINT_NUMBER));
        $this->assertSame('Signal 15', $this->command->testGetSignalName(15));
    }

    public function testProcessStateOnWindows(): void
    {
        $this->command->testRegisterSignals([]);

        $state = $this->command->testGetProcessState();

        $this->assertFalse($state['pcntl_available']);
        $this->assertSame(1, $state['registered_signals']);
        $this->assertArrayNotHasKey('session_id', $state);
    }

    public function testNothingIsRegisteredWithoutAnyHandlerSupport(): void
    {
        // Neither PCNTL nor a console control handler: Unix without PCNTL, or Windows
        // before sapi_windows_set_ctrl_handler() existed.
        $command = new SignalCommandNoPcntl(service('logger'), service('commands'));
        $command->testRegisterSignals([]);

        $this->assertFalse($command->testHasSignals());
    }

    #[RequiresOperatingSystem('Linux|Darwin')]
    public function testSetCtrlHandlerFailsWithoutTheNativeFunction(): void
    {
        // Called directly, without checking isWindowsCtrlHandlerAvailable() first.
        $command = new SignalCommandNoPcntl(service('logger'), service('commands'));

        $this->assertFalse($command->testSetWindowsCtrlHandler(static function (int $event): void {}, true));
        $this->assertFalse($command->testSetWindowsCtrlHandler(null, false));
    }
}
