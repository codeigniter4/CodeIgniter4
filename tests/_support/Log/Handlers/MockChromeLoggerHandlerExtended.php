<?php

namespace Tests\Support\Log\Handlers;

use CodeIgniter\Log\Handlers\ChromeLoggerHandler;
use CodeIgniter\Log\Handlers\HandlerInterface;

/**
 * Extends ChromeLoggerHandler to expose protected methods for testing
 */
class MockChromeLoggerHandlerExtended extends ChromeLoggerHandler implements HandlerInterface
{
	public function __call(string $name, array $params)
	{
		if (method_exists($this, $name))
		{
			return $this->$name(...$params);
		}
	}

	public function __get(string $key)
	{
		if (isset($this->$key))
		{
			return $this->$key;
		}

		return null;
	}

}
