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

namespace CodeIgniter\Test;

use CodeIgniter\Log\Logger;

class TestLogger extends Logger
{
	protected static $op_logs = [];

	/**
	 * The log method is overridden so that we can store log history during
	 * the tests to allow us to check ->assertLogged() methods.
	 *
	 * @param string $level
	 * @param string $message
	 * @param array  $context
	 *
	 * @return bool
	 */
	public function log($level, $message, array $context = []): bool
	{
		// While this requires duplicate work, we want to ensure
		// we have the final message to test against.
		$logMessage = $this->interpolate($message, $context);

		// Determine the file and line by finding the first
		// backtrace that is not part of our logging system.
		$trace = debug_backtrace();
		$file  = null;

		foreach ($trace as $row)
		{
			if (! in_array($row['function'], ['log', 'log_message'], true))
			{
				$file = basename($row['file'] ?? '');

				break;
			}
		}

		self::$op_logs[] = [
			'level'   => $level,
			'message' => $logMessage,
			'file'    => $file,
		];

		// Let the parent do it's thing.
		return parent::log($level, $message, $context);
	}

	/**
	 * Used by CIUnitTestCase class to provide ->assertLogged() methods.
	 *
	 * @param string $level
	 * @param string $message
	 *
	 * @return bool
	 */
	public static function didLog(string $level, $message)
	{
		foreach (self::$op_logs as $log)
		{
			if (strtolower($log['level']) === strtolower($level) && $message === $log['message'])
			{
				return true;
			}
		}

		return false;
	}

	// Expose cleanFileNames()
	public function cleanup($file)
	{
		return $this->cleanFileNames($file);
	}
}
