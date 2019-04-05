<?php namespace Test;

use App\Controllers\Home;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTester;

class ControllerTest extends CIUnitTestCase
{
	use ControllerTester;

	public function testHomePage()
	{
		$result = $this->withUri('http://localhost:8080')
			->controller(Home::class)
			->execute('index');

		// Success Status
		$this->assertTrue($result->isOK());

		// Not a Redirect
		$this->assertFalse($result->isRedirect());

		$request = $result->request();
		$this->assertInstanceOf(IncomingRequest::class, $request);

		$response = $result->response();
		$this->assertInstanceOf(Response::class, $response);

		$body = $result->getBody();
		$this->assertNotEmpty($body);

		// Check the content of the page
		$this->assertTrue($result->see('CodeIgniter'));

		$this->assertTrue($result->dontSee('Laravel'));

		$this->assertTrue($result->seeElement('.logo'));

		$this->assertTrue($result->seeLink('User Guide'));
	}
}
