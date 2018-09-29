<?php namespace CodeIgniter\Log\Handlers;

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2018 British Columbia Institute of Technology
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
 * @copyright	2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license	https://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */

/**
 * Log error messages to file system
 */
class FileHandler extends BaseHandler implements HandlerInterface
{

	/**
	 * Folder to hold logs
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Extension to use for log files
	 * @var string
	 */
	protected $fileExtension;

	/**
	 * Permissions for new log files
	 * @var int
	 */
	protected $filePermissions;

	//--------------------------------------------------------------------

	/**
	 * Constructor
	 * @param array $config
	 */
	public function __construct(array $config = [])
	{
		parent::__construct($config);

		$this->path = $config['path'] ?? WRITEPATH . 'logs/';

		$this->fileExtension = $config['fileExtension'] ?? 'php';
		$this->fileExtension = ltrim($this->fileExtension, '.');

		$this->filePermissions = $config['filePermissions'] ?? 0644;
	}

	//--------------------------------------------------------------------

	/**
	 * Handles logging the message.
	 * If the handler returns false, then execution of handlers
	 * will stop. Any handlers that have not run, yet, will not
	 * be run.
	 *
	 * @param $level
	 * @param $message
	 *
	 * @return bool
	 */
	public function handle($level, $message): bool
	{
		$filepath = $this->path . 'log-' . date('Y-m-d') . '.' . $this->fileExtension;

		$msg = '';

		if ( ! file_exists($filepath))
		{
			$newfile = true;

			// Only add protection to php files
			if ($this->fileExtension === 'php')
			{
				$msg .= "<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>\n\n";
			}
		}

		if ( ! $fp = @fopen($filepath, 'ab'))
		{
			return false;
		}

		// Instantiating DateTime with microseconds appended to initial date is needed for proper support of this format
		if (strpos($this->dateFormat, 'u') !== false)
		{
			$microtime_full = microtime(true);
			$microtime_short = sprintf("%06d", ($microtime_full - floor($microtime_full)) * 1000000);
			$date = new \DateTime(date('Y-m-d H:i:s.' . $microtime_short, $microtime_full));
			$date = $date->format($this->dateFormat);
		}
		else
		{
			$date = date($this->dateFormat);
		}

		$msg .= strtoupper($level) . ' - ' . $date . ' --> ' . $message . "\n";

		flock($fp, LOCK_EX);

		for ($written = 0, $length = strlen($msg); $written < $length; $written += $result)
		{
			if (($result = fwrite($fp, substr($msg, $written))) === false)
			{
				break;
			}
		}

		flock($fp, LOCK_UN);
		fclose($fp);

		if (isset($newfile) && $newfile === true)
		{
			chmod($filepath, $this->filePermissions);
		}

		return is_int($result);
	}

	//--------------------------------------------------------------------
}
