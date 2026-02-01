###########
CLI Signals
###########

Unix signals are a fundamental part of process communication and control. They provide a way to interrupt, control,
and communicate with running processes. CodeIgniter's SignalTrait makes it easy to handle signals in your CLI commands,
enabling graceful shutdowns, pause/resume functionality, and custom signal handling.

.. contents::
    :local:
    :depth: 2

*****************
What are Signals?
*****************

Signals are software interrupts delivered to a process by the operating system. They notify processes of various
events, from user-initiated interruptions (like pressing Ctrl+C) to system events (like terminal disconnection).

``SignalTrait`` adds the ability to perform certain actions before the signal is consumed, as well as the capability
to protect certain pieces of code from signal interruption. This protection mechanism guarantees the proper execution
of critical command operations by ensuring they complete atomically without being interrupted by incoming signals.

Common Unix Signals
===================

Here are the most commonly used signals in CLI applications:

**Handleable Signals:**

* **SIGTERM (15)**: Termination signal - requests graceful shutdown
* **SIGINT (2)**: Interrupt signal - typically sent by Ctrl+C
* **SIGHUP (1)**: Hangup signal - terminal disconnected or closed
* **SIGQUIT (3)**: Quit signal - typically sent by Ctrl+\\
* **SIGTSTP (20)**: Terminal stop - typically sent by Ctrl+Z (suspend)
* **SIGCONT (18)**: Continue signal - resume suspended process (fg command)
* **SIGUSR1 (10)**: User-defined signal 1
* **SIGUSR2 (12)**: User-defined signal 2

**Unhandleable Signals:**

Some signals cannot be caught, blocked, or handled by user processes:

* **SIGKILL (9)**: Forceful termination - cannot be caught or ignored
* **SIGSTOP (19)**: Forceful suspend - cannot be caught or ignored

These signals are handled directly by the kernel and will terminate or suspend your process immediately, bypassing any custom handlers.

System Requirements
===================

Signal handling requires:

* **Unix-based system** (Linux, macOS, BSD) - Windows is not supported
* **PCNTL extension** - for signal registration and handling
* **POSIX extension** - required for pause/resume functionality (SIGTSTP/SIGCONT)

.. note:: On systems without these extensions, the SignalTrait will gracefully degrade and disable signal handling.

*********************
Using the SignalTrait
*********************

The ``SignalTrait`` provides a comprehensive signal handling system for CLI commands. To use it, simply add the trait
to your command class and register signals in your command's ``run()`` method:

.. literalinclude:: cli_signals/001.php

This registers three termination signals that will set the ``$running`` state to ``false`` when received.

Custom Signal Handlers
======================

You can map signals to custom methods for specific behavior:

.. literalinclude:: cli_signals/002.php

Fallback Signal Handler
=======================

For signals without explicit mappings, you can implement a generic ``onInterruption()`` method:

.. literalinclude:: cli_signals/003.php

*****************
Critical Sections
*****************

Some operations should never be interrupted (database transactions, file operations). Use ``withSignalsBlocked()``
to create atomic operations:

.. literalinclude:: cli_signals/004.php

During critical sections, ALL signals (including Ctrl+Z) are blocked to prevent data corruption.

****************
Pause and Resume
****************

The SignalTrait supports proper Unix job control with custom handlers:

.. literalinclude:: cli_signals/005.php

How Pause/Resume Works
======================

1. **SIGTSTP received**: Custom ``onPause()`` handler runs
2. **Process suspends**: Using standard Unix job control
3. **SIGCONT received**: Process resumes, then ``onResume()`` handler runs

This allows you to save state before suspension and restore it after resumption while maintaining proper shell integration.

Important Limitations
=====================

**Shell Job Control vs Manual Signals**

There's a critical difference between using shell job control and manually sending signals:

.. code-block:: bash

    # RECOMMENDED: Use shell job control
    php spark my:command
    # Press Ctrl+Z to suspend
    fg  # Resume - maintains terminal control

    # PROBLEMATIC: Manual signal sending
    php spark my:command &
    kill -TSTP $PID   # Suspend
    kill -CONT $PID   # Resume - may lose terminal control

**The Problem with Manual SIGCONT**

When you manually send ``kill -CONT`` from a different terminal:

**Expected behavior:**
  - Process resumes and custom handlers execute

**Side effects:**
  - Process loses foreground terminal control
  - Ctrl+C and Ctrl+Z may stop working
  - Process runs in background state

This happens because manual ``kill -CONT`` doesn't restore the process to the terminal's foreground process group.

