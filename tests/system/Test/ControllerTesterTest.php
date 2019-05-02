<?php
namespace CodeIgniter\Test;

use CodeIgniter\Log\Logger;
use Config\App;
use Config\Services;
use Tests\Support\Config\MockLogger as LoggerConfig;

class ControllerTesterTest extends \CIUnitTestCase
{

	use ControllerTester;

	public function setUp()
	{
		parent::setUp();
	}

	public function tearDown()
	{
		parent::tearDown();
	}

	public function testBadController()
	{
		$this->expectException(\InvalidArgumentException::class);
		$logger = new Logger(new LoggerConfig());
		$result = $this->withURI('http://example.com')
				->withLogger($logger)
				->controller(\App\Controllers\NeverHeardOfIt::class)
				->execute('index');
	}

	public function testBadControllerMethod()
	{
		$this->expectException(\InvalidArgumentException::class);
		$logger = new Logger(new LoggerConfig());
		$result = $this->withURI('http://example.com')
				->withLogger($logger)
				->controller(\App\Controllers\Home::class)
				->execute('nothere');
	}

	public function testController()
	{
		$logger = new Logger(new LoggerConfig());
		$result = $this->withURI('http://example.com')
				->withLogger($logger)
				->controller(\App\Controllers\Home::class)
				->execute('index');

		$body = $result->getBody();
		$this->assertTrue($result->isOK());
	}

	public function testControllerWithoutLogger()
	{
		$result = $this->withURI('http://example.com')
				->controller(\App\Controllers\Home::class)
				->execute('index');

		$body = $result->getBody();
		$this->assertTrue($result->isOK());
	}

	public function testPopcornIndex()
	{
		$logger = new Logger(new LoggerConfig());
		$result = $this->withURI('http://example.com')
				->withLogger($logger)
				->controller(\Tests\Support\Controllers\Popcorn::class)
				->execute('index');

		$body = $result->getBody();
		$this->assertTrue($result->isOK());
	}

	public function testPopcornIndex2()
	{
		$logger = new Logger(new LoggerConfig());
		$result = $this->withURI('http://example.com')
				->withLogger($logger)
				->controller(\Tests\Support\Controllers\Popcorn::class)
				->execute('index');

		$body = $result->getBody();
		$this->assertEquals('Hi there', $body);
	}

	public function testPopcornFailure()
	{
		$logger = new Logger(new LoggerConfig());
		$result = $this->withURI('http://example.com')
				->withLogger($logger)
				->controller(\Tests\Support\Controllers\Popcorn::class)
				->execute('pop');

		$this->assertEquals(567, $result->response()->getStatusCode());
	}

	public function testPopcornException()
	{
		$logger = new Logger(new LoggerConfig());
		$result = $this->withURI('http://example.com')
				->withLogger($logger)
				->controller(\Tests\Support\Controllers\Popcorn::class)
				->execute('popper');

		$this->assertEquals(500, $result->response()->getStatusCode());
	}

	public function testPopcornIndexWithSupport()
	{
		$logger = new Logger(new LoggerConfig());
		$config = new App();
		$body   = '';

		$result = $this->withURI('http://example.com')
				->withConfig($config)
				->withRequest(Services::request($config))
				->withResponse(Services::response($config))
				->withLogger($logger)
				->withBody($body)
				->controller(\Tests\Support\Controllers\Popcorn::class)
				->execute('index');

		$body = $result->getBody();
		$this->assertEquals('Hi there', $body);
	}

	public function testRequestPassthrough()
	{
		$logger = new Logger(new LoggerConfig());
		$result = $this->withURI('http://example.com')
				->withLogger($logger)
				->controller(\Tests\Support\Controllers\Popcorn::class)
				->execute('popper');

		$req = $result->request();
		$this->assertEquals('get', $req->getMethod());
	}

	public function testFailureResponse()
	{
		$logger = new Logger(new LoggerConfig());
		$result = $this->withURI('http://example.com')
				->withLogger($logger)
				->controller(\Tests\Support\Controllers\Popcorn::class)
				->execute('oops');

		$this->assertFalse($result->isOK());
		$this->assertEquals(401, $result->response()->getStatusCode());
	}

	public function testEmptyResponse()
	{
		$logger = new Logger(new LoggerConfig());
		$result = $this->withURI('http://example.com')
				->withLogger($logger)
				->controller(\Tests\Support\Controllers\Popcorn::class)
				->execute('weasel');

		$body = $result->getBody(); // empty
		$this->assertEmpty($body);
		$this->assertFalse($result->isOK());
	}

	public function testRedirect()
	{
		$logger = new Logger(new LoggerConfig());
		$result = $this->withURI('http://example.com')
				->withLogger($logger)
				->controller(\Tests\Support\Controllers\Popcorn::class)
				->execute('goaway');

		$this->assertTrue($result->isRedirect());
	}

	public function testDOMParserForward()
	{
		$logger = new Logger(new LoggerConfig());
		$result = $this->withURI('http://example.com')
				->withLogger($logger)
				->controller(\Tests\Support\Controllers\Popcorn::class)
				->execute('index');

		$this->assertTrue($result->see('Hi'));
	}

	public function testFailsForward()
	{
		$logger = new Logger(new LoggerConfig());
		$result = $this->withURI('http://example.com')
				->withLogger($logger)
				->controller(\Tests\Support\Controllers\Popcorn::class)
				->execute('index');

		// won't fail, but doesn't do anything
		$this->assertNull($result->ohno('Hi'));
	}

	// @see https://github.com/codeigniter4/CodeIgniter4/issues/1834
	public function testResponseOverriding()
	{
		$result = $this->withURI('http://example.com/rest/')
				->controller(\Tests\Support\Controllers\Popcorn::class)
				->execute('index3');

		$response = json_decode($result->getBody());
		$this->assertEquals('en', $response->lang);
	}

}
