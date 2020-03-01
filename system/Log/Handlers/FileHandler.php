<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019 CodeIgniter Foundation
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

use CodeIgniter\Log\Exceptions\LogException;

/**
 * Writes messages to log file on system
 */
class FileHandler extends BaseHandler implements HandlerInterface
{

	/**
	 * Directory to hold logs
	 *
	 * @var string
	 */
	protected $logsDir;

	/**
	 * file name (a prefix to the date)
	 *
	 * @var string
	 */
	protected $fileName;

	/**
	 * Extension to use for log files
	 *
	 * @var string
	 */
	protected $fileExtension;

	/**
	 * Permissions for new log files
	 *
	 * @var integer
	 */
	protected $filePermissions;

	/**
	 * Constructor
	 *
	 * @param array $config
	 */
	public function __construct($config = null)
	{
		$config->levelsHandled = $config->fileLevelsHandled ?? null;
		parent::__construct($config);

		$this->logsDir = $config->logsDir ?? WRITEPATH . 'logs/';

		$this->fileName = $config->fileName ?? 'CI_';

		$this->fileExtension = $config->fileExtension ?? 'log';
		$this->fileExtension = ltrim($this->fileExtension, '.');

		$this->filePermissions = $config->filePermissions ?? 0664;
	}

	/**
	 * Handles writing the message.
	 *
	 * @param  string
	 * @param  string
	 * @return boolean
	 */
	public function handle($level, $message, array $context = []): bool
	{
		if (\is_string($message))
		{
			// Parse our placeholders
			$message = $this->interpolate($message, $context);
		}

		$file = $this->logsDir . $this->fileName . date('Y-m-d') . '.' . $this->fileExtension;

		$msg = '';

		if (! is_file($file))
		{
			$newfile = true;
			$msg    .= "******** CodeIgniter Application Log ********\n\n";
		}

		if (! $fp = @fopen($file, 'ab'))
		{
			return false;
		}

		// Instantiating DateTime with microseconds appended to initial date is needed for proper support of this format
		if (strpos($this->dateFormat, 'u') !== false)
		{
			$microtime_full  = microtime(true);
			$microtime_short = sprintf('%06d', ($microtime_full - floor($microtime_full)) * 1000000);

			$date = new \DateTime(date('Y-m-d H:i:s.' . $microtime_short, $microtime_full));
			$date = $date->format($this->dateFormat);
		}
		else
		{
			$date = date($this->dateFormat);
		}

		if (is_numeric($level))
		{
			$level = $this->getLevelName($level);
		}

		if (\is_object($message) || \is_array($message))
		{
			$message = \print_r($message, true);
		}

		$msg .= strtoupper($level) . ' - ' . $date . ' --> ' . $message . "\n";

		flock($fp, LOCK_EX);

		for ($written = 0, $length = strlen($msg); $written < $length; $written += $result)
		{
			if (($result = fwrite($fp, substr($msg, $written))) === false)
			{
				// if we get this far, we'll never see this during travis-ci
				// @codeCoverageIgnoreStart
				break;
				// @codeCoverageIgnoreEnd
			}
		}

		flock($fp, LOCK_UN);
		fclose($fp);

		if (isset($newfile) && $newfile === true)
		{
			chmod($file, $this->filePermissions);
		}

		return is_int($result);
	}

	/**
	 * $path setter
	 *
	 * @param  string $path
	 * @return CodeIgniter\Log\Handlers\HandlerInterface For method chaining
	 */
	public function setPath(string $path): HandlerInterface
	{
		$this->logsDir = $path;
		return $this;
	}

	/**
	 * $fileName setter
	 *
	 * @param  string $name
	 * @return CodeIgniter\Log\Handlers\HandlerInterface For method chaining
	 * @throws \LogException
	 */
	public function setFileName(string $name): HandlerInterface
	{
		$blackList = [
			'/',
			'\\',
			'?',
			'%',
			'*',
			':',
			'|',
			'"',
			'<',
			'>',
			'.',
			',',
			' ',
		];

		//remove leading/trailing whitespace
		$cleaned = trim($name);

		// replace internal spaces with underscores
		$cleaned = str_replace(' ', '_', $cleaned);

		//strip tags, low, and high chars
		$cleaned = filter_var($cleaned, FILTER_SANITIZE_STRING,
		FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK);

		// remove blacklist chars
		$cleaned = str_replace($blackList, '', $cleaned);

		if (empty($cleaned) || $cleaned === false)
		{
			throw LogException::forInvalidFileName($name);
		}

		// accept only alphanumeric characters
		$this->fileName = $cleaned;

		return $this;
	}

	/**
	 * $fileExtension setter
	 *
	 * @param  string $ext
	 * @return CodeIgniter\Log\Handlers\HandlerInterface For method chaining
	 */
	public function setFileExtension(string $ext = 'log'): HandlerInterface
	{
		//remove whitespace, force lowercase
		$ext = strtolower(trim($ext));

		// accept only alphanumeric characters
		$this->fileExtension = preg_replace('/[^\da-z]/i', '', $ext);

		return $this;
	}

}
