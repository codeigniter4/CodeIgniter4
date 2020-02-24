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
 * Set of static methods useful for CLI request handling.
 *
 * Portions of this code were initially from the FuelPHP Framework,
 * version 1.7.x, and used here under the MIT license they were
 * originally made available under. Reference: http://fuelphp.com
 *
 * Some of the code in this class is Windows-specific, and not
 * possible to test using travis-ci. It has been phpunit-annotated
 * to prevent messing up code coverage.
 *
 * Some of the methods require keyboard input, and are not unit-testable
 * as a result: input() and prompt().
 * validate() is internal, and not testable if prompt() isn't.
 * The wait() method is mostly testable, as long as you don't give it
 * an argument of "0".
 * These have been flagged to ignore for code coverage purposes.
 *
 * @package CodeIgniter\CLI
 */
class CLI
{

	/**
	 * Is the readline library on the system?
	 *
	 * @var boolean
	 */
	public static $readline_support = false;

	/**
	 * The message displayed at prompts.
	 *
	 * @var string
	 */
	public static $wait_msg = 'Press any key to continue...';

	/**
	 * Has the class already been initialized?
	 *
	 * @var boolean
	 */
	protected static $initialized = false;

	/**
	 * Foreground color list
	 *
	 * @var array
	 */
	protected static $foreground_colors = [
		'black'        => '0;30',
		'dark_gray'    => '1;30',
		'blue'         => '0;34',
		'dark_blue'    => '1;34',
		'light_blue'   => '1;34',
		'green'        => '0;32',
		'light_green'  => '1;32',
		'cyan'         => '0;36',
		'light_cyan'   => '1;36',
		'red'          => '0;31',
		'light_red'    => '1;31',
		'purple'       => '0;35',
		'light_purple' => '1;35',
		'light_yellow' => '0;33',
		'yellow'       => '1;33',
		'light_gray'   => '0;37',
		'white'        => '1;37',
	];

	/**
	 * Background color list
	 *
	 * @var array
	 */
	protected static $background_colors = [
		'black'      => '40',
		'red'        => '41',
		'green'      => '42',
		'yellow'     => '43',
		'blue'       => '44',
		'magenta'    => '45',
		'cyan'       => '46',
		'light_gray' => '47',
	];

	/**
	 * List of array segments.
	 *
	 * @var array
	 */
	protected static $segments = [];

	/**
	 * @var array
	 */
	protected static $options = [];

	/**
	 * Helps track internally whether the last
	 * output was a "write" or a "print" to
	 * keep the output clean and as expected.
	 *
	 * @var string
	 */
	protected static $lastWrite;

	//--------------------------------------------------------------------

	/**
	 * Static "constructor".
	 */
	public static function init()
	{
		// Readline is an extension for PHP that makes interactivity with PHP
		// much more bash-like.
		// http://www.php.net/manual/en/readline.installation.php
		static::$readline_support = extension_loaded('readline');

		// clear segments & options to keep testing clean
		static::$segments = [];
		static::$options  = [];

		static::parseCommandLine();

		static::$initialized = true;
	}

	//--------------------------------------------------------------------

	/**
	 * Get input from the shell, using readline or the standard STDIN
	 *
	 * Named options must be in the following formats:
	 * php index.php user -v --v -name=John --name=John
	 *
	 * @param  string $prefix
	 * @return string
	 *
	 * @codeCoverageIgnore
	 */
	public static function input(string $prefix = null): string
	{
		if (static::$readline_support)
		{
			return readline($prefix);
		}

		echo $prefix;

		return fgets(STDIN);
	}

	//--------------------------------------------------------------------

	/**
	 * Asks the user for input.
	 *
	 * Usage:
	 *
	 * // Takes any input
	 * $color = CLI::prompt('What is your favorite color?');
	 *
	 * // Takes any input, but offers default
	 * $color = CLI::prompt('What is your favourite color?', 'white');
	 *
	 * // Will validate options with the in_list rule and accept only if one of the list
	 * $color = CLI::prompt('What is your favourite color?', array('red','blue'));
	 *
	 * // Do not provide options but requires a valid email
	 * $email = CLI::prompt('What is your email?', null, 'required|valid_email');
	 *
	 * @param string       $field      Output "field" question
	 * @param string|array $options    String to a default value, array to a list of options (the first option will be the default value)
	 * @param string       $validation Validation rules
	 *
	 * @return             string                   The user input
	 * @codeCoverageIgnore
	 */
	public static function prompt(string $field, $options = null, string $validation = null): string
	{
		$extra_output = '';
		$default      = '';

		if (is_string($options))
		{
			$extra_output = ' [' . static::color($options, 'white') . ']';
			$default      = $options;
		}

		if (is_array($options) && $options)
		{
			$opts                 = $options;
			$extra_output_default = static::color($opts[0], 'white');

			unset($opts[0]);

			if (empty($opts))
			{
				$extra_output = $extra_output_default;
			}
			else
			{
				$extra_output = ' [' . $extra_output_default . ', ' . implode(', ', $opts) . ']';
				$validation  .= '|in_list[' . implode(',', $options) . ']';
				$validation   = trim($validation, '|');
			}

			$default = $options[0];
		}

		fwrite(STDOUT, $field . $extra_output . ': ');

		// Read the input from keyboard.
		$input = trim(static::input()) ?: $default;

		if (isset($validation))
		{
			while (! static::validate($field, $input, $validation))
			{
				$input = static::prompt($field, $options, $validation);
			}
		}

		return empty($input) ? '' : $input;
	}

