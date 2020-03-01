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

namespace CodeIgniter\Log\Handlers;

use Psr\Log\InvalidArgumentException;
use CodeIgniter\Log\Exceptions\LogException;

/**
 * Base "Handler" class for logging
 */
abstract class BaseHandler implements HandlerInterface
{
	/**
	 * The $levelsHandled property is an array of strings that define the
	 * severity levels that a handler "canHandle".
	 *
	 * Here are the values and the message type
	 *     0 - off
	 *     1 - 'emergency'
	 *     2 - 'alert'
	 *     3 - 'critical'
	 *     4 - 'error'
	 *     5 - 'warning'
	 *     6 - 'notice'
	 *     7 - 'info'
	 *     8 - 'debug'
	 *
	 * Setting a value of 0 (zero) turns the handler off.
	 * You can enable logging by setting a value in the range 1 to 8.
	 * Not setting a value (leaving it blank) will cause a LogException with
	 * the message - 'null' is an invalid log level.
	 *
	 * If a single value is supplied, all log levels less than or equal to the value
	 * will be handled. In other words, setting
	 *     $levelsHandled = 3;
	 * would mean that 'critical', 'alert', and 'emergency' severity levels are handled.
	 *
	 * You can also pass an array to create a mix of severity levels to be handled.
	 * For example,
	 *     $levelsHandled = [1, 3, 8];
	 * would result in 'emergency', 'alert', and 'debug' messages being handled.
	 *
	 * There is no meaning to the order of the values in the array.
	 *
	 * If you put a zero in an array all other levels in the array will ignored.
	 * In other words, it is same as if a single value of 0 (zero) is passed.
	 *
	 * If you put a single value in an array then only that level will be handled.
	 * For example,
	 *     $levelsHandled = [8];
	 * results in only 'debug' messages being handled.
	 *
	 * @var array
	 */
	protected $levelsHandled = [];

	/**
	 * Array that maps level number to string equivalent.
	 *
	 * @var array
	 */
	protected $logLevels = [
		'emergency' => 1,
		'alert'     => 2,
		'critical'  => 3,
		'error'     => 4,
		'warning'   => 5,
		'notice'    => 6,
		'info'      => 7,
		'debug'     => 8,
	];

	/**
	 * Date format for logging
	 *
	 * @var string
	 */
	protected $dateFormat;

	/**
	 * Constructor
	 *
	 * @param array $config
	 */
	public function __construct($config = null)
	{
		$this->setLevelsHandled($config->levelsHandled);
		$this->dateFormat = $config->dateFormat ?? 'Y-m-d H:i:s';
	}

	/**
	 * Sets the levels to be logged. @see the docblock for $levelsHandled
	 *
	 * @param  integer|array $level
	 * @return HandlerInterface Allows method chaining
	 * @throws InvalidArgumentException
	 * @throws LogException\
	 */
	public function setLevelsHandled($level = 0): HandlerInterface
	{
		// In theory, only happens when config file entry is blank
		if (is_null($level))
		{
			throw LogException::forInvalidLogLevel("'null'");
		}

		$this->levelsHandled = [];  // Turns handler off

		if (empty($level))
		{
			return $this;
		}

		// validate the input
		if (! \is_array($level))
		{
			if (! is_numeric($level) || $level > count($this->logLevels))
			{
				throw new InvalidArgumentException(lang('Log.invalidLogLevel', [$level]));
			}

			$loggableLevels = range(1, (int) $level);
		}
		else
		{
			foreach ($level as $value)
			{
				if (! is_numeric($value))
				{
					throw new InvalidArgumentException(lang('Log.invalidLogLevel', [$value]));
				}
				if (empty($value)) //probably a zero in the array - we'll honor that
				{
					return $this; //nothing is going to be logged
				}
			}

			$loggableLevels = $level;
		}

		// Now convert loggable level number(s) to string(s).
		// We only use numbers to make the levelsHandled setting convenient for users.
		foreach ($loggableLevels as $level)
		{
			$this->levelsHandled[] = $this->getLevelName($level);
		}

		return $this;
	}

	/**
	 * Names of the levels handled. An empty array indicates handler is "off".
	 *
	 * @return array
	 */
	public function getLevelsHandled()
	{
		return $this->levelsHandled;
	}

	/**
	 * Returns a level's string value for the supplied level number
	 *
	 * @param  integer $level
	 * @return string
	 * @throws InvalidArgumentException
	 */
	protected function getLevelName(int $level)
	{
		// Is the level a valid level?
		if (false === ($name = array_search($level, $this->logLevels)))
		{
			throw new InvalidArgumentException(lang('Log.invalidLogLevel', [$level]));
		}
		return $name;
	}

