<?php namespace CodeIgniter\CLI;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2016, British Columbia Institute of Technology
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
 * @package	CodeIgniter
 * @author	CodeIgniter Dev Team
 * @copyright	Copyright (c) 2014 - 2016, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	http://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

/**
 * Class CLI
 *
 * Tools to interact with that request since CLI requests are not
 * static like HTTP requests might be.
 *
 * Portions of this code were initially from the FuelPHP Framework,
 * version 1.7.x, and used here under the MIT license they were
 * originally made available under.
 *
 * http://fuelphp.com
 *
 * @package CodeIgniter\HTTP
 */
class CLI
{
	/**
	 * Is the readline library on the system?
	 *
	 * @var bool
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
	 * @var bool
	 */
	protected static $initialized = false;

	/**
	 * Used by the progress bar
	 *
	 * @var bool
	 */
	protected static $inProgress = false;

	/**
	 * Foreground color list
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

	//--------------------------------------------------------------------

	/**
	 * Static "constructor".
	 */
	public static function init()
	{
		static::$readline_support = extension_loaded('readline');

		static::$initialized = true;
	}

	//--------------------------------------------------------------------

	/**
	 * Get input from the shell, using readline or the standard STDIN
	 *
	 * Named options must be in the following formats:
	 * php index.php user -v --v -name=John --name=John
	 *
	 * @param    string $prefix
	 *
	 * @return    string
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
	 * Asks the user for input.  This can have either 1 or 2 arguments.
	 *
	 * Usage:
	 *
	 * // Waits for any key press
	 * CLI::prompt();
	 *
	 * // Takes any input
	 * $color = CLI::prompt('What is your favorite color?');
	 *
	 * // Takes any input, but offers default
	 * $color = CLI::prompt('What is your favourite color?', 'white');
	 *
	 * // Will only accept the options in the array
	 * $ready = CLI::prompt('Are you ready?', array('y','n'));
	 *
	 * @return    string    the user input
	 */
	public static function prompt(): string
	{
		$args = func_get_args();

		$options = [];
		$output  = '';
		$default = null;

		// How many we got
		$arg_count = count($args);

		// Is the last argument a boolean? True means required
		$required = end($args) === true;

		// Reduce the argument count if required was passed, we don't care about that anymore
		$required === true && --$arg_count;

		// This method can take a few crazy combinations of arguments, so lets work it out
		switch ($arg_count)
		{
			case 2:

				// E.g: $ready = CLI::prompt('Are you ready?', array('y','n'));
				if (is_array($args[1]))
				{
					list($output, $options) = $args;
				}

				// E.g: $color = CLI::prompt('What is your favourite color?', 'white');
				elseif (is_string($args[1]))
				{
					list($output, $default) = $args;
				}

				break;

			case 1:

				// No question (probably been asked already) so just show options
				// E.g: $ready = CLI::prompt(array('y','n'));
				if (is_array($args[0]))
				{
					$options = $args[0];
				}

				// Question without options
				// E.g: $ready = CLI::prompt('What did you do today?');
				elseif (is_string($args[0]))
				{
					$output = $args[0];
				}

				break;
		}

		// If a question has been asked with the read
		if ($output !== '')
		{
			$extra_output = '';

			if ($default !== null)
			{
				$extra_output = ' [ Default: "'.$default.'" ]';
			}

			elseif ($options !== [])
			{
				$extra_output = ' [ '.implode(', ', $options).' ]';
			}

			fwrite(STDOUT, $output.$extra_output.': ');
		}

		// Read the input from keyboard.
		$input = trim(static::input()) ? : $default;

		// No input provided and we require one (default will stop this being called)
		if (empty($input) && $required === true)
		{
			static::write('This is required.');
			static::newLine();

			$input = forward_static_call_array([__CLASS__, 'prompt'], $args);
		}

		// If options are provided and the choice is not in the array, tell them to try again
		if ( ! empty($options) && ! in_array($input, $options))
		{
			static::write('This is not a valid option. Please try again.');
			static::newLine();

			$input = forward_static_call_array([__CLASS__, 'prompt'], $args);
		}

		return empty($input) ? '' : $input;
	}

	//--------------------------------------------------------------------

	/**
	 * Outputs a string to the cli. 
	 *
	 * @param string $text          the text to output
	 * @param string $foreground
	 * @param string $background
	 */
	public static function write(string $text, string $foreground = null, string $background = null)
	{
		if ($foreground || $background)
		{
			$text = static::color($text, $foreground, $background);
		}

		fwrite(STDOUT, $text.PHP_EOL);
	}

	//--------------------------------------------------------------------

	/**
	 * Outputs an error to the CLI using STDERR instead of STDOUT
	 *
	 * @param    string|array $text the text to output, or array of errors
	 * @param string $foreground
	 * @param string $background
	 */
	public static function error(string $text, string $foreground = 'light_red', string $background = null)
	{
		if ($foreground || $background)
		{
			$text = static::color($text, $foreground, $background);
		}

		fwrite(STDERR, $text.PHP_EOL);
	}

	//--------------------------------------------------------------------

