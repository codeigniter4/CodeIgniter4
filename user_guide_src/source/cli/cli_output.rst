##################
CLI Output Library
##################

CodeIgniter's CLI Output library is the dedicated class for providing output
to the command line.

Originally, the methods here are from the :doc:`CLI library </cli/cli_library>`
but moved to incorporate new and improved features, which include:

* Automatic detection for ANSI color support for your favorite terminal.
* Enforce/disable ANSI-colored output.
* Foreground and background colors available are expanded to include the colors in the ECMA-48 Standard.
* Additional formatting options besides from ``underline``.
* Choice of other streams for output aside from ``STDOUT`` and ``STDERR``.

.. contents::
	:local:
	:depth: 2

Initializing the Class
======================

First, create an instance of ``CLIOutput``::

	$output = new CLIOutput();

Optionally you may supply the stream to use in the first argument (defaults to
``STDOUT``) and whether to enforce color support in the second argument (defaults
to ``null``). If ``null`` is provided in the second argument, automatic detection for
ANSI color support will be made::

	$output = new CLIOutput(STDERR, true);

Colors and Options Available
============================

The following available foreground and background colors and other formatting options are
specified in the `ECMA-48 Standard <https://www.ecma-international.org/publications/files/ECMA-ST/Ecma-048.pdf>`_
and summarized in this Wikipedia article: `ANSI escape code <https://en.wikipedia.org/wiki/ANSI_escape_code#SGR_parameters>`_

=================== =================== ===========
 Foreground colors   Background colors   Options
=================== =================== ===========
 black               black               reset
 red                 red                 bold
 green               green               italic
 yellow              yellow              underline
 blue                blue                blink
 magenta             magenta             inverse
 cyan                cyan                conceal
 white               white               strike
 bright_black        bright_black
 bright_red          bright_red
 bright_green        bright_green
 bright_yellow       bright_yellow
 bright_blue         bright_blue
 bright_magenta      bright_magenta
 bright_cyan         bright_cyan
 bright_white        bright_white
=================== =================== ===========

Providing Feedback
==================

**write()**

Several methods are provided for you to provide feedback to your users. This can be as simple as a single status update
or a complex table of information that wraps to the user's terminal window. At the core of this is the ``write()``
method which takes the string to output as the first parameter::

	$output->write('The rain in Spain falls mainly on the plains.');

You can change the color of the text by passing in a color name as the second parameter::

	$output->write('File created.', 'green');

This could be used to differentiate messages by status, or create 'headers' by using a different color. You can
even set background colors by passing the color name in as the third parameter::

	$output->write('File overwritten.', 'bright_red', 'white');

If you need to write multiple items at once, pass an array or iterable in the first parameter::

	$messages = [
		'Deleting file...',
		'File successfully deleted.',
	];
	$output->write($messages, 'bright_red');

**print()**

Print functions identically to the ``write()`` method, except that it does not force a newline either before or after.
Instead it prints it to the screen wherever the cursor is currently. This allows you to print multiple items all on
the same line, from different calls. This is especially helpful when you want to show a status, do something, then
print "Done" on the same line::

    for ($i = 0; $i <= 10; $i++)
    {
        $output->print($i);
    }

**color()**

While the ``write()`` command will write a single line to the terminal, ending it with an EOL character, you can
use the ``color()`` method to make a string fragment that can be used in the same way, except that it will not force
an EOL after printing. This allows you to create multiple outputs on the same row. Or, more commonly, you can use
it inside of a ``write()`` method to create a string of a different color inside::

	$output->write("fileA \t" . $output->color('/path/to/file', 'white'), 'yellow');

This example would write a single line to the window, with ``fileA`` in yellow, followed by a tab, and then
``/path/to/file`` in white text.

**error()**

If you need to output errors, you should use the appropriately named ``error()`` method. This writes bright-red text
to ``STDERR``, instead of ``STDOUT``, like ``write()`` and ``color()`` do. This can be useful if you have scripts watching
for errors so they don't have to sift through all of the information, only the actual error messages. You use it
exactly as you would the ``write()`` method::

	$output->error('Cannot write to file: ' . $file);

**newLine()**

The ``newLine()`` method displays a blank line to the user. It does not take any parameters::

	$output->newLine();

**clearScreen()**

You can clear the current terminal window with the ``clearScreen()`` method. In most versions of Windows, this will
simply insert 40 blank lines since Windows doesn't support this feature. Windows 10 bash integration should change
this::

	$output->clearScreen();

**showProgress()**

If you have a long-running task that you would like to keep the user updated with the progress, you can use the
``showProgress()`` method which displays something like the following:

