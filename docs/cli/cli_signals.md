# CLI Signals

Unix signals are a fundamental part of process communication and control. They provide a way to interrupt, control, and communicate with running processes. CodeIgniter's SignalTrait makes it easy to handle signals in your CLI commands, enabling graceful shutdowns, pause/resume functionality, and custom signal handling.

## What are Signals?

Signals are software interrupts delivered to a process by the operating system. They notify processes of various events, from user-initiated interruptions (like pressing Ctrl+C) to system events (like terminal disconnection).

`SignalTrait` adds the ability to perform certain actions before the signal is consumed, as well as the capability to protect certain pieces of code from signal interruption. This protection mechanism guarantees the proper execution of critical command operations by ensuring they complete atomically without being interrupted by incoming signals.

### Common Unix Signals

Here are the most commonly used signals in CLI applications. The numbers shown are Linux values. Signal numbers differ on macOS and BSD, so use the named PHP signal constants in portable code:

**Handleable Signals:**

- **SIGTERM (15)**: Termination signal - requests graceful shutdown
- **SIGINT (2)**: Interrupt signal - typically sent by Ctrl+C
- **SIGHUP (1)**: Hangup signal - terminal disconnected or closed
- **SIGQUIT (3)**: Quit signal - typically sent by Ctrl+\\
- **SIGTSTP (20)**: Terminal stop - typically sent by Ctrl+Z (suspend)
- **SIGCONT (18)**: Continue signal - resume suspended process (fg command)
- **SIGUSR1 (10)**: User-defined signal 1
- **SIGUSR2 (12)**: User-defined signal 2

**Unhandleable Signals:**

Some signals cannot be caught, blocked, or handled by user processes:

- **SIGKILL (9)**: Forceful termination - cannot be caught or ignored
- **SIGSTOP (19)**: Forceful suspend - cannot be caught or ignored

These signals are handled directly by the kernel and will terminate or suspend your process immediately, bypassing any custom handlers.

### System Requirements

Signal handling requires:

- **Unix-based system** (Linux, macOS, BSD) - Windows is not supported
- **PCNTL extension** - for signal registration and handling
- **POSIX extension** - required for pause/resume functionality (SIGTSTP/SIGCONT)

!!! note "Note"
    On systems without these extensions, the SignalTrait will gracefully degrade and disable signal handling.

## Using the SignalTrait

The `SignalTrait` provides a comprehensive signal handling system for CLI commands. To use it, simply add the trait to your command class and register signals in your command's `run()` method:

```php
<?php

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SampleCommand extends BaseCommand
{
    public function run(array $params): int
    {
        // Register basic termination signals
        $this->registerSignals();

        // Main processing loop
        while ($this->isRunning()) {
            // Do work here
            $this->processItem();

            sleep(3);
        }

        CLI::write('Command terminated gracefully', 'green');

        return EXIT_SUCCESS;
    }
}
```

This registers three termination signals that will set the `$running` state to `false` when received.

### Custom Signal Handlers

You can map signals to custom methods for specific behavior:

```php
<?php

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SampleCommand extends BaseCommand
{
    public function run(array $params): int
    {
        // Register signals with custom handlers
        $this->registerSignals(
            [SIGTERM, SIGINT, SIGUSR1, SIGUSR2],
            [
                SIGTERM => 'onGracefulShutdown',
                SIGINT  => 'onInterrupt',
                SIGUSR1 => 'onToggleDebug',
                SIGUSR2 => 'onStatusReport',
            ],
        );

        while ($this->isRunning()) {
            // Call custom method
            $this->doWork();
            sleep(1);
        }

        return EXIT_SUCCESS;
    }

    protected function onGracefulShutdown(int $signal): void
    {
        CLI::write('Received SIGTERM - shutting down gracefully...', 'yellow');
    }

    protected function onInterrupt(int $signal): void
    {
        CLI::write('Received SIGINT - stopping!', 'red');
    }

    protected function onToggleDebug(int $signal): void
    {
        // Custom debug mode
        $this->debugMode = ! $this->debugMode;
        CLI::write('Debug mode: ' . ($this->debugMode ? 'ON' : 'OFF'), 'blue');
    }

    protected function onStatusReport(int $signal): void
    {
        $state = $this->getProcessState();
        CLI::write('Status: ' . json_encode($state, JSON_PRETTY_PRINT), 'cyan');
    }
}
```

### Fallback Signal Handler

For signals without explicit mappings, you can implement a generic `onInterruption()` method:

```php
<?php

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SampleCommand extends BaseCommand
{
    public function run(array $params): int
    {
        // Register signals without explicit mappings
        $this->registerSignals([SIGTERM, SIGINT, SIGHUP, SIGUSR1]);

        while ($this->isRunning()) {
            $this->doWork();
            sleep(1);
        }

        return EXIT_SUCCESS;
    }

    /**
     * Generic handler for all unmapped signals
     */
    protected function onInterruption(int $signal): void
    {
        $signalName = $this->getSignalName($signal);
        CLI::write("Received {$signalName} - handling generically", 'yellow');

        switch ($signal) {
            case SIGTERM:
                CLI::write('Graceful shutdown requested', 'green');
                break;

            case SIGINT:
                CLI::write('Immediate shutdown requested', 'red');
                break;

            case SIGHUP:
                CLI::write('Configuration reload requested', 'blue');
                break;

            case SIGUSR1:
                CLI::write('User signal 1 received', 'cyan');
                break;

            default:
                CLI::write('Unknown signal received', 'light_gray');
                break;
        }
    }
}
```

## Critical Sections

Some operations should never be interrupted (database transactions, file operations). Use `withSignalsBlocked()` to create atomic operations:

```php
<?php

use CodeIgniter\CLI\CLI;

class SampleCommand extends \BaseCommand
{
    // ...
    private function processOrder(array $orderData): void
    {
        // Critical section - no interruptions allowed
        $result = $this->withSignalsBlocked(function () use ($orderData) {
            CLI::write('Starting critical transaction - signals blocked', 'yellow');

            // Start database transaction
            $this->db->transStart();

            try {
                // Create order record
                $orderId = $this->createOrder($orderData);

                // Update inventory
                $this->updateInventory($orderData['items']);

                // Process payment
                $this->processPayment($orderId, $orderData['payment']);

                // Commit transaction
                $this->db->transCommit();

                CLI::write('Transaction completed successfully', 'green');

                return $orderId;
            } catch (\Exception $e) {
                // Rollback on error
                $this->db->transRollback();

                throw $e;
            }
        });

        CLI::write('Critical section complete - signals restored', 'cyan');
        CLI::write("Order {$result} processed successfully", 'green');
    }
}
```

During critical sections, ALL signals (including Ctrl+Z) are blocked to prevent data corruption.

## Pause and Resume

The SignalTrait supports proper Unix job control with custom handlers:

```php
<?php

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;

class SampleCommand extends BaseCommand
{
    public function run(array $params): int
    {
        // Register pause/resume signals with custom handlers
        $this->registerSignals(
            [SIGTERM, SIGINT, SIGTSTP, SIGCONT],
            [
                SIGTSTP => 'onPause',
                SIGCONT => 'onResume',
            ],
        );

        while ($this->isRunning()) {
            $this->processWork();
            sleep(2);
        }

        return EXIT_SUCCESS;
    }

    protected function onPause(int $signal): void
    {
        CLI::write('Pausing - saving current date...', 'yellow');

        // Save current timestamp
        $state = [
            'timestamp' => Time::now()->getTimestamp(),
        ];

        file_put_contents(WRITEPATH . 'app_state.json', json_encode($state));

        CLI::write('State saved. Process will now suspend.', 'green');
    }

    protected function onResume(int $signal): void
    {
        CLI::write('Resuming - restoring...', 'green');

        $file = WRITEPATH . 'app_state.json';

        // Restore saved state
        if (file_exists($file)) {
            $state = json_decode(file_get_contents($file), true);
            $date  = Time::createFromTimestamp($state['timestamp'])->format('Y-m-d H:i:s');

            CLI::write('Restored from ' . $date, 'cyan');
        }

        CLI::write('Resuming normal operation...', 'green');
    }
}
```

### How Pause/Resume Works

1.  **SIGTSTP received**: Custom `onPause()` handler runs
2.  **Process suspends**: Using standard Unix job control
3.  **SIGCONT received**: Process resumes, then `onResume()` handler runs

This allows you to save state before suspension and restore it after resumption while maintaining proper shell integration.

### Important Limitations

**Shell Job Control vs Manual Signals**

There's a critical difference between using shell job control and manually sending signals:

```bash
# RECOMMENDED: Use shell job control
php spark my:command
# Press Ctrl+Z to suspend
fg  # Resume - maintains terminal control

# PROBLEMATIC: Manual signal sending
php spark my:command &
kill -TSTP $PID   # Suspend
kill -CONT $PID   # Resume - may lose terminal control
```

**The Problem with Manual SIGCONT**

When you manually send `kill -CONT` from a different terminal:

**Expected behavior:**  

- Process resumes and custom handlers execute

**Side effects:**  

- Process loses foreground terminal control
- Ctrl+C and Ctrl+Z may stop working
- Process runs in background state

This happens because manual `kill -CONT` doesn't restore the process to the terminal's foreground process group.