	/**
	 * Beeps a certain number of times.
	 *
	 * @param    int $num the number of times to beep
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
	 * @param    int  $seconds   number of seconds
	 * @param    bool $countdown show a countdown or not
	 */
	public static function wait(int $seconds, bool $countdown = false)
	{
		if ($countdown === true)
		{
			$time = $seconds;

			while ($time > 0)
			{
				fwrite(STDOUT, $time.'... ');
				sleep(1);
				$time--;
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
				static::write(static::$wait_msg);
				static::input();
			}
		}
	}


	//--------------------------------------------------------------------

	/**
	 * if operating system === windows
	 */
	public static function isWindows()
	{
		return 'win' === strtolower(substr(php_uname("s"), 0, 3));
	}

	//--------------------------------------------------------------------

	/**
	 * Enter a number of empty lines
	 *
	 * @param    int $num   Number of lines to output
	 *
	 * @return    void
	 */
	public static function newLine(int $num = 1)
	{
		// Do it once or more, write with empty string gives us a new line
		for ($i = 0; $i < $num; $i++)
		{
			static::write();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Clears the screen of output
	 *
	 * @return    void
	 */
	public static function clearScreen()
	{
		static::isWindows()

			// Windows is a bit crap at this, but their terminal is tiny so shove this in
			? static::newLine(40)

			// Anything with a flair of Unix will handle these magic characters
			: fwrite(STDOUT, chr(27)."[H".chr(27)."[2J");
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the given text with the correct color codes for a foreground and
	 * optionally a background color.
	 *
	 * @param    string $text       the text to color
	 * @param    string $foreground the foreground color
	 * @param    string $background the background color
	 * @param    string $format     other formatting to apply. Currently only 'underline' is understood
	 *
	 * @return    string    the color coded string
	 */
	public static function color(string $text, string $foreground, string $background = null, string $format = null)
	{
		if (static::isWindows() && ! isset($_SERVER['ANSICON']))
		{
			return $text;
		}

		if ( ! array_key_exists($foreground, static::$foreground_colors))
		{
			throw new \RuntimeException('Invalid CLI foreground color: '.$foreground);
		}

		if ($background !== null && ! array_key_exists($background, static::$background_colors))
		{
			throw new \RuntimeException('Invalid CLI background color: '.$background);
		}

		$string = "\033[".static::$foreground_colors[$foreground]."m";

		if ($background !== null)
		{
			$string .= "\033[".static::$background_colors[$background]."m";
		}

		if ($format === 'underline')
		{
			$string .= "\033[4m";
		}

		$string .= $text."\033[0m";

		return $string;
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to determine the width of the viewable CLI window.
	 * This only works on *nix-based systems, so return a sane default
	 * for Windows environments.
	 *
	 * @param int $default
	 *
	 * @return int
	 */
	public static function getWidth(int $default = 80): int
	{
		if (static::isWindows())
		{
			return $default;
		}

		return (int)shell_exec('tput cols');
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to determine the height of the viewable CLI window.
	 * This only works on *nix-based systems, so return a sane default
	 * for Windows environments.
	 *
	 * @param int $default
	 *
	 * @return int
	 */
	public static function getHeight(int $default = 32): int
	{
		if (static::isWindows())
		{
			return $default;
		}

		return (int)shell_exec('tput lines');
	}

	//--------------------------------------------------------------------

	/**
	 * Displays a progress bar on the CLI. You must call it repeatedly
	 * to update it. Set $thisStep = false to erase the progress bar.
	 *
	 * @param int $thisStep
	 * @param int $totalSteps
	 */
	public static function showProgress(int $thisStep = 1, int $totalSteps = 10)
	{
		// The first time through, save
		// our position so the script knows where to go
		// back to when writing the bar, and
		// at the end of the script.
		if ( ! static::$inProgress)
		{
			fwrite(STDOUT, "\0337");
			static::$inProgress = true;
		}

		// Restore position
		fwrite(STDERR, "\0338");

		if ($thisStep !== false)
		{
			// Don't allow div by zero or negative numbers....
			$thisStep   = abs($thisStep);
			$totalSteps = $totalSteps < 1 ? 1 : $totalSteps;

			$percent = intval(($thisStep / $totalSteps) * 100);
			$step    = (int)round($percent / 10);

			// Write the progress bar
			fwrite(STDOUT, "[\033[32m".str_repeat('#', $step).str_repeat('.', 10 - $step)."\033[0m]");
			// Textual representation...
			fwrite(STDOUT, " {$percent}% Complete".PHP_EOL);
			// Move up, undo the PHP_EOL
			fwrite(STDOUT, "\033[1A");
		}
		else
		{
			fwrite(STDERR, "\007");
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
	 * @param string $string
	 * @param int  $max
	 * @param int  $pad_left
	 *
	 * @return string
	 */
	public static function wrap(string $string = null, int $max = 0, int $pad_left = 0): string
	{
		if (empty($string))
		{
			return '';
		}

		if ($max == 0)
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
			$lines = explode("\n", $lines);

			$first = true;

			array_walk($lines, function (&$line, $index) use ($max, $pad_left, &$first)
			{
				if ( ! $first)
				{
					$line = str_repeat(" ", $pad_left).$line;
				}
				else
				{
					$first = false;
				}
			});

			$lines = implode("\n", $lines);
		}

		return $lines;
	}

	//--------------------------------------------------------------------

}

// Ensure the class is initialized.
CLI::init();
