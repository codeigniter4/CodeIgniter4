<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\CLI;

use CodeIgniter\CLI\Exceptions\CLIException;

/**
 * Dedicated output class for the CLI.
 *
 * This class was originally from the CLI class but was separated
 * to support new features. For BC, the original static methods
 * in \CodeIgniter\CLI\CLI have been retained with a link to this
 * class' methods.
 *
 * @package CodeIgniter
 */
class CLIOutput
{
	/**
	 * Available ANSI foreground colors
	 *
	 * @var array
	 * @see https://www.ecma-international.org/publications/files/ECMA-ST/Ecma-048.pdf
	 */
	protected static $availableForegroundColors = [
		'black'          => [
			'open'  => 30,
			'close' => 39,
		],
		'red'            => [
			'open'  => 31,
			'close' => 39,
		],
		'green'          => [
			'open'  => 32,
			'close' => 39,
		],
		'yellow'         => [
			'open'  => 33,
			'close' => 39,
		],
		'blue'           => [
			'open'  => 34,
			'close' => 39,
		],
		'magenta'        => [
			'open'  => 35,
			'close' => 39,
		],
		'cyan'           => [
			'open'  => 36,
			'close' => 39,
		],
		'white'          => [
			'open'  => 37,
			'close' => 39,
		],
		// aixterm (not in standard)
		'bright_black'   => [
			'open'  => 90,
			'close' => 39,
		],
		'bright_red'     => [
			'open'  => 91,
			'close' => 39,
		],
		'bright_green'   => [
			'open'  => 92,
			'close' => 39,
		],
		'bright_yellow'  => [
			'open'  => 93,
			'close' => 39,
		],
		'bright_blue'    => [
			'open'  => 94,
			'close' => 39,
		],
		'bright_magenta' => [
			'open'  => 95,
			'close' => 39,
		],
		'bright_cyan'    => [
			'open'  => 96,
			'close' => 39,
		],
		'bright_white'   => [
			'open'  => 97,
			'close' => 39,
		],
	];

	/**
	 * Available ANSI background colors
	 *
	 * @var array
	 * @see https://www.ecma-international.org/publications/files/ECMA-ST/Ecma-048.pdf
	 */
	protected static $availableBackgroundColors = [
		'black'          => [
			'open'  => 40,
			'close' => 49,
		],
		'red'            => [
			'open'  => 41,
			'close' => 49,
		],
		'green'          => [
			'open'  => 42,
			'close' => 49,
		],
		'yellow'         => [
			'open'  => 43,
			'close' => 49,
		],
		'blue'           => [
			'open'  => 44,
			'close' => 49,
		],
		'magenta'        => [
			'open'  => 45,
			'close' => 49,
		],
		'cyan'           => [
			'open'  => 46,
			'close' => 49,
		],
		'white'          => [
			'open'  => 47,
			'close' => 49,
		],
		// aixterm (not in standard)
		'bright_black'   => [
			'open'  => 100,
			'close' => 49,
		],
		'bright_red'     => [
			'open'  => 101,
			'close' => 49,
		],
		'bright_green'   => [
			'open'  => 102,
			'close' => 49,
		],
		'bright_yellow'  => [
			'open'  => 103,
			'close' => 49,
		],
		'bright_blue'    => [
			'open'  => 104,
			'close' => 49,
		],
		'bright_magenta' => [
			'open'  => 105,
			'close' => 49,
		],
		'bright_cyan'    => [
			'open'  => 106,
			'close' => 49,
		],
		'bright_white'   => [
			'open'  => 107,
			'close' => 49,
		],
	];

	/**
	 * Available ANSI options
	 *
	 * @var array
	 * @see https://www.ecma-international.org/publications/files/ECMA-ST/Ecma-048.pdf
	 */
	protected static $availableOptions = [
		'reset'     => [
			'open'  => '',
			'close' => 0,
		], // using this will reset all color codes after
		'bold'      => [
			'open'  => 1,
			'close' => 22,
		],
		'italic'    => [
			'open'  => 3,
			'close' => 23,
		], // not widely supported
		'underline' => [
			'open'  => 4,
			'close' => 24,
		],
		'blink'     => [
			'open'  => 5,
			'close' => 25,
		],
		'inverse'   => [
			'open'  => 7,
			'close' => 27,
		],
		'conceal'   => [
			'open'  => 8,
			'close' => 28,
		], // not widely supported
		'strike'    => [
			'open'  => 9,
			'close' => 29,
		],
	];