	/**
	 * Checks whether the Handler will log items at the given $level.
	 *
	 * @param  string|integer
	 * @return boolean
	 * @throws Psr\Log\InvalidArgumentException
	 */
	public function canHandle($level): bool
	{
		if (empty($this->levelsHandled) || empty($level))
		{
			return false;
		}

		if (is_numeric($level))
		{
			$level = $this->getLevelName($level);
		}
		elseif (! array_key_exists($level, $this->logLevels))
		{
			throw new InvalidArgumentException(lang('Log.invalidLogLevel', [$level]));
		}

		// Does the handler want to log this level?
		return in_array($level, $this->levelsHandled);
	}

	/**
	 * Replaces any placeholders in the message with variables
	 * from the context, as well as a few special items like:
	 *
	 * {session_vars}
	 * {post_vars}
	 * {get_vars}
	 * {env}
	 * {env:foo}
	 * {file}
	 * {line}
	 *
	 * @param mixed $message
	 * @param array $context
	 *
	 * @return mixed
	 */
	protected function interpolate($message, array $context = [])
	{
		// build a replacement array with braces around the context keys
		$replace = [];

		foreach ($context as $key => $val)
		{
			// Verify that the 'exception' key is actually an exception
			// or error, both of which implement the 'Throwable' interface.
			if ($key === 'exception' && $val instanceof \Throwable)
			{
				$val = $val->getMessage() . ' ' . $this->cleanFileNames($val->getFile()) . ':' . $val->getLine();
			}
			elseif (\is_object($val) || \is_array($val))
			{
				$val = \print_r($val, true);
			}

			// todo - sanitize input before writing to file?
			$replace['{' . $key . '}'] = $val;
		}

		// Add special placeholders
		$replace['{post_vars}'] = '$_POST: ' . print_r($_POST, true);
		$replace['{get_vars}']  = '$_GET: ' . print_r($_GET, true);
		$replace['{env}']       = ENVIRONMENT;

		// Allow us to log the file/line that we are logging from
		if (strpos($message, '{file}') !== false)
		{
			[
				$file,
				$line,
			] = $this->backTrace();

			$replace['{file}'] = $file;
			$replace['{line}'] = $line;
		}

		// Match up environment variables in {env:foo} tags.
		if (strpos($message, 'env:') !== false)
		{
			preg_match('/env:[^}]+/', $message, $matches);

			if ($matches)
			{
				foreach ($matches as $str)
				{
					$key                 = str_replace('env:', '', $str);
					$replace["{{$str}}"] = $_ENV[$key] ?? 'n/a';
				}
			}
		}

		if (isset($_SESSION))
		{
			$replace['{session_vars}'] = '$_SESSION: ' . print_r($_SESSION, true);
		}

		// interpolate replacement values into the message and return
		return strtr($message, $replace);
	}

	/**
	 * Determines the file and line that the logging call
	 * was made from by analyzing the backtrace.
	 * Find the earliest backtrace that is part of our logging system.
	 *
	 * @return array
	 */
	protected function backTrace(): array
	{
		$logFunctions = [
			'log_message',
			'log',
			'error',
			'debug',
			'info',
			'warning',
			'critical',
			'emergency',
			'alert',
			'notice',
		];

		// Generate Backtrace info
		$trace = \debug_backtrace(false);

		// So we search from the bottom (earliest) of the stack frames
		$stackFrames = \array_reverse($trace);

		// Find the first reference to a Logger class method
		foreach ($stackFrames as $frame)
		{
			if (\in_array($frame['function'], $logFunctions))
			{
				$file = isset($frame['file']) ? $this->cleanFileNames($frame['file']) : 'unknown';
				$line = $frame['line'] ?? 'unknown';
				return [
					$file,
					$line,
				];
			}
		}

		return [
			'unknown',
			'unknown',
		];
	}

	/**
	 * Cleans the paths of filenames by replacing APPPATH, SYSTEMPATH, FCPATH
	 * with the actual var. i.e.
	 *
	 *  /var/www/site/app/Controllers/Home.php
	 *      becomes:
	 *  APPPATH/Controllers/Home.php
	 *
	 * @param $file
	 *
	 * @return string
	 */
	protected function cleanFileNames(string $file): string
	{
		$file = str_replace(APPPATH, 'APPPATH/', $file);
		$file = str_replace(SYSTEMPATH, 'SYSTEMPATH/', $file);
		$file = str_replace(FCPATH, 'FCPATH/', $file);

		return $file;
	}

	/**
	 * Stores the date format to use while logging messages.
	 *
	 * @param  string $format
	 * @return HandlerInterface
	 */
	public function setDateFormat(string $format): HandlerInterface
	{
		$this->dateFormat = $format;

		return $this;
	}

	/**
	 * Performs the actual logging of the message.
	 *
	 * @param  string
	 * @param  string
	 * @return boolean
	 */
	abstract public function handle($level, $message, array $context = []): bool;
}
