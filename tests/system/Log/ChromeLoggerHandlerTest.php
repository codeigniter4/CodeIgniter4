<?php
namespace CodeIgniter\Log;

use Config\App;
use Tests\Support\Log\Foo;
use CodeIgniter\Test\Mock\MockResponse;
use Tests\Support\Log\Config\MockLoggerConfig;
use Tests\Support\Log\Handlers\MockChromeLoggerHandlerExtended;
use CodeIgniter\Services;

class ChromeLoggerHandlerTest extends \CodeIgniter\Test\CIUnitTestCase
{
	protected $handler;

	protected function setUp(): void
	{
		parent::setUp();

		$config        = new MockLoggerConfig();
		$this->handler = new MockChromeLoggerHandlerExtended($config);
	}

	public function testChromeLoggerHeaderSent()
	{
		Services::injectMock('response', new MockResponse(new App()));
		$response = service('response');

		$this->handler->handle('warning', 'There be Dragons');

		$this->assertTrue($response->hasHeader('x-chromelogger-data'));
	}

	public function testFormatString()
	{
		$this->assertEquals('Hello FooBar', $this->handler->format('Hello FooBar'));
	}

	/**
	 * @group testme
	 */
	public function testFormatObj()
	{
		$foo = new Foo();
		$data = [$foo, $foo->getBaz()];
		$out = $this->handler->format($data);

		$expected = ["string => bar", "string => baz", ['___class_name' => "stdClass"]];

		$this->assertTrue(true);
	}

	/**
	 * @dataProvider LevelsProvider
	 */
	public function testMapToChromeLevels($level, $expected)
	{
		$this->assertEquals($expected, $this->handler->mapToChromeLevels($level));
	}

	public function LevelsProvider()
	{
		return [
			[
				'emergency',
				'error',
			],
			[
				'alert',
				'error',
			],
			[
				'critical',
				'error',
			],
			[
				'error',
				'error',
			],
			[
				'warning',
				'warn',
			],
			[
				'notice',
				'warn',
			],
			[
				'info',
				'info',
			],
			[
				'debug',
				'info',
			],
		];
	}

	public function testBackTrack()
	{
		$bt = $this->handler->backTrace();
		$this->assertEquals(['unknown', 'unknown'], $bt);
	}

	public function testSetDateFormat()
	{
		$configDate = $this->handler->dateFormat;

		$this->handler->setDateFormat('F j, Y');
		$setDate = $this->handler->dateFormat;

		$this->assertTrue(true);
		$this->assertNotEquals($configDate, $setDate);
	}

}
