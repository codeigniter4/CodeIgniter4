<?php namespace CodeIgniter\HTTP;

use Config\App;
use Config\Autoload;
use CodeIgniter\Config\Services;
use CodeIgniter\Validation\Validation;
use CodeIgniter\Router\RouteCollection;
use CodeIgniter\Autoloader\MockFileLocator;

class RedirectResponseTest extends \CIUnitTestCase
{
	protected $routes;

	protected $request;

	protected $config;

	public function setUp()
	{
		parent::setUp();

		$_SERVER['REQUEST_METHOD'] = 'GET';

		$this->config = new App();
		$this->config->baseURL = 'http://example.com';

		$this->routes = new RouteCollection(new MockFileLocator(new Autoload()));
		Services::injectMock('routes', $this->routes);

		$this->request = new MockIncomingRequest($this->config, new URI('http://example.com'));
		Services::injectMock('request', $this->request);
	}

	public function testRedirectToFullURI()
	{
		$response = new RedirectResponse(new App());

		$response = $response->to('http://example.com/foo');

		$this->assertTrue($response->hasHeader('Location'));
		$this->assertEquals('http://example.com/foo', $response->getHeaderLine('Location'));
	}

	public function testRedirectRelativeConvertsToFullURI()
	{
		$response = new RedirectResponse($this->config);

		$response = $response->to('/foo');

		$this->assertTrue($response->hasHeader('Location'));
		$this->assertEquals('http://example.com/foo', $response->getHeaderLine('Location'));
	}

	public function testWithInput()
	{
		$_SESSION = [];
		$_GET = ['foo' => 'bar'];
		$_POST = ['bar' => 'baz'];

		$response = new RedirectResponse(new App());

		$returned = $response->withInput();

		$this->assertSame($response, $returned);
		$this->assertTrue(array_key_exists('_ci_old_input', $_SESSION));
		$this->assertEquals('bar', $_SESSION['_ci_old_input']['get']['foo']);
		$this->assertEquals('baz', $_SESSION['_ci_old_input']['post']['bar']);
	}

	public function testWithValidationErrors()
	{
		$_SESSION = [];

		$response = new RedirectResponse(new App());

		$validation = $this->createMock(Validation::class);
		$validation->method('getErrors')
		           ->willReturn(['foo' =>'bar']);

		Services::injectMock('validation', $validation);

		$response->withInput();

		$this->assertTrue(array_key_exists('_ci_validation_errors', $_SESSION));
	}

	public function testWith()
	{
		$_SESSION = [];

		$response = new RedirectResponse(new App());

		$returned = $response->with('foo', 'bar');

		$this->assertSame($response, $returned);
		$this->assertTrue(array_key_exists('foo', $_SESSION));
	}
}
