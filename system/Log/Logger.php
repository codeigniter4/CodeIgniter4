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
 * @copyright  2019 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Log;

use Psr\Log\LoggerInterface;
use CodeIgniter\Log\Exceptions\LogException;

/**
 * The CodeIgniter Logger
 *
 * The message MAY contain placeholders in the form: {foo} where foo
 * will be replaced by the context data in key "foo".
 *
 * The context array can contain arbitrary data, the only assumption that
 * can be made by implementors is that if an Exception instance is given
 * to produce a stack trace, it MUST be in a key named "exception".
 *
 * @package CodeIgniter\Log
 */
class Logger implements LoggerInterface
{

	/**
	 * Allows (true) or blocks (false) the creation of a ChromeLoggerHandler instance
	 *
	 * @var boolean
	 */
	protected $enableChromeLogger;

	/**
	 * Format of the timestamp for log files.
	 *
	 * @var string
	 */
	protected $dateFormat;

	/**
	 * Caches logging calls for debug toolbar.
	 *
	 * @var array
	 */
	public $logCache;

	/**
	 * Should we cache our logged items?
	 *
	 * @var boolean
	 */
	protected $cacheLogs = false;

	/**
	 * Cached instances of the handlers.
	 *
	 * @var array
	 */
	protected $handlerObjs = [];

	/**
	 * Constructor.
	 *
	 * @param  \Config\Logger $config
	 * @param  boolean        $debug
	 * @throws \RuntimeException
	 */
	public function __construct($config, bool $debug = CI_DEBUG)
	{
		if (empty($config->handlers))
		{
			throw LogException::forNoHandlers('LoggerConfig');
		}

		$this->enableChromeLogger = $config->enableChromeLogger ?? false;
		$this->dateFormat         = $config->dateFormat ?? 'Y-m-d H:i:s';

		$this->setHandlers($config->handlers);

		$this->cacheLogs = $debug;
		if ($this->cacheLogs)
		{
			$this->logCache = [];
		}
	}

	/**
	 * Takes an array of one or more handlers and add an instance of each to
	 * the $handlerObjs property
	 *
	 * @param array $handlers
	 */
	public function setHandlers(array $handlers)
	{
		//clear the $handlerObjs property
		$this->handlerObjs = [];

		// instantiate each handler
		foreach ($handlers as $name => $handler)
		{
			// Chrome Logger only allowed when the planets align
			// Don't create it if conditions are not right
			if ($name === 'ChromeLoggerHandler' &&
				($this->enableChromeLogger !== true || ENVIRONMENT !== 'development'))
			{
				continue;
			}

			$config                   = new \Config\Logger();
			$this->handlerObjs[$name] = new $handler($config);
		}
	}

	/**
	 * If $key is not provided returns the array of handler instances created for the logger.
	 * In a returned array the key is the class name and the value is the class instance.
	 * If $key is provided and exists in $handlerObjs the handler instance
	 * is returned. If the key is not found, null is returned
	 *
	 * @return array| CodeIgniter\Log\HandlerInterface | null
	 */
	public function getHandlers($key = null)
	{
		if (empty($key))
		{
			return $this->handlerObjs;
		}

		return $this->handlerObjs[$key] ?? null;
	}

	/**
	 * System is unusable.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return boolean
	 */
	public function emergency($message, array $context = [])
	{
		$this->log('emergency', $message, $context);
	}

	/**
	 * Action must be taken immediately.
	 *
	 * Example: Entire website down, database unavailable, etc. This should
	 * trigger the SMS alerts and wake you up.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return boolean
	 */
	public function alert($message, array $context = [])
	{
		$this->log('alert', $message, $context);
	}

	/**
	 * Critical conditions.
	 *
	 * Example: Application component unavailable, unexpected exception.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return boolean
	 */
	public function critical($message, array $context = [])
	{
		$this->log('critical', $message, $context);
	}

	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return boolean
	 */
	public function error($message, array $context = [])
	{
		$this->log('error', $message, $context);
	}

	/**
	 * Exceptional occurrences that are not errors.
	 *
	 * Example: Use of deprecated APIs, poor use of an API, undesirable things
	 * that are not necessarily wrong.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return boolean
	 */
	public function warning($message, array $context = [])
	{
		$this->log('warning', $message, $context);
	}

	/**
	 * Normal but significant events.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return boolean
	 */
	public function notice($message, array $context = [])
	{
		$this->log('notice', $message, $context);
	}

	/**
	 * Interesting events.
	 *
	 * Example: User logs in, SQL logs.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return boolean
	 */
	public function info($message, array $context = [])
	{
		$this->log('info', $message, $context);
	}

	/**
	 * Detailed debug information.
	 *
	 * @param string $message
	 * @param array  $context
	 *
	 * @return boolean
	 */
	public function debug($message, array $context = [])
	{
		$this->log('debug', $message, $context);
	}

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param string $level
	 * @param string $message
	 * @param array  $context
	 *
	 * @return boolean
	 */
	public function log($level, $message, array $context = [])
	{
		foreach ($this->handlerObjs as $handler)
		{
			if (! $handler->canHandle($level))
			{
				continue;
			}

			$handler->handle($level, $message, $context);

			if ($this->cacheLogs)
			{
				$this->logCache[] = [
					'level' => $level,
					'msg'   => $message,
				];
			}
		}
	}

}