**Best Practices for Pause/Resume**

1.  **Use shell job control** (Ctrl+Z, fg, bg) when possible
2.  **Document the limitation** if your application needs manual signal control
3.  **Provide alternative control methods** for automated environments
4.  **Test thoroughly** in your deployment environment

## Triggering Signals

### From Command Line

You can send signals to running processes using the `kill` command:

```bash
# Get the process ID
php spark long:running:command &
echo $!  # Shows PID, e.g., 12345

# Send different signals
kill -TERM 12345   # Graceful shutdown
kill -INT 12345    # Interrupt (same as Ctrl+C)
kill -HUP 12345    # Hangup
kill -USR1 12345   # User-defined signal 1
kill -USR2 12345   # User-defined signal 2

# Pause and resume
kill -TSTP 12345   # Suspend (same as Ctrl+Z)
kill -CONT 12345   # Resume (same as fg)
```

### Keyboard Shortcuts

These keyboard shortcuts send signals to the foreground process:

- **Ctrl+C**: Sends SIGINT (interrupt)
- **Ctrl+Z**: Sends SIGTSTP (suspend/pause)
- **Ctrl+\\**: Sends SIGQUIT (quit with core dump)

### Job Control

Standard Unix job control works seamlessly:

```bash
php spark long:command     # Run in foreground
# Press Ctrl+Z to suspend
bg                         # Move to background
fg                         # Bring back to foreground
jobs                       # List suspended jobs
```

## Debugging Signals

### Process State Information

Use `getProcessState()` to debug signal issues:

```php
<?php

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SampleCommand extends BaseCommand
{
    // ...

    protected function debugProcessState(): void
    {
        $state = $this->getProcessState();

        CLI::write('=== PROCESS DEBUG INFO ===', 'yellow');
        CLI::write('PID: ' . $state['pid'], 'cyan');
        CLI::write('Running: ' . ($state['running'] ? 'YES' : 'NO'), 'cyan');
        CLI::write('PCNTL Available: ' . ($state['pcntl_available'] ? 'YES' : 'NO'), 'cyan');
        CLI::write('Signals Registered: ' . $state['registered_signals'], 'cyan');
        CLI::write('Signal Names: ' . implode(', ', $state['registered_signals_names']), 'cyan');
        CLI::write('Explicit Mappings: ' . $state['explicit_mappings'], 'cyan');
        CLI::write('Signals Blocked: ' . ($state['signals_blocked'] ? 'YES' : 'NO'), 'cyan');
        CLI::write('Memory Usage: ' . $state['memory_usage_mb'] . ' MB', 'cyan');
        CLI::write('Peak Memory: ' . $state['memory_peak_mb'] . ' MB', 'cyan');

        // POSIX info (if available)
        if (isset($state['session_id'])) {
            CLI::write('Session ID: ' . $state['session_id'], 'cyan');
            CLI::write('Process Group: ' . $state['process_group'], 'cyan');
            CLI::write('Has Terminal: ' . ($state['has_controlling_terminal'] ? 'YES' : 'NO'), 'cyan');
        }

        CLI::write('========================', 'yellow');
    }
}
```

This returns comprehensive information including:

- Process ID and running state
- Registered signals and mappings
- Memory usage statistics
- Terminal control information (session, process group)
- Signal blocking status

## Class Reference

## CodeIgniter\CLI

### SignalTrait

#### registerSignals($signals = [SIGTERM, SIGINT, SIGHUP, SIGQUIT], $methodMap = [])

- **Parameters**  
    - **$signals** `array` List of signals to handle
    - **$methodMap** `array` Optional signal-to-method mapping

- **Return type**: `void`

Register signal handlers with optional custom method mapping.

```php
<?php

// Basic signal registration
$this->registerSignals();

// Register specific signals
$this->registerSignals([SIGTERM, SIGINT]);

// Register signals with custom method mapping
$this->registerSignals(
    [SIGTERM, SIGINT, SIGUSR1],
    [
        SIGTERM => 'handleGracefulShutdown',
        SIGUSR1 => 'handleReload',
    ],
);
```

!!! note "Note"
    Requires the PCNTL extension. On Windows, signal handling is automatically disabled.

#### isRunning()

- **Returns**: true if the process should continue running, false if not
- **Return type**: `bool`

Check if the process should continue running (not terminated).

```php
<?php

use CodeIgniter\CLI\CLI;

// Main application loop
while ($this->isRunning()) {
    // Process work items
    $this->processNextItem();

    // Small delay to prevent CPU spinning
    usleep(100000); // 0.1 seconds
}

CLI::write('Process terminated gracefully.');
```

#### shouldTerminate()

- **Returns**: true if termination has been requested, false if not
- **Return type**: `bool`

