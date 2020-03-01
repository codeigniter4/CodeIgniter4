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

use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

/**
 * Class ChromeLoggerHandler
 *
 * Allows for logging items to the Chrome console for debugging.
 * Requires the ChromeLogger extension installed in your browser.
 *
 * @see https://chrome.google.com/webstore/detail/chrome-logger/noaneddfkdjfnfdakjjmocngnfkfehhd
 *
 * This handler is ONLY LOADED when
 *         ENVIRONMENT === 'development'
 * AND with the following setting in \Config\Logger
 *         public $enableChromeLogger = true;
 *
 * @package CodeIgniter\Log\Handlers
 */
class ChromeLoggerHandler extends BaseHandler implements HandlerInterface
{
	/**
	 * Version of this library - for ChromeLogger use.
	 *
	 * @var float
	 */
	const VERSION = 2.0;

	/**
	 * The final data that is sent to the browser.
	 *
	 * @var array
	 */
	protected $json = [
		'version' => self::VERSION,
		'columns' => [
			'log',
			'backtrace',
			'type',
		],
		'rows'    => [],
	];

	/**
	 * Track processed objects prevents objects from referring to each other
	 * due to recursion
	 *
	 * @var array
	 */
	protected $processedObjs = [];

	/**
	 * Constructor
	 *
	 * @param array $config
	 */
	public function __construct($config = [])
	{
		$config->levelsHandled = $config->chromeLoggerLevelsHandled ?? null;

		parent::__construct($config);

		$request = Services::request(null, true);

		$this->json['request_uri'] = (string) $request->uri;
	}

	/**
	 * Handles logging a message to Chrome Logger.
	 *
	 * @param $level
	 * @param $message
	 *
	 * @return boolean
	 */
	public function handle($level, $message, array $context = []): bool
	{
		$type = $this->mapToChromeLevels($level);

		// Format the message
		$logs[] = $this->format($message, $context);

		// Generate Backtrace info
		// The call to $logger-> should be the fourth stackframe
		$backtraceMessage = implode(':', $this->backTrace());

		$this->json['rows'][] = [
			$logs,
			$backtraceMessage,
			$type,
		];

		$this->sendLogs();

		return true;
	}

	/**
	 * Maps from CI (PSR-3) levels to Chrome Console levels
	 *
	 * @param  string $level
	 * @return string
	 */
	protected function mapToChromeLevels($level): string
	{
		$levels = [
			'emergency' => 'error',
			'alert'     => 'error',
			'critical'  => 'error',
			'error'     => 'error',
			'warning'   => 'warn',
			'notice'    => 'warn',
			'info'      => 'info',
			'debug'     => 'info',
		];

		return \array_key_exists($level, $levels) ? $levels[$level] : '';
	}

	/**
	 * Converts the object to display nicely in the Chrome Logger UI.
	 *
	 * @param  $object
	 * @return array
	 */
	public function format($object, $context = [])
	{
		if (\is_string($object))
		{
			return $this->interpolate($object, $context);
		}

		if (\is_array($object))
		{
			$items = [];
			foreach ($object as $key => $value)
			{
				if (\is_object($value))
				{
					$items[$key] = $this->format($value);
					continue;
				}

				$type        = gettype($value);
				$items[$key] = "$type => $value";
			}

			return $items;
		}

		if (! \is_object($object))
		{
			return \print_r($object, true);
		}

		// Track objects processed so we don't do it twice
		$this->processedObjs[] = $object;

		$objectAsArray = [];

		// first add the class name
		$objectAsArray['___class_name'] = \get_class($object);

		$reflection = new \ReflectionClass($object);

		// loop through the properties and add those
		foreach ($reflection->getProperties() as $property)
		{
			$type = $this->getPropertyKey($property);

			if (\strpos($type, 'public') === false)
			{
				$property->setAccessible(true);
			}

			$value = $property->getValue($object);

			// same instance as parent object
			if ($value === $object && \in_array($value, $this->processedObjs, true))
			{
				$value = 'recursion - parent object [' . \get_class($value) . ']';
			}

			$objectAsArray[$type] = $this->format($value);
		}
		return $objectAsArray;
	}

	/**
	 * Turn reflection property into a property name
	 *
	 * @param  ReflectionProperty
	 * @return string
	 */
	protected function getPropertyKey($property)
	{
		$static = $property->isStatic() ? ' static' : '';

		if ($property->isPublic())
		{
			return 'public' . $static . ' ' . $property->getName();
		}
		elseif ($property->isProtected())
		{
			return 'protected' . $static . ' ' . $property->getName();
		}

		//$property->isPrivate())
			return 'private' . $static . ' ' . $property->getName();
	}

	/**
	 * Attaches the header and its value to a response object.
	 *
	 * @param ResponseInterface $response
	 */
	protected function sendLogs(ResponseInterface &$response = null)
	{
		if (is_null($response))
		{
			$response = Services::response(null, true);
		}

		$data = \base64_encode(\utf8_encode(\json_encode($this->json)));

		$response->setHeader('X-ChromeLogger-Data', $data);
	}

}
