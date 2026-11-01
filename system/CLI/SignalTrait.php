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

/**
 * Signal Trait
 *
 * Provides signal handling capabilities for CLI commands.
 *
 * On Unix it requires the PCNTL extension. On Windows, where PCNTL does not
 * exist, only Ctrl+C and Ctrl+Break are supported, through
 * `sapi_windows_set_ctrl_handler()`. Both are reported as SIGINT (2).
 */
trait SignalTrait
{
    /**
     * The number of SIGINT on every platform that defines it. The SIG*
     * constants come from the PCNTL extension, which Windows does not have.
     * Ctrl+C and Ctrl+Break are reported to handlers as this value.
     */
    private const INTERRUPT_SIGNAL = 2;

    /**
     * Whether the process should continue running (false = termination requested).
     */
    private bool $running = true;

    /**
     * Whether signals are currently blocked.
     */
    private bool $signalsBlocked = false;

    /**
     * Array of registered signals.
     *
     * @var list<int>
     */
    private array $registeredSignals = [];

    /**
     * Signal-to-method mapping.
     *
     * @var array<int, string>
     */
    private array $signalMethodMap = [];

    /**
     * Whether the Windows console control handler is registered.
     */
    private bool $windowsCtrlHandlerRegistered = false;

    /**
     * Cached result of PCNTL extension availability.
     */
    private static ?bool $isPcntlAvailable = null;

    /**
     * Cached result of POSIX extension availability.
     */
    private static ?bool $isPosixAvailable = null;

    /**
     * Check if PCNTL extension is available (cached).
     */
    protected function isPcntlAvailable(): bool
    {
        if (self::$isPcntlAvailable === null) {
            if (is_windows()) {
                self::$isPcntlAvailable = false;
            } else {
                self::$isPcntlAvailable = extension_loaded('pcntl');
                if (! self::$isPcntlAvailable) {
                    CLI::write(lang('CLI.signals.noPcntlExtension'), 'yellow');
                }
            }
        }

        return self::$isPcntlAvailable;
    }

    /**
     * Check if POSIX extension is available (cached).
     */
    protected function isPosixAvailable(): bool
    {
        self::$isPosixAvailable ??= is_windows() ? false : extension_loaded('posix');

        return self::$isPosixAvailable;
    }

    /**
     * Check if the Windows console control handler is available.
     *
     * It is the Windows counterpart of PCNTL, and handles Ctrl+C and Ctrl+Break.
     */
    protected function isWindowsCtrlHandlerAvailable(): bool
    {
        return is_windows() && function_exists('sapi_windows_set_ctrl_handler');
    }

    /**
     * Add or remove the Windows console control handler.
     *
     * Wraps `sapi_windows_set_ctrl_handler()` so it can be replaced in tests,
     * as the function is only available on Windows.
     *
     * @param callable(int): void $handler
     */
    protected function setWindowsCtrlHandler(callable $handler, bool $add): bool
    {
        return sapi_windows_set_ctrl_handler($handler, $add);
    }

    /**
     * Register signal handlers.
     *
     * On Windows only Ctrl+C and Ctrl+Break can be handled. They are reported
     * as SIGINT (2), and the SIG* constants are not available there, so call it
     * without arguments or pass `2`. Any other signal is ignored.
     *
     * @param list<int>          $signals   List of signals to handle
     * @param array<int, string> $methodMap Optional signal-to-method mapping
     */
    protected function registerSignals(
        array $signals = [],
        array $methodMap = [],
    ): void {
        if (! $this->isPcntlAvailable()) {
            if ($this->isWindowsCtrlHandlerAvailable()) {
                $this->registerWindowsCtrlHandler($signals, $methodMap);
            }

            return;
        }

        if ($signals === []) {
            $signals = [SIGTERM, SIGINT, SIGHUP, SIGQUIT];
        }

        if (! $this->isPosixAvailable() && (in_array(SIGTSTP, $signals, true) || in_array(SIGCONT, $signals, true))) {
            CLI::write(lang('CLI.signals.noPosixExtension'), 'yellow');
            $signals = array_diff($signals, [SIGTSTP, SIGCONT]);

            // Remove from method map as well
            unset($methodMap[SIGTSTP], $methodMap[SIGCONT]);

            if ($signals === []) {
                return;
            }
        }

        // Enable async signals for immediate response
        pcntl_async_signals(true);

        $this->signalMethodMap = $methodMap;

        foreach ($signals as $signal) {
            if (pcntl_signal($signal, [$this, 'handleSignal'])) {
                $this->registeredSignals[] = $signal;
            } else {
                $signal = $this->getSignalName($signal);
                CLI::write(lang('CLI.signals.failedSignal', [$signal]), 'red');
            }
        }
    }