Check if termination has been requested (opposite of `isRunning()`).

```php
<?php

use CodeIgniter\CLI\CLI;

// Check for termination before expensive operations
if ($this->shouldTerminate()) {
    CLI::write('Termination requested, skipping file processing.');

    return;
}

// Process large file
foreach ($largeDataSet as $item) {
    // Check periodically during long operations
    if ($this->shouldTerminate()) {
        CLI::write('Termination requested during processing.');
        break;
    }

    $this->processItem($item);
}
```

#### requestTermination()

- **Return type**: `void`

Manually request process termination.

```php
<?php

use CodeIgniter\CLI\CLI;

// Request termination based on conditions
if ($errorCount > $this->maxErrors) {
    CLI::write("Too many errors ({$errorCount}), requesting termination.", 'red');
    $this->requestTermination();

    return;
}
```

#### resetState()

- **Return type**: `void`

Reset all states - useful for testing or restart scenarios.

#### withSignalsBlocked($operation)

- **Parameters**  
    - **$operation** `callable` The critical operation to execute without interruption

- **Returns**: The result of the operation

- **Return type**: `mixed`

Execute a critical operation with ALL signals blocked to prevent ANY interruption.

!!! note "Note"
    This blocks ALL interruptible signals including termination signals (SIGTERM, SIGINT), pause/resume signals (SIGTSTP, SIGCONT), and custom signals (SIGUSR1, SIGUSR2). Only SIGKILL (unblockable) can still terminate the process.

#### areSignalsBlocked()

- **Returns**: true if signals are currently blocked, false if not
- **Return type**: `bool`

Check if signals are currently blocked.

#### mapSignal($signal, $method)

- **Parameters**  
    - **$signal** `int` Signal constant
    - **$method** `string` Method name to call for this signal

- **Return type**: `void`

Add or update signal-to-method mapping at runtime.

```php
<?php

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\SignalTrait;

class SampleCommand extends BaseCommand
{
    use SignalTrait;
    public function run(array $params): int
    {
        // Register signals first
        $this->registerSignals([SIGTERM, SIGINT, SIGUSR1, SIGUSR2]);

        // Map signals to specific methods at runtime
        $this->mapSignal(SIGUSR1, 'handleReload');
        $this->mapSignal(SIGUSR2, 'handleStatusDump');
    }

    // Custom signal handlers
    public function handleReload(int $signal): void
    {
        CLI::write('Received reload signal, reloading configuration...');
        $this->reloadConfig();
    }

    public function handleStatusDump(int $signal): void
    {
        CLI::write('=== Process Status ===');
        $this->printStatus($this->getProcessState());
    }
}
```

#### getSignalName($signal)

- **Parameters**  
    - **$signal** `int` Signal constant

- **Returns**: Human-readable signal name

- **Return type**: `string`

Get human-readable name for a signal constant.

```php
<?php

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\SignalTrait;

class SampleCommand extends BaseCommand
{
    use SignalTrait;

    public function run(array $params): int
    {
        // ...
    }

    // Log signal information
    public function onInterruption(int $signal): void
    {
        $signalName = $this->getSignalName($signal);
        CLI::write("Received signal: {$signalName} ({$signal})", 'yellow');
    }
}
```

#### hasSignals()

- **Returns**: true if any signals are registered, false if not
- **Return type**: `bool`

Check if any signals are registered.

#### getSignals()

- **Returns**: Array of registered signal constants
- **Return type**: `array`

Get array of registered signal constants.

#### getProcessState()

- **Returns**: Comprehensive process state information
- **Return type**: `array`

Get comprehensive process state information including process ID, memory usage, signal handling status, and terminal control information.

```php
<?php

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\SignalTrait;

class SampleCommand extends BaseCommand
{
    use SignalTrait;

    public function run(array $params): int
    {
        // ...
    }

    // Debug process state
    public function debugProcessState(): void
    {
        $state = $this->getProcessState();

        CLI::write('=== Process Debug Information ===');
        CLI::write("PID: {$state['pid']}");
        CLI::write('Running: ' . ($state['running'] ? 'Yes' : 'No'));
        CLI::write("Memory Usage: {$state['memory_usage_mb']} MB");
        CLI::write("Peak Memory: {$state['memory_peak_mb']} MB");
        CLI::write('Registered Signals: ' . implode(', ', $state['registered_signals_names']));
        CLI::write('Signals Blocked: ' . ($state['signals_blocked'] ? 'Yes' : 'No'));
    }
}
```

#### unregisterSignals()

- **Return type**: `void`

Unregister all signals and clean up resources.

!!! note "Note"
    This removes all signal handling behavior for all previously registered signals.