**Best Practices for Pause/Resume**

1. **Use shell job control** (Ctrl+Z, fg, bg) when possible
2. **Document the limitation** if your application needs manual signal control
3. **Provide alternative control methods** for automated environments
4. **Test thoroughly** in your deployment environment

******************
Triggering Signals
******************

From Command Line
=================

You can send signals to running processes using the ``kill`` command:

.. code-block:: bash

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

Keyboard Shortcuts
==================

These keyboard shortcuts send signals to the foreground process:

* **Ctrl+C**: Sends SIGINT (interrupt)
* **Ctrl+Z**: Sends SIGTSTP (suspend/pause)
* **Ctrl+\\**: Sends SIGQUIT (quit with core dump)

Job Control
===========

Standard Unix job control works seamlessly:

.. code-block:: bash

    php spark long:command     # Run in foreground
    # Press Ctrl+Z to suspend
    bg                         # Move to background
    fg                         # Bring back to foreground
    jobs                       # List suspended jobs

*****************
Debugging Signals
*****************

Process State Information
=========================

Use ``getProcessState()`` to debug signal issues:

.. literalinclude:: cli_signals/006.php

This returns comprehensive information including:

* Process ID and running state
* Registered signals and mappings
* Memory usage statistics
* Terminal control information (session, process group)
* Signal blocking status

***************
Class Reference
***************

.. php:namespace:: CodeIgniter\CLI

.. php:trait:: SignalTrait

    .. php:method:: registerSignals($signals = [SIGTERM, SIGINT, SIGHUP, SIGQUIT], $methodMap = [])

        :param    array    $signals: List of signals to handle
        :param    array    $methodMap: Optional signal-to-method mapping
        :rtype:    void

        Register signal handlers with optional custom method mapping.

        .. literalinclude:: cli_signals/007.php

        .. note:: Requires the PCNTL extension. On Windows, signal handling is automatically disabled.

    .. php:method:: isRunning()

        :returns:    true if the process should continue running, false if not
        :rtype:    bool

        Check if the process should continue running (not terminated).

        .. literalinclude:: cli_signals/008.php

    .. php:method:: shouldTerminate()

        :returns:    true if termination has been requested, false if not
        :rtype:    bool

        Check if termination has been requested (opposite of ``isRunning()``).

        .. literalinclude:: cli_signals/009.php

    .. php:method:: requestTermination()

        :rtype:    void

        Manually request process termination.

        .. literalinclude:: cli_signals/010.php

    .. php:method:: resetState()

        :rtype:    void

        Reset all states - useful for testing or restart scenarios.

    .. php:method:: withSignalsBlocked($operation)

        :param    callable    $operation: The critical operation to execute without interruption
        :returns:    The result of the operation
        :rtype:    mixed

        Execute a critical operation with ALL signals blocked to prevent ANY interruption.

        .. note:: This blocks ALL interruptible signals including termination signals (SIGTERM, SIGINT),
                  pause/resume signals (SIGTSTP, SIGCONT), and custom signals (SIGUSR1, SIGUSR2).
                  Only SIGKILL (unblockable) can still terminate the process.

    .. php:method:: areSignalsBlocked()

        :returns:    true if signals are currently blocked, false if not
        :rtype:    bool

        Check if signals are currently blocked.

    .. php:method:: mapSignal($signal, $method)

        :param    int    $signal: Signal constant
        :param    string    $method: Method name to call for this signal
        :rtype:    void

        Add or update signal-to-method mapping at runtime.

        .. literalinclude:: cli_signals/011.php

    .. php:method:: getSignalName($signal)

        :param    int    $signal: Signal constant
        :returns:    Human-readable signal name
        :rtype:    string

        Get human-readable name for a signal constant.

        .. literalinclude:: cli_signals/012.php

    .. php:method:: hasSignals()

        :returns:    true if any signals are registered, false if not
        :rtype:    bool

        Check if any signals are registered.

    .. php:method:: getSignals()

        :returns:    Array of registered signal constants
        :rtype:    array

        Get array of registered signal constants.

    .. php:method:: getProcessState()

        :returns:    Comprehensive process state information
        :rtype:    array

        Get comprehensive process state information including process ID, memory usage,
        signal handling status, and terminal control information.

        .. literalinclude:: cli_signals/013.php

    .. php:method:: unregisterSignals()

        :rtype:    void

        Unregister all signals and clean up resources.

        .. note:: This removes all signal handling behavior for all previously registered signals.