	/**
	 * Helps track internally whether the last
	 * output was a "write" or a "print" to
	 * keep the output clean and as expected.
	 *
	 * @var string
	 */
	protected static $lastWrite;

	/**
	 * Valid CLI output stream
	 *
	 * @var resource
	 */
	private $stream;

	/**
	 * Whether the stream resource supports ANSI colors
	 *
	 * @var boolean
	 */
	private $useColors;

	//--------------------------------------------------------------------

	/**
	 * @param resource     $stream        A valid CLI output stream. Defaults to use STDOUT.
	 * @param boolean|null $enforceColors Whether to enforce color output. Use null for auto-detection.
	 */
	public function __construct($stream = STDOUT, ?bool $enforceColors = null)
	{
		$this->setStream($stream);
		$this->setEnforceColors($enforceColors ?? $this->hasColorSupport());
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the stream resource
	 *
	 * @param resource $stream
	 *
	 * @return void
	 * @throws \InvalidArgumentException When the stream is not a resource.
	 */
	public function setStream($stream): void
	{
		if (! is_resource($stream) || get_resource_type($stream) !== 'stream')
		{
			throw new \InvalidArgumentException('The CLIOutput class needs a stream as its first argument.');
		}

		$this->stream = $stream;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the current output stream
	 *
	 * @return resource
	 */
	public function getStream()
	{
		return $this->stream;
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the useColors flag
	 *
	 * @param boolean $enforceColors
	 *
	 * @return void
	 */
	public function setEnforceColors(bool $enforceColors)
	{
		$this->useColors = $enforceColors;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns true if output is to be colored
	 *
	 * @return boolean
	 */
	public function isColored(): bool
	{
		return $this->useColors;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns true if output stream supports colors.
	 *
	 * This is tricky on Windows, because Cygwin, Msys2 etc. emulate
	 * pseudo-terminals via named pipes, so we can only check
	 * the environment.
	 *
	 * Reference: Composer\XdebugHandler\Process::supportsColor
	 * https://github.com/composer/xdebug-handler
	 *
	 * @return boolean
	 */
	public function hasColorSupport(): bool
	{
		// Adhere to https://no-color.org
		if (isset($_SERVER['NO_COLOR']) || getenv('NO_COLOR') !== false)
		{
			return false;
		}

		if (getenv('TERM_PROGRAM') === 'Hyper')
		{
			return true;
		}

		if (static::isWindows())
		{
			// @codeCoverageIgnoreStart
			return (function_exists('sapi_windows_vt100_support()')
				&& sapi_windows_vt100_support($this->stream))
				|| isset($_SERVER['ANSICON'])
				|| getenv('ANSICON') !== false
				|| getenv('ConEmuANSI') === 'ON'
				|| getenv('TERM') === 'xterm';
			// @codeCoverageIgnoreEnd
		}

		return stream_isatty($this->stream);
	}

	//--------------------------------------------------------------------

	/**
	 * if operating system === windows
	 *
	 * @return boolean
	 */
	public static function isWindows(): bool
	{
		return stripos(PHP_OS, 'WIN') === 0;
	}

	//--------------------------------------------------------------------

	/**
	 * Waits a certain number of seconds, optionally showing a wait message and
	 * waiting for a key press.
	 *
	 * @param integer $seconds   Number of seconds
	 * @param boolean $countdown Show a countdown or not
	 */
	public function wait(int $seconds, bool $countdown = false)
	{
		if ($countdown === true)
		{
			$time = $seconds;

			while ($time > 0)
			{
				$this->print($time . '... ');
				sleep(1);
				$time --;
			}
			$this->write();
		}
		else
		{
			if ($seconds > 0)
			{
				sleep($seconds);
			}
			else
			{
				// this chunk cannot be tested because of keyboard input
				// @codeCoverageIgnoreStart
				$this->write(CLI::$wait_msg);
				CLI::input();
				// @codeCoverageIgnoreEnd
			}
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Outputs a string (or series of strings) to the CLI without any surrounding newlines.
	 * Useful for showing repeating elements on a single line.
	 *
	 * @param string|array|\Traversable $texts      The text to output
	 * @param string|null               $foreground The foreground color
	 * @param string|null               $background The background color
	 * @param array                     $options    Other formatting options
	 */
	public function print($texts = '', ?string $foreground = null, ?string $background = null, array $options = [])
	{
		if (! is_iterable($texts))
		{
			$texts = [$texts];
		}

		foreach ($texts as $text)
		{
			if ($foreground || $background || $options)
			{
				$text = $this->color($text, $foreground, $background, $options);
			}

			static::$lastWrite = null;

			fwrite($this->stream, $text);
			fflush($this->stream);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Outputs a string to the CLI on its own line.
	 *
	 * @param string|array|\Traversable $texts      The text to output
	 * @param string|null               $foreground The foreground color
	 * @param string|null               $background The background color
	 * @param array                     $options    Other formatting options
	 */
	public function write($texts = '', ?string $foreground = null, ?string $background = null, array $options = [])
	{
		if (! is_iterable($texts))
		{
			$texts = [$texts];
		}

		foreach ($texts as $text)
		{
			if ($foreground || $background || $options)
			{
				$text = $this->color($text, $foreground, $background, $options);
			}

			if (static::$lastWrite !== 'write')
			{
				$text              = "\n" . $text;
				static::$lastWrite = 'write';
			}

			fwrite($this->stream, $text . "\n");
			fflush($this->stream);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Outputs an error to the CLI using STDERR instead of STDOUT
	 *
	 * @param string|array|\Traversable $texts      The error text to output, or array of errors
	 * @param string                    $foreground The foreground color
	 * @param string|null               $background The background color
	 * @param array                     $options    Other formatting options
	 * @param resource                  $stream     Set stream to STDERR. Made a parameter for testability
	 */
	public function error($texts, string $foreground = 'bright_red', ?string $background = null, array $options = [], $stream = STDERR)
	{
		if (! is_iterable($texts))
		{
			$texts = [$texts];
		}

		foreach ($texts as $text)
		{
			if ($foreground || $background || $options)
			{
				$text = $this->color($text, $foreground, $background, $options);
			}

			fwrite($stream, $text . "\n");
			fflush($stream);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Enter a number of empty lines
	 *
	 * @param integer $num Number of lines to output
	 *
	 * @return void
	 */
	public function newLine(int $num = 1)
	{
		// Do it once or more, write with empty string gives us a new line
		for ($i = 0; $i < $num; $i ++)
		{
			$this->write();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Clears the screen of output
	 *
	 * @return             void
	 * @codeCoverageIgnore
	 */
	public function clearScreen()
	{
		static::isWindows() && version_compare(PHP_WINDOWS_VERSION_MAJOR, '10', '<')
			// Windows before Win10 are a bit crap at this, but their terminal is tiny so shove this in
			? $this->newLine(40)
			// Win10 and anything with a flair of Unix will handle this
			: fwrite($this->stream, "\033[H\033[2J");
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the given text with the correct color codes for a foreground and
	 * optionally a background color and other options
	 *
	 * @param string      $text       The text to color
	 * @param string|null $foreground The foreground color
	 * @param string|null $background The background color
	 * @param array       $options    Other formatting options to apply
	 *
	 * @return string     The color coded text
	 */
	public function color(string $text, ?string $foreground = null, ?string $background = null, array $options = [])
	{
		if (! $this->isColored())
		{
			return $text;
		}

		$open  = [];
		$close = [];

		if ($foreground !== null)
		{
			if (! isset(static::$availableForegroundColors[$foreground]))
			{
				throw CLIException::forInvalidColor('foreground', $foreground);
			}

			$open[]  = static::$availableForegroundColors[$foreground]['open'];
			$close[] = static::$availableForegroundColors[$foreground]['close'];
		}

		if ($background !== null)
		{
			if (! isset(static::$availableBackgroundColors[$background]))
			{
				throw CLIException::forInvalidColor('background', $background);
			}

			$open[]  = static::$availableBackgroundColors[$background]['open'];
			$close[] = static::$availableBackgroundColors[$background]['close'];
		}

		if (count($options))
		{
			foreach ($options as $option)
			{
				if (! isset(static::$availableOptions[$option]))
				{
					throw CLIException::forInvalidOption($option);
				}

				$open[]  = static::$availableOptions[$option]['open'];
				$close[] = static::$availableOptions[$option]['close'];
			}
		}

		if (empty($open))
		{
			return $text;
		}

		return sprintf(
			"\033[%sm%s\033[%sm",
			trim(implode(';', $open), ';'),
			$text,
			trim(implode(';', $close), ';')
		);
	}

	//--------------------------------------------------------------------

	/**
	 * Get the number of characters in string having encoded characters
	 * and ignores styles set by the color() function
	 *
	 * @param string|null $string
	 *
	 * @return integer
	 */
	public static function strlen(?string $string): int
	{
		if ($string === null)
		{
			return 0;
		}

		$string = preg_replace("/(\033\[[\d;]+[m])/i", '', $string);

		return mb_strlen($string);
	}

	//--------------------------------------------------------------------

	/**
	 * Displays a progress bar on the CLI. You must call it repeatedly
	 * to update it. Set $thisStep = false to erase the progress bar.
	 *
	 * @param integer|boolean $thisStep
	 * @param integer         $totalSteps
	 */
	public function showProgress($thisStep = 1, int $totalSteps = 10)
	{
		static $inProgress = false;

		// restore cursor position when progress is continuing.
		if ($inProgress !== false && $inProgress <= $thisStep)
		{
			fwrite($this->stream, "\033[1A");
		}
		$inProgress = $thisStep;

		if ($thisStep !== false)
		{
			// Don't allow div by zero or negative numbers....
			$thisStep   = abs($thisStep);
			$totalSteps = $totalSteps < 1 ? 1 : $totalSteps;

			$percent = intval(($thisStep / $totalSteps) * 100);
			$step    = (int) round($percent / 10);

			// Write the progress bar
			fwrite($this->stream, "[\033[32m" . str_repeat('#', $step) . str_repeat('.', 10 - $step) . "\033[39m]");
			// Textual representation...
			fwrite($this->stream, sprintf(' %3d%% Complete', $percent) . "\n");
		}
		else
		{
			fwrite($this->stream, "\007");
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Returns a well formatted table
	 *
	 * @param array $tbody List of rows
	 * @param array $thead List of columns
	 *
	 * @return void
	 */
	public function table(array $tbody, array $thead = [])
	{
		// All the rows in the table will be here until the end
		$table_rows = [];

		// We need only indexes and not keys
		if (! empty($thead))
		{
			$table_rows[] = array_values($thead);
		}

		foreach ($tbody as $tr)
		{
			$table_rows[] = array_values($tr);
		}

		// Yes, it really is necessary to know this count
		$total_rows = count($table_rows);

		// Store all columns lengths
		// $all_cols_lengths[row][column] = length
		$all_cols_lengths = [];

		// Store maximum lengths by column
		// $max_cols_lengths[column] = length
		$max_cols_lengths = [];

		// Read row by row and define the longest columns
		for ($row = 0; $row < $total_rows; $row ++)
		{
			$column = 0; // Current column index
			foreach ($table_rows[$row] as $col)
			{
				// Sets the size of this column in the current row
				$all_cols_lengths[$row][$column] = static::strlen($col);

				// If the current column does not have a value among the larger ones
				// or the value of this is greater than the existing one
				// then, now, this assumes the maximum length
				if (! isset($max_cols_lengths[$column]) || $all_cols_lengths[$row][$column] > $max_cols_lengths[$column])
				{
					$max_cols_lengths[$column] = $all_cols_lengths[$row][$column];
				}

				// We can go check the size of the next column...
				$column ++;
			}
		}

		// Read row by row and add spaces at the end of the columns
		// to match the exact column length
		for ($row = 0; $row < $total_rows; $row ++)
		{
			$column = 0;
			foreach ($table_rows[$row] as $col)
			{
				$diff = $max_cols_lengths[$column] - static::strlen($col);
				if ($diff)
				{
					$table_rows[$row][$column] = $table_rows[$row][$column] . str_repeat(' ', $diff);
				}
				$column ++;
			}
		}

		$table = '';

		// Joins columns and append the well formatted rows to the table
		for ($row = 0; $row < $total_rows; $row ++)
		{
			// Set the table border-top
			if ($row === 0)
			{
				$cols = '+';
				foreach ($table_rows[$row] as $col)
				{
					$cols .= str_repeat('-', static::strlen($col) + 2) . '+';
				}
				$table .= $cols . "\n";
			}

			// Set the column borders
			$table .= '| ' . implode(' | ', $table_rows[$row]) . ' |' . "\n";

			// Set the thead and table borders-bottom
			if ($row === 0 && ! empty($thead) || $row + 1 === $total_rows)
			{
				$table .= $cols . "\n";
			}
		}

		$this->write($table);
	}

	//--------------------------------------------------------------------
}