	//--------------------------------------------------------------------

	/**
	 * Validate one prompt "field" at a time
	 *
	 * @param string $field Prompt "field" output
	 * @param string $value Input value
	 * @param string $rules Validation rules
	 *
	 * @return             boolean
	 * @codeCoverageIgnore
	 */
	protected static function validate(string $field, string $value, string $rules): bool
	{
		$validation = \Config\Services::validation(null, false);
		$validation->setRule($field, null, $rules);
		$validation->run([$field => $value]);

		if ($validation->hasError($field))
		{
			static::error($validation->getError($field));

			return false;
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Outputs a string to the CLI without any surrounding newlines.
	 * Useful for showing repeating elements on a single line.
	 *
	 * @param string      $text
	 * @param string|null $foreground
	 * @param string|null $background
	 */
	public static function print(string $text = '', string $foreground = null, string $background = null)
	{
		if ($foreground || $background)
		{
			$text = static::color($text, $foreground, $background);
		}

		static::$lastWrite = null;

		fwrite(STDOUT, $text);
	}

	/**
	 * Outputs a string to the cli on it's own line.
	 *
	 * @param string $text       The text to output
	 * @param string $foreground
	 * @param string $background
	 */
	public static function write(string $text = '', string $foreground = null, string $background = null)
	{
		if ($foreground || $background)
		{
			$text = static::color($text, $foreground, $background);
		}

		if (static::$lastWrite !== 'write')
		{
			$text              = PHP_EOL . $text;
			static::$lastWrite = 'write';
		}

		fwrite(STDOUT, $text . PHP_EOL);
	}

	//--------------------------------------------------------------------

	/**
	 * Outputs an error to the CLI using STDERR instead of STDOUT
	 *
	 * @param string|array $text       The text to output, or array of errors
	 * @param string       $foreground
	 * @param string       $background
	 */
	public static function error(string $text, string $foreground = 'light_red', string $background = null)
	{
		if ($foreground || $background)
		{
			$text = static::color($text, $foreground, $background);
		}

		fwrite(STDERR, $text . PHP_EOL);
	}

	//--------------------------------------------------------------------

	/**
	 * Beeps a certain number of times.
	 *
	 * @param integer $num The number of times to beep
	 */
	public static function beep(int $num = 1)
	{
		echo str_repeat("\x07", $num);
	}

	//--------------------------------------------------------------------

	/**
	 * Waits a certain number of seconds, optionally showing a wait message and
	 * waiting for a key press.
	 *
	 * @param integer $seconds   Number of seconds
	 * @param boolean $countdown Show a countdown or not
	 */
	public static function wait(int $seconds, bool $countdown = false)
	{
		if ($countdown === true)
		{
			$time = $seconds;

			while ($time > 0)
			{
				fwrite(STDOUT, $time . '... ');
				sleep(1);
				$time --;
			}
			static::write();
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
				static::write(static::$wait_msg);
				static::input();
				// @codeCoverageIgnoreEnd
			}
		}
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
	 * Enter a number of empty lines
	 *
	 * @param integer $num Number of lines to output
	 *
	 * @return void
	 */
	public static function newLine(int $num = 1)
	{
		// Do it once or more, write with empty string gives us a new line
		for ($i = 0; $i < $num; $i ++)
		{
			static::write('');
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Clears the screen of output
	 *
	 * @return             void
	 * @codeCoverageIgnore
	 */
	public static function clearScreen()
	{
		static::isWindows()

				// Windows is a bit crap at this, but their terminal is tiny so shove this in
						? static::newLine(40)

				// Anything with a flair of Unix will handle these magic characters
						: fwrite(STDOUT, chr(27) . '[H' . chr(27) . '[2J');
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the given text with the correct color codes for a foreground and
	 * optionally a background color.
	 *
	 * @param string $text       The text to color
	 * @param string $foreground The foreground color
	 * @param string $background The background color
	 * @param string $format     Other formatting to apply. Currently only 'underline' is understood
	 *
	 * @return string    The color coded string
	 */
	public static function color(string $text, string $foreground, string $background = null, string $format = null): string
	{
		if (static::isWindows() && ! isset($_SERVER['ANSICON']))
		{
			// @codeCoverageIgnoreStart
			return $text;
			// @codeCoverageIgnoreEnd
		}

		if (! array_key_exists($foreground, static::$foreground_colors))
		{
			throw CLIException::forInvalidColor('foreground', $foreground);
		}

		if ($background !== null && ! array_key_exists($background, static::$background_colors))
		{
			throw CLIException::forInvalidColor('background', $background);
		}

		$string = "\033[" . static::$foreground_colors[$foreground] . 'm';

		if ($background !== null)
		{
			$string .= "\033[" . static::$background_colors[$background] . 'm';
		}

		if ($format === 'underline')
		{
			$string .= "\033[4m";
		}

		$string .= $text . "\033[0m";

		return $string;
	}

	//--------------------------------------------------------------------

	/**
	 * Get the number of characters in string having encoded characters
	 * and ignores styles set by the color() function
	 *
	 * @param string $string
	 *
	 * @return integer
	 */
	public static function strlen(?string $string): int
	{
		if (is_null($string))
		{
			return 0;
		}
		foreach (static::$foreground_colors as $color)
		{
			$string = strtr($string, ["\033[" . $color . 'm' => '']);
		}

		foreach (static::$background_colors as $color)
		{
			$string = strtr($string, ["\033[" . $color . 'm' => '']);
		}

		$string = strtr($string, ["\033[4m" => '', "\033[0m" => '']);

		return mb_strlen($string);
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to determine the width of the viewable CLI window.
	 * This only works on *nix-based systems, so return a sane default
	 * for Windows environments.
	 *
	 * @param integer $default
	 *
	 * @return integer
	 */
	public static function getWidth(int $default = 80): int
	{
		if (static::isWindows() || (int) shell_exec('tput cols') === 0)
		{
			// @codeCoverageIgnoreStart
			return $default;
			// @codeCoverageIgnoreEnd
		}

		return (int) shell_exec('tput cols');
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to determine the height of the viewable CLI window.
	 * This only works on *nix-based systems, so return a sane default
	 * for Windows environments.
	 *
	 * @param integer $default
	 *
	 * @return integer
	 */
	public static function getHeight(int $default = 32): int
	{
		if (static::isWindows())
		{
			// @codeCoverageIgnoreStart
			return $default;
			// @codeCoverageIgnoreEnd
		}

		return (int) shell_exec('tput lines');
	}

	//--------------------------------------------------------------------

	/**
	 * Displays a progress bar on the CLI. You must call it repeatedly
	 * to update it. Set $thisStep = false to erase the progress bar.
	 *
	 * @param integer|boolean $thisStep
	 * @param integer         $totalSteps
	 */
	public static function showProgress($thisStep = 1, int $totalSteps = 10)
	{
		static $inProgress = false;

		// restore cursor position when progress is continuing.
		if ($inProgress !== false && $inProgress <= $thisStep)
		{
			fwrite(STDOUT, "\033[1A");
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
			fwrite(STDOUT, "[\033[32m" . str_repeat('#', $step) . str_repeat('.', 10 - $step) . "\033[0m]");
			// Textual representation...
			fwrite(STDOUT, sprintf(' %3d%% Complete', $percent) . PHP_EOL);
		}
		else
		{
			fwrite(STDOUT, "\007");
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Takes a string and writes it to the command line, wrapping to a maximum
	 * width. If no maximum width is specified, will wrap to the window's max
	 * width.
	 *
	 * If an int is passed into $pad_left, then all strings after the first
	 * will padded with that many spaces to the left. Useful when printing
	 * short descriptions that need to start on an existing line.
	 *
	 * @param string  $string
	 * @param integer $max
	 * @param integer $pad_left
	 *
	 * @return string
	 */
	public static function wrap(string $string = null, int $max = 0, int $pad_left = 0): string
	{
		if (empty($string))
		{
			return '';
		}

		if ($max === 0)
		{
			$max = CLI::getWidth();
		}

		if (CLI::getWidth() < $max)
		{
			$max = CLI::getWidth();
		}

		$max = $max - $pad_left;

		$lines = wordwrap($string, $max);

		if ($pad_left > 0)
		{
			$lines = explode(PHP_EOL, $lines);

			$first = true;

			array_walk($lines, function (&$line, $index) use ($pad_left, &$first) {
				if (! $first)
				{
					$line = str_repeat(' ', $pad_left) . $line;
				}
				else
				{
					$first = false;
				}
			});

			$lines = implode(PHP_EOL, $lines);
		}

		return $lines;
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Command-Line 'URI' support
	//--------------------------------------------------------------------

	/**
	 * Parses the command line it was called from and collects all
	 * options and valid segments.
	 *
	 * I tried to use getopt but had it fail occasionally to find any
	 * options but argc has always had our back. We don't have all of the power
	 * of getopt but this does us just fine.
	 */
	protected static function parseCommandLine()
	{
		$optionsFound = false;

		// start picking segments off from #1, ignoring the invoking program
		for ($i = 1; $i < $_SERVER['argc']; $i ++)
		{
			// If there's no '-' at the beginning of the argument
			// then add it to our segments.
			if (! $optionsFound && mb_strpos($_SERVER['argv'][$i], '-') === false)
			{
				static::$segments[] = $_SERVER['argv'][$i];
				continue;
			}

			// We set $optionsFound here so that we know to
			// skip the next argument since it's likely the
			// value belonging to this option.
			$optionsFound = true;

			$arg   = str_replace('-', '', $_SERVER['argv'][$i]);
			$value = null;

			// if there is a following segment, and it doesn't start with a dash, it's a value.
			if (isset($_SERVER['argv'][$i + 1]) && mb_strpos($_SERVER['argv'][$i + 1], '-') !== 0)
			{
				$value = $_SERVER['argv'][$i + 1];
				$i ++;
			}

			static::$options[$arg] = $value;

			// Reset $optionsFound so it can collect segments
			// past any options.
			$optionsFound = false;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the command line string portions of the arguments, minus
	 * any options, as a string. This is used to pass along to the main
	 * CodeIgniter application.
	 *
	 * @return string
	 */
	public static function getURI(): string
	{
		return implode('/', static::$segments);
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an individual segment.
	 *
	 * This ignores any options that might have been dispersed between
	 * valid segments in the command:
	 *
	 *  // segment(3) is 'three', not '-f' or 'anOption'
	 *  > php spark one two -f anOption three
	 *
	 * @param integer $index
	 *
	 * @return mixed|null
	 */
	public static function getSegment(int $index)
	{
		if (! isset(static::$segments[$index - 1]))
		{
			return null;
		}

		return static::$segments[$index - 1];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the raw array of segments found.
	 *
	 * @return array
	 */
	public static function getSegments(): array
	{
		return static::$segments;
	}

	//--------------------------------------------------------------------

	/**
	 * Gets a single command-line option. Returns TRUE if the option
	 * exists, but doesn't have a value, and is simply acting as a flag.
	 *
	 * @param string $name
	 *
	 * @return boolean|mixed|null
	 */
	public static function getOption(string $name)
	{
		if (! array_key_exists($name, static::$options))
		{
			return null;
		}

		// If the option didn't have a value, simply return TRUE
		// so they know it was set, otherwise return the actual value.
		$val = static::$options[$name] === null ? true : static::$options[$name];

		return $val;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the raw array of options found.
	 *
	 * @return array
	 */
	public static function getOptions(): array
	{
		return static::$options;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the options as a string, suitable for passing along on
	 * the CLI to other commands.
	 *
	 * @return string
	 */
	public static function getOptionString(): string
	{
		if (empty(static::$options))
		{
			return '';
		}

		$out = '';

		foreach (static::$options as $name => $value)
		{
			// If there's a space, we need to group
			// so it will pass correctly.
			if (mb_strpos($value, ' ') !== false)
			{
				$value = '"' . $value . '"';
			}

			$out .= "-{$name} $value ";
		}

		return $out;
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
	public static function table(array $tbody, array $thead = [])
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
				$table .= $cols . PHP_EOL;
			}

			// Set the columns borders
			$table .= '| ' . implode(' | ', $table_rows[$row]) . ' |' . PHP_EOL;

			// Set the thead and table borders-bottom
			if ($row === 0 && ! empty($thead) || $row + 1 === $total_rows)
			{
				$table .= $cols . PHP_EOL;
			}
		}

		fwrite(STDOUT, $table);
	}

	//--------------------------------------------------------------------
}

// Ensure the class is initialized. Done outside of code coverage
// @codeCoverageIgnoreStart
CLI::init();
// @codeCoverageIgnoreEnd