    /**
     * Register the handler for Ctrl+C and Ctrl+Break on Windows.
     *
     * @param list<int>          $signals   List of signals to handle
     * @param array<int, string> $methodMap Optional signal-to-method mapping
     */
    private function registerWindowsCtrlHandler(array $signals, array $methodMap): void
    {
        if ($signals !== [] && ! in_array(self::INTERRUPT_SIGNAL, $signals, true)) {
            return;
        }

        $this->signalMethodMap = $methodMap;

        if ($this->windowsCtrlHandlerRegistered) {
            return;
        }

        if ($this->setWindowsCtrlHandler([$this, 'handleWindowsCtrlEvent'], true)) {
            $this->windowsCtrlHandlerRegistered = true;
            $this->registeredSignals[]          = self::INTERRUPT_SIGNAL;
        } else {
            CLI::write(lang('CLI.signals.failedSignal', [$this->getSignalName(self::INTERRUPT_SIGNAL)]), 'red');
        }
    }

    /**
     * Handle a Windows console control event (Ctrl+C or Ctrl+Break).
     *
     * Windows hands the handler no signal number, so the event is reported as SIGINT.
     * Like on Unix, the process is no longer stopped by the event, and the command
     * is expected to check `shouldTerminate()` and exit by itself.
     */
    protected function handleWindowsCtrlEvent(int $event): void
    {
        $this->callCustomHandler(self::INTERRUPT_SIGNAL);

        $this->running = false;
    }

    /**
     * Handle incoming signals.
     */
    protected function handleSignal(int $signal): void
    {
        $this->callCustomHandler($signal);

        // Apply standard Unix signal behavior for registered signals
        switch ($signal) {
            case SIGTERM:
            case SIGINT:
            case SIGQUIT:
            case SIGHUP:
                $this->running = false;
                break;

            case SIGTSTP:
                // Restore default handler and re-send signal to actually suspend
                pcntl_signal(SIGTSTP, SIG_DFL);
                posix_kill(posix_getpid(), SIGTSTP);
                break;

            case SIGCONT:
                // Re-register SIGTSTP handler after resume
                pcntl_signal(SIGTSTP, [$this, 'handleSignal']);
                break;
        }
    }

    /**
     * Call custom signal handler if one is mapped for this signal.
     * Falls back to generic onInterruption() method if no explicit mapping exists.
     */
    private function callCustomHandler(int $signal): void
    {
        // Check for explicit mapping first
        $method = $this->signalMethodMap[$signal] ?? null;

        if ($method !== null && method_exists($this, $method)) {
            $this->{$method}($signal);

            return;
        }

        // If no explicit mapping, try generic catch-all method
        if (method_exists($this, 'onInterruption')) {
            $this->onInterruption($signal);
        }
    }

    /**
     * Check if command should terminate.
     */
    protected function shouldTerminate(): bool
    {
        return ! $this->running;
    }

    /**
     * Check if the process is currently running (not terminated).
     */
    protected function isRunning(): bool
    {
        return $this->running;
    }

    /**
     * Request immediate termination.
     */
    protected function requestTermination(): void
    {
        $this->running = false;
    }

    /**
     * Reset all states (for testing or restart scenarios).
     */
    protected function resetState(): void
    {
        $this->running = true;

        // Unblock signals if they were blocked
        if ($this->signalsBlocked) {
            $this->unblockSignals();
        }
    }

    /**
     * Execute a callable with ALL signals blocked to prevent ANY interruption during critical operations.
     *
     * This blocks ALL interruptible signals including:
     * - Termination signals (SIGTERM, SIGINT, etc.)
     * - Pause/resume signals (SIGTSTP, SIGCONT)
     * - Custom signals (SIGUSR1, SIGUSR2)
     *
     * Only SIGKILL (unblockable) can still terminate the process.
     * Use this for database transactions, file operations, or any critical atomic operations.
     *
     * @template TReturn
     *
     * @param Closure():TReturn $operation
     *
     * @return TReturn
     */
    protected function withSignalsBlocked(Closure $operation)
    {
        $this->blockSignals();

        try {
            return $operation();
        } finally {
            $this->unblockSignals();
        }
    }

