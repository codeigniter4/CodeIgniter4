<?php
namespace CodeIgniter\Log\Handlers;

use CodeIgniter\Test\Mock\MockLogger as LoggerConfig;

class ErrorlogHandlerTest extends \CodeIgniter\Test\CIUnitTestCase
{

	protected function setUp(): void
	{
		parent::setUp();
	}

	public function testHandle()
	{
		$config = new LoggerConfig();

		$config->handlers['CodeIgniter\Log\Handlers\TestHandler']['handles'] = ['critical'];

		$logger = new ErrorlogHandler($config->handlers['CodeIgniter\Log\Handlers\TestHandler']);
		$this->assertTrue($logger->handle('warning', 'This is a test log'));
	}

}
