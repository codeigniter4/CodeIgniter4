<?php

namespace CodeIgniter\Log\Handlers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockLogger as LoggerConfig;

final class ErrorlogHandlerTest extends CIUnitTestCase
{
	public function testHandle()
	{
		$config = new LoggerConfig();

		$config->handlers['CodeIgniter\Log\Handlers\TestHandler']['handles'] = ['critical'];

		$logger = new ErrorlogHandler($config->handlers['CodeIgniter\Log\Handlers\TestHandler']);
		$this->assertTrue($logger->handle('warning', 'This is a test log'));
	}
}