.. code-block:: none

	[####......] 40% Complete

This block is animated in place for a very nice effect.

To use it, pass in the current step as the first parameter, and the total number of steps as the second parameter.
The percent complete and the length of the display will be determined based on that number. When you are done,
pass ``false`` as the first parameter and the progress bar will be removed.
::

	$totalSteps = count($tasks);
	$currStep   = 1;

	foreach ($tasks as $task)
	{
		$output->showProgress($currStep++, $totalSteps);
		$task->run();
	}

	// Done, so erase it...
	$output->showProgress(false);

**table()**

::

	$thead = ['ID', 'Title', 'Updated At', 'Active'];
	$tbody = [
		[7, 'A great item title', '2017-11-15 10:35:02', 1],
		[8, 'Another great item title', '2017-11-16 13:46:54', 0]
	];

	$output->table($tbody, $thead);

.. code-block:: none

	+----+--------------------------+---------------------+--------+
	| ID | Title                    | Updated At          | Active |
	+----+--------------------------+---------------------+--------+
	| 7  | A great item title       | 2017-11-16 10:35:02 | 1      |
	| 8  | Another great item title | 2017-11-16 13:46:54 | 0      |
	+----+--------------------------+---------------------+--------+

**wait()**

Waits a certain number of seconds, optionally showing a wait message and
waiting for a key press.

::

        // wait for specified interval, with countdown displayed
        $output->wait($seconds, true);

        // show continuation message and wait for input
        $output->wait(0, false);

        // wait for specified interval
        $output->wait($seconds, false);

Class Reference
===============

.. php:class:: CodeIgniter\\CLI\\CLIOutput

	.. php:method:: __construct([$stream = STDOUT[, $enforceColors = null]])

		:param resource $stream: A valid CLI output stream. Defaults to use STDOUT.
		:param bool|null $enforceColors: Whether to enforce color output. Use null for auto-detection.

	.. php:method:: setStream($resource)

		:param resource $stream: A valid CLI output stream

	.. php:method:: getStream()

		:returns: Returns the current output stream
		:rtype: resource

	.. php:method:: setEnforceColors($enforceColors)

		:param bool $enforceColors: Whether to enforce colored output to terminals

	.. php:method:: isColored()

		:returns: Returns true if output is to be colored
		:rtype: bool

	.. php:method:: hasColorSupport()

		:returns: Returns true if output stream supports colors.
		:rtype: bool

		If the second argument in ``__construct()`` is passed as ``null``, this method is invoked to
		check if the current terminal supports ANSI colors by using a number of heuristics and
		checking the environment.

	.. php:method:: wait($seconds[, $countdown = false])

		:param int $seconds: Number of seconds
		:param bool $countdown: Show a countdown timer or not

		This waits for a certain number of ``$seconds``, optionally showing a wait message
		and waiting for a key press.

	.. php:method:: print([$texts = ''[, $foreground = null[, $background = null[, $options = []]]]])

		:param string|array|Traversable $texts: The text (or array of texts) to output
		:param string|null $foreground: The foreground color
		:param string|null $background: The background color
		:param array $options: Other formatting options

		This outputs a string (or series of strings) to the CLI without any surrounding newlines.
		This is useful for showing repeating elements on a single line.

	.. php:method:: write([$texts = ''[, $foreground = null[, $background = null[, $options = []]]]])

		:param string|array|Traversable $texts: The text (or array of texts) to output
		:param string|null $foreground: The foreground color
		:param string|null $background: The background color
		:param array $options: Other formatting options

		This method outputs a string to the CLI on its own line.

	.. php:method:: error($texts[, $foreground = 'bright_red'[, $background = null[, $options = [][, $stream = STDERR]]]])

		:param string|array|Traversable $texts: The error text to output, or array of errors
		:param string $foreground: The foreground color
		:param string|null $background: The background color
		:param array $options: Other formatting options
		:param resource $stream: Error stream to use. Defaults to ``STDERR``

		This method outputs an error to the CLI using ``STDERR`` instead of ``STDOUT``.

	.. php:method:: newLine([$num = 1])

		:param int $num: Number of lines to output

		This method enters empty lines in multiple of ``$num``

	.. php:method:: clearScreen()

		Clears the screen of output.

	.. php:method:: color($text[, $foreground = null[, $background = null[, $options = []]]])

		:param string $text: The text to color
		:param string|null $foreground: The foreground color
		:param string|null $background: The background color
		:param array $options: Other formatting options
		:returns: The color coded text, or a plain text if color support is disabled.
		:rtype: string

		This returns the given text with the correct color codes for a foreground and
		optionally a background color and other options

	.. php:method:: strlen($string)

		:param string|null $string: The string to get the length
		:returns: An integer length, or 0 if ``null`` is given
		:rtype: int

		This gets the number of characters in string having encoded characters
		and ignores styles set by the ``color()`` function.

		.. note:: This is a static method. You may use this without having an instance of ``CLIOutput``.

	.. php:method:: showProgress([$thisStep = 1[, $totalSteps = 10]])

		:param int|bool $thisStep: Current step
		:param int $totalSteps: Total steps

		This displays a progress bar on the CLI. You must call it repeatedly to update it.
		Set ``$thisStep`` to ``false`` to erase the progress bar.

	.. php:method:: table($tbody[, $thead = []])

		:param array $tbody: List of rows
		:param array $thead: List of columns

		This returns a well-formatted table.