    /**
     * Block ALL interruptible signals during critical sections.
     * Only SIGKILL (unblockable) can terminate the process.
     */
    protected function blockSignals(): void
    {
        if (! $this->signalsBlocked && $this->isPcntlAvailable()) {
            // Block ALL signals that could interrupt critical operations
            pcntl_sigprocmask(SIG_BLOCK, [
                SIGTERM, SIGINT, SIGHUP, SIGQUIT, // Termination signals
                SIGTSTP, SIGCONT,                 // Pause/resume signals
                SIGUSR1, SIGUSR2,                 // Custom signals
                SIGPIPE, SIGALRM,                 // Other common signals
            ]);
            $this->signalsBlocked = true;
        }
    }

    /**
     * Unblock previously blocked signals.
     */
    protected function unblockSignals(): void
    {
        if ($this->signalsBlocked && $this->isPcntlAvailable()) {
            // Unblock the same signals we blocked
            pcntl_sigprocmask(SIG_UNBLOCK, [
                SIGTERM, SIGINT, SIGHUP, SIGQUIT, // Termination signals
                SIGTSTP, SIGCONT,                 // Pause/resume signals
                SIGUSR1, SIGUSR2,                 // Custom signals
                SIGPIPE, SIGALRM,                 // Other common signals
            ]);
            $this->signalsBlocked = false;
        }
    }

    /**
     * Check if signals are currently blocked.
     */
    protected function signalsBlocked(): bool
    {
        return $this->signalsBlocked;
    }

    /**
     * Add or update signal-to-method mapping at runtime.
     */
    protected function mapSignal(int $signal, string $method): void
    {
        $this->signalMethodMap[$signal] = $method;
    }

    /**
     * Get human-readable signal name.
     */
    protected function getSignalName(int $signal): string
    {
        // The SIG* constants come from PCNTL, which Windows does not have.
        if (! $this->isPcntlAvailable()) {
            return $signal === self::INTERRUPT_SIGNAL ? 'SIGINT' : "Signal {$signal}";
        }

        return match ($signal) {
            SIGTERM => 'SIGTERM',
            SIGINT  => 'SIGINT',
            SIGHUP  => 'SIGHUP',
            SIGQUIT => 'SIGQUIT',
            SIGUSR1 => 'SIGUSR1',
            SIGUSR2 => 'SIGUSR2',
            SIGPIPE => 'SIGPIPE',
            SIGALRM => 'SIGALRM',
            SIGTSTP => 'SIGTSTP',
            SIGCONT => 'SIGCONT',
            default => "Signal {$signal}",
        };
    }

    /**
     * Unregister all signals (cleanup).
     */
    protected function unregisterSignals(): void
    {
        if ($this->windowsCtrlHandlerRegistered) {
            $this->setWindowsCtrlHandler([$this, 'handleWindowsCtrlEvent'], false);

            $this->windowsCtrlHandlerRegistered = false;
            $this->registeredSignals            = [];
            $this->signalMethodMap              = [];

            return;
        }

        if (! $this->isPcntlAvailable()) {
            return;
        }

        foreach ($this->registeredSignals as $signal) {
            pcntl_signal($signal, SIG_DFL);
        }

        $this->registeredSignals = [];
        $this->signalMethodMap   = [];
    }

    /**
     * Check if signals are registered.
     */
    protected function hasSignals(): bool
    {
        return $this->registeredSignals !== [];
    }

    /**
     * Get list of registered signals.
     *
     * @return list<int>
     */
    protected function getSignals(): array
    {
        return $this->registeredSignals;
    }

    /**
     * Get comprehensive process state information.
     *
     * @return array{
     *      pid: int,
     *      running: bool,
     *      pcntl_available: bool,
     *      registered_signals: int,
     *      registered_signals_names: array<int, string>,
     *      signals_blocked: bool,
     *      explicit_mappings: int,
     *      memory_usage_mb: float,
     *      memory_peak_mb: float,
     *      session_id?: false|int,
     *      process_group?: false|int,
     *      has_controlling_terminal?: bool
     *  }
     */
    protected function getProcessState(): array
    {
        $pid   = getmypid();
        $state = [
            // Process identification
            'pid'     => $pid,
            'running' => $this->running,

            // Signal handling status
            'pcntl_available'          => $this->isPcntlAvailable(),
            'registered_signals'       => count($this->registeredSignals),
            'registered_signals_names' => array_map([$this, 'getSignalName'], $this->registeredSignals),
            'signals_blocked'          => $this->signalsBlocked,
            'explicit_mappings'        => count($this->signalMethodMap),

            // System resources
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'memory_peak_mb'  => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        ];

        // Add terminal control info if POSIX extension is available
        if ($this->isPosixAvailable()) {
            $state['session_id']               = posix_getsid($pid);
            $state['process_group']            = posix_getpgid($pid);
            $state['has_controlling_terminal'] = is_cli() && posix_isatty(STDIN);
        }

        return $state;
    }
}
